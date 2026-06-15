<?php

namespace App\Support;

class TimeFormatter
{
    public static function normalizeLapTime(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (preg_match('/^\d+$/', $value)) {
            $digits = preg_replace('/\D+/', '', $value) ?? '';

            if ($digits === '') {
                return null;
            }

            $milliseconds = substr($digits, -3);
            $secondsSource = substr($digits, 0, -3);
            $seconds = $secondsSource === '' ? '00' : substr($secondsSource, -2);
            $minutes = $secondsSource === '' ? '0' : substr($secondsSource, 0, -2);

            return sprintf('%d:%02d.%03d', (int) ($minutes === '' ? '0' : $minutes), (int) $seconds, (int) $milliseconds);
        }

        if (! preg_match('/^(?:(\d+):)?(\d{1,2})\.(\d{1,3})$/', $value, $matches)) {
            return null;
        }

        return sprintf(
            '%d:%02d.%03d',
            (int) ($matches[1] ?? 0),
            (int) $matches[2],
            (int) str_pad($matches[3], 3, '0', STR_PAD_LEFT),
        );
    }

    public static function parseLapTime(?string $value): ?int
    {
        $normalized = self::normalizeLapTime($value);

        if ($normalized === null) {
            return null;
        }

        preg_match('/^(?:(\d+):)?(\d{1,2})\.(\d{3})$/', $normalized, $matches);

        $minutes = (int) ($matches[1] ?? 0);
        $seconds = (int) $matches[2];
        $milliseconds = (int) $matches[3];

        return ($minutes * 60 * 1000) + ($seconds * 1000) + $milliseconds;
    }

    public static function formatLapTime(?int $milliseconds): ?string
    {
        if ($milliseconds === null) {
            return null;
        }

        $minutes = intdiv($milliseconds, 60000);
        $seconds = intdiv($milliseconds % 60000, 1000);
        $fraction = $milliseconds % 1000;

        return sprintf('%d:%02d.%03d', $minutes, $seconds, $fraction);
    }
}
