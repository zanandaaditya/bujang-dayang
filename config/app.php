<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => env_value('APP_NAME', 'Bujang Dayang Bangka Belitung'),
        'env' => env_value('APP_ENV', 'production'),
        'debug' => (bool) env_value('APP_DEBUG', false),
        'url' => env_value('APP_URL', 'http://localhost'),
        'timezone' => env_value('APP_TIMEZONE', 'Asia/Jakarta'),
        'install_key' => env_value('APP_INSTALL_KEY', ''),
        'encryption_key' => env_value('APP_ENCRYPTION_KEY', ''),
        'cron_secret' => env_value('CRON_SECRET', ''),
    ],
    'database' => [
        'host' => env_value('DB_HOST', 'localhost'),
        'port' => (int) env_value('DB_PORT', 3306),
        'database' => env_value('DB_DATABASE', 'bujang_dayang'),
        'username' => env_value('DB_USERNAME', 'root'),
        'password' => env_value('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
    ],
    'xendit' => [
        'api_url' => rtrim((string) env_value('XENDIT_API_URL', 'https://api.xendit.co'), '/'),
        'secret_key' => env_value('XENDIT_SECRET_KEY', ''),
        'business_id' => env_value('XENDIT_BUSINESS_ID', ''),
        'webhook_token' => env_value('XENDIT_WEBHOOK_TOKEN', ''),
        'allowed_channels' => array_values(array_filter(array_map('trim', explode(',', (string) env_value('XENDIT_ALLOWED_CHANNELS', 'QRIS,DANA,OVO,SHOPEEPAY'))))),
        'expiry_minutes' => (int) env_value('PAYMENT_EXPIRY_MINUTES', 30),
    ],
];
