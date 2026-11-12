<?php

declare(strict_types=1);

namespace PrimeSales\Models;

use PDO;

final class PrototypeModel extends Model
{
    public function paginate(array $filters, int $page, int $perPage): array
    {
        [$where, $parameters] = $this->whereClause($filters);
        $countSql = 'SELECT COUNT(*) FROM prototypes p INNER JOIN categories c ON c.id = p.category_id INNER JOIN domains d ON d.id = p.domain_id' . $where;
        $countStatement = $this->db->prepare($countSql);
        $countStatement->execute($parameters);
        $total = (int) $countStatement->fetchColumn();
        $pages = max(1, (int) ceil($total / max(1, $perPage)));
        $page = min(max(1, $page), $pages);

        $orderBy = match ((string) ($filters['sort'] ?? 'recent')) {
            'alpha' => 'p.name ASC, p.created_at DESC',
            'oldest' => 'p.created_at ASC',
            default => 'p.created_at DESC',
        };

        $sql = 'SELECT p.id, p.name, p.description, p.url, p.cover_path, p.created_at, c.id AS category_id, c.name AS category_name, d.id AS domain_id, d.name AS domain_name FROM prototypes p INNER JOIN categories c ON c.id = p.category_id INNER JOIN domains d ON d.id = p.domain_id' . $where . ' ORDER BY ' . $orderBy . ' LIMIT :limit OFFSET :offset';
        $statement = $this->db->prepare($sql);
        foreach ($parameters as $key => $value) {
            $statement->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $statement->execute();

        return [
            'items' => $statement->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => $pages,
        ];
    }

    private function whereClause(array $filters): array
    {
        $clauses = [];
        $parameters = [];
        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $clauses[] = '(p.name LIKE :q_name OR p.description LIKE :q_description OR c.name LIKE :q_category OR d.name LIKE :q_domain)';
            $pattern = '%' . $search . '%';
            $parameters['q_name'] = $pattern;
            $parameters['q_description'] = $pattern;
            $parameters['q_category'] = $pattern;
            $parameters['q_domain'] = $pattern;
        }

        foreach (['category' => 'p.category_id', 'domain' => 'p.domain_id'] as $filter => $column) {
            $value = filter_var($filters[$filter] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($value !== false && $value !== null) {
                $key = 'filter_' . $filter;
                $clauses[] = $column . ' = :' . $key;
                $parameters[$key] = (int) $value;
            }
        }

        return [$clauses ? ' WHERE ' . implode(' AND ', $clauses) : '', $parameters];
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT p.*, c.name AS category_name, d.name AS domain_name FROM prototypes p INNER JOIN categories c ON c.id = p.category_id INNER JOIN domains d ON d.id = p.domain_id WHERE p.id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function latest(int $limit = 5): array
    {
        $statement = $this->db->prepare('SELECT p.id, p.name, p.description, p.url, p.cover_path, p.created_at, c.name AS category_name, d.name AS domain_name FROM prototypes p INNER JOIN categories c ON c.id = p.category_id INNER JOIN domains d ON d.id = p.domain_id ORDER BY p.created_at DESC LIMIT :limit');
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare('INSERT INTO prototypes (name, category_id, domain_id, description, url, created_by) VALUES (:name, :category_id, :domain_id, :description, :url, :created_by)');
        $statement->execute([
            'name' => trim($data['name']),
            'category_id' => (int) $data['category_id'],
            'domain_id' => (int) $data['domain_id'],
            'description' => trim($data['description']) ?: null,
            'url' => trim($data['url']),
            'created_by' => (int) $data['created_by'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $statement = $this->db->prepare('UPDATE prototypes SET name = :name, category_id = :category_id, domain_id = :domain_id, description = :description, url = :url WHERE id = :id');
        $statement->execute([
            'id' => $id,
            'name' => trim($data['name']),
            'category_id' => (int) $data['category_id'],
            'domain_id' => (int) $data['domain_id'],
            'description' => trim($data['description']) ?: null,
            'url' => trim($data['url']),
        ]);
    }

    public function updateCover(int $id, ?string $filename): void
    {
        $statement = $this->db->prepare('UPDATE prototypes SET cover_path = :cover_path WHERE id = :id');
        $statement->execute(['id' => $id, 'cover_path' => $filename]);
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM prototypes WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM prototypes')->fetchColumn();
    }
}
