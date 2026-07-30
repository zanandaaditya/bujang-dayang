<?php

declare(strict_types=1);

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function verify(string $token): void
    {
        if (!hash_equals((string) ($_SESSION['_csrf'] ?? ''), $token)) {
            http_response_code(419);
            exit('Sesi formulir telah berakhir. Muat ulang halaman dan coba kembali.');
        }
    }
}
