<?php

namespace App\Services;

use App\Models\SeasonCategory;
use App\Models\SeasonCategoryRegistration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    public function __construct(
        protected SettingService $settingService,
        protected StageProvisioningService $stageProvisioningService,
    ) {
    }

    public function refreshStatuses(SeasonCategory $seasonCategory): void
    {
        $registrations = $seasonCategory->registrations()
            ->orderByRaw("CASE WHEN registration_type = 'annual' THEN 0 ELSE 1 END")
            ->orderBy('created_at')
            ->get();

        DB::transaction(function () use ($registrations, $seasonCategory): void {
            $waitlist = 1;

            foreach ($registrations as $registration) {
                $status = in_array($registration->status, [
                    SeasonCategoryRegistration::STATUS_CONFIRMED,
                    SeasonCategoryRegistration::STATUS_WAITING,
                    SeasonCategoryRegistration::STATUS_CANCELLED,
                ], true)
                    ? $registration->status
                    : SeasonCategoryRegistration::STATUS_CONFIRMED;

                $registration->forceFill([
                    'status' => $status,
                    'confirmed_at' => $status === SeasonCategoryRegistration::STATUS_CONFIRMED
                        ? ($registration->confirmed_at ?: now())
                        : null,
                    'waitlist_order' => $status === SeasonCategoryRegistration::STATUS_WAITING ? $waitlist++ : null,
                    'registered_at' => $registration->registered_at ?: now()->toDateString(),
                ])->save();
            }

            $this->stageProvisioningService->syncSeasonCategoryStages($seasonCategory);
        });
    }

    public function createMany(array $data): int
    {
        $seasonCategory = SeasonCategory::query()->findOrFail($data['season_category_id']);
        $pilotIds = collect($data['pilot_ids'] ?? [])
            ->map(fn ($pilotId) => (int) $pilotId)
            ->unique()
            ->values()
            ->all();
        $payload = Arr::except($data, ['pilot_ids']);

        DB::transaction(function () use ($payload, $pilotIds): void {
            foreach ($pilotIds as $pilotId) {
                SeasonCategoryRegistration::query()->create([
                    ...$payload,
                    'pilot_id' => $pilotId,
                ]);
            }
        });

        $this->refreshStatuses($seasonCategory);

        return count($pilotIds);
    }

    public function ensureSingleEntriesAllowed(string $registrationType): void
    {
        $allowSingles = (bool) $this->settingService->get('stage.allow_single_entries', true);

        if (! $allowSingles && $registrationType === SeasonCategoryRegistration::TYPE_SINGLE) {
            abort(422, 'Inscrições avulsas estão desabilitadas nas configurações.');
        }
    }
}
