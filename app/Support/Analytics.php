<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;

class Analytics
{
    protected const BLOCKED_PARAMETER_KEYS = [
        'address',
        'cpf',
        'email',
        'full_name',
        'name',
        'phone',
        'telephone',
        'whatsapp',
    ];

    public static function enabled(): bool
    {
        return (bool) config('analytics.ga4.enabled') && filled(self::measurementId());
    }

    public static function measurementId(): ?string
    {
        $measurementId = trim((string) config('analytics.ga4.measurement_id'));

        return $measurementId !== '' ? $measurementId : null;
    }

    public static function source(): string
    {
        return (string) config('analytics.ga4.source', 'website');
    }

    public static function pageEvent(string $name, array $params = []): array
    {
        return [
            'name' => $name,
            'params' => self::sanitizeParameters($params),
        ];
    }

    public static function flash(string $name, array $params = []): void
    {
        if (! self::enabled()) {
            return;
        }

        $events = Arr::wrap(Session::get('analytics.ga4.events', []));
        $events[] = self::pageEvent($name, $params);

        Session::flash('analytics.ga4.events', $events);
    }

    public static function flashedEvents(): array
    {
        return collect(Arr::wrap(Session::get('analytics.ga4.events', [])))
            ->map(function (mixed $event): ?array {
                if (! is_array($event) || blank($event['name'] ?? null)) {
                    return null;
                }

                return self::pageEvent((string) $event['name'], (array) ($event['params'] ?? []));
            })
            ->filter()
            ->values()
            ->all();
    }

    public static function sanitizeParameters(array $params): array
    {
        $normalized = [];

        foreach ($params as $key => $value) {
            $normalizedKey = mb_strtolower(trim((string) $key));

            if ($normalizedKey === '' || in_array($normalizedKey, self::BLOCKED_PARAMETER_KEYS, true)) {
                continue;
            }

            if (is_string($value)) {
                $normalized[$normalizedKey] = trim($value);

                continue;
            }

            if (is_bool($value) || is_int($value) || is_float($value) || is_null($value)) {
                $normalized[$normalizedKey] = $value;
            }
        }

        if (! array_key_exists('source', $normalized)) {
            $normalized['source'] = self::source();
        }

        return $normalized;
    }
}
