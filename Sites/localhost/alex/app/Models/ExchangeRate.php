<?php

/**
 * Model ExchangeRate
 * Gerencia taxas de câmbio PTAX
 */
class ExchangeRate
{
    protected static string $table = 'exchange_rates';

    /**
     * Buscar taxa atual (sempre do dia anterior, conforme tarefas.txt)
     */
    public static function getCurrentRate(string $currency = 'USD'): ?array
    {
        // Calcular o dia anterior útil
        $dateObj = new DateTime();

        // Se é domingo ou segunda-feira, buscar sexta-feira
        $dayOfWeek = $dateObj->format('N');
        if ($dayOfWeek == 7) { // Domingo
            $dateObj->modify('-2 days'); // Voltar para sexta
        } elseif ($dayOfWeek == 1) { // Segunda-feira
            $dateObj->modify('-3 days'); // Voltar para sexta
        } else {
            $dateObj->modify('-1 day'); // Dia anterior
        }

        $targetDate = $dateObj->format('Y-m-d');

        // Primeiro tentar buscar a taxa exata do dia anterior
        $sql = "SELECT * FROM " . static::$table . "
                WHERE date = :date AND currency = :currency AND is_active = 1
                LIMIT 1";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['date' => $targetDate, 'currency' => $currency]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se não encontrou a taxa do dia anterior, buscar a mais recente antes dessa data
        if (!$result) {
            $sql = "SELECT * FROM " . static::$table . "
                    WHERE date <= :date AND currency = :currency AND is_active = 1
                    ORDER BY date DESC
                    LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['date' => $targetDate, 'currency' => $currency]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return $result ?: null;
    }

    /**
     * Buscar taxa por data específica
     */
    public static function getRateByDate(string $date, string $currency = 'USD'): ?array
    {
        $sql = "SELECT * FROM " . static::$table . "
                WHERE date = :date AND currency = :currency AND is_active = 1";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['date' => $date, 'currency' => $currency]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Verificar se já existe taxa para a data
     */
    public static function existsForDate(string $date, string $currency = 'USD'): bool
    {
        $sql = "SELECT COUNT(*) as total FROM " . static::$table . "
                WHERE date = :date AND currency = :currency";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['date' => $date, 'currency' => $currency]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }

    /**
     * Criar ou atualizar taxa de câmbio
     */
    public static function upsert(array $data): bool
    {
        $sql = "INSERT INTO " . static::$table . "
                (date, currency, rate, source, is_active)
                VALUES (:date, :currency, :rate, :source, :is_active)
                ON DUPLICATE KEY UPDATE
                rate = VALUES(rate),
                source = VALUES(source),
                updated_at = CURRENT_TIMESTAMP";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'date' => $data['date'],
            'currency' => $data['currency'] ?? 'USD',
            'rate' => $data['rate'],
            'source' => $data['source'] ?? 'BACEN',
            'is_active' => $data['is_active'] ?? 1
        ]);
    }

    /**
     * Buscar taxa do dia útil anterior (ontem ou sexta-feira se for segunda)
     */
    public static function getPreviousBusinessDayRate(string $currency = 'USD'): ?array
    {
        // Calcular data do último dia útil
        $date = new DateTime();

        // Se é segunda-feira, voltar para sexta-feira
        if ($date->format('N') == 1) { // 1 = segunda-feira
            $date->modify('-3 days'); // Voltar para sexta
        } else {
            $date->modify('-1 day'); // Dia anterior
        }

        return self::getRateByDate($date->format('Y-m-d'), $currency);
    }

    /**
     * Buscar histórico de taxas
     */
    public static function getHistory(int $days = 30, string $currency = 'USD'): array
    {
        $sql = "SELECT * FROM " . static::$table . "
                WHERE currency = :currency AND is_active = 1
                ORDER BY date DESC
                LIMIT :days";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':currency', $currency, PDO::PARAM_STR);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar taxa PTAX do Banco Central via API
     * Retorna taxa do último dia útil
     */
    public static function fetchFromBacen(?string $date = null): ?float
    {
        try {
            // Se não foi especificada data, usar último dia útil
            if (!$date) {
                $dateObj = new DateTime();
                // Se é segunda-feira, buscar sexta-feira
                if ($dateObj->format('N') == 1) {
                    $dateObj->modify('-3 days');
                } else {
                    $dateObj->modify('-1 day');
                }
                $date = $dateObj->format('m-d-Y');
            } else {
                // Converter formato se necessário
                $dateObj = DateTime::createFromFormat('Y-m-d', $date);
                $date = $dateObj->format('m-d-Y');
            }

            // URL da API do Banco Central
            $url = "https://olinda.bcb.gov.br/olinda/servico/PTAX/versao/v1/odata/CotacaoMoedaDia(moeda=@moeda,dataCotacao=@dataCotacao)?@moeda='USD'&@dataCotacao='{$date}'&\$format=json";

            // Fazer requisição
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'Sistema Aduaneiro/1.0'
                ]
            ]);

            $response = file_get_contents($url, false, $context);

            if ($response === false) {
                error_log("Erro ao buscar PTAX: Falha na requisição HTTP");
                return null;
            }

            $data = json_decode($response, true);

            if (!$data || !isset($data['value']) || empty($data['value'])) {
                error_log("Erro ao buscar PTAX: Dados não encontrados para a data {$date}");
                return null;
            }

            // Procurar por "Fechamento PTAX" primeiro, se não encontrar usar o último registro
            $cotacao = null;

            // Primeiro, procurar pelo boletim de "Fechamento PTAX"
            foreach ($data['value'] as $registro) {
                if (isset($registro['tipoBoletim']) && $registro['tipoBoletim'] === 'Fechamento PTAX') {
                    $cotacao = $registro;
                    break;
                }
            }

            // Se não encontrou "Fechamento PTAX", usar o último registro do array
            if (!$cotacao) {
                $cotacao = end($data['value']);
            }

            // Se ainda não tem cotação, usar o primeiro
            if (!$cotacao) {
                $cotacao = $data['value'][0];
            }

            $taxa = $cotacao['cotacaoVenda'] ?? $cotacao['cotacaoCompra'] ?? null;

            return $taxa ? (float)$taxa : null;

        } catch (Exception $e) {
            error_log("Erro ao buscar PTAX: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Atualizar taxa do dia se necessário (sempre busca a taxa do dia anterior)
     */
    public static function updateDailyRate(): bool
    {
        try {
            // Sempre buscar a taxa do dia anterior (conforme tarefas.txt)
            $dateObj = new DateTime();

            // Se é domingo ou segunda-feira, buscar sexta-feira
            $dayOfWeek = $dateObj->format('N');
            if ($dayOfWeek == 7) { // Domingo
                $dateObj->modify('-2 days'); // Voltar para sexta
            } elseif ($dayOfWeek == 1) { // Segunda-feira
                $dateObj->modify('-3 days'); // Voltar para sexta
            } else {
                $dateObj->modify('-1 day'); // Dia anterior
            }

            $targetDate = $dateObj->format('Y-m-d');

            // Verificar se já existe taxa para a data
            if (self::existsForDate($targetDate)) {
                // Se já existe, atualizar com a taxa mais recente
                $rate = self::fetchFromBacen($targetDate);
                if ($rate) {
                    return self::upsert([
                        'date' => $targetDate,
                        'currency' => 'USD',
                        'rate' => $rate,
                        'source' => 'BACEN',
                        'is_active' => 1
                    ]);
                }
                return true; // Já existe e não precisa atualizar
            }

            // Buscar taxa do BACEN para o dia anterior
            $rate = self::fetchFromBacen($targetDate);

            if (!$rate) {
                error_log("Não foi possível obter taxa PTAX do BACEN para {$targetDate}");
                return false;
            }

            // Salvar taxa
            return self::upsert([
                'date' => $targetDate,
                'currency' => 'USD',
                'rate' => $rate,
                'source' => 'BACEN',
                'is_active' => 1
            ]);

        } catch (Exception $e) {
            error_log("Erro ao atualizar taxa diária: " . $e->getMessage());
            return false;
        }
    }
}