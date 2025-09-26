<?php
require_once 'BaseController.php';

class VaultController extends BaseController {
    private $vaultModel;
    private $accountModel;
    private $transactionModel;
    private $categoryModel;
    
    public function __construct() {
        parent::__construct();
        $this->vaultModel = new Vault();
        $this->accountModel = new Account();
        $this->transactionModel = new Transaction();
        $this->categoryModel = new Category();
    }
    
    public function index() {
        try {
            $orgId = $this->getCurrentOrgId();
            
            // Buscar vaults
            $vaults = $this->vaultModel->getVaultsWithGoals($orgId);
            
            // Buscar todas as contas para o select de origem (não precisamos mais de contas vault específicas)
            $accounts = $this->accountModel->getActiveAccountsByOrg($orgId);
            
            // Estatísticas
            $statistics = $this->vaultModel->getVaultStatistics($orgId);
            $categoryStats = $this->vaultModel->getVaultsByCategory($orgId);
            
            $data = [
                'title' => 'Vaults e Objetivos - Sistema Financeiro',
                'page' => 'vaults',
                'vaults' => $vaults,
                'accounts' => $accounts,
                'statistics' => $statistics,
                'categoryStats' => $categoryStats,
                'pageTitle' => 'Vaults e Objetivos'
            ];
            
            $this->render('layout', $data);
            
        } catch (Exception $e) {
            $this->handleError($e, 'Erro ao carregar vaults');
        }
    }
    
