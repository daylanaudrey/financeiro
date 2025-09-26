<?php

/**
 * Model Process
 * Gerencia processos de importação do sistema
 */
class Process
{
    protected static string $table = 'processes';

    /**
     * Status aduaneiros disponíveis
     */
    public static function getStatusOptions(): array
    {
        return [
            'PRE_EMBARQUE' => 'Pré Embarque',
            'EMBARCADO' => 'Embarcado',
            'REGISTRADO' => 'Registrado',
            'CANAL_VERDE' => 'Canal Verde',
            'CANAL_VERMELHO' => 'Canal Vermelho',
            'CANAL_CINZA' => 'Canal Cinza'
        ];
    }

    /**
     * Tipos de processo disponíveis
     */
    public static function getTypeOptions(): array
    {
        return [
            'NUMERARIO' => 'Numerário',
            'MAPA' => 'Mapa'
        ];
    }

    /**
     * Modais de transporte disponíveis
     */
    public static function getModalOptions(): array
    {
        return [
            'MARITIME' => 'Marítimo',
            'AIR' => 'Aéreo',
            'ROAD' => 'Rodoviário',
            'RAIL' => 'Ferroviário'
        ];
    }

    /**
     * Incoterms disponíveis
     */
    public static function getIncotermOptions(): array
    {
        return [
            'FOB' => 'FOB - Free On Board',
            'CIF' => 'CIF - Cost, Insurance and Freight',
            'CFR' => 'CFR - Cost and Freight',
            'EXW' => 'EXW - Ex Works',
            'FCA' => 'FCA - Free Carrier',
            'CPT' => 'CPT - Carriage Paid To',
            'CIP' => 'CIP - Carriage and Insurance Paid',
            'DAP' => 'DAP - Delivered at Place',
            'DPU' => 'DPU - Delivered at Place Unloaded',
            'DDP' => 'DDP - Delivered Duty Paid'
        ];
    }

    /**
     * Buscar todos os processos ativos
     */
    public static function getAll(): array
    {
        $sql = "SELECT p.*, c.name as client_name, c.type as client_type
                FROM " . static::$table . " p
                LEFT JOIN clients c ON p.client_id = c.id
                WHERE p.deleted = 0
                ORDER BY p.process_date DESC, p.code ASC";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar processo por ID
     */
    public static function findById(int $id): ?array
    {
        $sql = "SELECT p.*, c.name as client_name, c.type as client_type
                FROM " . static::$table . " p
                LEFT JOIN clients c ON p.client_id = c.id
                WHERE p.id = :id AND p.deleted = 0";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Buscar processo por código
     */
    public static function findByCode(string $code): ?array
    {
        $sql = "SELECT * FROM " . static::$table . "
                WHERE code = :code AND deleted = 0";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['code' => $code]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Criar novo processo
     */
    public static function create(array $data): int
    {
        $sql = "INSERT INTO " . static::$table . "
                (code, client_id, type, status, process_date, arrival_date, clearance_date,
                 modal, destination_port_id, container_number, bl_number, incoterm, total_fob_usd, total_freight_usd,
                 total_insurance_usd, total_cif_usd, exchange_rate, total_cif_brl, notes, created_by)
                VALUES (:code, :client_id, :type, :status, :process_date, :arrival_date, :clearance_date,
                        :modal, :destination_port_id, :container_number, :bl_number, :incoterm, :total_fob_usd, :total_freight_usd,
                        :total_insurance_usd, :total_cif_usd, :exchange_rate, :total_cif_brl, :notes, :created_by)";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'code' => $data['code'],
            'client_id' => $data['client_id'],
            'type' => $data['type'],
            'status' => $data['status'],
            'process_date' => $data['process_date'],
            'arrival_date' => $data['arrival_date'] ?? null,
            'clearance_date' => $data['clearance_date'] ?? null,
            'modal' => $data['modal'],
            'destination_port_id' => $data['destination_port_id'] ?? null,
            'container_number' => $data['container_number'] ?? null,
            'bl_number' => $data['bl_number'] ?? null,
            'incoterm' => $data['incoterm'],
            'total_fob_usd' => $data['total_fob_usd'] ?? 0,
            'total_freight_usd' => $data['total_freight_usd'] ?? 0,
            'total_insurance_usd' => $data['total_insurance_usd'] ?? 0,
            'total_cif_usd' => $data['total_cif_usd'] ?? 0,
            'exchange_rate' => $data['exchange_rate'],
            'total_cif_brl' => $data['total_cif_brl'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'created_by' => $_SESSION['user_id'] ?? null
        ]);

        return $pdo->lastInsertId();
    }

    /**
     * Atualizar processo
     */
    public static function update(int $id, array $data): bool
    {
        $sql = "UPDATE " . static::$table . "
                SET code = :code, client_id = :client_id, type = :type, status = :status,
                    process_date = :process_date, arrival_date = :arrival_date, clearance_date = :clearance_date,
                    modal = :modal, destination_port_id = :destination_port_id, container_number = :container_number, bl_number = :bl_number,
                    incoterm = :incoterm, total_fob_usd = :total_fob_usd, total_freight_usd = :total_freight_usd,
                    total_insurance_usd = :total_insurance_usd, total_cif_usd = :total_cif_usd,
                    exchange_rate = :exchange_rate, total_cif_brl = :total_cif_brl, notes = :notes,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND deleted = 0";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'code' => $data['code'],
            'client_id' => $data['client_id'],
            'type' => $data['type'],
            'status' => $data['status'],
            'process_date' => $data['process_date'],
            'arrival_date' => $data['arrival_date'] ?? null,
            'clearance_date' => $data['clearance_date'] ?? null,
            'modal' => $data['modal'],
            'destination_port_id' => $data['destination_port_id'] ?? null,
            'container_number' => $data['container_number'] ?? null,
            'bl_number' => $data['bl_number'] ?? null,
            'incoterm' => $data['incoterm'],
            'total_fob_usd' => $data['total_fob_usd'] ?? 0,
            'total_freight_usd' => $data['total_freight_usd'] ?? 0,
            'total_insurance_usd' => $data['total_insurance_usd'] ?? 0,
            'total_cif_usd' => $data['total_cif_usd'] ?? 0,
            'exchange_rate' => $data['exchange_rate'],
            'total_cif_brl' => $data['total_cif_brl'] ?? 0,
            'notes' => $data['notes'] ?? null
        ]);
    }

