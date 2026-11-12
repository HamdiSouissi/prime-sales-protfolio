<?php

declare(strict_types=1);

namespace PrimeSales\Middleware;

use PrimeSales\Core\Csrf;
use PrimeSales\Core\Request;

final class CsrfMiddleware
{
    public static function check(Request $request): bool
    {
        $token = $request->input('_token');
        return Csrf::verify(is_string($token) ? $token : null);
    }
}
