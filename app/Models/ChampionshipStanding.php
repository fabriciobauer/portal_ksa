<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChampionshipStanding extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'season_category_id',
        'pilot_id',
        'total_gross_points',
        'discarded_points',
        'total_valid_points',
        'final_position',
        'tiebreak_counters',
        'promotion_eligible',
        'notes',
        'last_recalculated_at',
    ];

    protected function casts(): array
    {
        return [
            'total_gross_points' => 'decimal:2',
            'discarded_points' => 'decimal:2',
            'total_valid_points' => 'decimal:2',
            'tiebreak_counters' => 'array',
            'promotion_eligible' => 'boolean',
            'last_recalculated_at' => 'datetime',
        ];
    }

    public function seasonCategory(): BelongsTo
    {
        return $this->belongsTo(SeasonCategory::class);
    }

    public function pilot(): BelongsTo
    {
        return $this->belongsTo(Pilot::class);
    }
}
