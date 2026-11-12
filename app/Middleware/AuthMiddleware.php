<?php

declare(strict_types=1);

namespace PrimeSales\Middleware;

use PrimeSales\Core\Auth;

final class AuthMiddleware
{
    public static function check(Auth $auth): bool
    {
        return $auth->user() !== null;
    }
}
