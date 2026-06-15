<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'name',
        'slug',
        'target_weight',
        'description',
        'is_active',
        'default_pilot_limit',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'target_weight' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function seasonCategories(): HasMany
    {
        return $this->hasMany(SeasonCategory::class);
    }
}
