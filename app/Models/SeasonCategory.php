<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeasonCategory extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'season_id',
        'category_id',
        'pilot_limit',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(SeasonCategoryRegistration::class);
    }

    public function stageCategories(): HasMany
    {
        return $this->hasMany(StageCategory::class);
    }

    public function championshipStandings(): HasMany
    {
        return $this->hasMany(ChampionshipStanding::class);
    }

    public function effectivePilotLimit(): int
    {
        return $this->pilot_limit ?: ($this->category?->default_pilot_limit ?? 12);
    }

    public function confirmedRegistrationsCount(): int
    {
        return $this->registrations()->where('status', 'confirmed')->count();
    }

    public function remainingSlots(): int
    {
        return max(0, $this->effectivePilotLimit() - $this->confirmedRegistrationsCount());
    }
}
