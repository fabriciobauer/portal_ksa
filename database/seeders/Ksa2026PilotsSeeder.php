<?php

namespace Database\Seeders;

use App\Models\Pilot;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class Ksa2026PilotsSeeder extends Seeder
{
    public function run(): void
    {
        $created = 0;
        $updated = 0;
        $unchanged = 0;
        $rows = [];
        $pilots = Pilot::withTrashed()->get();

        foreach ($this->pilots() as $payload) {
            $name = $payload['name'];
            $phone = $this->normalizePhone($payload['phone']);
            $baseWeight = (float) $payload['base_weight'];

            $pilot = $this->findMatchingPilot($pilots, $name, $phone);

            if ($pilot === null) {
                $pilot = Pilot::query()->create([
                    'name' => $name,
                    'phone' => $phone,
                    'base_weight' => $baseWeight,
                ]);

                $pilots->push($pilot);
                $created++;
            } else {
                $dirty = $pilot->name !== $name
                    || $this->normalizePhone($pilot->phone) !== $phone
                    || (float) ($pilot->base_weight ?? 0) !== $baseWeight
                    || $pilot->trashed();

                if ($dirty) {
                    $pilot->forceFill([
                        'name' => $name,
                        'phone' => $phone,
                        'base_weight' => $baseWeight,
                    ]);

                    if ($pilot->trashed()) {
                        $pilot->restore();
                    } else {
                        $pilot->save();
                    }

                    $updated++;
                } else {
                    $unchanged++;
                }
            }

            $rows[] = [
                $name,
                $phone ?? 'null',
                number_format($baseWeight, 2, '.', ''),
            ];
        }

        if ($this->command !== null) {
            $this->command->info('Importacao de pilotos KSA 2026 concluida.');
            $this->command->line("Criados: {$created}");
            $this->command->line("Atualizados: {$updated}");
            $this->command->line("Sem alteracoes: {$unchanged}");
            $this->command->table(['Nome', 'Telefone', 'Peso base (kg)'], $rows);
        }
    }

    protected function findMatchingPilot(Collection $pilots, string $name, ?string $phone): ?Pilot
    {
        if ($phone !== null) {
            return $pilots->first(fn (Pilot $pilot): bool => $this->normalizePhone($pilot->phone) === $phone);
        }

        return $pilots->first(fn (Pilot $pilot): bool => $pilot->name === $name);
    }

    protected function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        return $digits !== '' ? $digits : null;
    }

    protected function pilots(): array
    {
        return [
            ['name' => 'Miguel gatão', 'phone' => null, 'base_weight' => 80],
            ['name' => 'Jose Piva Junior', 'phone' => null, 'base_weight' => 88],
            ['name' => 'Michel Alamini', 'phone' => null, 'base_weight' => 100],
            ['name' => 'Jeniffer Rodrigues Calegari', 'phone' => null, 'base_weight' => 68],
            ['name' => 'Gustavo Laureano', 'phone' => null, 'base_weight' => 90],
            ['name' => 'João Cardoso', 'phone' => '48998033288', 'base_weight' => 71],
            ['name' => 'Fabrício Bauer Chaves Junior', 'phone' => null, 'base_weight' => 76],
            ['name' => 'Rodolfo Lucas Bortoluzzi', 'phone' => '48998406452', 'base_weight' => 113],
            ['name' => 'Maickel de caldas jorge', 'phone' => '51996220502', 'base_weight' => 80],
            ['name' => 'Luiz Rafael Rosso Perraro', 'phone' => '48999183992', 'base_weight' => 82],
            ['name' => 'Luiz Gustavo Costa Ceolin', 'phone' => '48996084470', 'base_weight' => 82],
            ['name' => 'Josué Santiago', 'phone' => '48998592358', 'base_weight' => 75],
            ['name' => 'Carlos Henrique Abreu Martins', 'phone' => '48991336417', 'base_weight' => 77],
            ['name' => 'Mateus Cidade De Oliveira', 'phone' => '48991225389', 'base_weight' => 85],
            ['name' => 'Marlon Batista', 'phone' => '48996525895', 'base_weight' => 81],
            ['name' => 'Guilherme Melim Ferreira', 'phone' => '48996560440', 'base_weight' => 82],
            ['name' => 'Bismarck antunes paes', 'phone' => '48998001667', 'base_weight' => 90],
            ['name' => 'Leonardo de Souza', 'phone' => '48988641424', 'base_weight' => 70],
            ['name' => 'Maicon Viana May', 'phone' => '48992191984', 'base_weight' => 123],
            ['name' => 'Paulo Antônio da Costa Filho', 'phone' => '48991571438', 'base_weight' => 78],
            ['name' => 'Leandro maiato torazzi', 'phone' => '48996759133', 'base_weight' => 86],
            ['name' => 'Henrique Boeing', 'phone' => '48991793745', 'base_weight' => 92],
            ['name' => 'Frander Vieira', 'phone' => '48999371850', 'base_weight' => 85],
            ['name' => 'Leonardo Tasca', 'phone' => '48996389900', 'base_weight' => 108],
            ['name' => 'Vanderlei kaupezinski', 'phone' => '48999517557', 'base_weight' => 80],
            ['name' => 'Guilherme Batista', 'phone' => '48998317171', 'base_weight' => 74],
            ['name' => 'Matheus Alves', 'phone' => '48999540115', 'base_weight' => 90],
            ['name' => 'Jefferson Nazareno Alves da Silva', 'phone' => '48988556363', 'base_weight' => 67],
            ['name' => 'Jorge Glanert', 'phone' => '47997696060', 'base_weight' => 85],
            ['name' => 'HIago Dalmolin', 'phone' => '48991687567', 'base_weight' => 85],
        ];
    }
}
