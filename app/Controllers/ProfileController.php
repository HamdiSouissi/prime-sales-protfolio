<?php

declare(strict_types=1);

namespace PrimeSales\Controllers;

use PDOException;
use PrimeSales\Core\Request;

final class ProfileController extends BaseController
{
    public function index(Request $request): void
    {
        $user = $this->auth->user();
        $this->page('profile/index', ['profile' => $user], 'Mon profil', 'profile');
    }

    public function update(Request $request): void
    {
        $user = $this->auth->user();
        $name = $this->textInput($request->input('name'), 120);
        $email = mb_strtolower(trim((string) $request->input('email', '')), 'UTF-8');
        $currentPassword = (string) $request->input('current_password', '');
        if (mb_strlen($name, 'UTF-8') < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email, 'UTF-8') > 190) {
            flash('danger', 'Vérifiez votre nom et votre adresse e-mail.');
            redirect('/profile');
        }
        $account = $this->users->findByEmail((string) $user['email']);
        if (!$account || !password_verify($currentPassword, (string) $account['password_hash'])) {
            flash('danger', 'Saisissez votre mot de passe actuel pour confirmer ces modifications.');
            redirect('/profile');
        }
        try {
            $this->users->updateProfile((int) $user['id'], $name, $email);
            flash('success', 'Vos informations ont été mises à jour.');
        } catch (PDOException $exception) {
            flash('danger', $exception->getCode() === '23000' ? 'Cette adresse e-mail est déjà utilisée.' : 'Impossible de mettre à jour votre profil.');
        }
        redirect('/profile');
    }

    public function changePassword(Request $request): void
    {
        $user = $this->auth->user();
        $account = $this->users->findByEmail((string) $user['email']);
        $current = (string) $request->input('current_password', '');
        $password = (string) $request->input('new_password', '');
        $confirmation = (string) $request->input('password_confirmation', '');

        if (!$account || !password_verify($current, (string) $account['password_hash'])) {
            flash('danger', 'Votre mot de passe actuel est incorrect.');
            redirect('/profile');
        }
        if (mb_strlen($password, 'UTF-8') < 12 || strlen($password) > 72) {
            flash('danger', 'Le mot de passe doit contenir 12 caractères minimum et 72 octets maximum.');
            redirect('/profile');
        }
        if (!hash_equals($password, $confirmation)) {
            flash('danger', 'La confirmation du nouveau mot de passe ne correspond pas.');
            redirect('/profile');
        }
        if (password_verify($password, (string) $account['password_hash'])) {
            flash('warning', 'Choisissez un mot de passe différent de l’actuel.');
            redirect('/profile');
        }

        $this->users->setPassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT), false);
        $this->auth->refreshSession();
        flash('success', 'Votre mot de passe a été changé. Votre compte est sécurisé.');
        redirect('/profile');
    }
}
