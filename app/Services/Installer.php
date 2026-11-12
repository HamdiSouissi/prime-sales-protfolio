<?php

declare(strict_types=1);

namespace PrimeSales\Services;

use PDO;
use PrimeSales\Models\SettingModel;
use PrimeSales\Models\UserModel;
use RuntimeException;

final class Installer
{
    public static function hasAdmin(PDO $db): bool
    {
        $statement = $db->query("SELECT COUNT(*) FROM users WHERE role = 'ADMIN'");
        return (int) $statement->fetchColumn() > 0;
    }

    public static function install(PDO $db, string $name, string $email, string $password, string $companyName): void
    {
        $schemaPath = ROOT_PATH . '/database/schema.sql';
        $schema = file_get_contents($schemaPath);
        if ($schema === false) {
            throw new RuntimeException('The database schema file could not be read.');
        }

        $schema = preg_replace('/^\s*--.*$/m', '', $schema) ?? $schema;
        $statements = preg_split('/;\s*(?:\r?\n|$)/', $schema) ?: [];
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement !== '') {
                $db->exec($statement);
            }
        }
        self::ensureAuthVersionColumn($db);

        if (self::hasAdmin($db)) {
            throw new RuntimeException('An administrator account already exists.');
        }

        $db->beginTransaction();
        try {
            self::seedCatalog($db);
            $users = new UserModel($db);
            $users->create($name, $email, password_hash($password, PASSWORD_DEFAULT), 'ADMIN');

            $settings = new SettingModel($db);
            $defaults = [
                'company_name' => $companyName,
                'primary_color' => '#4F8CFF',
                'accent_color' => '#8B5CF6',
                'logo_filename' => '',
                'favicon_filename' => '',
                'setup_completed' => '1',
            ];
            foreach ($defaults as $key => $value) {
                $settings->set($key, $value);
            }
            $db->commit();
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }

        foreach ([STORAGE_PATH, STORAGE_PATH . '/covers', STORAGE_PATH . '/branding', STORAGE_PATH . '/logs'] as $directory) {
            if (!is_dir($directory)) {
                @mkdir($directory, 0750, true);
            }
        }
    }

    private static function ensureAuthVersionColumn(PDO $db): void
    {
        $column = $db->query("SHOW COLUMNS FROM users LIKE 'auth_version'")->fetch();
        if (!$column) {
            $db->exec('ALTER TABLE users ADD COLUMN auth_version INT UNSIGNED NOT NULL DEFAULT 1 AFTER must_change_password');
        }
    }

    private static function seedCatalog(PDO $db): void
    {
        $categories = [
            'Site Web', 'Application Web', 'Application Mobile', 'Logiciel Métier',
            'Application IA', 'Landing Page', 'E-commerce', 'ERP', 'CRM',
        ];
        $categoryInsert = $db->prepare('INSERT IGNORE INTO categories (name, slug) VALUES (:name, :slug)');
        foreach ($categories as $name) {
            $categoryInsert->execute(['name' => $name, 'slug' => slugify($name)]);
        }

        $domains = [
            'Restaurant', 'Café', 'Salon de coiffure', 'Institut de beauté', 'Architecte',
            'Bureau d’études', 'Garage', 'Plombier', 'Électricien', 'Menuisier', 'Clinique',
            'Dentiste', 'Médecin', 'Pharmacie', 'Salle de sport', 'Hôtel', 'Agence immobilière',
            'Notaire', 'Avocat', 'Expert-comptable', 'Transport', 'Industrie', 'Association',
            'Collectivité', 'Formation', 'Startup', 'PME', 'TPE',
        ];
        $domainInsert = $db->prepare('INSERT IGNORE INTO domains (name, slug) VALUES (:name, :slug)');
        foreach ($domains as $name) {
            $domainInsert->execute(['name' => $name, 'slug' => slugify($name)]);
        }
    }
}
