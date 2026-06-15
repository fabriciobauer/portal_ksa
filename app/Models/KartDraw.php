<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KartDraw extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'kart_draw_batch_id',
        'stage_category_entry_id',
        'queue_position',
        'draw_order',
        'kart_number',
        'assigned_at',
        'assigned_by',
        'is_manual',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'is_manual' => 'boolean',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(KartDrawBatch::class, 'kart_draw_batch_id');
    }

    public function stageCategoryEntry(): BelongsTo
    {
        return $this->belongsTo(StageCategoryEntry::class);
    }
}
