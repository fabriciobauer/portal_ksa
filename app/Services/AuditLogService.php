<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public function logModelEvent(Model $model, string $action): void
    {
        $oldValues = [];
        $newValues = [];

        if ($action === 'updated') {
            $oldValues = Arr::only($model->getOriginal(), array_keys($model->getChanges()));
            $newValues = $model->getChanges();
        } elseif ($action === 'created') {
            $newValues = $model->getAttributes();
        } elseif ($action === 'deleted') {
            $oldValues = $model->getOriginal();
        }

        $this->log(
            action: $action,
            auditable: $model,
            description: class_basename($model).' '.$this->humanizeAction($action),
            oldValues: $oldValues,
            newValues: $newValues,
        );
    }

    public function log(
        string $action,
        ?Model $auditable = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        array $metadata = [],
    ): AuditLog {
        return AuditLog::query()->create([
            'user_id' => Auth::id(),
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    protected function humanizeAction(string $action): string
    {
        return match ($action) {
            'created' => 'criado',
            'updated' => 'atualizado',
            'deleted' => 'removido',
            default => $action,
        };
    }
}
