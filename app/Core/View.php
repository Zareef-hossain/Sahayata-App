<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Renders PHP templates from app/Views.
 *
 *   View::render('dev/components', ['title' => 'Components'])
 *     -> renders app/Views/dev/components.php inside app/Views/layouts/app.php
 */
final class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/app'): string
    {
        $content = self::renderFile($view, $data);
        if ($layout === null) {
            return $content;
        }
        return self::renderFile($layout, [...$data, 'content' => $content]);
    }

    /** Render one reusable component from app/Views/components. */
    public static function component(string $name, array $props = []): string
    {
        return self::renderFile('components/' . $name, $props);
    }

    private static function renderFile(string $view, array $data): string
    {
        $file = APP_ROOT . '/app/Views/' . $view . '.php';
        if (!is_file($file)) {
            throw new RuntimeException("View not found: {$view}");
        }

        // Each template gets its own variables ($title, $content, ...).
        $render = static function (string $__file, array $__data): void {
            extract($__data, EXTR_SKIP);
            require $__file;
        };

        ob_start();
        try {
            $render($file, $data);
        } catch (\Throwable $error) {
            ob_end_clean();
            throw $error;
        }
        return (string) ob_get_clean();
    }
}
