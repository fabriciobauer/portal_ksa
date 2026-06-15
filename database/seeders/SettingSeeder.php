<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\ChampionshipSettings;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ChampionshipSettings::DEFAULTS as $key => $definition) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'group_name' => $definition['group_name'],
                    'label' => $definition['label'],
                    'type' => $definition['type'],
                    'value' => $definition['value'],
                    'description' => $definition['description'],
                ],
            );
        }
    }
}
