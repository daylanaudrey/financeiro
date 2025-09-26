<?php

/**
 * Model AuditLog
 * Gerencia logs de auditoria do sistema
 */
class AuditLog
{
    protected static string $table = 'audit_logs';

    /**
     * Registrar ação de auditoria
     */
    public static function log(string $action, string $tableName, ?int $recordId = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $userName = $_SESSION['user_name'] ?? null;
        $userEmail = $_SESSION['user_email'] ?? null;
        $ipAddress = self::getClientIpAddress();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $sql = "INSERT INTO " . static::$table . "
                (user_id, user_name, user_email, action, table_name, record_id, old_values, new_values, ip_address, user_agent, created_at)
                VALUES (:user_id, :user_name, :user_email, :action, :table_name, :record_id, :old_values, :new_values, :ip_address, :user_agent, :created_at)";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'user_name' => $userName,
            'user_email' => $userEmail,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Buscar log por ID
     */
    public static function findById(int $id): ?array
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE id = :id";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Buscar todos os logs com filtros
     */
    public static function getAll(?string $action = null, ?string $tableName = null, ?int $userId = null, ?string $startDate = null, ?string $endDate = null, int $limit = 100, int $offset = 0): array
    {
        $where = [];
        $params = [];

        if ($action) {
            $where[] = "action = :action";
            $params['action'] = $action;
        }

        if ($tableName) {
            $where[] = "table_name = :table_name";
            $params['table_name'] = $tableName;
        }

        if ($userId) {
            $where[] = "user_id = :user_id";
            $params['user_id'] = $userId;
        }

        if ($startDate) {
            $where[] = "created_at >= :start_date";
            $params['start_date'] = $startDate . ' 00:00:00';
        }

        if ($endDate) {
            $where[] = "created_at <= :end_date";
            $params['end_date'] = $endDate . ' 23:59:59';
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT * FROM " . static::$table . "
                $whereClause
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Contar total de logs com filtros
     */
    public static function count(?string $action = null, ?string $tableName = null, ?int $userId = null, ?string $startDate = null, ?string $endDate = null): int
    {
        $where = [];
        $params = [];

        if ($action) {
            $where[] = "action = :action";
            $params['action'] = $action;
        }

        if ($tableName) {
            $where[] = "table_name = :table_name";
            $params['table_name'] = $tableName;
        }

        if ($userId) {
            $where[] = "user_id = :user_id";
            $params['user_id'] = $userId;
        }

        if ($startDate) {
            $where[] = "created_at >= :start_date";
            $params['start_date'] = $startDate . ' 00:00:00';
        }

        if ($endDate) {
            $where[] = "created_at <= :end_date";
            $params['end_date'] = $endDate . ' 23:59:59';
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT COUNT(*) as total FROM " . static::$table . " $whereClause";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    /**
     * Buscar logs por registro específico
     */
    public static function getByRecord(string $tableName, int $recordId): array
    {
        $sql = "SELECT * FROM " . static::$table . "
                WHERE table_name = :table_name AND record_id = :record_id
                ORDER BY created_at DESC";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'table_name' => $tableName,
            'record_id' => $recordId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar ações únicas para filtro
     */
    public static function getUniqueActions(): array
    {
        $sql = "SELECT DISTINCT action FROM " . static::$table . " ORDER BY action";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'action');
    }

    /**
     * Buscar tabelas únicas para filtro
     */
    public static function getUniqueTables(): array
    {
        $sql = "SELECT DISTINCT table_name FROM " . static::$table . " ORDER BY table_name";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'table_name');
    }

    /**
     * Obter endereço IP do cliente
     */
    private static function getClientIpAddress(): ?string
    {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];

        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    /**
     * Limpar logs antigos (manter apenas últimos X dias)
     */
    public static function cleanup(int $daysToKeep = 365): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-$daysToKeep days"));

        $sql = "DELETE FROM " . static::$table . " WHERE created_at < :cutoff_date";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['cutoff_date' => $cutoffDate]);

        return $stmt->rowCount();
    }
}