    /**
     * Soft delete do processo
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
     * Verificar se código já existe (para validação)
     */
    public static function codeExists(string $code, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM " . static::$table . "
                WHERE code = :code";

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $params = ['code' => $code];

        if ($excludeId) {
            $params['exclude_id'] = $excludeId;
        }

        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }

    /**
     * Buscar processos com filtros
     */
    public static function search(array $filters = []): array
    {
        $sql = "SELECT p.*, c.name as client_name, c.type as client_type
                FROM " . static::$table . " p
                LEFT JOIN clients c ON p.client_id = c.id
                WHERE p.deleted = 0";
        $params = [];

        if (!empty($filters['code'])) {
            $sql .= " AND p.code LIKE :code";
            $params['code'] = '%' . $filters['code'] . '%';
        }

        if (!empty($filters['client_id'])) {
            $sql .= " AND p.client_id = :client_id";
            $params['client_id'] = $filters['client_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND p.type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['modal'])) {
            $sql .= " AND p.modal = :modal";
            $params['modal'] = $filters['modal'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND p.process_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND p.process_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $sql .= " ORDER BY p.process_date DESC, p.code ASC";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Contar processos por status
     */
    public static function countByStatus(): array
    {
        $sql = "SELECT status, COUNT(*) as total
                FROM " . static::$table . "
                WHERE deleted = 0
                GROUP BY status";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calcular valor CIF em BRL baseado na taxa de câmbio
     */
    public static function calculateCifBrl(float $cifUsd, float $exchangeRate): float
    {
        return $cifUsd * $exchangeRate;
    }

    /**
     * Calcular valor CIF em USD (FOB + Frete + Seguro)
     */
    public static function calculateCifUsd(float $fobUsd, float $freightUsd, float $insuranceUsd): float
    {
        return $fobUsd + $freightUsd + $insuranceUsd;
    }

    /**
     * Obter próximo número sequencial para código do processo
     */
    public static function getNextProcessNumber(): string
    {
        $sql = "SELECT MAX(CAST(SUBSTRING(code, 4) AS UNSIGNED)) as last_number
                FROM " . static::$table . "
                WHERE code LIKE 'IMP%'";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $nextNumber = ($result['last_number'] ?? 0) + 1;
        return 'IMP' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}