<?php

declare(strict_types=1);

namespace PrimeSales\Core;

use PrimeSales\Middleware\AdminMiddleware;
use PrimeSales\Middleware\AuthMiddleware;
use PrimeSales\Middleware\CsrfMiddleware;

final class Router
{
    private array $routes = [];

    public function __construct(private readonly Auth $auth, private readonly View $view)
    {
    }

    public function get(string $pattern, callable $handler, bool $authRequired = true, bool $adminRequired = false): void
    {
        $this->add('GET', $pattern, $handler, $authRequired, $adminRequired);
    }

    public function post(string $pattern, callable $handler, bool $authRequired = true, bool $adminRequired = false): void
    {
        $this->add('POST', $pattern, $handler, $authRequired, $adminRequired);
    }

    private function add(string $method, string $pattern, callable $handler, bool $authRequired, bool $adminRequired): void
    {
        $quoted = preg_quote($pattern, '~');
        $regex = preg_replace_callback('/\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\}/', static fn (array $match): string => '(?P<' . $match[1] . '>[^/]+)', $quoted);
        $this->routes[] = [
            'method' => $method,
            'regex' => '~^' . $regex . '/?$~D',
            'handler' => $handler,
            'auth' => $authRequired,
            'admin' => $adminRequired,
        ];
    }

    public function dispatch(Request $request): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method() || !preg_match($route['regex'], $request->path(), $matches)) {
                continue;
            }

            if ($request->method() === 'POST' && !CsrfMiddleware::check($request)) {
                http_response_code(419);
                $this->view->render('errors/error', [
                    'pageTitle' => 'Session expirée',
                    'message' => 'Le formulaire a expiré. Actualisez la page puis réessayez.',
                    'statusCode' => 419,
                ], $this->auth->user() ? 'layouts/app' : 'layouts/guest');
                return;
            }

            if ($route['auth'] && !AuthMiddleware::check($this->auth)) {
                redirect('/login');
            }
            if ($route['admin'] && !AdminMiddleware::check($this->auth)) {
                http_response_code(403);
                $this->view->render('errors/error', [
                    'pageTitle' => 'Accès refusé',
                    'message' => 'Cette action est réservée à un administrateur.',
                    'statusCode' => 403,
                ], 'layouts/app');
                return;
            }

            $path = $request->path();
            if ($this->auth->user() && $this->auth->mustChangePassword() && !in_array($path, ['/profile', '/profile/password', '/logout'], true) && !str_starts_with($path, '/media/brand/')) {
                flash('info', 'Choisissez un nouveau mot de passe pour continuer.');
                redirect('/profile');
            }

            $params = array_filter($matches, static fn ($key): bool => is_string($key), ARRAY_FILTER_USE_KEY);
            ($route['handler'])($request, $params);
            return;
        }

        http_response_code(404);
        $this->view->render('errors/error', [
            'pageTitle' => 'Page introuvable',
            'message' => 'Cette page n’existe pas ou a été déplacée.',
            'statusCode' => 404,
        ], $this->auth->user() ? 'layouts/app' : 'layouts/guest');
    }
}
