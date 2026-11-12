<?php

declare(strict_types=1);

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $path = '/' . ltrim($path, '/');
        return APP_BASE_PATH . ($path === '/' ? '/' : $path);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('/assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path, int $status = 302): never
    {
        header('Location: ' . (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') ? $path : url($path)), true, $status);
        exit;
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(\PrimeSales\Core\Csrf::token()) . '">';
    }
}

if (!function_exists('flash')) {
    function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('consume_flash')) {
    function consume_flash(): array
    {
        $messages = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return is_array($messages) ? $messages : [];
    }
}

if (!function_exists('remember_input')) {
    function remember_input(array $input): void
    {
        unset($input['password'], $input['password_confirmation'], $input['_token']);
        $_SESSION['_old_input'] = $input;
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['_old_input'][$key] ?? $default;
    }
}

if (!function_exists('clear_old_input')) {
    function clear_old_input(): void
    {
        unset($_SESSION['_old_input']);
    }
}

if (!function_exists('slugify')) {
    function slugify(string $value): string
    {
        $value = trim($value);
        if (function_exists('iconv')) {
            $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if ($transliterated !== false) {
                $value = $transliterated;
            }
        }
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        return trim($value, '-') ?: 'element-' . bin2hex(random_bytes(3));
    }
}

if (!function_exists('initials')) {
    function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $first = $parts[0] ?? 'P';
        $last = count($parts) > 1 ? $parts[count($parts) - 1] : '';
        $result = mb_substr($first, 0, 1, 'UTF-8') . ($last !== '' ? mb_substr($last, 0, 1, 'UTF-8') : '');
        return mb_strtoupper($result, 'UTF-8');
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'd/m/Y'): string
    {
        if (!$date) {
            return '—';
        }
        try {
            return (new DateTimeImmutable($date))->format($format);
        } catch (Throwable) {
            return '—';
        }
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime(?string $date): string
    {
        return $date ? format_date($date, 'd/m/Y à H:i') : 'Jamais';
    }
}

if (!function_exists('icon')) {
    function icon(string $name, string $class = ''): string
    {
        $paths = [
            'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>',
            'layers' => '<path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5M3 16l9 5 9-5"/>',
            'shapes' => '<circle cx="8" cy="8" r="4.5"/><path d="M14 14h7v7h-7z"/><path d="m17.5 3 4 7h-8l4-7Z"/>',
            'link' => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 1 0 7.07 7.07l1.71-1.71"/>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
            'settings' => '<path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/><path d="m19.4 15 .1.1a1.8 1.8 0 1 1-2.55 2.55l-.1-.1a1.8 1.8 0 0 0-3.07 1.28v.28a1.8 1.8 0 1 1-3.6 0v-.15a1.8 1.8 0 0 0-3.07-1.27l-.1.1a1.8 1.8 0 1 1-2.55-2.55l.1-.1a1.8 1.8 0 0 0-1.28-3.07H3a1.8 1.8 0 1 1 0-3.6h.15a1.8 1.8 0 0 0 1.27-3.07l-.1-.1a1.8 1.8 0 1 1 2.55-2.55l.1.1a1.8 1.8 0 0 0 3.07-1.28V1.3a1.8 1.8 0 1 1 3.6 0v.15a1.8 1.8 0 0 0 3.07 1.27l.1-.1a1.8 1.8 0 1 1 2.55 2.55l-.1.1a1.8 1.8 0 0 0 1.28 3.07h.28a1.8 1.8 0 1 1 0 3.6h-.15A1.8 1.8 0 0 0 19.4 15Z" transform="translate(2 2) scale(.83)"/>',
            'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'logout' => '<path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h6a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3h-6"/>',
            'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
            'plus' => '<path d="M12 5v14M5 12h14"/>',
            'arrow' => '<path d="M7 17 17 7M7 7h10v10"/>',
            'arrow-right' => '<path d="M5 12h14M12 5l7 7-7 7"/>',
            'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
            'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
            'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
            'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
            'close' => '<path d="m18 6-12 12M6 6l12 12"/>',
            'sparkle' => '<path d="m12 3 1.9 5.8L20 11l-6.1 2.2L12 19l-2-5.8L4 11l6-2.2L12 3Z"/><path d="m19 14 1.1 2.2L22 17l-1.9.8L19 20l-1-2.2-2-.8 2-.8 1-2.2Z"/>',
            'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'filter' => '<path d="M4 6h16M7 12h10m-7 6h4"/>',
            'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/>',
            'trash' => '<path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m4 4v6m6-6v6"/>',
            'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
            'check' => '<path d="m5 12 4 4L19 6"/>',
            'shield' => '<path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/><path d="m9 12 2 2 4-4"/>',
            'external' => '<path d="M14 3h7v7M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
            'globe' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/>',
            'building' => '<path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M15 9h.01M9 13h.01M15 13h.01M10 21v-4h4v4"/>',
            'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
            'lock' => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3"/>',
            'image' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/>',
        ];
        $body = $paths[$name] ?? $paths['sparkle'];
        return '<svg class="icon ' . e($class) . '" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $body . '</svg>';
    }
}
