<?php

declare(strict_types=1);

namespace PrimeSales\Controllers;

use PrimeSales\Core\Request;
use Throwable;

final class AuthController extends BaseController
{
    public function showLogin(Request $request): void
    {
        if ($this->auth->user()) {
            redirect('/dashboard');
        }
        $this->guestPage('auth/login', ['oldEmail' => old('email', '')]);
        clear_old_input();
    }

    public function login(Request $request): void
    {
        $email = mb_strtolower(trim((string) $request->input('email', '')), 'UTF-8');
        $password = (string) $request->input('password', '');
        $email = mb_substr($email, 0, 190, 'UTF-8');
        remember_input(['email' => $email]);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            flash('danger', 'Vérifiez votre adresse e-mail et votre mot de passe.');
            redirect('/login');
        }

        $attempts = $this->db->prepare('SELECT COUNT(*) FROM login_logs WHERE ip_address = :ip AND success = 0 AND created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)');
        $attempts->execute(['ip' => $request->ip()]);
        if ((int) $attempts->fetchColumn() >= 8) {
            flash('warning', 'Trop de tentatives. Réessayez dans quelques minutes.');
            redirect('/login');
        }

        $user = $this->users->findByEmail($email);
        if (!$user || !(bool) $user['is_active'] || !password_verify($password, (string) $user['password_hash'])) {
            $this->loginLogs->record(null, $email, $request->ip(), $request->userAgent(), false);
            flash('danger', 'Identifiants incorrects ou compte suspendu.');
            redirect('/login');
        }

        $this->loginLogs->record((int) $user['id'], $email, $request->ip(), $request->userAgent(), true);
        $update = $this->db->prepare('UPDATE users SET last_login = NOW() WHERE id = :id');
        $update->execute(['id' => (int) $user['id']]);
        $this->auth->login($user);
        clear_old_input();
        redirect(!empty($user['must_change_password']) ? '/profile' : '/dashboard');
    }

    public function logout(Request $request): void
    {
        $this->auth->logout();
        redirect('/login');
    }
}
