<!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;font-size:12px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ccc;padding:6px;text-align:left}</style></head>
<body>
    <h2>Classificacao da etapa</h2>
    <p>{{ $stageCategory->stage->name }} / {{ $stageCategory->seasonCategory->category->name }}</p>
    <table>
        <thead><tr><th>Pos.</th><th>Piloto</th><th>R1</th><th>R2</th><th>Bonus camp.</th><th>Bruto etapa</th><th>Campeonato</th></tr></thead>
        <tbody>@foreach($standings as $standing)<tr><td>{{ $standing->stage_position }}</td><td>{{ $standing->stageCategoryEntry->pilot->displayName() }}</td><td>{{ $standing->race1_points }}</td><td>{{ $standing->race2_points }}</td><td>{{ $standing->completion_bonus }}</td><td>{{ $standing->gross_stage_points }}</td><td>{{ $standing->championship_points }}</td></tr>@endforeach</tbody>
    </table>
</body></html>
