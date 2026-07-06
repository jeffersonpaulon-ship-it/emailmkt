<?php

declare(strict_types=1);

/**
 * Carrega variáveis de um arquivo .env simples (KEY=VALUE por linha) para o ambiente,
 * sem depender de nenhuma biblioteca externa.
 */
function load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"");
        if ($key !== '' && getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

load_env(dirname(__DIR__) . '/.env');

function env(string $key, mixed $default = null): mixed
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

return [
    'app' => [
        'env' => env('APP_ENV', 'production'),
        'url' => rtrim(env('APP_URL', 'http://localhost:8000'), '/'),
        'key' => env('APP_KEY', 'insecure-default-key'),
    ],
    'db' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'name' => env('DB_NAME', 'emailmkt'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', ''),
    ],
    'smtp' => [
        'host' => env('SMTP_HOST', ''),
        'port' => (int) env('SMTP_PORT', 587),
        'encryption' => env('SMTP_ENCRYPTION', 'tls'),
        'username' => env('SMTP_USERNAME', ''),
        'password' => env('SMTP_PASSWORD', ''),
        'from_email' => env('SMTP_FROM_EMAIL', 'no-reply@example.com'),
        'from_name' => env('SMTP_FROM_NAME', 'RSVP Events'),
    ],
];
