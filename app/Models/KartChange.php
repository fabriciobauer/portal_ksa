<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KartChange extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'stage_category_entry_id',
        'qualifying_result_id',
        'previous_kart_number',
        'new_kart_number',
        'reason_type',
        'counts_as_regular_swap',
        'happened_at',
        'created_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'counts_as_regular_swap' => 'boolean',
            'happened_at' => 'datetime',
        ];
    }

    public function stageCategoryEntry(): BelongsTo
    {
        return $this->belongsTo(StageCategoryEntry::class);
    }

    public function qualifyingResult(): BelongsTo
    {
        return $this->belongsTo(QualifyingResult::class);
    }
}
