<?php

declare(strict_types=1);

namespace PrimeSales\Core;

use RuntimeException;

final class View
{
    public function render(string $template, array $data = [], string $layout = 'layouts/app'): void
    {
        $viewFile = APP_PATH . '/Views/' . $template . '.php';
        $layoutFile = APP_PATH . '/Views/' . $layout . '.php';
        if (!is_file($viewFile) || !is_file($layoutFile)) {
            throw new RuntimeException('View not found.');
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = (string) ob_get_clean();
        require $layoutFile;
    }

    public function renderStandalone(string $template, array $data = []): void
    {
        $viewFile = APP_PATH . '/Views/' . $template . '.php';
        if (!is_file($viewFile)) {
            throw new RuntimeException('View not found.');
        }
        extract($data, EXTR_SKIP);
        require $viewFile;
    }
}
