<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StageStanding extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'stage_category_entry_id',
        'race1_points',
        'race2_points',
        'completion_bonus',
        'gross_stage_points',
        'championship_penalty_points',
        'manual_adjustment_points',
        'manual_override_points',
        'championship_points',
        'stage_position',
        'is_technical_tie',
        'tie_breaker_resolved_manually',
        'tie_break_notes',
        'is_disqualified',
        'disqualification_reason',
        'discard_blocked',
        'override_reason',
        'override_user_id',
        'override_at',
        'last_recalculated_at',
    ];

    protected function casts(): array
    {
        return [
            'race1_points' => 'decimal:2',
            'race2_points' => 'decimal:2',
            'completion_bonus' => 'decimal:2',
            'gross_stage_points' => 'decimal:2',
            'championship_penalty_points' => 'decimal:2',
            'manual_adjustment_points' => 'decimal:2',
            'manual_override_points' => 'decimal:2',
            'championship_points' => 'decimal:2',
            'is_technical_tie' => 'boolean',
            'tie_breaker_resolved_manually' => 'boolean',
            'is_disqualified' => 'boolean',
            'discard_blocked' => 'boolean',
            'override_at' => 'datetime',
            'last_recalculated_at' => 'datetime',
        ];
    }

    public function stageCategoryEntry(): BelongsTo
    {
        return $this->belongsTo(StageCategoryEntry::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(PointAdjustment::class);
    }

    public function getEffectiveChampionshipPointsAttribute(): float
    {
        return (float) ($this->manual_override_points ?? $this->championship_points);
    }
}
