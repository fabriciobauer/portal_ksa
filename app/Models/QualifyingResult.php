<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use App\Support\TimeFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QualifyingResult extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'stage_category_entry_id',
        'initial_kart_number',
        'current_kart_number',
        'lap_time_ms',
        'status',
        'auto_grid_position',
        'final_grid_position',
        'notes',
        'recorded_by',
        'confirmed_at',
    ];

    protected $appends = ['formatted_lap_time'];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
        ];
    }

    public function stageCategoryEntry(): BelongsTo
    {
        return $this->belongsTo(StageCategoryEntry::class);
    }

    public function kartChanges(): HasMany
    {
        return $this->hasMany(KartChange::class);
    }

    public function getFormattedLapTimeAttribute(): ?string
    {
        return TimeFormatter::formatLapTime($this->lap_time_ms);
    }
}
