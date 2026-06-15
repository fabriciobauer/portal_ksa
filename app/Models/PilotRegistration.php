<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PilotRegistration extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'full_name',
        'whatsapp',
        'cpf',
        'email',
        'address',
        'has_kart_experience',
        'has_championship_experience',
        'weight_kg',
        'age',
        'pilot_registration_category_id',
        'payment_status',
        'paid_at',
        'converted_to_pilot_at',
        'pilot_id',
        'metadata',
        'notes',
        'is_archived',
        'archived_at',
        'created_by',
        'updated_by',
        'payment_marked_by',
        'payment_unmarked_by',
        'archived_by',
    ];

    protected function casts(): array
    {
        return [
            'has_kart_experience' => 'boolean',
            'has_championship_experience' => 'boolean',
            'weight_kg' => 'decimal:2',
            'age' => 'integer',
            'payment_status' => 'boolean',
            'paid_at' => 'datetime',
            'converted_to_pilot_at' => 'datetime',
            'metadata' => 'array',
            'is_archived' => 'boolean',
            'archived_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PilotRegistrationCategory::class, 'pilot_registration_category_id');
    }

    public function pilot(): BelongsTo
    {
        return $this->belongsTo(Pilot::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function paymentMarkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_marked_by');
    }

    public function paymentUnmarkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_unmarked_by');
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}
