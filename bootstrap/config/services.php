<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * This file is for storing the credentials for third party services such as Mailgun, Postmark, AWS and more.
 */

return [

    /*
     * Third Party Services
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
     * Gemini AI Configuration
     * 
     * API key isolation: stored only in .env, never committed to VCS.
     * Timeout and retry strategy for resilient API calls.
    */
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', ''),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'timeout' => (int) env('GEMINI_TIMEOUT', 30),
        'max_retries' => (int) env('GEMINI_MAX_RETRIES', 2),
    ],

    /* 
     *
     * Pexels API configuration for image search and retrieval.
     * API key stored securely in .env file. 
     *
     */
    'pexels' => [
        'api_key' => env('PEXELS_API_KEY', ''),
    ],
];
