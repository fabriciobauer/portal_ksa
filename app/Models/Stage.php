<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stage extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'season_id',
        'name',
        'stage_number',
        'stage_date',
        'briefing_time',
        'draw_time',
        'location',
        'track_layout',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'stage_date' => 'date',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function stageCategories(): HasMany
    {
        return $this->hasMany(StageCategory::class);
    }
}
