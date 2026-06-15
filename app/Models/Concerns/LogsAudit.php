<?php

namespace App\Models\Concerns;

use App\Services\AuditLogService;

trait LogsAudit
{
    protected static function bootLogsAudit(): void
    {
        static::created(function ($model): void {
            static::writeAudit($model, 'created');
        });

        static::updated(function ($model): void {
            static::writeAudit($model, 'updated');
        });

        static::deleted(function ($model): void {
            static::writeAudit($model, 'deleted');
        });
    }

    protected static function writeAudit(object $model, string $action): void
    {
        if ($model instanceof \App\Models\AuditLog) {
            return;
        }

        try {
            app(AuditLogService::class)->logModelEvent($model, $action);
        } catch (\Throwable) {
            // Falhas de auditoria não devem bloquear a operação principal.
        }
    }
}
