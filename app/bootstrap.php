<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Csrf.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/LeaderboardService.php';
require_once __DIR__ . '/VoteService.php';
require_once __DIR__ . '/XenditService.php';
require_once __DIR__ . '/RateLimiter.php';

$config = app_config();
date_default_timezone_set((string) $config['app']['timezone']);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('bd_evoting_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => str_starts_with((string) $config['app']['url'], 'https://'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (!(bool) $config['app']['debug']) {
    ini_set('display_errors', '0');
}
