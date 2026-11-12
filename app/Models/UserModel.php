<?php

declare(strict_types=1);

namespace PrimeSales\Models;

final class UserModel extends Model
{
    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => mb_strtolower(trim($email), 'UTF-8')]);
        return $statement->fetch() ?: null;
    }

    public function findActiveById(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT id, name, email, role, is_active, must_change_password, auth_version, last_login, created_at FROM users WHERE id = :id AND is_active = 1 LIMIT 1');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function all(): array
    {
        return $this->db->query('SELECT id, name, email, role, is_active, must_change_password, last_login, created_at FROM users ORDER BY created_at DESC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT id, name, email, role, is_active, must_change_password, last_login, created_at FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function create(string $name, string $email, string $passwordHash, string $role): int
    {
        $statement = $this->db->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');
        $statement->execute([
            'name' => trim($name),
            'email' => mb_strtolower(trim($email), 'UTF-8'),
            'password_hash' => $passwordHash,
            'role' => $role,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $email, string $role): void
    {
        $statement = $this->db->prepare('UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id');
        $statement->execute([
            'id' => $id,
            'name' => trim($name),
            'email' => mb_strtolower(trim($email), 'UTF-8'),
            'role' => $role,
        ]);
    }

    public function setActive(int $id, bool $active): void
    {
        $statement = $this->db->prepare('UPDATE users SET is_active = :active WHERE id = :id');
        $statement->execute(['id' => $id, 'active' => $active ? 1 : 0]);
    }

    public function setPassword(int $id, string $passwordHash, bool $mustChange = false): void
    {
        $statement = $this->db->prepare('UPDATE users SET password_hash = :password_hash, must_change_password = :must_change, auth_version = auth_version + 1 WHERE id = :id');
        $statement->execute([
            'id' => $id,
            'password_hash' => $passwordHash,
            'must_change' => $mustChange ? 1 : 0,
        ]);
    }

    public function updateProfile(int $id, string $name, string $email): void
    {
        $statement = $this->db->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id');
        $statement->execute([
            'id' => $id,
            'name' => trim($name),
            'email' => mb_strtolower(trim($email), 'UTF-8'),
        ]);
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public function countCommercials(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'COMMERCIAL'")->fetchColumn();
    }

    public function countAdmins(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'ADMIN'")->fetchColumn();
    }

    public function countActiveAdmins(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'ADMIN' AND is_active = 1")->fetchColumn();
    }
}
