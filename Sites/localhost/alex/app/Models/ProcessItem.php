<?php

/**
 * Model ProcessItem
 * Gerencia itens dos processos de importação
 */
class ProcessItem
{
    protected static string $table = 'process_items';

    /**
     * Buscar itens de um processo
     */
    public static function getByProcessId(int $processId): array
    {
        $sql = "SELECT pi.*, p.ncm,
                       COALESCE(p.variant_description, p.description) as description,
                       p.ii_rate, p.ipi_rate,
                       p.pis_rate, p.cofins_rate, p.icms_rate, p.division_type,
                       p.rfb_min, p.rfb_max
                FROM " . static::$table . " pi
                LEFT JOIN products p ON pi.product_id = p.id
                WHERE pi.process_id = :process_id
                ORDER BY pi.id ASC";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['process_id' => $processId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar item por ID
     */
    public static function findById(int $id): ?array
    {
        $sql = "SELECT pi.*, p.ncm,
                       COALESCE(p.variant_description, p.description) as description,
                       p.ii_rate, p.ipi_rate,
                       p.pis_rate, p.cofins_rate, p.icms_rate, p.division_type,
                       p.rfb_min, p.rfb_max
                FROM " . static::$table . " pi
                LEFT JOIN products p ON pi.product_id = p.id
                WHERE pi.id = :id";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Criar novo item do processo
     */
    public static function create(array $data): int
    {
        // Calcular valores automáticamente
        $calculatedData = self::calculateValues($data);

        $sql = "INSERT INTO " . static::$table . "
                (process_id, product_id, quantity, unit, weight_kg, gross_weight, weight_discount, net_weight,
                 unit_price_usd, total_fob_input, total_fob_usd, freight_ttl_kg, freight_usd, insurance_usd,
                 cif_usd, cif_brl, ii_value, ipi_value, pis_value, cofins_value, icms_value,
                 total_taxes, total_cost_brl, notes, rfb_used, rfb_margin, inv_used, rfb_option)
                VALUES (:process_id, :product_id, :quantity, :unit, :weight_kg, :gross_weight, :weight_discount, :net_weight,
                        :unit_price_usd, :total_fob_input, :total_fob_usd, :freight_ttl_kg, :freight_usd, :insurance_usd,
                        :cif_usd, :cif_brl, :ii_value, :ipi_value, :pis_value, :cofins_value, :icms_value,
                        :total_taxes, :total_cost_brl, :notes, :rfb_used, :rfb_margin, :inv_used, :rfb_option)";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($calculatedData);

        return $pdo->lastInsertId();
    }

    /**
     * Atualizar item do processo
     */
    public static function update(int $id, array $data): bool
    {
        // Calcular valores automáticamente
        $calculatedData = self::calculateValues($data);
        $calculatedData['id'] = $id;

        $sql = "UPDATE " . static::$table . "
                SET process_id = :process_id, product_id = :product_id, quantity = :quantity,
                    unit = :unit, weight_kg = :weight_kg, gross_weight = :gross_weight,
                    weight_discount = :weight_discount, net_weight = :net_weight,
                    unit_price_usd = :unit_price_usd, total_fob_input = :total_fob_input,
                    total_fob_usd = :total_fob_usd, freight_ttl_kg = :freight_ttl_kg,
                    freight_usd = :freight_usd, insurance_usd = :insurance_usd,
                    cif_usd = :cif_usd, cif_brl = :cif_brl,
                    ii_value = :ii_value, ipi_value = :ipi_value, pis_value = :pis_value,
                    cofins_value = :cofins_value, icms_value = :icms_value,
                    total_taxes = :total_taxes, total_cost_brl = :total_cost_brl, notes = :notes,
                    rfb_used = :rfb_used, rfb_margin = :rfb_margin, inv_used = :inv_used, rfb_option = :rfb_option
                WHERE id = :id";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($calculatedData);
    }

    /**
     * Excluir item do processo
     */
    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM " . static::$table . " WHERE id = :id";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Calcular valores do item
     */
    private static function calculateValues(array $data): array
    {
        // Obter informações do produto para cálculo de impostos
        $product = Product::findById($data['product_id']);
        if (!$product) {
            throw new Exception('Produto não encontrado');
        }

        // Obter informações do processo para taxa de câmbio
        $process = Process::findById($data['process_id']);
        if (!$process) {
            throw new Exception('Processo não encontrado');
        }

        $quantity = (float)$data['quantity'];
        $unitPrice = (float)$data['unit_price_usd'];
        $exchangeRate = (float)$process['exchange_rate'];

        // Calcular valores básicos
        // Se temos total_fob_input, usamos ele, senão calculamos
        $totalFobUsd = (float)($data['total_fob_input'] ?? ($quantity * $unitPrice));

        // Usar freight_ttl_kg ao invés de freight_usd para CIF
        $freightTtlKg = (float)($data['freight_ttl_kg'] ?? 0);
        $freightUsd = (float)($data['freight_usd'] ?? 0);
        $insuranceUsd = (float)($data['insurance_usd'] ?? 0);

        // CIF = Total FOB + TTL/KG (conforme tarefas.txt)
        $cifUsd = $totalFobUsd + $freightTtlKg;
        $cifBrl = $cifUsd * $exchangeRate;

        // Calcular impostos - usar alíquotas editadas se fornecidas, senão usar do produto
        $iiRate = isset($data['ii_rate']) ? (float)$data['ii_rate'] : (float)$product['ii_rate'];
        $ipiRate = isset($data['ipi_rate']) ? (float)$data['ipi_rate'] : (float)$product['ipi_rate'];
        $pisRate = isset($data['pis_rate']) ? (float)$data['pis_rate'] : (float)$product['pis_rate'];
        $cofinsRate = isset($data['cofins_rate']) ? (float)$data['cofins_rate'] : (float)$product['cofins_rate'];
        $icmsRate = isset($data['icms_rate']) ? (float)$data['icms_rate'] : (float)$product['icms_rate'];

        $iiValue = $cifBrl * ($iiRate / 100);
        $ipiBase = $cifBrl + $iiValue;
        $ipiValue = $ipiBase * ($ipiRate / 100);

        $pisBase = $cifBrl + $iiValue + $ipiValue;
        $pisValue = $pisBase * ($pisRate / 100);

        $cofinsBase = $cifBrl + $iiValue + $ipiValue;
        $cofinsValue = $cofinsBase * ($cofinsRate / 100);

        $icmsBase = $cifBrl + $iiValue + $ipiValue + $pisValue + $cofinsValue;
        $icmsValue = $icmsBase * ($icmsRate / 100);

        $totalTaxes = $iiValue + $ipiValue + $pisValue + $cofinsValue + $icmsValue;
        $totalCostBrl = $cifBrl + $totalTaxes;

        return [
            'process_id' => $data['process_id'],
            'product_id' => $data['product_id'],
            'quantity' => $quantity,
            'unit' => $data['unit'] ?? 'UN',
            'weight_kg' => (float)($data['weight_kg'] ?? $data['gross_weight'] ?? 0), // Manter compatibilidade
            'gross_weight' => (float)($data['gross_weight'] ?? 0),
            'weight_discount' => (float)($data['weight_discount'] ?? 0),
            'net_weight' => (float)($data['net_weight'] ?? 0),
            'unit_price_usd' => $unitPrice,
            'total_fob_input' => (float)($data['total_fob_input'] ?? 0),
            'total_fob_usd' => $totalFobUsd,
            'freight_ttl_kg' => (float)($data['freight_ttl_kg'] ?? 0),
            'freight_usd' => $freightUsd,
            'insurance_usd' => $insuranceUsd,
            'cif_usd' => $cifUsd,
            'cif_brl' => $cifBrl,
            'ii_value' => $iiValue,
            'ipi_value' => $ipiValue,
            'pis_value' => $pisValue,
            'cofins_value' => $cofinsValue,
            'icms_value' => $icmsValue,
            'total_taxes' => $totalTaxes,
            'total_cost_brl' => $totalCostBrl,
            'notes' => $data['notes'] ?? null,
            'rfb_used' => (float)($data['rfb_used'] ?? 0),
            'rfb_margin' => (float)($data['rfb_margin'] ?? 0),
            'inv_used' => (float)($data['inv_used'] ?? 0),
            'rfb_option' => $data['rfb_option'] ?? 'custom'
        ];
    }

    /**
     * Obter totais dos itens de um processo
     */
    public static function getTotalsByProcessId(int $processId): array
    {
        $sql = "SELECT
                    SUM(total_fob_usd) as total_fob_usd,
                    SUM(freight_usd) as total_freight_usd,
                    SUM(freight_ttl_kg) as total_freight_ttl_kg,
                    SUM(insurance_usd) as total_insurance_usd,
                    SUM(cif_usd) as total_cif_usd,
                    SUM(cif_brl) as total_cif_brl,
                    SUM(ii_value) as total_ii,
                    SUM(ipi_value) as total_ipi,
                    SUM(pis_value) as total_pis,
                    SUM(cofins_value) as total_cofins,
                    SUM(icms_value) as total_icms,
                    SUM(total_taxes) as total_taxes,
                    SUM(total_cost_brl) as total_cost_brl,
                    COUNT(*) as total_items
                FROM " . static::$table . "
                WHERE process_id = :process_id";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['process_id' => $processId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: [
            'total_fob_usd' => 0,
            'total_freight_usd' => 0,
            'total_freight_ttl_kg' => 0,
            'total_insurance_usd' => 0,
            'total_cif_usd' => 0,
            'total_cif_brl' => 0,
            'total_ii' => 0,
            'total_ipi' => 0,
            'total_pis' => 0,
            'total_cofins' => 0,
            'total_icms' => 0,
            'total_taxes' => 0,
            'total_cost_brl' => 0,
            'total_items' => 0
        ];
    }

    /**
     * Atualizar totais do processo baseado nos itens
     */
    public static function updateProcessTotals(int $processId): bool
    {
        $totals = self::getTotalsByProcessId($processId);

        $sql = "UPDATE processes
                SET total_fob_usd = :total_fob_usd,
                    total_insurance_usd = :total_insurance_usd,
                    total_cif_usd = :total_cif_usd,
                    total_cif_brl = :total_cif_brl,
                    total_taxes_brl = :total_taxes,
                    total_cost_brl = :total_cost_brl,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :process_id";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            'process_id' => $processId,
            'total_fob_usd' => $totals['total_fob_usd'],
            'total_insurance_usd' => $totals['total_insurance_usd'],
            'total_cif_usd' => $totals['total_cif_usd'],
            'total_cif_brl' => $totals['total_cif_brl'],
            'total_taxes' => $totals['total_taxes'],
            'total_cost_brl' => $totals['total_cost_brl']
        ]);
    }

    /**
     * Recalcular Frete TTL/KG de todos os itens do processo
     * Usado quando peso total muda (novo item, edição, exclusão)
     */
    public static function recalculateAllFreights(int $processId): bool
    {
        try {
            // Obter dados do processo
            $process = Process::findById($processId);
            if (!$process || $process['total_freight_usd'] <= 0) {
                return true; // Sem frete para calcular
            }

            $totalFreightUsd = (float)$process['total_freight_usd'];

            // Obter peso bruto total de todos os itens
            $sql = "SELECT SUM(gross_weight) as total_gross_weight FROM process_items WHERE process_id = :process_id";
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['process_id' => $processId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $totalGrossWeight = (float)($result['total_gross_weight'] ?? 0);

            if ($totalGrossWeight <= 0) {
                return true; // Sem peso para calcular
            }

            // Buscar todos os itens do processo
            $itemsSql = "SELECT id, gross_weight FROM process_items WHERE process_id = :process_id";
            $itemsStmt = $pdo->prepare($itemsSql);
            $itemsStmt->execute(['process_id' => $processId]);
            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Recalcular frete para cada item
            foreach ($items as $item) {
                $itemGrossWeight = (float)$item['gross_weight'];

                // Calcular proporção e frete do item
                $weightProportion = $itemGrossWeight / $totalGrossWeight;
                $itemFreightTtlKg = $totalFreightUsd * $weightProportion;

                // Atualizar apenas o freight_ttl_kg do item
                $updateSql = "UPDATE process_items SET freight_ttl_kg = :freight_ttl_kg WHERE id = :id";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->execute([
                    'freight_ttl_kg' => $itemFreightTtlKg,
                    'id' => $item['id']
                ]);

                // Recalcular CIF do item (CIF = FOB + FreightTtlKg)
                $recalcSql = "UPDATE process_items
                             SET cif_usd = total_fob_usd + freight_ttl_kg,
                                 cif_brl = (total_fob_usd + freight_ttl_kg) * :exchange_rate
                             WHERE id = :id";
                $recalcStmt = $pdo->prepare($recalcSql);
                $recalcStmt->execute([
                    'exchange_rate' => (float)$process['exchange_rate'],
                    'id' => $item['id']
                ]);
            }

            return true;

        } catch (Exception $e) {
            error_log("Erro ao recalcular fretes: " . $e->getMessage());
            return false;
        }
    }
}