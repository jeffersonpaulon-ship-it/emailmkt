<?php

declare(strict_types=1);

namespace App;

/**
 * Gera URLs cientes de um "base path" opcional, para permitir rodar o app
 * numa subpasta do domínio (ex: https://seusite.com/rsvp/) em vez da raiz.
 * O base path é derivado do caminho presente em APP_URL.
 */
final class Url
{
    private static ?string $basePath = null;

    public static function basePath(): string
    {
        if (self::$basePath === null) {
            $path = (string) parse_url(Config::get('app.url', ''), PHP_URL_PATH);
            self::$basePath = rtrim($path, '/');
        }

        return self::$basePath;
    }

    /** Caminho raiz-relativo (para href, action, header Location): /rsvp/login */
    public static function to(string $path): string
    {
        return self::basePath() . '/' . ltrim($path, '/');
    }

    /** URL absoluta completa (para links em e-mails): https://seusite.com/rsvp/login */
    public static function full(string $path): string
    {
        return rtrim(Config::get('app.url', ''), '/') . '/' . ltrim($path, '/');
    }
}
