<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'group_name',
        'key',
        'label',
        'type',
        'value',
        'description',
    ];

    public function getTypedValueAttribute(): mixed
    {
        $raw = $this->getRawOriginal('value');

        if ($raw === null) {
            return null;
        }

        $value = json_decode((string) $raw, true);
        $value = json_last_error() === JSON_ERROR_NONE ? $value : $this->getRawOriginal('value');

        return match ($this->type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => $value,
            default => $value,
        };
    }

    public function setValueAttribute(mixed $value): void
    {
        $this->attributes['value'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
