<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Race extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'stage_category_id',
        'number',
        'name',
        'grid_generated_from',
        'is_grid_confirmed',
        'is_result_confirmed',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_grid_confirmed' => 'boolean',
            'is_result_confirmed' => 'boolean',
            'confirmed_at' => 'datetime',
        ];
    }

    public function stageCategory(): BelongsTo
    {
        return $this->belongsTo(StageCategory::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(RaceResult::class)->orderBy('grid_position');
    }
}
