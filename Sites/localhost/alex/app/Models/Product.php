<?php

/**
 * Model Product
 * Gerencia produtos do sistema
 */
class Product
{
    protected static string $table = 'products';

    /**
     * Buscar todos os produtos ativos
     */
    public static function getAllActive(): array
    {
        $sql = "SELECT *, COALESCE(variant_description, description) as display_description
                FROM " . static::$table . "
                WHERE deleted = 0 AND is_active = 1
                ORDER BY COALESCE(variant_description, description) ASC";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar apenas produtos principais (não variações)
     */
    public static function getMainProducts(): array
    {
        $sql = "SELECT * FROM " . static::$table . "
                WHERE deleted = 0 AND is_active = 1 AND parent_id IS NULL
                ORDER BY description ASC";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar variações de um produto
     */
    public static function getVariants(int $parentId): array
    {
        $sql = "SELECT * FROM " . static::$table . "
                WHERE parent_id = :parent_id AND deleted = 0
                ORDER BY variant_description ASC";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['parent_id' => $parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar produto por ID
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
     * Buscar produto por NCM
     */
    public static function findByNCM(string $ncm): ?array
    {
        $sql = "SELECT * FROM " . static::$table . "
                WHERE ncm = :ncm AND deleted = 0 AND parent_id IS NULL";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['ncm' => $ncm]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Buscar produto por NCM e descrição (para verificar se já existe variação)
     */
    public static function findByNCMAndDescription(string $ncm, string $description): ?array
    {
        $sql = "SELECT * FROM " . static::$table . "
                WHERE ncm = :ncm
                AND (description = :desc1 OR variant_description = :desc2)
                AND deleted = 0";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'ncm' => $ncm,
            'desc1' => $description,
            'desc2' => $description
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Criar novo produto
     */
    public static function create(array $data): int
    {
        $sql = "INSERT INTO " . static::$table . "
                (parent_id, is_variant, name, ncm, description, variant_description, rfb_min, rfb_max, weight_kg, unit,
                 ii_rate, ipi_rate, pis_rate, cofins_rate, icms_rate, is_active)
                VALUES (:parent_id, :is_variant, :name, :ncm, :description, :variant_description, :rfb_min, :rfb_max, :weight_kg, :unit,
                        :ii_rate, :ipi_rate, :pis_rate, :cofins_rate, :icms_rate, :is_active)";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'parent_id' => $data['parent_id'] ?? null,
            'is_variant' => ($data['is_variant'] ?? false) ? 1 : 0,
            'name' => $data['name'],
            'ncm' => $data['ncm'],
            'description' => $data['description'] ?? null,
            'variant_description' => $data['variant_description'] ?? null,
            'rfb_min' => $data['rfb_min'] ?? null,
            'rfb_max' => $data['rfb_max'] ?? null,
            'weight_kg' => $data['weight_kg'] ?? null,
            'unit' => $data['unit'] ?? null,
            'ii_rate' => $data['ii_rate'] ?? 0,
            'ipi_rate' => $data['ipi_rate'] ?? 0,
            'pis_rate' => $data['pis_rate'] ?? 0,
            'cofins_rate' => $data['cofins_rate'] ?? 0,
            'icms_rate' => $data['icms_rate'] ?? 0,
            'is_active' => $data['is_active'] ?? 1
        ]);

        return $pdo->lastInsertId();
    }

    /**
     * Criar variação de produto (filho)
     */
    public static function createVariant(int $parentId, string $variantDescription): int
    {
        // Buscar dados do produto pai
        $parent = self::findById($parentId);
        if (!$parent) {
            throw new Exception("Produto pai não encontrado");
        }

        // Criar cópia com nova descrição
        $variantData = $parent;
        unset($variantData['id']);
        unset($variantData['created_at']);
        unset($variantData['updated_at']);
        $variantData['parent_id'] = $parentId;
        $variantData['is_variant'] = true;
        $variantData['variant_description'] = $variantDescription;
        // Atualizar name e description com a nova descrição da variação
        $variantData['name'] = $variantDescription;
        $variantData['description'] = $variantDescription;

        $variantId = self::create($variantData);

        // Copiar configurações RFB por porto do produto pai
        self::copyPortConfigs($parentId, $variantId);

        return $variantId;
    }

    /**
     * Copiar configurações RFB por porto do produto pai para o filho
     */
    private static function copyPortConfigs(int $parentId, int $childId): void
    {
        try {
            $sql = "INSERT INTO port_product_configs
                    (port_id, product_id, rfb_min_override, rfb_max_override, division_type,
                     default_margin, freight_percentage, insurance_percentage, notes, is_active, created_at)
                    SELECT port_id, :child_id, rfb_min_override, rfb_max_override, division_type,
                           default_margin, freight_percentage, insurance_percentage,
                           CONCAT('Copiado do produto pai ID: ', :parent_id, ' - ', COALESCE(notes, '')), is_active, NOW()
                    FROM port_product_configs
                    WHERE product_id = :parent_id AND deleted = 0";

            $pdo = Database::getConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'child_id' => $childId,
                'parent_id' => $parentId
            ]);

            $copiedCount = $stmt->rowCount();
            if ($copiedCount > 0) {
                error_log("Copiadas {$copiedCount} configurações RFB por porto do produto {$parentId} para {$childId}");
            }

        } catch (Exception $e) {
            error_log("Erro ao copiar configurações RFB por porto: " . $e->getMessage());
            // Não falhar a criação do produto se houver erro na cópia das configurações
        }
    }

    /**
     * Atualizar produto
     */
    public static function update(int $id, array $data): bool
    {
        $sql = "UPDATE " . static::$table . "
                SET name = :name, ncm = :ncm, description = :description,
                    rfb_min = :rfb_min, rfb_max = :rfb_max, weight_kg = :weight_kg,
                    unit = :unit, division_type = :division_type,
                    ii_rate = :ii_rate, ipi_rate = :ipi_rate, pis_rate = :pis_rate,
                    cofins_rate = :cofins_rate, icms_rate = :icms_rate,
                    is_active = :is_active, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND deleted = 0";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'ncm' => $data['ncm'],
            'description' => $data['description'] ?? null,
            'rfb_min' => $data['rfb_min'] ?? null,
            'rfb_max' => $data['rfb_max'] ?? null,
            'weight_kg' => $data['weight_kg'] ?? null,
            'unit' => $data['unit'] ?? null,
            'division_type' => $data['division_type'] ?? 'KG',
            'ii_rate' => $data['ii_rate'] ?? 0,
            'ipi_rate' => $data['ipi_rate'] ?? 0,
            'pis_rate' => $data['pis_rate'] ?? 0,
            'cofins_rate' => $data['cofins_rate'] ?? 0,
            'icms_rate' => $data['icms_rate'] ?? 0,
            'is_active' => $data['is_active'] ?? 1
        ]);
    }

    /**
     * Atualizar campos específicos do produto (para auto-save)
     */
    public static function updateField(int $id, array $fields): bool
    {
        if (empty($fields)) {
            return false;
        }

        // Construir SQL dinamicamente apenas com campos fornecidos
        $setClause = [];
        $params = ['id' => $id];

        foreach ($fields as $field => $value) {
            // Validar campos permitidos
            $allowedFields = [
                'name', 'description', 'ncm', 'rfb_min', 'rfb_max', 'weight_kg',
                'unit', 'division_type', 'ii_rate', 'ipi_rate', 'pis_rate',
                'cofins_rate', 'icms_rate', 'is_active'
            ];

            if (in_array($field, $allowedFields)) {
                $setClause[] = "$field = :$field";
                $params[$field] = $value;
            }
        }

        if (empty($setClause)) {
            return false;
        }

        $sql = "UPDATE " . static::$table . "
                SET " . implode(', ', $setClause) . ", updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND deleted = 0";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Soft delete do produto
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
     * Verificar se NCM já existe (para validação)
     */
    public static function ncmExists(string $ncm, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM " . static::$table . "
                WHERE ncm = :ncm AND deleted = 0";

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $params = ['ncm' => $ncm];

        if ($excludeId) {
            $params['exclude_id'] = $excludeId;
        }

        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }

    /**
     * Buscar produtos com filtros e paginação
     */
    public static function search(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE deleted = 0";
        $params = [];

        if (!empty($filters['name'])) {
            $sql .= " AND name LIKE :name";
            $params['name'] = '%' . $filters['name'] . '%';
        }

        if (!empty($filters['ncm'])) {
            // Normalizar NCM removendo pontuação (conforme tarefas.txt)
            $ncmClean = preg_replace('/[^0-9]/', '', $filters['ncm']);

            // Se tem pelo menos 4 dígitos, fazer busca flexível
            if (strlen($ncmClean) >= 4) {
                // Buscar tanto pelo NCM formatado quanto pelo NCM limpo
                $sql .= " AND (ncm LIKE :ncm_original OR REPLACE(REPLACE(ncm, '.', ''), '-', '') LIKE :ncm_clean)";
                $params['ncm_original'] = '%' . $filters['ncm'] . '%';
                $params['ncm_clean'] = '%' . $ncmClean . '%';
            } else {
                // Para buscas menores, usar busca normal
                $sql .= " AND ncm LIKE :ncm";
                $params['ncm'] = '%' . $filters['ncm'] . '%';
            }
        }

        if (!empty($filters['division_type'])) {
            $sql .= " AND division_type = :division_type";
            $params['division_type'] = $filters['division_type'];
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND is_active = :is_active";
            $params['is_active'] = $filters['is_active'];
        }

        $sql .= " ORDER BY name ASC";

        // Calcular OFFSET
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);

        // Bind de parâmetros LIMIT e OFFSET como inteiros
        foreach ($params as $key => $value) {
            if ($key === 'limit' || $key === 'offset') {
                $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':' . $key, $value);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Contar total de produtos com filtros (para paginação)
     */
    public static function countSearch(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM " . static::$table . " WHERE deleted = 0";
        $params = [];

        if (!empty($filters['name'])) {
            $sql .= " AND name LIKE :name";
            $params['name'] = '%' . $filters['name'] . '%';
        }

        if (!empty($filters['ncm'])) {
            // Normalizar NCM removendo pontuação (conforme tarefas.txt)
            $ncmClean = preg_replace('/[^0-9]/', '', $filters['ncm']);

            // Se tem pelo menos 4 dígitos, fazer busca flexível
            if (strlen($ncmClean) >= 4) {
                // Buscar tanto pelo NCM formatado quanto pelo NCM limpo
                $sql .= " AND (ncm LIKE :ncm_original OR REPLACE(REPLACE(ncm, '.', ''), '-', '') LIKE :ncm_clean)";
                $params['ncm_original'] = '%' . $filters['ncm'] . '%';
                $params['ncm_clean'] = '%' . $ncmClean . '%';
            } else {
                // Para buscas menores, usar busca normal
                $sql .= " AND ncm LIKE :ncm";
                $params['ncm'] = '%' . $filters['ncm'] . '%';
            }
        }

        if (!empty($filters['division_type'])) {
            $sql .= " AND division_type = :division_type";
            $params['division_type'] = $filters['division_type'];
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND is_active = :is_active";
            $params['is_active'] = $filters['is_active'];
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }

    /**
     * Contar produtos por tipo de divisão
     */
    public static function countByDivisionType(): array
    {
        $sql = "SELECT division_type, COUNT(*) as total
                FROM " . static::$table . "
                WHERE deleted = 0 AND is_active = 1
                GROUP BY division_type";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar produtos por termo (para AJAX)
     */
    public static function searchByTerm(string $term, int $limit = 20): array
    {
        $params = [];
        $sql = "SELECT *, COALESCE(variant_description, description) as display_description
                FROM " . static::$table . "
                WHERE deleted = 0 AND is_active = 1";

        // Normalizar termo de busca
        $termClean = trim($term);
        $termNumbers = preg_replace('/[^0-9]/', '', $termClean);

        // Se o termo contém números (possível NCM)
        if (!empty($termNumbers) && strlen($termNumbers) >= 4) {
            // Busca por NCM com várias estratégias
            $sql .= " AND (
                -- NCM exato (com pontuação)
                ncm LIKE :ncm_exact
                -- NCM sem pontuação
                OR REPLACE(REPLACE(REPLACE(ncm, '.', ''), '-', ''), ' ', '') LIKE :ncm_clean
                -- NCM começando com os dígitos (para casos como 3038114 -> 30381120)
                OR REPLACE(REPLACE(REPLACE(ncm, '.', ''), '-', ''), ' ', '') LIKE :ncm_partial
            ";

            // Para 7 dígitos, adicionar variações
            if (strlen($termNumbers) === 7) {
                $sql .= "
                -- Adicionar 0 no final (3038114 -> 30381140)
                OR REPLACE(REPLACE(REPLACE(ncm, '.', ''), '-', ''), ' ', '') LIKE :ncm_with_zero
                -- Remover último dígito (3038114 -> 303811)
                OR REPLACE(REPLACE(REPLACE(ncm, '.', ''), '-', ''), ' ', '') LIKE :ncm_without_last
                ";
                $params['ncm_with_zero'] = $termNumbers . '0%';
                $params['ncm_without_last'] = substr($termNumbers, 0, 6) . '%';
            }

            $sql .= ")";

            $params['ncm_exact'] = '%' . $termClean . '%';
            $params['ncm_clean'] = '%' . $termNumbers . '%';
            $params['ncm_partial'] = $termNumbers . '%';
        } else {
            // Busca por descrição
            $sql .= " AND (
                name LIKE :term_name
                OR description LIKE :term_desc
                OR variant_description LIKE :term_variant
            )";
            $params['term_name'] = '%' . $termClean . '%';
            $params['term_desc'] = '%' . $termClean . '%';
            $params['term_variant'] = '%' . $termClean . '%';
        }

        // Ordenação: primeiro por match exato no NCM, depois por descrição
        if (!empty($termNumbers) && strlen($termNumbers) >= 4) {
            // Se busca por números, ordenar por relevância do NCM
            $sql .= " ORDER BY
                    CASE
                        WHEN REPLACE(REPLACE(REPLACE(ncm, '.', ''), '-', ''), ' ', '') = :order_ncm_exact THEN 1
                        WHEN REPLACE(REPLACE(REPLACE(ncm, '.', ''), '-', ''), ' ', '') LIKE :order_ncm_start THEN 2
                        ELSE 3
                    END,
                    COALESCE(variant_description, description) ASC
                    LIMIT :limit";

            $params['order_ncm_exact'] = $termNumbers;
            $params['order_ncm_start'] = $termNumbers . '%';
        } else {
            // Se busca por texto, ordenar apenas por descrição
            $sql .= " ORDER BY COALESCE(variant_description, description) ASC LIMIT :limit";
        }

        $params['limit'] = $limit;

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);

        // Bind dos parâmetros (PDO exige bind separado para LIMIT)
        foreach ($params as $key => $value) {
            if ($key === 'limit') {
                $stmt->bindValue(":$key", $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}