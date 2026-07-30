<?php

declare(strict_types=1);

final class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $stmt = db()->prepare("SELECT * FROM users WHERE email = ? AND role = 'superadmin' AND is_active = 1 LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        session_regenerate_id(true);
        $_SESSION['admin_user_id'] = (int) $user['id'];
        db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);
        audit('LOGIN', 'users', (int) $user['id']);
        return true;
    }

    public static function user(): ?array
    {
        static $user = false;
        if ($user !== false) return $user ?: null;
        $id = self::id();
        if (!$id) return $user = null;
        $stmt = db()->prepare('SELECT id, name, email, role, is_active, last_login_at FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $user = ($stmt->fetch() ?: null);
    }

    public static function id(): ?int
    {
        return isset($_SESSION['admin_user_id']) ? (int) $_SESSION['admin_user_id'] : null;
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    public static function requireAdmin(): void
    {
        if (!self::check()) {
            flash('warning', 'Silakan masuk sebagai Superadmin.');
            redirect('admin/login.php');
        }
    }

    public static function logout(): void
    {
        $id = self::id();
        if ($id) audit('LOGOUT', 'users', $id);
        unset($_SESSION['admin_user_id']);
        session_regenerate_id(true);
    }
}
