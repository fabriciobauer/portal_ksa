<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pilot extends Model
{
    use HasFactory;
    use LogsAudit;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'cpf',
        'nickname',
        'photo_path',
        'birth_date',
        'phone',
        'email',
        'city',
        'address',
        'base_weight',
        'notes',
        'metadata',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'base_weight' => 'decimal:2',
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(SeasonCategoryRegistration::class);
    }

    public function publicRegistrations(): HasMany
    {
        return $this->hasMany(PilotRegistration::class);
    }

    public function stageEntries(): HasMany
    {
        return $this->hasMany(StageCategoryEntry::class);
    }

    public function championshipStandings(): HasMany
    {
        return $this->hasMany(ChampionshipStanding::class);
    }

    public function displayName(): string
    {
        return $this->nickname ? "{$this->name} ({$this->nickname})" : $this->name;
    }
}
