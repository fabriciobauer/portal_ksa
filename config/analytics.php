<?php

return [
    'ga4' => [
        'enabled' => (bool) env('GA4_ENABLED', false),
        'measurement_id' => env('GA4_MEASUREMENT_ID'),
        'source' => env('GA4_SOURCE', 'website'),
    ],
];
