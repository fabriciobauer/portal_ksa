<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointAdjustment extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'stage_standing_id',
        'type',
        'delta',
        'previous_value',
        'new_value',
        'reason',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'delta' => 'decimal:2',
            'previous_value' => 'decimal:2',
            'new_value' => 'decimal:2',
        ];
    }

    public function stageStanding(): BelongsTo
    {
        return $this->belongsTo(StageStanding::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
