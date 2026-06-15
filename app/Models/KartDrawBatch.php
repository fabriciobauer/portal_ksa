<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KartDrawBatch extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'stage_category_id',
        'session_key',
        'sequence',
        'status',
        'range_start',
        'range_end',
        'drawn_at',
        'locked_at',
        'created_by',
        'recreated_from_batch_id',
    ];

    protected function casts(): array
    {
        return [
            'drawn_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function stageCategory(): BelongsTo
    {
        return $this->belongsTo(StageCategory::class);
    }

    public function draws(): HasMany
    {
        return $this->hasMany(KartDraw::class)->orderBy('queue_position')->orderBy('draw_order');
    }

    public function previousBatch(): BelongsTo
    {
        return $this->belongsTo(self::class, 'recreated_from_batch_id');
    }
}
