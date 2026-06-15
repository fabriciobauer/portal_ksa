<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeighIn extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'stage_category_entry_id',
        'kart_number',
        'combined_weight',
        'tolerance_used',
        'within_tolerance',
        'exception_no_spare_kart',
        'notes',
        'recorded_by',
        'weighed_at',
    ];

    protected function casts(): array
    {
        return [
            'combined_weight' => 'decimal:2',
            'tolerance_used' => 'decimal:2',
            'within_tolerance' => 'boolean',
            'exception_no_spare_kart' => 'boolean',
            'weighed_at' => 'datetime',
        ];
    }

    public function stageCategoryEntry(): BelongsTo
    {
        return $this->belongsTo(StageCategoryEntry::class);
    }
}
