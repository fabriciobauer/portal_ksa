<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StageKartQueuePosition extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'stage_category_id',
        'queue_position',
        'kart_number',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function stageCategory(): BelongsTo
    {
        return $this->belongsTo(StageCategory::class);
    }
}
