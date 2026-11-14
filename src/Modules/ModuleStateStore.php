<?php

declare(strict_types=1);

namespace ArpPhpTest\Modules;

use PDO;

final class ModuleStateStore
{
    public function __construct(private readonly PDO $db) {}

    public function initialize(): void
    {
        $this->db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS app_modules (
    id VARCHAR(100) NOT NULL PRIMARY KEY,
    version VARCHAR(50) NOT NULL,
    enabled BOOLEAN NOT NULL DEFAULT FALSE,
    installed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin
SQL);
    }

    /** @return array<string, array{version: string, enabled: bool}> */
    public function installed(): array
    {
        $rows = $this->db->query('SELECT id, version, enabled FROM app_modules')->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[(string) $row['id']] = [
                'version' => (string) $row['version'],
                'enabled' => (bool) $row['enabled'],
            ];
        }
        return $result;
    }

    public function insertInstalled(ModuleManifest $manifest): void
    {
        $stmt = $this->db->prepare('INSERT INTO app_modules (id, version, enabled) VALUES (?, ?, FALSE)');
        $stmt->execute([$manifest->id, $manifest->version]);
    }

    public function setEnabled(string $id, bool $enabled): void
    {
        $stmt = $this->db->prepare('UPDATE app_modules SET enabled = ? WHERE id = ?');
        $stmt->execute([$enabled ? 1 : 0, $id]);
    }
}
