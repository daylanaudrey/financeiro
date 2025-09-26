<?php

/**
 * NCMController
 * Gerencia consultas da tabela NCM
 */
class NCMController
{
    /**
     * Buscar NCMs via AJAX
     */
    public function search(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(['success' => false, 'message' => 'Método não permitido']);
        }

        Permission::requireAuth();

        $query = trim($_GET['q'] ?? '');

        if (strlen($query) < 3) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Digite pelo menos 3 caracteres para buscar'
            ]);
        }

        try {
            $filters = [];

            // Se o query parece um código NCM (números com ou sem pontos)
            if (preg_match('/^\d+[\d\.]*$/', $query)) {
                $filters['code'] = $query;
            } else {
                // Buscar na descrição
                $filters['description'] = $query;
            }

            $results = NCM::search($filters);

            // Formatar resultados para o frontend
            $formattedResults = [];
            foreach ($results as $ncm) {
                $formattedResults[] = [
                    'id' => $ncm['id'],
                    'code' => NCM::formatCode($ncm['code']),
                    'description' => $ncm['description'],
                    'unit' => $ncm['unit'],
                    'display' => NCM::formatCode($ncm['code']) . ' - ' . $ncm['description']
                ];
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $formattedResults,
                'count' => count($formattedResults)
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Erro na busca: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Buscar detalhes de um NCM específico
     */
    public function details(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(['success' => false, 'message' => 'Método não permitido']);
        }

        Permission::requireAuth();

        $code = trim($_GET['code'] ?? '');

        if (empty($code)) {
            $this->jsonResponse(['success' => false, 'message' => 'Código NCM é obrigatório']);
        }

        try {
            $ncm = NCM::findByCode($code);

            if (!$ncm) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'NCM não encontrado na base oficial'
                ]);
            }

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'id' => $ncm['id'],
                    'code' => NCM::formatCode($ncm['code']),
                    'description' => $ncm['description'],
                    'unit' => $ncm['unit'],
                    'ii_rate' => $ncm['ii_rate'],
                    'ipi_rate' => $ncm['ipi_rate'],
                    'pis_rate' => $ncm['pis_rate'],
                    'cofins_rate' => $ncm['cofins_rate'],
                    'icms_rate' => $ncm['icms_rate'],
                    'start_date' => $ncm['start_date'],
                    'end_date' => $ncm['end_date'],
                    'is_active' => $ncm['is_active']
                ]
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Erro ao buscar NCM: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obter estatísticas da tabela NCM
     */
    public function stats(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(['success' => false, 'message' => 'Método não permitido']);
        }

        Permission::requireAuth();

        try {
            $stats = NCM::getStats();

            $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Resposta JSON
     */
    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}