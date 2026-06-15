<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StageCategory extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'stage_id',
        'season_category_id',
        'status',
        'management_locked_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'management_locked_at' => 'datetime',
        ];
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function seasonCategory(): BelongsTo
    {
        return $this->belongsTo(SeasonCategory::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(StageCategoryEntry::class);
    }

    public function drawBatches(): HasMany
    {
        return $this->hasMany(KartDrawBatch::class);
    }

    public function kartQueuePositions(): HasMany
    {
        return $this->hasMany(StageKartQueuePosition::class)->orderBy('queue_position');
    }

    public function races(): HasMany
    {
        return $this->hasMany(Race::class)->orderBy('number');
    }

    public function effectivePilotLimit(): int
    {
        $this->loadMissing('seasonCategory.category');

        return $this->seasonCategory?->effectivePilotLimit() ?? 12;
    }

    public function confirmedEntriesCount(): int
    {
        return $this->entries()->where('confirmation_status', 'confirmed')->count();
    }
}
