<?php

declare(strict_types=1);

namespace PrimeSales\Models;

final class SettingModel extends Model
{
    public function all(): array
    {
        $rows = $this->db->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $statement = $this->db->prepare('SELECT setting_value FROM settings WHERE setting_key = :setting_key LIMIT 1');
        $statement->execute(['setting_key' => $key]);
        $value = $statement->fetchColumn();
        return $value === false ? $default : (string) $value;
    }

    public function set(string $key, ?string $value): void
    {
        $statement = $this->db->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (:setting_key, :setting_value) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        $statement->execute(['setting_key' => $key, 'setting_value' => $value]);
    }
}
