<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChampionshipPoint extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'season_category_id',
        'stage_id',
        'pilot_id',
        'stage_standing_id',
        'gross_points',
        'adjustment_points',
        'valid_points',
        'is_discarded',
        'discard_blocked',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'gross_points' => 'decimal:2',
            'adjustment_points' => 'decimal:2',
            'valid_points' => 'decimal:2',
            'is_discarded' => 'boolean',
            'discard_blocked' => 'boolean',
        ];
    }

    public function seasonCategory(): BelongsTo
    {
        return $this->belongsTo(SeasonCategory::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function pilot(): BelongsTo
    {
        return $this->belongsTo(Pilot::class);
    }

    public function stageStanding(): BelongsTo
    {
        return $this->belongsTo(StageStanding::class);
    }
}
