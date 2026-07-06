<?php

declare(strict_types=1);

namespace App;

use App\Models\User;

final class Auth
{
    private static ?array $userCache = null;

    public static function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);

        if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        self::$userCache = $user;

        return true;
    }

    public static function logout(): void
    {
        self::$userCache = null;
        $_SESSION = [];
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (self::$userCache === null) {
            self::$userCache = User::find((int) $_SESSION['user_id']);
        }

        return self::$userCache;
    }

    public static function role(): ?string
    {
        return self::user()['role'] ?? null;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole(string|array $roles): void
    {
        self::requireLogin();
        $roles = is_array($roles) ? $roles : [$roles];

        if (!in_array(self::role(), $roles, true)) {
            http_response_code(403);
            require dirname(__DIR__) . '/views/errors/403.php';
            exit;
        }
    }
}
