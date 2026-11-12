<?php

declare(strict_types=1);

namespace PrimeSales\Services;

final class ScreenshotService
{
    public function capture(string $url): ?string
    {
        $config = $GLOBALS['app_config']['screenshot'] ?? [];
        $accessKey = trim((string) ($config['access_key'] ?? ''));
        $endpoint = (string) ($config['endpoint'] ?? 'https://api.screenshotone.com/take');
        if ($accessKey === '' || !$this->isSafeHttpUrl($url)) {
            return null;
        }

        $captureUrl = $endpoint . '?' . http_build_query([
            'access_key' => $accessKey,
            'url' => $url,
            'format' => 'jpg',
            'image_quality' => 78,
            'viewport_width' => 1440,
            'viewport_height' => 900,
            'device_scale_factor' => 1,
            'block_ads' => 'true',
            'block_cookie_banners' => 'true',
            'timeout' => 30,
        ], '', '&', PHP_QUERY_RFC3986);

        $image = $this->request($captureUrl, (int) ($config['timeout'] ?? 75));
        if ($image === null || strlen($image) > 8 * 1024 * 1024) {
            return null;
        }

        $imageInfo = @getimagesizefromstring($image);
        if (!is_array($imageInfo) || !in_array($imageInfo['mime'] ?? '', ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }
        $extension = match ($imageInfo['mime']) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $directory = STORAGE_PATH . '/covers';
        if (!is_dir($directory) && !@mkdir($directory, 0750, true) && !is_dir($directory)) {
            return null;
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        if (file_put_contents($directory . '/' . $filename, $image, LOCK_EX) === false) {
            return null;
        }
        @chmod($directory . '/' . $filename, 0640);
        return $filename;
    }

    public function delete(?string $filename): void
    {
        if (!$filename || basename($filename) !== $filename) {
            return;
        }
        $path = STORAGE_PATH . '/covers/' . $filename;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function request(string $url, int $timeout): ?string
    {
        if (function_exists('curl_init')) {
            $handle = curl_init($url);
            if ($handle === false) {
                return null;
            }
            curl_setopt_array($handle, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT => max(15, min($timeout, 120)),
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HTTPHEADER => ['Accept: image/jpeg,image/png,image/webp'],
                CURLOPT_USERAGENT => 'PrimeSalesPortfolio/1.0',
            ]);
            $body = curl_exec($handle);
            $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
            $contentType = (string) curl_getinfo($handle, CURLINFO_CONTENT_TYPE);
            curl_close($handle);
            if (!is_string($body) || $status < 200 || $status >= 300 || !str_starts_with(strtolower($contentType), 'image/')) {
                return null;
            }
            return $body;
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => max(15, min($timeout, 120)),
                'ignore_errors' => true,
                'follow_location' => 0,
                'header' => "Accept: image/jpeg,image/png,image/webp\r\nUser-Agent: PrimeSalesPortfolio/1.0\r\n",
            ],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $body = @file_get_contents($url, false, $context);
        $headers = $http_response_header ?? [];
        $status = 0;
        foreach ($headers as $header) {
            if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $match)) {
                $status = (int) $match[1];
            }
        }
        return is_string($body) && $status >= 200 && $status < 300 && strlen($body) <= 8 * 1024 * 1024 ? $body : null;
    }

    private function isSafeHttpUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (!is_array($parts) || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true) || empty($parts['host'])) {
            return false;
        }
        $host = strtolower(rtrim((string) $parts['host'], '.'));
        if ($host === 'localhost' || str_ends_with($host, '.localhost') || str_ends_with($host, '.local')) {
            return false;
        }
        if (filter_var($host, FILTER_VALIDATE_IP) && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }
        return true;
    }
}
