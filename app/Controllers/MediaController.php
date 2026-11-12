<?php

declare(strict_types=1);

namespace PrimeSales\Controllers;

use PrimeSales\Core\Request;

final class MediaController extends BaseController
{
    public function cover(Request $request, array $params): void
    {
        $id = filter_var($params['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $prototype = $id ? $this->prototypes->find((int) $id) : null;
        $filename = $prototype['cover_path'] ?? null;
        $path = is_string($filename) && basename($filename) === $filename
            ? STORAGE_PATH . '/covers/' . $filename
            : '';

        if ($path === '' || !is_file($path)) {
            $path = ROOT_PATH . '/assets/images/cover-fallback.svg';
            $mime = 'image/svg+xml';
        } else {
            $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
                'png' => 'image/png',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };
        }
        $this->sendFile($path, $mime, 'private, max-age=86400');
    }

    public function brand(Request $request, array $params): void
    {
        $kind = (string) ($params['kind'] ?? '');
        if (!in_array($kind, ['logo', 'favicon'], true)) {
            http_response_code(404);
            exit;
        }
        $filename = $this->settings->get($kind === 'logo' ? 'logo_filename' : 'favicon_filename');
        $path = $filename && basename($filename) === $filename
            ? STORAGE_PATH . '/branding/' . $filename
            : '';
        if ($path === '' || !is_file($path)) {
            $path = ROOT_PATH . '/assets/images/' . ($kind === 'logo' ? 'brand-mark.svg' : 'favicon.svg');
            $mime = 'image/svg+xml';
        } else {
            $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
                'png' => 'image/png',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };
        }
        $this->sendFile($path, $mime, 'private, no-cache, must-revalidate');
    }

    private function sendFile(string $path, string $mime, string $cacheControl): never
    {
        if (!is_file($path)) {
            http_response_code(404);
            exit;
        }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($path));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: ' . $cacheControl);
        readfile($path);
        exit;
    }
}
