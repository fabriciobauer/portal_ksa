<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StagePenalty extends Model
{
    use HasFactory;
    use LogsAudit;

    public const TYPE_PODIUM_ATTIRE = 'podium_attire';
    public const TYPE_PODIUM_ABSENT = 'podium_absent';
    public const TYPE_STAGE_DSQ = 'stage_dsq';
    public const TYPE_RACE_DSQ = 'race_dsq';

    protected $fillable = [
        'stage_category_entry_id',
        'race_id',
        'type',
        'penalty_scope',
        'points_delta',
        'capped_group',
        'is_disqualification',
        'affects_discard_block',
        'reason',
        'description',
        'created_by',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'points_delta' => 'decimal:2',
            'is_disqualification' => 'boolean',
            'affects_discard_block' => 'boolean',
            'applied_at' => 'datetime',
        ];
    }

    public function stageCategoryEntry(): BelongsTo
    {
        return $this->belongsTo(StageCategoryEntry::class);
    }

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }
}
