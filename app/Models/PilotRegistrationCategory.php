<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PilotRegistrationCategory extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'sort_order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(PilotRegistration::class);
    }
}
