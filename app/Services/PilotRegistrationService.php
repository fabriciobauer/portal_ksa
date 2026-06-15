<?php

namespace App\Services;

use App\Models\Pilot;
use App\Models\PilotRegistration;
use App\Support\Analytics;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PilotRegistrationService
{
    public function __construct(protected AuditLogService $auditLogService)
    {
    }

    public function createFromPublic(array $data): PilotRegistration
    {
        return DB::transaction(function () use ($data): PilotRegistration {
            $registration = PilotRegistration::query()->create([
                ...$data,
                'payment_status' => false,
                'metadata' => $this->mergeRegistrationMetadata($data['metadata'] ?? [], [
                    'source' => Analytics::source(),
                    'submitted_from' => 'public_form',
                ]),
            ]);

            return $registration->load('category');
        });
    }

    public function update(PilotRegistration $registration, array $data, ?Authenticatable $actor = null): PilotRegistration
    {
        return DB::transaction(function () use ($registration, $data, $actor): PilotRegistration {
            $registration->fill([
                ...$data,
                'updated_by' => $actor?->getAuthIdentifier(),
            ]);
            $registration->metadata = $this->mergeRegistrationMetadata($registration->metadata, [
                'last_admin_update_at' => now()->toIso8601String(),
            ]);
            $registration->save();

            return $registration->fresh([
                'category',
                'pilot',
                'createdBy',
                'updatedBy',
                'paymentMarkedBy',
                'paymentUnmarkedBy',
            ]);
        });
    }

    public function archive(PilotRegistration $registration, ?Authenticatable $actor = null): PilotRegistration
    {
        if ($registration->is_archived) {
            return $registration;
        }

        return DB::transaction(function () use ($registration, $actor): PilotRegistration {
            $registration->forceFill([
                'is_archived' => true,
                'archived_at' => now(),
                'archived_by' => $actor?->getAuthIdentifier(),
                'updated_by' => $actor?->getAuthIdentifier(),
            ])->save();

            $this->auditLogService->log(
                action: 'registration_archived',
                auditable: $registration,
                description: 'Inscrição arquivada',
                oldValues: ['is_archived' => false],
                newValues: ['is_archived' => true],
                metadata: ['source' => 'admin'],
            );

            return $registration->fresh([
                'category',
                'pilot',
                'archivedBy',
            ]);
        });
    }

    public function setPaymentStatus(PilotRegistration $registration, bool $shouldBePaid, ?Authenticatable $actor = null): array
    {
        return DB::transaction(function () use ($registration, $shouldBePaid, $actor): array {
            $paymentChanged = false;
            $convertedToPilot = false;
            $pilot = $registration->pilot;

            if ($shouldBePaid) {
                if (! $registration->payment_status) {
                    $registration->forceFill([
                        'payment_status' => true,
                        'paid_at' => now(),
                        'payment_marked_by' => $actor?->getAuthIdentifier(),
                        'updated_by' => $actor?->getAuthIdentifier(),
                    ])->save();

                    $paymentChanged = true;

                    $this->auditLogService->log(
                        action: 'registration_payment_marked',
                        auditable: $registration,
                        description: 'Pagamento da inscrição marcado como pago',
                        oldValues: ['payment_status' => false],
                        newValues: [
                            'payment_status' => true,
                            'paid_at' => optional($registration->paid_at)->toIso8601String(),
                        ],
                        metadata: [
                            'registration_category' => $registration->category?->slug,
                            'registration_status' => 'paid',
                        ],
                    );
                }

                $syncResult = $this->syncPilot($registration, $actor);
                $registration = $syncResult['registration'];
                $pilot = $syncResult['pilot'];
                $convertedToPilot = $syncResult['converted_to_pilot'];
            } else {
                if ($registration->payment_status) {
                    $oldPaidAt = $registration->paid_at;

                    $registration->forceFill([
                        'payment_status' => false,
                        'paid_at' => null,
                        'payment_unmarked_by' => $actor?->getAuthIdentifier(),
                        'updated_by' => $actor?->getAuthIdentifier(),
                    ])->save();

                    $paymentChanged = true;

                    $this->auditLogService->log(
                        action: 'registration_payment_unmarked',
                        auditable: $registration,
                        description: 'Pagamento da inscrição desmarcado',
                        oldValues: [
                            'payment_status' => true,
                            'paid_at' => optional($oldPaidAt)->toIso8601String(),
                        ],
                        newValues: ['payment_status' => false, 'paid_at' => null],
                        metadata: [
                            'registration_category' => $registration->category?->slug,
                            'registration_status' => 'unpaid',
                        ],
                    );
                }

                $registration = $registration->fresh([
                    'category',
                    'pilot',
                    'paymentMarkedBy',
                    'paymentUnmarkedBy',
                ]);
            }

            return [
                'registration' => $registration,
                'payment_changed' => $paymentChanged,
                'converted_to_pilot' => $convertedToPilot,
                'pilot' => $pilot,
            ];
        });
    }

    protected function syncPilot(PilotRegistration $registration, ?Authenticatable $actor = null): array
    {
        $registration->loadMissing('category');

        $pilot = $registration->pilot ?? $this->findMatchingPilot($registration);
        $pilot = $this->upsertPilot($pilot, $registration);

        $convertedToPilot = blank($registration->converted_to_pilot_at);
        $registrationNeedsSave = false;

        if ((int) $registration->pilot_id !== (int) $pilot->id) {
            $registration->pilot_id = $pilot->id;
            $registrationNeedsSave = true;
        }

        if ($convertedToPilot) {
            $registration->converted_to_pilot_at = now();
            $registrationNeedsSave = true;
        }

        if ($registrationNeedsSave) {
            $registration->updated_by = $actor?->getAuthIdentifier();
            $registration->save();
        }

        if ($convertedToPilot) {
            $this->auditLogService->log(
                action: 'registration_converted_to_pilot',
                auditable: $registration,
                description: 'Inscrição convertida para piloto',
                oldValues: ['pilot_id' => null],
                newValues: ['pilot_id' => $pilot->id],
                metadata: [
                    'registration_category' => $registration->category?->slug,
                    'pilot_id' => $pilot->id,
                ],
            );
        }

        return [
            'registration' => $registration->fresh([
                'category',
                'pilot',
                'paymentMarkedBy',
                'paymentUnmarkedBy',
            ]),
            'pilot' => $pilot,
            'converted_to_pilot' => $convertedToPilot,
        ];
    }

    protected function findMatchingPilot(PilotRegistration $registration): ?Pilot
    {
        if (filled($registration->cpf)) {
            $pilot = Pilot::query()->withTrashed()->where('cpf', $registration->cpf)->first();

            if ($pilot) {
                return $pilot;
            }
        }

        if (filled($registration->email)) {
            $pilot = Pilot::query()
                ->withTrashed()
                ->whereRaw('LOWER(email) = ?', [mb_strtolower($registration->email)])
                ->first();

            if ($pilot) {
                return $pilot;
            }
        }

        if (filled($registration->whatsapp)) {
            $pilot = Pilot::query()
                ->withTrashed()
                ->whereNotNull('phone')
                ->get()
                ->first(fn (Pilot $pilot) => $this->onlyDigits($pilot->phone) === $registration->whatsapp);

            if ($pilot) {
                return $pilot;
            }
        }

        return Pilot::query()
            ->withTrashed()
            ->get()
            ->first(function (Pilot $pilot) use ($registration): bool {
                return mb_strtolower(trim((string) $pilot->name)) === mb_strtolower($registration->full_name);
            });
    }

    protected function upsertPilot(?Pilot $pilot, PilotRegistration $registration): Pilot
    {
        $pilot = $pilot ?? new Pilot();

        if ($pilot->trashed()) {
            $pilot->restore();
        }

        $pilot->forceFill([
            'name' => $this->preferName($pilot->name, $registration->full_name) ?? $registration->full_name,
            'phone' => $this->preferDigitsValue($pilot->phone, $registration->whatsapp),
            'cpf' => $this->preferValue($pilot->cpf, $registration->cpf),
            'email' => $this->preferEmail($pilot->email, $registration->email),
            'address' => $this->preferLongText($pilot->address, $registration->address),
            'base_weight' => $pilot->base_weight ?? $registration->weight_kg,
            'notes' => $pilot->notes ?: 'Registro sincronizado a partir de inscrição pública.',
            'is_active' => $pilot->exists ? (bool) $pilot->is_active : true,
            'metadata' => $this->mergePilotMetadata($pilot->metadata, $registration),
        ]);
        $pilot->save();

        return $pilot->fresh();
    }

    protected function mergeRegistrationMetadata(array|null $currentMetadata, array $extraMetadata): array
    {
        return array_filter([
            ...Arr::wrap($currentMetadata),
            ...$extraMetadata,
        ], fn ($value) => $value !== null);
    }

    protected function mergePilotMetadata(array|null $currentMetadata, PilotRegistration $registration): array
    {
        $metadata = Arr::wrap($currentMetadata);
        $metadata['public_registration'] = array_filter([
            'last_registration_id' => $registration->id,
            'category' => $registration->category?->name,
            'category_slug' => $registration->category?->slug,
            'age' => $registration->age,
            'has_kart_experience' => $registration->has_kart_experience,
            'has_championship_experience' => $registration->has_championship_experience,
            'address' => $registration->address,
            'source' => data_get($registration->metadata, 'source', Analytics::source()),
            'synced_at' => now()->toIso8601String(),
        ], fn ($value) => $value !== null);

        return $metadata;
    }

    protected function preferValue(mixed $currentValue, mixed $incomingValue): mixed
    {
        if (blank($currentValue) && filled($incomingValue)) {
            return $incomingValue;
        }

        return $currentValue;
    }

    protected function preferDigitsValue(?string $currentValue, ?string $incomingValue): ?string
    {
        $normalizedCurrent = $this->onlyDigits($currentValue);

        if ($normalizedCurrent === null && filled($incomingValue)) {
            return $incomingValue;
        }

        return $normalizedCurrent ?? $currentValue;
    }

    protected function preferEmail(?string $currentValue, ?string $incomingValue): ?string
    {
        if (blank($currentValue) && filled($incomingValue)) {
            return mb_strtolower($incomingValue);
        }

        return $currentValue ? mb_strtolower($currentValue) : $currentValue;
    }

    protected function preferLongText(?string $currentValue, ?string $incomingValue): ?string
    {
        if (blank($currentValue) && filled($incomingValue)) {
            return $incomingValue;
        }

        if (filled($currentValue) && filled($incomingValue) && mb_strlen($incomingValue) > mb_strlen($currentValue)) {
            return $incomingValue;
        }

        return $currentValue;
    }

    protected function preferName(?string $currentValue, ?string $incomingValue): ?string
    {
        if (blank($currentValue) && filled($incomingValue)) {
            return $incomingValue;
        }

        if (blank($currentValue) || blank($incomingValue)) {
            return $currentValue;
        }

        $normalizedCurrent = mb_strtolower(trim($currentValue));
        $normalizedIncoming = mb_strtolower(trim($incomingValue));

        if (str_contains($normalizedIncoming, $normalizedCurrent) && mb_strlen($incomingValue) > mb_strlen($currentValue)) {
            return $incomingValue;
        }

        return $currentValue;
    }

    protected function onlyDigits(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value) ?: '';

        return $digits !== '' ? $digits : null;
    }
}
