<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * This file is part of the Mini LMS application.
 */

return [

    'name' => env('APP_NAME', 'Mini LMS'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'Europe/Paris',
    'locale' => 'fr',
    'fallback_locale' => 'fr',
    'faker_locale' => 'fr_FR',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'maintenance' => [
        'driver' => 'file',
    ],

];
