<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StageCategoryEntry extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'stage_category_id',
        'pilot_id',
        'season_category_registration_id',
        'confirmation_status',
        'attendance_status',
        'briefing_status',
        'briefing_penalty_grid_positions',
        'notes',
        'created_by',
        'checked_in_at',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
        ];
    }

    public function stageCategory(): BelongsTo
    {
        return $this->belongsTo(StageCategory::class);
    }

    public function pilot(): BelongsTo
    {
        return $this->belongsTo(Pilot::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(SeasonCategoryRegistration::class, 'season_category_registration_id');
    }

    public function qualifyingResult(): HasOne
    {
        return $this->hasOne(QualifyingResult::class);
    }

    public function kartDraws(): HasMany
    {
        return $this->hasMany(KartDraw::class);
    }

    public function raceResults(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }

    public function weighIn(): HasOne
    {
        return $this->hasOne(WeighIn::class);
    }

    public function penalties(): HasMany
    {
        return $this->hasMany(StagePenalty::class);
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(RaceOccurrence::class);
    }

    public function stageStanding(): HasOne
    {
        return $this->hasOne(StageStanding::class);
    }
}
