<?php

declare(strict_types=1);

namespace PrimeSales\Models;

final class LoginLogModel extends Model
{
    public function record(?int $userId, string $email, string $ip, string $userAgent, bool $success): void
    {
        $statement = $this->db->prepare('INSERT INTO login_logs (user_id, email, ip_address, user_agent, success) VALUES (:user_id, :email, :ip_address, :user_agent, :success)');
        $statement->execute([
            'user_id' => $userId,
            'email' => mb_substr($email, 0, 190, 'UTF-8'),
            'ip_address' => mb_substr($ip, 0, 45, 'UTF-8'),
            'user_agent' => mb_substr($userAgent, 0, 500, 'UTF-8'),
            'success' => $success ? 1 : 0,
        ]);
    }

    public function latest(int $limit = 8): array
    {
        $statement = $this->db->prepare('SELECT ll.id, ll.email, ll.ip_address, ll.success, ll.created_at, u.name AS user_name FROM login_logs ll LEFT JOIN users u ON u.id = ll.user_id ORDER BY ll.created_at DESC LIMIT :limit');
        $statement->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }
}
