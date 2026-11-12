<?php

declare(strict_types=1);

namespace PrimeSales\Controllers;

use PDOException;
use PrimeSales\Core\Request;

final class UserController extends BaseController
{
    public function index(Request $request): void
    {
        $this->page('users/index', ['usersList' => $this->users->all()], 'Équipe commerciale', 'users');
    }

    public function create(Request $request): void
    {
        $this->page('users/form', [
            'person' => null,
            'formAction' => url('/admin/users/create'),
        ], 'Ajouter un collaborateur', 'users');
    }

    public function store(Request $request): void
    {
        $name = $this->textInput($request->input('name'), 120);
        $email = mb_strtolower(trim((string) $request->input('email', '')), 'UTF-8');
        $role = (string) $request->input('role', 'COMMERCIAL');
        $password = (string) $request->input('password', '');
        $confirmation = (string) $request->input('password_confirmation', '');
        $passwordLengthValid = mb_strlen($password, 'UTF-8') >= 12 && strlen($password) <= 72;
        if (!$this->validIdentity($name, $email, $role) || !$passwordLengthValid || !hash_equals($password, $confirmation)) {
            remember_input(['name' => $name, 'email' => $email, 'role' => $role]);
            flash('danger', !$passwordLengthValid ? 'Le mot de passe doit contenir 12 caractères minimum et 72 octets maximum.' : 'Vérifiez le nom, l’adresse e-mail, le rôle et la confirmation du mot de passe.');
            redirect('/admin/users/create');
        }

        try {
            $this->users->create($name, $email, password_hash($password, PASSWORD_DEFAULT), $role);
            clear_old_input();
            flash('success', 'Le compte de ' . $name . ' a été créé.');
            redirect('/admin/users');
        } catch (PDOException $exception) {
            remember_input(['name' => $name, 'email' => $email, 'role' => $role]);
            flash('danger', $exception->getCode() === '23000' ? 'Cette adresse e-mail est déjà utilisée.' : 'Impossible de créer ce compte.');
            redirect('/admin/users/create');
        }
    }

    public function edit(Request $request, array $params): void
    {
        $person = $this->users->find((int) ($params['id'] ?? 0));
        if (!$person) {
            flash('danger', 'Ce compte est introuvable.');
            redirect('/admin/users');
        }
        $this->page('users/form', [
            'person' => $person,
            'formAction' => url('/admin/users/' . (int) $person['id'] . '/edit'),
        ], 'Modifier un compte', 'users');
    }

    public function update(Request $request, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $person = $this->users->find($id);
        if (!$person) {
            flash('danger', 'Ce compte est introuvable.');
            redirect('/admin/users');
        }
        $name = $this->textInput($request->input('name'), 120);
        $email = mb_strtolower(trim((string) $request->input('email', '')), 'UTF-8');
        $role = (string) $request->input('role', 'COMMERCIAL');
        if (!$this->validIdentity($name, $email, $role)) {
            remember_input(['name' => $name, 'email' => $email, 'role' => $role]);
            flash('danger', 'Vérifiez le nom, l’adresse e-mail et le rôle sélectionné.');
            redirect('/admin/users/' . $id . '/edit');
        }
        if ($person['role'] === 'ADMIN' && $role !== 'ADMIN' && (int) $person['is_active'] === 1 && $this->users->countActiveAdmins() <= 1) {
            flash('danger', 'Conservez au moins un administrateur actif dans l’espace.');
            redirect('/admin/users/' . $id . '/edit');
        }

        try {
            $this->users->update($id, $name, $email, $role);
            clear_old_input();
            flash('success', 'Le compte de ' . $name . ' a été mis à jour.');
        } catch (PDOException $exception) {
            remember_input(['name' => $name, 'email' => $email, 'role' => $role]);
            flash('danger', $exception->getCode() === '23000' ? 'Cette adresse e-mail est déjà utilisée.' : 'Impossible de mettre à jour ce compte.');
            redirect('/admin/users/' . $id . '/edit');
        }
        redirect('/admin/users');
    }

    public function toggle(Request $request, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $person = $this->users->find($id);
        if (!$person) {
            flash('danger', 'Ce compte est introuvable.');
            redirect('/admin/users');
        }
        if ($id === (int) $this->auth->user()['id']) {
            flash('warning', 'Vous ne pouvez pas suspendre votre propre compte.');
            redirect('/admin/users');
        }
        $active = !(bool) $person['is_active'];
        if (!$active && $person['role'] === 'ADMIN' && $this->users->countActiveAdmins() <= 1) {
            flash('danger', 'Impossible de suspendre le dernier administrateur actif.');
            redirect('/admin/users');
        }
        $this->users->setActive($id, $active);
        flash($active ? 'success' : 'warning', $active ? 'Compte réactivé.' : 'Compte suspendu.');
        redirect('/admin/users');
    }

    public function resetPassword(Request $request, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $person = $this->users->find($id);
        if (!$person) {
            flash('danger', 'Ce compte est introuvable.');
            redirect('/admin/users');
        }
        if ($id === (int) $this->auth->user()['id']) {
            flash('warning', 'Utilisez la section Sécurité de votre profil pour changer votre propre mot de passe.');
            redirect('/profile');
        }
        $temporaryPassword = bin2hex(random_bytes(12));
        $this->users->setPassword($id, password_hash($temporaryPassword, PASSWORD_DEFAULT), true);
        flash('success', 'Mot de passe temporaire pour ' . $person['email'] . ' : ' . $temporaryPassword . ' — transmettez-le de façon sécurisée. Il devra être changé à la prochaine connexion.');
        redirect('/admin/users');
    }

    public function delete(Request $request, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $person = $this->users->find($id);
        if (!$person) {
            flash('warning', 'Ce compte a déjà été supprimé.');
            redirect('/admin/users');
        }
        if ($id === (int) $this->auth->user()['id']) {
            flash('danger', 'Vous ne pouvez pas supprimer votre propre compte.');
            redirect('/admin/users');
        }
        if ($person['role'] === 'ADMIN' && (int) $person['is_active'] === 1 && $this->users->countActiveAdmins() <= 1) {
            flash('danger', 'Impossible de supprimer le dernier administrateur actif.');
            redirect('/admin/users');
        }
        $this->users->delete($id);
        flash('success', 'Le compte de ' . $person['name'] . ' a été supprimé.');
        redirect('/admin/users');
    }

    private function validIdentity(string $name, string $email, string $role): bool
    {
        return mb_strlen($name, 'UTF-8') >= 2
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            && mb_strlen($email, 'UTF-8') <= 190
            && in_array($role, ['ADMIN', 'COMMERCIAL'], true);
    }
}