    public function create() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido');
            }
            
            $data = $_POST;
            $data['org_id'] = $this->getCurrentOrgId();
            $data['created_by'] = $_SESSION['user_id'] ?? null;
            
            $vaultId = $this->vaultModel->createVaultGoal($data);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Objetivo criado com sucesso!',
                'vault_id' => $vaultId
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function update() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido');
            }
            
            $id = $_POST['id'] ?? null;
            if (!$id) {
                throw new Exception('ID do vault não informado');
            }
            
            $data = $_POST;
            unset($data['id']);
            
            $this->vaultModel->updateVaultGoal($id, $data);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Objetivo atualizado com sucesso!'
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function delete() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido');
            }
            
            $id = $_POST['id'] ?? null;
            if (!$id) {
                throw new Exception('ID do vault não informado');
            }
            
            $this->vaultModel->delete($id);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Objetivo excluído com sucesso!'
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function getVault() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID do vault não informado');
            }
            
            $vault = $this->vaultModel->getVaultById($id);
            if (!$vault) {
                throw new Exception('Vault não encontrado');
            }
            
            // Buscar movimentações
            $movements = $this->vaultModel->getVaultMovements($id);
            
            $this->jsonResponse([
                'success' => true,
                'vault' => $vault,
                'movements' => $movements
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function addMovement() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido');
            }
            
            $vaultGoalId = $_POST['vault_goal_id'] ?? null;
            $transactionId = $_POST['transaction_id'] ?? null;
            $tipo = $_POST['tipo'] ?? null;
            $valor = $_POST['valor'] ?? null;
            $descricao = $_POST['descricao'] ?? null;
            
            if (!$vaultGoalId || !$transactionId || !$tipo || !$valor) {
                throw new Exception('Dados obrigatórios não informados');
            }
            
            $movementId = $this->vaultModel->addMovement($vaultGoalId, $transactionId, $tipo, $valor, $descricao);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Movimentação registrada com sucesso!',
                'movement_id' => $movementId
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function getStatistics() {
        try {
            $orgId = $this->getCurrentOrgId();
            
            $statistics = $this->vaultModel->getVaultStatistics($orgId);
            $categoryStats = $this->vaultModel->getVaultsByCategory($orgId);
            
            $this->jsonResponse([
                'success' => true,
                'statistics' => $statistics,
                'categoryStats' => $categoryStats
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    
    public function getVaultsWithGoals() {
        try {
            $orgId = $this->getCurrentOrgId();
            
            // Filtrar apenas objetivos ativos se solicitado
            $activeOnly = isset($_GET['active_only']) && $_GET['active_only'] == '1';
            
            $vaults = $this->vaultModel->getVaultsWithGoals($orgId);
            
            // Se solicitado apenas ativos, filtrar apenas não concluídos
            if ($activeOnly) {
                $vaults = array_filter($vaults, function($vault) {
                    return !$vault['concluido'];
                });
            }
            
            $this->jsonResponse([
                'success' => true,
                'vaults' => array_values($vaults) // Re-indexar array após filtro
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function deposit() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido');
            }
            
            // Dados do depósito
            $accountFromId = (int)($_POST['account_from'] ?? 0);
            $vaultGoalId = (int)($_POST['vault_goal_id'] ?? 0);
            $valor = $this->convertBrazilianCurrencyToDecimal($_POST['valor'] ?? '0');
            $dataCompetencia = $_POST['data_competencia'] ?? date('Y-m-d');
            $descricao = trim($_POST['descricao'] ?? 'Depósito para objetivo Vault');
            $observacoes = trim($_POST['observacoes'] ?? '') ?: null;
            
            // Validações
            if (!$accountFromId || !$vaultGoalId) {
                throw new Exception('Conta de origem e objetivo Vault são obrigatórios');
            }
            
            if ($valor <= 0) {
                throw new Exception('Valor deve ser maior que zero');
            }
            
            // Verificar se a conta de origem existe
            $accountFrom = $this->accountModel->findById($accountFromId);
            if (!$accountFrom) {
                throw new Exception('Conta de origem não encontrada');
            }
            
            // Verificar se o vault goal existe
            $vaultGoal = $this->vaultModel->getVaultById($vaultGoalId);
            if (!$vaultGoal) {
                throw new Exception('Objetivo Vault não encontrado');
            }
            
            // Verificar saldo suficiente
            if ($accountFrom['saldo_atual'] < $valor) {
                throw new Exception('Saldo insuficiente na conta de origem');
            }
            
            // 1. Obter ID da categoria "Vaults"
            $vaultsCategoryId = $this->getVaultsCategoryId();
            
            // 2. Criar transação de débito na conta origem
            $transactionData = [
                'org_id' => $this->getCurrentOrgId(),
                'account_id' => $accountFromId,
                'kind' => 'saida',
                'valor' => $valor,
                'data_competencia' => $dataCompetencia,
                'data_pagamento' => $dataCompetencia,
                'status' => 'confirmado',
                'category_id' => $vaultsCategoryId,
                'contact_id' => null,
                'descricao' => $descricao,
                'observacoes' => $observacoes,
                'created_by' => $_SESSION['user_id'] ?? 1
            ];
            
            $transactionId = $this->transactionModel->createTransaction($transactionData);
            
            if (!$transactionId) {
                throw new Exception('Erro ao criar transação de débito');
            }
            
            // 3. Registrar movimento no vault
            $movementId = $this->vaultModel->addMovement(
                $vaultGoalId, 
                $transactionId, 
                'deposito', 
                $valor, 
                $descricao
            );
            
            // 4. Atualizar valor atual do vault goal
            $novoValorAtual = $vaultGoal['valor_atual'] + $valor;
            $this->vaultModel->update($vaultGoalId, [
                'valor_atual' => $novoValorAtual
            ]);
            
            // 5. Verificar se objetivo foi alcançado
            if ($novoValorAtual >= $vaultGoal['valor_meta']) {
                $this->vaultModel->update($vaultGoalId, [
                    'concluido' => 1,
                    'data_conclusao' => date('Y-m-d')
                ]);
            }
            
            $message = 'Depósito realizado com sucesso!';
            if ($novoValorAtual >= $vaultGoal['valor_meta']) {
                $message .= ' 🎉 Parabéns! Você atingiu sua meta!';
            }
            
            $this->jsonResponse([
                'success' => true,
                'message' => $message,
                'transaction_id' => $transactionId,
                'movement_id' => $movementId
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    private function convertBrazilianCurrencyToDecimal($value) {
        if (is_numeric($value)) {
            return floatval($value);
        }
        
        $value = str_replace(['R$', ' ', '.'], '', $value);
        $value = str_replace(',', '.', $value);
        return floatval($value);
    }
    
    private function getVaultsCategoryId() {
        // Verificar se a categoria "Vaults" já existe
        $categories = $this->categoryModel->getCategoriesByOrg(1);
        
        foreach ($categories as $category) {
            if (strtolower($category['nome']) === 'vaults') {
                return $category['id'];
            }
        }
        
        // Se não existe, usar a primeira categoria disponível ou retornar null
        if (!empty($categories)) {
            // Procurar por uma categoria de despesa
            foreach ($categories as $category) {
                if ($category['tipo'] === 'despesa') {
                    return $category['id'];
                }
            }
            // Se não encontrou categoria de despesa, usar a primeira disponível
            return $categories[0]['id'];
        }
        
        return null; // Se não há categorias, não usar categoria
    }
    
    public function withdraw() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido');
            }
            
            // Dados do resgate
            $accountToId = (int)($_POST['account_to'] ?? 0);
            $vaultGoalId = (int)($_POST['vault_goal_id'] ?? 0);
            $valor = $this->convertBrazilianCurrencyToDecimal($_POST['valor'] ?? '0');
            $dataCompetencia = $_POST['data_competencia'] ?? date('Y-m-d');
            $descricao = trim($_POST['descricao'] ?? 'Resgate de objetivo Vault');
            $observacoes = trim($_POST['observacoes'] ?? '') ?: null;
            
            // Validações
            if (!$accountToId || !$vaultGoalId) {
                throw new Exception('Conta de destino e objetivo Vault são obrigatórios');
            }
            
            if ($valor <= 0) {
                throw new Exception('Valor deve ser maior que zero');
            }
            
            // Verificar se a conta de destino existe
            $accountTo = $this->accountModel->findById($accountToId);
            if (!$accountTo) {
                throw new Exception('Conta de destino não encontrada');
            }
            
            // Verificar se o vault goal existe e tem saldo suficiente
            $vaultGoal = $this->vaultModel->getVaultById($vaultGoalId);
            if (!$vaultGoal) {
                throw new Exception('Objetivo Vault não encontrado');
            }
            
            // Verificar saldo suficiente no vault
            if ($vaultGoal['valor_atual'] < $valor) {
                throw new Exception('Saldo insuficiente no Vault. Disponível: R$ ' . number_format($vaultGoal['valor_atual'], 2, ',', '.'));
            }
            
            // 1. Obter ID da categoria "Vaults"
            $vaultsCategoryId = $this->getVaultsCategoryId();
            
            // 2. Criar transação de crédito na conta destino
            $transactionData = [
                'org_id' => $this->getCurrentOrgId(),
                'account_id' => $accountToId,
                'kind' => 'entrada',
                'valor' => $valor,
                'data_competencia' => $dataCompetencia,
                'data_pagamento' => $dataCompetencia,
                'status' => 'confirmado',
                'category_id' => $vaultsCategoryId,
                'contact_id' => null,
                'descricao' => $descricao,
                'observacoes' => $observacoes,
                'created_by' => $_SESSION['user_id'] ?? 1
            ];
            
            $transactionId = $this->transactionModel->createTransaction($transactionData);
            
            if (!$transactionId) {
                throw new Exception('Erro ao criar transação de crédito');
            }
            
            // 3. Registrar movimento de retirada no vault
            $movementId = $this->vaultModel->addMovement(
                $vaultGoalId, 
                $transactionId, 
                'retirada', 
                $valor, 
                $descricao
            );
            
            // 4. Atualizar valor atual do vault goal
            $novoValorAtual = $vaultGoal['valor_atual'] - $valor;
            $this->vaultModel->update($vaultGoalId, [
                'valor_atual' => $novoValorAtual
            ]);
            
            // 5. Se estava concluído e agora não está mais, atualizar status
            if ($vaultGoal['concluido'] && $novoValorAtual < $vaultGoal['valor_meta']) {
                $this->vaultModel->update($vaultGoalId, [
                    'concluido' => 0,
                    'data_conclusao' => null
                ]);
            }
            
            $message = 'Resgate realizado com sucesso!';
            
            $this->jsonResponse([
                'success' => true,
                'message' => $message,
                'transaction_id' => $transactionId,
                'movement_id' => $movementId,
                'novo_saldo' => $novoValorAtual
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}