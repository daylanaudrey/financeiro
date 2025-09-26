<?php

namespace App\Core;

use App\Core\Database;
use PDO;

/**
 * Classe Model
 * Model base para todos os models
 */
abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    /**
     * Buscar todos os registros
     */
    public static function all(array $conditions = []): array
    {
        $sql = "SELECT * FROM " . static::$table;
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = :{$field}";
                $params[$field] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY id DESC";

        return Database::query($sql, $params)->fetchAll();
    }

    /**
     * Buscar por ID
     */
    public static function find(int $id): ?array
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id";
        $result = Database::query($sql, ['id' => $id])->fetch();
        return $result ?: null;
    }

    /**
     * Buscar primeiro registro
     */
    public static function first(array $conditions = []): ?array
    {
        $sql = "SELECT * FROM " . static::$table;
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = :{$field}";
                $params[$field] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " LIMIT 1";

        $result = Database::query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Criar novo registro
     */
    public static function create(array $data): int
    {
        // Adicionar timestamps
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $fields = array_keys($data);
        $values = array_map(fn($f) => ":{$f}", $fields);

        $sql = "INSERT INTO " . static::$table . " (" . implode(', ', $fields) . ")
                VALUES (" . implode(', ', $values) . ")";

        Database::query($sql, $data);
        return (int) Database::getConnection()->lastInsertId();
    }

    /**
     * Atualizar registro
     */
    public static function update(int $id, array $data): bool
    {
        // Adicionar updated_at
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $sets = [];
        foreach ($data as $field => $value) {
            $sets[] = "{$field} = :{$field}";
        }

        $sql = "UPDATE " . static::$table . "
                SET " . implode(', ', $sets) . "
                WHERE " . static::$primaryKey . " = :id";

        $data['id'] = $id;
        Database::query($sql, $data);

        return true;
    }

    /**
     * Excluir registro (soft delete)
     */
    public static function delete(int $id): bool
    {
        $sql = "UPDATE " . static::$table . "
                SET deleted = 1, deleted_at = :deleted_at
                WHERE " . static::$primaryKey . " = :id";

        Database::query($sql, [
            'id' => $id,
            'deleted_at' => date('Y-m-d H:i:s')
        ]);

        return true;
    }

    /**
     * Excluir permanentemente
     */
    public static function destroy(int $id): bool
    {
        $sql = "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id";
        Database::query($sql, ['id' => $id]);
        return true;
    }

    /**
     * Contar registros
     */
    public static function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM " . static::$table;
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = :{$field}";
                $params[$field] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        return (int) Database::query($sql, $params)->fetch()['total'];
    }

    /**
     * Executar query customizada
     */
    protected static function query(string $sql, array $params = []): \PDOStatement
    {
        return Database::query($sql, $params);
    }
}