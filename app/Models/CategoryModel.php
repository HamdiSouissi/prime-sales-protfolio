<?php

declare(strict_types=1);

namespace PrimeSales\Models;

final class CategoryModel extends Model
{
    public function all(): array
    {
        return $this->db->query('SELECT c.id, c.name, c.slug, c.description, c.created_at, c.updated_at, COUNT(p.id) AS prototype_count FROM categories c LEFT JOIN prototypes p ON p.category_id = c.id GROUP BY c.id, c.name, c.slug, c.description, c.created_at, c.updated_at ORDER BY c.name ASC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function create(string $name, string $description): int
    {
        $statement = $this->db->prepare('INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :description)');
        $statement->execute(['name' => trim($name), 'slug' => slugify($name), 'description' => trim($description) ?: null]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $description): void
    {
        $statement = $this->db->prepare('UPDATE categories SET name = :name, slug = :slug, description = :description WHERE id = :id');
        $statement->execute(['id' => $id, 'name' => trim($name), 'slug' => slugify($name), 'description' => trim($description) ?: null]);
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM categories WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM categories')->fetchColumn();
    }
}
