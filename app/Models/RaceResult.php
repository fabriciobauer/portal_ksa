<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceResult extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'race_id',
        'stage_category_entry_id',
        'grid_position',
        'finish_position',
        'kart_number',
        'status',
        'points_awarded',
        'is_official',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'points_awarded' => 'decimal:2',
            'is_official' => 'boolean',
        ];
    }

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function stageCategoryEntry(): BelongsTo
    {
        return $this->belongsTo(StageCategoryEntry::class);
    }
}
