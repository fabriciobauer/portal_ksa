<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceOccurrence extends Model
{
    use HasFactory;
    use LogsAudit;

    public const TYPE_WARNING = 'warning';

    protected $fillable = [
        'stage_category_entry_id',
        'race_id',
        'type',
        'seconds_penalty',
        'auto_disqualified',
        'description',
        'evidence_path',
        'created_by',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'auto_disqualified' => 'boolean',
            'issued_at' => 'datetime',
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
