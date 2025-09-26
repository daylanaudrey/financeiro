<?php

/**
 * Model Port
 * Gerencia portos do sistema
 */
class Port
{
    protected static string $table = 'ports';

    /**
     * Buscar todos os portos ativos
     */
    public static function getAllActive(): array
    {
        $sql = "SELECT * FROM " . static::$table . "
                WHERE deleted = 0 AND is_active = 1
                ORDER BY name ASC";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar porto por ID
     */
    public static function findById(int $id): ?array
    {
        $sql = "SELECT * FROM " . static::$table . "
                WHERE id = :id AND deleted = 0";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Buscar porto por prefixo
     */
    public static function findByPrefix(string $prefix): ?array
    {
        $sql = "SELECT * FROM " . static::$table . "
                WHERE prefix = :prefix AND deleted = 0";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['prefix' => $prefix]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Criar novo porto
     */
    public static function create(array $data): int
    {
        $sql = "INSERT INTO " . static::$table . "
                (name, prefix, customs_code, city, state, country, is_active, notes)
                VALUES (:name, :prefix, :customs_code, :city, :state, :country, :is_active, :notes)";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'prefix' => $data['prefix'],
            'customs_code' => $data['customs_code'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? 'Brasil',
            'is_active' => $data['is_active'] ?? 1,
            'notes' => $data['notes'] ?? null
        ]);

        return $pdo->lastInsertId();
    }

    /**
     * Atualizar porto
     */
    public static function update(int $id, array $data): bool
    {
        $sql = "UPDATE " . static::$table . "
                SET name = :name, prefix = :prefix, customs_code = :customs_code, city = :city, state = :state,
                    country = :country, is_active = :is_active, notes = :notes, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND deleted = 0";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'prefix' => $data['prefix'],
            'customs_code' => $data['customs_code'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? 'Brasil',
            'is_active' => $data['is_active'] ?? 1,
            'notes' => $data['notes'] ?? null
        ]);
    }

    /**
     * Soft delete do porto
     */
    public static function delete(int $id): bool
    {
        $sql = "UPDATE " . static::$table . "
                SET deleted = 1, deleted_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Verificar se prefixo já existe (para validação)
     */
    public static function prefixExists(string $prefix, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM " . static::$table . "
                WHERE prefix = :prefix AND deleted = 0";

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $params = ['prefix' => $prefix];

        if ($excludeId) {
            $params['exclude_id'] = $excludeId;
        }

        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }

    /**
     * Buscar portos com filtros
     */
    public static function search(array $filters = []): array
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE deleted = 0";
        $params = [];

        if (!empty($filters['name'])) {
            $sql .= " AND name LIKE :name";
            $params['name'] = '%' . $filters['name'] . '%';
        }

        if (!empty($filters['city'])) {
            $sql .= " AND city LIKE :city";
            $params['city'] = '%' . $filters['city'] . '%';
        }

        if (!empty($filters['state'])) {
            $sql .= " AND state = :state";
            $params['state'] = $filters['state'];
        }

        if (isset($filters['is_active'])) {
            $sql .= " AND is_active = :is_active";
            $params['is_active'] = $filters['is_active'];
        }

        $sql .= " ORDER BY name ASC";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}