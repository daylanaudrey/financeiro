<?php

/**
 * Model NCM
 * Gerencia códigos NCM oficiais da Receita Federal
 */
class NCM
{
    protected static string $table = 'ncm_codes';

    /**
     * Buscar todos os códigos NCM ativos
     */
    public static function getAllActive(): array
    {
        $sql = "SELECT * FROM " . static::$table . "
                WHERE is_active = 1
                ORDER BY code ASC";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar NCM por código
     */
    public static function findByCode(string $code): ?array
    {
        // Remover formatação do código (pontos)
        $cleanCode = str_replace('.', '', $code);
        $cleanCode = str_pad($cleanCode, 8, '0', STR_PAD_LEFT);

        $sql = "SELECT * FROM " . static::$table . "
                WHERE code = :code AND is_active = 1";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['code' => $cleanCode]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Buscar NCM por ID
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
     * Buscar NCM com filtros
     */
    public static function search(array $filters = []): array
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE is_active = 1";
        $params = [];

        if (!empty($filters['code'])) {
            $cleanCode = str_replace('.', '', $filters['code']);
            $sql .= " AND code LIKE :code";
            $params['code'] = $cleanCode . '%';
        }

        if (!empty($filters['description'])) {
            $sql .= " AND description LIKE :description";
            $params['description'] = '%' . $filters['description'] . '%';
        }

        $sql .= " ORDER BY code ASC LIMIT 100"; // Limitar para performance

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Formatar código NCM para exibição (0000.00.00)
     */
    public static function formatCode(string $code): string
    {
        $cleanCode = str_replace('.', '', $code);
        $cleanCode = str_pad($cleanCode, 8, '0', STR_PAD_LEFT);

        return substr($cleanCode, 0, 4) . '.' .
               substr($cleanCode, 4, 2) . '.' .
               substr($cleanCode, 6, 2);
    }

    /**
     * Obter estatísticas da tabela NCM
     */
    public static function getStats(): array
    {
        $sql = "SELECT
                    COUNT(*) as total,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as ativos,
                    COUNT(CASE WHEN is_active = 0 THEN 1 END) as inativos
                FROM " . static::$table;

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar NCMs por categoria (primeiros dígitos)
     */
    public static function getByCategory(string $category): array
    {
        $sql = "SELECT * FROM " . static::$table . "
                WHERE code LIKE :category AND is_active = 1
                ORDER BY code ASC
                LIMIT 50";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['category' => $category . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atualizar alíquotas de um NCM
     */
    public static function updateRates(string $code, array $rates): bool
    {
        $cleanCode = str_replace('.', '', $code);
        $cleanCode = str_pad($cleanCode, 8, '0', STR_PAD_LEFT);

        $sql = "UPDATE " . static::$table . "
                SET ii_rate = :ii_rate, ipi_rate = :ipi_rate,
                    pis_rate = :pis_rate, cofins_rate = :cofins_rate,
                    icms_rate = :icms_rate, updated_at = CURRENT_TIMESTAMP
                WHERE code = :code";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'code' => $cleanCode,
            'ii_rate' => $rates['ii_rate'] ?? 0,
            'ipi_rate' => $rates['ipi_rate'] ?? 0,
            'pis_rate' => $rates['pis_rate'] ?? 1.65,
            'cofins_rate' => $rates['cofins_rate'] ?? 7.60,
            'icms_rate' => $rates['icms_rate'] ?? 18.00
        ]);
    }
}