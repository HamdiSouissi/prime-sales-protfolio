<?php

declare(strict_types=1);

namespace PrimeSales\Models;

final class DomainModel extends Model
{
    public function all(): array
    {
        return $this->db->query('SELECT d.id, d.name, d.slug, d.description, d.created_at, d.updated_at, COUNT(p.id) AS prototype_count FROM domains d LEFT JOIN prototypes p ON p.domain_id = d.id GROUP BY d.id, d.name, d.slug, d.description, d.created_at, d.updated_at ORDER BY d.name ASC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM domains WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function create(string $name, string $description): int
    {
        $statement = $this->db->prepare('INSERT INTO domains (name, slug, description) VALUES (:name, :slug, :description)');
        $statement->execute(['name' => trim($name), 'slug' => slugify($name), 'description' => trim($description) ?: null]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $description): void
    {
        $statement = $this->db->prepare('UPDATE domains SET name = :name, slug = :slug, description = :description WHERE id = :id');
        $statement->execute(['id' => $id, 'name' => trim($name), 'slug' => slugify($name), 'description' => trim($description) ?: null]);
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM domains WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM domains')->fetchColumn();
    }
}
