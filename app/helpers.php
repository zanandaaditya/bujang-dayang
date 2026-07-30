<?php

declare(strict_types=1);

function env_value(string $key, mixed $default = null): mixed
{
    static $values = null;
    if ($values === null) {
        $values = [];
        $path = dirname(__DIR__) . '/.env';
        if (is_file($path)) {
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$name, $value] = array_map('trim', explode('=', $line, 2));
                $value = trim($value, "\"'");
                $values[$name] = $value;
            }
        }
    }

    $value = $values[$key] ?? getenv($key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return match (strtolower((string) $value)) {
        'true' => true,
        'false' => false,
        'null' => null,
        default => $value,
    };
}

function app_config(?string $key = null, mixed $default = null): mixed
{
    static $config = null;
    $config ??= require dirname(__DIR__) . '/config/app.php';
    if ($key === null) {
        return $config;
    }

    $segments = explode('.', $key);
    $value = $config;
    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}

function db(): PDO
{
    return Database::connection();
}

function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim((string) app_config('app.url', ''), '/');
    return $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function upload_url(?string $path, string $fallback = 'images/placeholder-bujang.svg'): string
{
    if (!$path) {
        return asset($fallback);
    }
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }
    return url(ltrim($path, '/'));
}

function redirect(string $path): never
{
    header('Location: ' . (preg_match('#^https?://#', $path) ? $path : url($path)));
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function input(string $key, mixed $default = null): mixed
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

function old(string $key, mixed $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = compact('type', 'message');
}

function pull_flashes(): array
{
    $flashes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flashes;
}

function remember_old_input(): void
{
    $_SESSION['_old'] = $_POST;
}

function clear_old_input(): void
{
    unset($_SESSION['_old']);
}

function rupiah(int|float|string $amount): string
{
    return 'Rp' . number_format((float) $amount, 0, ',', '.');
}

function points(int|float|string $amount): string
{
    return number_format((float) $amount, 0, ',', '.') . ' poin';
}

function support_percentage(int|float|string $value, int $decimals = 2): string
{
    return number_format((float) $value, $decimals, ',', '.') . '%';
}

function bonus_percentage(int|float|string $base, int|float|string $bonus): float
{
    $baseValue = (float) $base;
    if ($baseValue <= 0) return 0.0;
    return round(((float) $bonus / $baseValue) * 100, 2);
}

function indonesia_date(?string $date, bool $withTime = false): string
{
    if (!$date) return '-';
    $months = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $dt = new DateTimeImmutable($date);
    $text = $dt->format('d') . ' ' . $months[(int) $dt->format('n')] . ' ' . $dt->format('Y');
    return $withTime ? $text . ' ' . $dt->format('H:i') . ' WIB' : $text;
}

function mask_phone(?string $phone): string
{
    if (!$phone) return '-';
    $length = strlen($phone);
    if ($length <= 7) return str_repeat('*', $length);
    return substr($phone, 0, 4) . str_repeat('*', max(3, $length - 7)) . substr($phone, -3);
}

function normalize_phone(string $phone): string
{
    $phone = preg_replace('/\D+/', '', $phone) ?? '';
    if (str_starts_with($phone, '0')) {
        $phone = '62' . substr($phone, 1);
    }
    if (!str_starts_with($phone, '62')) {
        $phone = '62' . $phone;
    }
    return '+' . $phone;
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text) ?? '';
    return trim($text, '-');
}

function encrypt_value(string $value): string
{
    $rawKey = (string) app_config('app.encryption_key', '');
    if ($rawKey === '') return $value;
    $rawKey = str_starts_with($rawKey, 'base64:') ? (base64_decode(substr($rawKey, 7), true) ?: substr($rawKey, 7)) : $rawKey;
    $key = hash('sha256', $rawKey, true);
    $iv = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt($value, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($cipher === false) throw new RuntimeException('Enkripsi data gagal.');
    return 'enc:' . base64_encode($iv . $tag . $cipher);
}

function decrypt_value(?string $value): string
{
    if ($value === null || !str_starts_with($value, 'enc:')) return (string) $value;
    $rawKey = (string) app_config('app.encryption_key', '');
    if ($rawKey === '') return '';
    $rawKey = str_starts_with($rawKey, 'base64:') ? (base64_decode(substr($rawKey, 7), true) ?: substr($rawKey, 7)) : $rawKey;
    $key = hash('sha256', $rawKey, true);
    $data = base64_decode(substr($value, 4), true);
    if ($data === false || strlen($data) < 29) return '';
    $iv = substr($data, 0, 12); $tag = substr($data, 12, 16); $cipher = substr($data, 28);
    $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    return $plain === false ? '' : $plain;
}

function setting(string $key, mixed $default = null): mixed
{
    static $settings = null;
    if ($settings === null) {
        $settings = [];
        try {
            foreach (db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll() as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable) {
            return $default;
        }
    }
    return $settings[$key] ?? $default;
}

function active_event(): ?array
{
    static $event = false;
    if ($event !== false) return $event ?: null;
    try {
        $stmt = db()->query("SELECT * FROM events WHERE status IN ('PUBLISHED','VOTING_ACTIVE','VOTING_CLOSED') ORDER BY year DESC, id DESC LIMIT 1");
        $event = $stmt->fetch() ?: null;
    } catch (Throwable) {
        $event = null;
    }
    return $event;
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function verify_csrf(): void
{
    Csrf::verify((string) ($_POST['_token'] ?? ''));
}

function audit(string $action, string $entityType = '', ?int $entityId = null, ?array $old = null, ?array $new = null): void
{
    try {
        $stmt = db()->prepare('INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            Auth::id(), $action, $entityType, $entityId,
            $old ? json_encode($old, JSON_UNESCAPED_UNICODE) : null,
            $new ? json_encode($new, JSON_UNESCAPED_UNICODE) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]);
    } catch (Throwable) {
    }
}

function save_upload(string $field, string $directory, array $allowed = ['image/jpeg','image/png','image/webp']): ?string
{
    if (!isset($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$field];
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload gagal. Silakan ulangi.');
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Ukuran file maksimal 5 MB.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!in_array($mime, $allowed, true)) {
        throw new RuntimeException('Format file harus JPG, PNG, atau WEBP.');
    }
    $ext = match ($mime) {'image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp', default=>'bin'};
    $name = bin2hex(random_bytes(12)) . '.' . $ext;
    $targetDir = dirname(__DIR__) . '/uploads/' . trim($directory, '/');
    if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        throw new RuntimeException('Folder upload tidak dapat dibuat.');
    }
    if (!move_uploaded_file($file['tmp_name'], $targetDir . '/' . $name)) {
        throw new RuntimeException('File tidak dapat disimpan.');
    }
    return 'uploads/' . trim($directory, '/') . '/' . $name;
}
