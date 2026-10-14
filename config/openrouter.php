<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenRouter API Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for connecting to OpenRouter, with DeepSeek V4 Flash as default.
    |
    */

    'api_key' => env('OPENROUTER_API_KEY', env('OPENROUTER_API')),

    'model' => env('OPENROUTER_MODEL', 'deepseek/deepseek-v4-flash'),

    'fallback_models' => array_filter(explode(',', (string) env('OPENROUTER_FALLBACK_MODELS', 'deepseek/deepseek-chat,meta-llama/llama-3.3-70b-instruct:free,poolside/laguna-s-2.1:free'))),

    'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),

    'timeout' => (int) env('OPENROUTER_TIMEOUT', 30),

    'app_url' => env('APP_URL', 'http://localhost:8000'),

    'app_name' => env('APP_NAME', 'Digitales Österreich 2030 - Bürgerservice'),
];
