<?php

/**
 * Model User
 * Gerencia usuários do sistema
 */
class User
{
    protected static string $table = 'users';

    /**
     * Buscar usuário por email
     */
    public static function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM " . static::$table . "
                WHERE email = :email
                AND deleted = 0
                AND is_active = 1";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Validar login
     */
    public static function validateLogin(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);

        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user['password'])) {
            return null;
        }

        // Atualizar último login
        self::updateLastLogin($user['id']);

        return $user;
    }

    /**
     * Atualizar último login
     */
    public static function updateLastLogin(int $userId): void
    {
        $sql = "UPDATE " . static::$table . "
                SET last_login = :last_login
                WHERE id = :id";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id' => $userId,
            'last_login' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Buscar todos os usuários ativos
     */
    public static function getAllActive(): array
    {
        $sql = "SELECT id, name, email, role FROM " . static::$table . "
                WHERE deleted = 0 AND is_active = 1
                ORDER BY name";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar usuário por ID
     */
    public static function findById(int $id): ?array
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE id = :id AND deleted = 0";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Buscar todos os usuários com filtros
     */
    public static function getAll(array $filters = []): array
    {
        $where = ['deleted = 0'];
        $params = [];

        if (!empty($filters['name'])) {
            $where[] = "name LIKE :name";
            $params['name'] = '%' . $filters['name'] . '%';
        }

        if (!empty($filters['email'])) {
            $where[] = "email LIKE :email";
            $params['email'] = '%' . $filters['email'] . '%';
        }

        if (!empty($filters['role'])) {
            $where[] = "role = :role";
            $params['role'] = $filters['role'];
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $where[] = "is_active = :is_active";
            $params['is_active'] = (int)$filters['is_active'];
        }

        $sql = "SELECT id, name, email, role, is_active, last_login, created_at, updated_at
                FROM " . static::$table . "
                WHERE " . implode(' AND ', $where) . "
                ORDER BY created_at DESC";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Criar usuário
     */
    public static function create(array $data): int
    {
        $sql = "INSERT INTO " . static::$table . "
                (name, email, password, role, is_active, created_at, updated_at)
                VALUES (:name, :email, :password, :role, :is_active, NOW(), NOW())";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'operator',
            'is_active' => $data['is_active'] ?? 1
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Atualizar usuário
     */
    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            if ($key === 'password' && !empty($value)) {
                $fields[] = "password = :password";
                $params['password'] = password_hash($value, PASSWORD_DEFAULT);
            } else {
                $fields[] = "$key = :$key";
                $params[$key] = $value;
            }
        }

        // Adicionar updated_at
        $fields[] = "updated_at = NOW()";

        $sql = "UPDATE " . static::$table . "
                SET " . implode(', ', $fields) . "
                WHERE id = :id AND deleted = 0";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Excluir usuário (soft delete)
     */
    public static function delete(int $id): bool
    {
        $sql = "UPDATE " . static::$table . "
                SET deleted = 1, deleted_at = NOW(), updated_at = NOW()
                WHERE id = :id";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Verificar se email já existe
     */
    public static function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM " . static::$table . "
                WHERE email = :email AND deleted = 0";
        $params = ['email' => $email];

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['count'] > 0;
    }

    /**
     * Obter opções de roles
     */
    public static function getRoleOptions(): array
    {
        return [
            'admin' => 'Administrador',
            'operator' => 'Operador',
            'viewer' => 'Visualizador'
        ];
    }

    /**
     * Contar usuários
     */
    public static function count(array $filters = []): int
    {
        $where = ['deleted = 0'];
        $params = [];

        if (!empty($filters['name'])) {
            $where[] = "name LIKE :name";
            $params['name'] = '%' . $filters['name'] . '%';
        }

        if (!empty($filters['email'])) {
            $where[] = "email LIKE :email";
            $params['email'] = '%' . $filters['email'] . '%';
        }

        if (!empty($filters['role'])) {
            $where[] = "role = :role";
            $params['role'] = $filters['role'];
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $where[] = "is_active = :is_active";
            $params['is_active'] = (int)$filters['is_active'];
        }

        $sql = "SELECT COUNT(*) as count FROM " . static::$table . "
                WHERE " . implode(' AND ', $where);

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['count'];
    }

}