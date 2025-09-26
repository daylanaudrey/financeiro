<?php

/**
 * Model Client
 * Gerencia importadores do sistema
 */
class Client
{
    protected static string $table = 'clients';

    /**
     * Buscar todos os clientes ativos
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
     * Buscar cliente por ID
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
     * Buscar cliente por documento (CPF/CNPJ)
     */
    public static function findByDocument(string $document): ?array
    {
        $sql = "SELECT * FROM " . static::$table . "
                WHERE document = :document AND deleted = 0";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['document' => $document]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Criar novo cliente
     */
    public static function create(array $data): int
    {
        $sql = "INSERT INTO " . static::$table . "
                (type, name, document, ie, im, address, number, complement, neighborhood,
                 city, state, zip_code, phone, mobile, email, contact_name, incoterm,
                 payment_terms, is_active)
                VALUES (:type, :name, :document, :ie, :im, :address, :number, :complement,
                        :neighborhood, :city, :state, :zip_code, :phone, :mobile, :email,
                        :contact_name, :incoterm, :payment_terms, :is_active)";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'type' => $data['type'],
            'name' => $data['name'],
            'document' => $data['document'],
            'ie' => $data['ie'] ?? null,
            'im' => $data['im'] ?? null,
            'address' => $data['address'] ?? null,
            'number' => $data['number'] ?? null,
            'complement' => $data['complement'] ?? null,
            'neighborhood' => $data['neighborhood'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'zip_code' => $data['zip_code'] ?? null,
            'phone' => $data['phone'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'email' => $data['email'] ?? null,
            'contact_name' => $data['contact_name'] ?? null,
            'incoterm' => $data['incoterm'] ?? null,
            'payment_terms' => $data['payment_terms'] ?? null,
            'is_active' => $data['is_active'] ?? 1
        ]);

        return $pdo->lastInsertId();
    }

    /**
     * Atualizar cliente
     */
    public static function update(int $id, array $data): bool
    {
        $sql = "UPDATE " . static::$table . "
                SET type = :type, name = :name, document = :document, ie = :ie, im = :im,
                    address = :address, number = :number, complement = :complement,
                    neighborhood = :neighborhood, city = :city, state = :state,
                    zip_code = :zip_code, phone = :phone, mobile = :mobile, email = :email,
                    contact_name = :contact_name, incoterm = :incoterm, payment_terms = :payment_terms,
                    is_active = :is_active, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND deleted = 0";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'type' => $data['type'],
            'name' => $data['name'],
            'document' => $data['document'],
            'ie' => $data['ie'] ?? null,
            'im' => $data['im'] ?? null,
            'address' => $data['address'] ?? null,
            'number' => $data['number'] ?? null,
            'complement' => $data['complement'] ?? null,
            'neighborhood' => $data['neighborhood'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'zip_code' => $data['zip_code'] ?? null,
            'phone' => $data['phone'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'email' => $data['email'] ?? null,
            'contact_name' => $data['contact_name'] ?? null,
            'incoterm' => $data['incoterm'] ?? null,
            'payment_terms' => $data['payment_terms'] ?? null,
            'is_active' => $data['is_active'] ?? 1
        ]);
    }

    /**
     * Soft delete do cliente
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
     * Verificar se documento já existe (para validação)
     */
    public static function documentExists(string $document, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM " . static::$table . "
                WHERE document = :document AND deleted = 0";

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $params = ['document' => $document];

        if ($excludeId) {
            $params['exclude_id'] = $excludeId;
        }

        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }

    /**
     * Buscar clientes com filtros
     */
    public static function search(array $filters = []): array
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE deleted = 0";
        $params = [];

        if (!empty($filters['name'])) {
            $sql .= " AND name LIKE :name";
            $params['name'] = '%' . $filters['name'] . '%';
        }

        if (!empty($filters['document'])) {
            $sql .= " AND document LIKE :document";
            $params['document'] = '%' . $filters['document'] . '%';
        }

        if (!empty($filters['type'])) {
            $sql .= " AND type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['city'])) {
            $sql .= " AND city LIKE :city";
            $params['city'] = '%' . $filters['city'] . '%';
        }

        if (!empty($filters['state'])) {
            $sql .= " AND state = :state";
            $params['state'] = $filters['state'];
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND is_active = :is_active";
            $params['is_active'] = $filters['is_active'];
        }

        $sql .= " ORDER BY name ASC";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Contar clientes por tipo
     */
    public static function countByType(): array
    {
        $sql = "SELECT type, COUNT(*) as total
                FROM " . static::$table . "
                WHERE deleted = 0 AND is_active = 1
                GROUP BY type";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Validar CPF
     */
    public static function validateCPF(string $cpf): bool
    {
        // Remove caracteres não numéricos
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Verifica se tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Calcula primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int)$cpf[$i] * (10 - $i);
        }
        $digit1 = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

        // Calcula segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int)$cpf[$i] * (11 - $i);
        }
        $digit2 = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

        // Verifica se os dígitos calculados conferem
        return $cpf[9] == $digit1 && $cpf[10] == $digit2;
    }

    /**
     * Validar CNPJ
     */
    public static function validateCNPJ(string $cnpj): bool
    {
        // Remove caracteres não numéricos
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        // Verifica se tem 14 dígitos
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Calcula primeiro dígito verificador
        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$cnpj[$i] * $weights1[$i];
        }
        $digit1 = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

        // Calcula segundo dígito verificador
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += (int)$cnpj[$i] * $weights2[$i];
        }
        $digit2 = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

        // Verifica se os dígitos calculados conferem
        return $cnpj[12] == $digit1 && $cnpj[13] == $digit2;
    }
}