<?php

namespace App\Services;

use App\Models\Setting;
use App\Support\ChampionshipSettings;
use Illuminate\Support\Collection;

class SettingService
{
    public function all(): Collection
    {
        $stored = Setting::query()->get()->keyBy('key');

        return collect(ChampionshipSettings::DEFAULTS)
            ->map(function (array $definition, string $key) use ($stored) {
                $setting = $stored->get($key);

                return [
                    ...$definition,
                    'key' => $key,
                    'value' => $setting?->typed_value ?? $definition['value'],
                ];
            })
            ->values();
    }

    public function allGrouped(): Collection
    {
        return $this->all()->groupBy('group_name');
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $stored = Setting::query()->where('key', $key)->first();

        if ($stored) {
            return $stored->typed_value;
        }

        return ChampionshipSettings::DEFAULTS[$key]['value'] ?? $default;
    }

    public function saveMany(array $values): void
    {
        foreach ($values as $key => $value) {
            $definition = ChampionshipSettings::DEFAULTS[$key] ?? null;

            if (! $definition) {
                continue;
            }

            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'group_name' => $definition['group_name'],
                    'label' => $definition['label'],
                    'type' => $definition['type'],
                    'value' => $value,
                    'description' => $definition['description'],
                ],
            );
        }
    }

    public function kartRange(): array
    {
        return [
            (int) $this->get('karts.range_start', 1),
            (int) $this->get('karts.range_end', 15),
        ];
    }

    public function pointsTable(): array
    {
        return array_map('floatval', $this->get('scoring.points_table', []));
    }

    public function discardCount(): int
    {
        return (int) $this->get('scoring.discard_count', 1);
    }
}
