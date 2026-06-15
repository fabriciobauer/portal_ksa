<?php

namespace App\Services;

use App\Models\KartDraw;
use App\Models\KartDrawBatch;
use App\Models\StageCategory;
use App\Models\StageCategoryEntry;
use App\Models\StageKartQueuePosition;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KartDrawService
{
    public function __construct(protected SettingService $settingService)
    {
    }

    public function latestBatch(StageCategory $stageCategory, string $sessionKey = 'qualifying'): ?KartDrawBatch
    {
        return $stageCategory->drawBatches()
            ->where('session_key', $sessionKey)
            ->latest('sequence')
            ->first();
    }

    public function queuePreview(StageCategory $stageCategory): array
    {
        $confirmedEntries = $this->confirmedEntries($stageCategory);
        $confirmedEntriesCount = $confirmedEntries->count();
        $operationalLimit = $stageCategory->effectivePilotLimit();
        $rows = $this->queueRows($stageCategory, $confirmedEntriesCount);
        $usedRows = $rows
            ->filter(fn (array $row): bool => $row['queue_position'] <= $confirmedEntriesCount)
            ->values();

        return [
            'confirmed_entries_count' => $confirmedEntriesCount,
            'operational_limit' => $operationalLimit,
            'rows' => $rows,
            'used_rows' => $usedRows,
            'issues' => $this->previewIssues($usedRows, $confirmedEntriesCount, $operationalLimit),
        ];
    }

    public function saveQueue(StageCategory $stageCategory, array $positions): void
    {
        $this->ensureQueueEditable($stageCategory);

        DB::transaction(function () use ($stageCategory, $positions): void {
            foreach (collect($positions)->sortBy('queue_position') as $payload) {
                $stageCategory->kartQueuePositions()->updateOrCreate(
                    [
                        'queue_position' => (int) $payload['queue_position'],
                    ],
                    [
                        'kart_number' => blank($payload['kart_number'] ?? null) ? null : (int) $payload['kart_number'],
                        'is_active' => (bool) ($payload['is_active'] ?? false),
                    ],
                );
            }
        });
    }

    public function draw(StageCategory $stageCategory, User $user, string $sessionKey = 'qualifying'): KartDrawBatch
    {
        $entries = $this->confirmedEntries($stageCategory);

        $this->ensureStageCategoryAvailableForDraw($stageCategory, $entries->count());
        $this->ensureRedrawAllowed($stageCategory, $sessionKey);

        $availablePositions = $this->resolveQueuePositionsForDraw($stageCategory, $entries->count())
            ->shuffle()
            ->values();

        return DB::transaction(function () use ($stageCategory, $user, $sessionKey, $entries, $availablePositions): KartDrawBatch {
            $previous = $this->latestBatch($stageCategory, $sessionKey);

            $batch = KartDrawBatch::query()->create([
                'stage_category_id' => $stageCategory->id,
                'session_key' => $sessionKey,
                'sequence' => (int) ($previous?->sequence ?? 0) + 1,
                'status' => 'draft',
                'range_start' => 1,
                'range_end' => $entries->count(),
                'drawn_at' => now(),
                'created_by' => $user->id,
                'recreated_from_batch_id' => $previous?->id,
            ]);

            $entries->values()->each(function (StageCategoryEntry $entry, int $index) use ($availablePositions, $batch, $user): void {
                /** @var StageKartQueuePosition $queuePosition */
                $queuePosition = $availablePositions[$index];

                KartDraw::query()->create([
                    'kart_draw_batch_id' => $batch->id,
                    'stage_category_entry_id' => $entry->id,
                    'queue_position' => $queuePosition->queue_position,
                    'draw_order' => $index + 1,
                    'kart_number' => $queuePosition->kart_number,
                    'assigned_at' => now(),
                    'assigned_by' => $user->id,
                ]);
            });

            return $batch->load('draws.stageCategoryEntry.pilot');
        });
    }

    public function lock(KartDrawBatch $batch): void
    {
        $batch->forceFill([
            'status' => 'locked',
            'locked_at' => now(),
        ])->save();
    }

    public function unlock(KartDrawBatch $batch): void
    {
        if ($batch->status !== 'locked') {
            return;
        }

        $this->ensureLatestBatchForUnlock($batch);

        $batch->forceFill([
            'status' => 'draft',
            'locked_at' => null,
        ])->save();
    }

    public function updatePosition(KartDraw $draw, int $queuePosition, User $user): void
    {
        $draw->loadMissing('batch.stageCategory.kartQueuePositions');

        $batch = $draw->batch;

        $this->ensureBatchEditable($batch);

        if ($draw->queue_position === $queuePosition) {
            return;
        }

        $allowedPositions = $this->resolveQueuePositionsForDraw($batch->stageCategory, $batch->draws()->count())
            ->keyBy('queue_position');
        $targetPosition = $allowedPositions->get($queuePosition);

        if (! $targetPosition) {
            throw ValidationException::withMessages([
                'queue_position' => "A posicao {$queuePosition} nao pode ser usada neste sorteio.",
            ]);
        }

        $currentPosition = $allowedPositions->get($draw->queue_position);
        $currentQueuePosition = $draw->queue_position;
        $currentKartNumber = $draw->kart_number;
        $swapDraw = KartDraw::query()
            ->where('kart_draw_batch_id', $batch->id)
            ->where('queue_position', $queuePosition)
            ->whereKeyNot($draw->id)
            ->first();

        DB::transaction(function () use ($draw, $swapDraw, $targetPosition, $currentPosition, $currentQueuePosition, $currentKartNumber, $user): void {
            $assignedAt = now();

            if ($swapDraw) {
                $draw->forceFill([
                    'queue_position' => 0,
                    'kart_number' => 0,
                    'assigned_at' => $assignedAt,
                    'assigned_by' => $user->id,
                    'is_manual' => true,
                ])->save();

                $swapDraw->forceFill([
                    'queue_position' => $currentPosition?->queue_position ?? $currentQueuePosition,
                    'kart_number' => $currentPosition?->kart_number ?? $currentKartNumber,
                    'assigned_at' => $assignedAt,
                    'assigned_by' => $user->id,
                    'is_manual' => true,
                ])->save();
            }

            $draw->forceFill([
                'queue_position' => $targetPosition->queue_position,
                'kart_number' => $targetPosition->kart_number,
                'assigned_at' => $assignedAt,
                'assigned_by' => $user->id,
                'is_manual' => true,
            ])->save();
        });
    }

    public function exportRows(KartDrawBatch $batch): Collection
    {
        return $batch->draws()
            ->with('stageCategoryEntry.pilot')
            ->get()
            ->map(fn (KartDraw $draw) => [
                'piloto' => $draw->stageCategoryEntry->pilot->displayName(),
                'posicao_da_fila' => $draw->queue_position,
                'kart' => $draw->kart_number,
                'ordem_do_sorteio' => $draw->draw_order,
                'manual' => $draw->is_manual ? 'Sim' : 'Nao',
                'atribuido_em' => optional($draw->assigned_at)->format('d/m/Y H:i'),
            ]);
    }

    protected function confirmedEntries(StageCategory $stageCategory): Collection
    {
        return $stageCategory->entries()
            ->where('confirmation_status', 'confirmed')
            ->with('pilot')
            ->orderBy('id')
            ->get();
    }

    protected function queueRows(StageCategory $stageCategory, int $confirmedEntriesCount): Collection
    {
        $stageCategory->loadMissing('kartQueuePositions');

        $configuredPositions = $stageCategory->kartQueuePositions->keyBy('queue_position');
        $maxConfiguredPosition = (int) ($configuredPositions->keys()->max() ?? 0);
        $maxPosition = max($this->defaultQueueLength(), $maxConfiguredPosition, $confirmedEntriesCount, 1);

        return collect(range(1, $maxPosition))->map(function (int $queuePosition) use ($configuredPositions, $confirmedEntriesCount) {
            /** @var StageKartQueuePosition|null $configured */
            $configured = $configuredPositions->get($queuePosition);
            $resolvedKartNumber = $configured?->kart_number ?? $queuePosition;

            return [
                'queue_position' => $queuePosition,
                'kart_number' => $resolvedKartNumber,
                'stored_kart_number' => $configured?->kart_number,
                'is_configured' => $configured !== null,
                'is_active' => $configured?->is_active ?? ($queuePosition <= $confirmedEntriesCount),
                'is_used' => $queuePosition <= $confirmedEntriesCount,
            ];
        });
    }

    protected function previewIssues(Collection $usedRows, int $confirmedEntriesCount, int $operationalLimit): array
    {
        if ($confirmedEntriesCount === 0) {
            return ['Nao ha pilotos confirmados nesta categoria/etapa.'];
        }

        $issues = [];

        if ($confirmedEntriesCount > $operationalLimit) {
            $issues[] = "Existem {$confirmedEntriesCount} pilotos confirmados, acima do limite operacional de {$operationalLimit} karts por etapa.";
        }

        if ($usedRows->contains(fn (array $row): bool => ! $row['is_configured'] || $row['stored_kart_number'] === null)) {
            $issues[] = 'Salve a fila para confirmar as posicoes usadas antes de sortear.';
        }

        foreach ($usedRows as $row) {
            if (! $row['is_active']) {
                $issues[] = "A posicao {$row['queue_position']} da fila nao esta ativa.";
            }
        }

        $duplicateKarts = $usedRows
            ->pluck('kart_number')
            ->filter(fn ($kartNumber) => $kartNumber !== null)
            ->countBy()
            ->filter(fn (int $count): bool => $count > 1)
            ->keys();

        if ($duplicateKarts->isNotEmpty()) {
            $issues[] = 'Ha karts repetidos nas posicoes usadas: '.$duplicateKarts->implode(', ').'.';
        }

        return array_values(array_unique($issues));
    }

    protected function defaultQueueLength(): int
    {
        [, $rangeEnd] = $this->settingService->kartRange();

        return max(1, $rangeEnd);
    }

    protected function ensureStageCategoryAvailableForDraw(StageCategory $stageCategory, int $confirmedEntriesCount): void
    {
        $stageCategory->loadMissing('stage', 'seasonCategory.category');

        if ($confirmedEntriesCount < 1) {
            throw ValidationException::withMessages([
                'draw' => 'Nao ha pilotos confirmados para realizar o sorteio desta categoria na etapa.',
            ]);
        }

        if ($confirmedEntriesCount > $stageCategory->effectivePilotLimit()) {
            throw ValidationException::withMessages([
                'draw' => "Ha mais pilotos confirmados do que o limite operacional de {$stageCategory->effectivePilotLimit()} karts nesta etapa.",
            ]);
        }

        if ($stageCategory->management_locked_at) {
            throw ValidationException::withMessages([
                'draw' => 'A gestao desta categoria na etapa esta travada e nao permite sorteio.',
            ]);
        }

        if (! in_array($stageCategory->status, ['planned', 'open'], true) || ! in_array($stageCategory->stage->status, ['planned', 'open'], true)) {
            throw ValidationException::withMessages([
                'draw' => 'A etapa/categoria nao esta aberta para sorteio.',
            ]);
        }
    }

    protected function resolveQueuePositionsForDraw(StageCategory $stageCategory, int $confirmedEntriesCount): Collection
    {
        $stageCategory->loadMissing('kartQueuePositions');

        $positions = $stageCategory->kartQueuePositions->keyBy('queue_position');
        $usablePositions = collect();

        foreach (range(1, $confirmedEntriesCount) as $queuePosition) {
            /** @var StageKartQueuePosition|null $configured */
            $configured = $positions->get($queuePosition);

            if (! $configured || ! $configured->is_active) {
                throw ValidationException::withMessages([
                    'draw' => "A posicao {$queuePosition} da fila nao esta configurada ou nao esta ativa.",
                ]);
            }

            if ($configured->kart_number === null) {
                throw ValidationException::withMessages([
                    'draw' => "A posicao {$queuePosition} da fila esta sem kart definido.",
                ]);
            }

            $usablePositions->push($configured);
        }

        $duplicateKarts = $usablePositions
            ->pluck('kart_number')
            ->countBy()
            ->filter(fn (int $count): bool => $count > 1)
            ->keys();

        if ($duplicateKarts->isNotEmpty()) {
            throw ValidationException::withMessages([
                'draw' => 'Existem karts repetidos nas posicoes usadas do sorteio: '.$duplicateKarts->implode(', ').'.',
            ]);
        }

        return $usablePositions->sortBy('queue_position')->values();
    }

    protected function ensureBatchEditable(KartDrawBatch $batch): void
    {
        if ($batch->status === 'locked') {
            throw ValidationException::withMessages([
                'queue_position' => 'O sorteio esta travado e nao pode mais ser alterado.',
            ]);
        }
    }

    protected function ensureQueueEditable(StageCategory $stageCategory, string $sessionKey = 'qualifying'): void
    {
        $latestBatch = $this->latestBatch($stageCategory, $sessionKey);

        if ($latestBatch?->status === 'locked') {
            throw ValidationException::withMessages([
                'positions' => 'O sorteio travado nao permite alterar a fila de karts.',
            ]);
        }
    }

    protected function ensureRedrawAllowed(StageCategory $stageCategory, string $sessionKey = 'qualifying'): void
    {
        $latestBatch = $this->latestBatch($stageCategory, $sessionKey);

        if ($latestBatch?->status === 'locked') {
            throw ValidationException::withMessages([
                'draw' => 'O sorteio travado nao pode ser refeito.',
            ]);
        }
    }

    protected function ensureLatestBatchForUnlock(KartDrawBatch $batch): void
    {
        $batch->loadMissing('stageCategory');

        $latestBatch = $this->latestBatch($batch->stageCategory, $batch->session_key);

        if (! $latestBatch || ! $latestBatch->is($batch)) {
            throw ValidationException::withMessages([
                'batch' => 'Somente o sorteio mais recente pode ser destravado.',
            ]);
        }
    }
}
