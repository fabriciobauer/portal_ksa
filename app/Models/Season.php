<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'name',
        'slug',
        'status',
        'start_date',
        'end_date',
        'is_current',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function seasonCategories(): HasMany
    {
        return $this->hasMany(SeasonCategory::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class)->orderBy('stage_number');
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }
}
