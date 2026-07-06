<?php

declare(strict_types=1);

namespace App;

final class View
{
    public static function render(string $template, array $data = [], ?string $layout = 'layout'): void
    {
        $viewsPath = dirname(__DIR__) . '/views';
        $file = $viewsPath . '/' . $template . '.php';

        if (!is_file($file)) {
            http_response_code(500);
            echo "View não encontrada: {$template}";
            return;
        }

        $render = function () use ($file, $data): string {
            extract($data, EXTR_SKIP);
            ob_start();
            require $file;
            return ob_get_clean();
        };

        if ($layout === null) {
            echo $render();
            return;
        }

        $content = $render();
        $layoutFile = $viewsPath . '/' . $layout . '.php';
        extract($data, EXTR_SKIP);
        require $layoutFile;
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
