<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeasonCategoryRegistration extends Model
{
    use HasFactory;
    use LogsAudit;

    public const TYPE_ANNUAL = 'annual';
    public const TYPE_SINGLE = 'single';

    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_WAITING = 'waiting';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'season_category_id',
        'pilot_id',
        'registration_type',
        'status',
        'registered_at',
        'confirmed_at',
        'waitlist_order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'date',
            'confirmed_at' => 'datetime',
        ];
    }

    public function seasonCategory(): BelongsTo
    {
        return $this->belongsTo(SeasonCategory::class);
    }

    public function pilot(): BelongsTo
    {
        return $this->belongsTo(Pilot::class);
    }

    public function stageEntries(): HasMany
    {
        return $this->hasMany(StageCategoryEntry::class);
    }

    public function isAnnual(): bool
    {
        return $this->registration_type === self::TYPE_ANNUAL;
    }
}
