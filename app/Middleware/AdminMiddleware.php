<?php

declare(strict_types=1);

namespace PrimeSales\Middleware;

use PrimeSales\Core\Auth;

final class AdminMiddleware
{
    public static function check(Auth $auth): bool
    {
        return $auth->isAdmin();
    }
}
