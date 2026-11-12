<?php

declare(strict_types=1);

namespace PrimeSales\Core;

use PrimeSales\Models\UserModel;

final class Auth
{
    private ?array $cachedUser = null;
    private bool $resolved = false;

    public function __construct(private readonly UserModel $users)
    {
    }

    public function user(): ?array
    {
        if ($this->resolved) {
            return $this->cachedUser;
        }
        $this->resolved = true;
        $id = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            return null;
        }
        $this->cachedUser = $this->users->findActiveById((int) $id);
        $sessionVersion = (int) ($_SESSION['auth_version'] ?? 0);
        if (!$this->cachedUser || $sessionVersion !== (int) $this->cachedUser['auth_version']) {
            $this->logout();
            return null;
        }
        return $this->cachedUser;
    }

    public function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['auth_version'] = (int) ($user['auth_version'] ?? 1);
        unset($_SESSION['_old_input']);
        $this->cachedUser = $user;
        $this->resolved = true;
    }

    public function refreshSession(): void
    {
        $id = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            return;
        }
        $this->cachedUser = $this->users->findActiveById((int) $id);
        if (!$this->cachedUser) {
            $this->logout();
            return;
        }
        session_regenerate_id(true);
        $_SESSION['auth_version'] = (int) $this->cachedUser['auth_version'];
        $this->resolved = true;
    }

    public function logout(): void
    {
        $this->cachedUser = null;
        $this->resolved = true;
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
    }

    public function isAdmin(): bool
    {
        return ($this->user()['role'] ?? null) === 'ADMIN';
    }

    public function mustChangePassword(): bool
    {
        return (bool) ($this->user()['must_change_password'] ?? false);
    }
}
