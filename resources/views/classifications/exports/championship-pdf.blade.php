<!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;font-size:12px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ccc;padding:6px;text-align:left}</style></head>
<body>
    <h2>Classificação do campeonato</h2>
    <p>{{ $seasonCategory->season->name }} / {{ $seasonCategory->category->name }}</p>
    <table>
        <thead><tr><th>Pos.</th><th>Piloto</th><th>Pontos válidos</th><th>Descartados</th></tr></thead>
        <tbody>@foreach($points as $standing)<tr><td>{{ $standing->final_position }}</td><td>{{ $standing->pilot->displayName() }}</td><td>{{ $standing->total_valid_points }}</td><td>{{ $standing->discarded_points }}</td></tr>@endforeach</tbody>
    </table>
</body></html>
