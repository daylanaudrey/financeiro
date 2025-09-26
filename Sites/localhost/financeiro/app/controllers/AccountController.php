<?php
require_once 'BaseController.php';
require_once 'AuthMiddleware.php';

class AccountController extends BaseController {
    private $accountModel;
    private $auditModel;
    
    public function __construct() {
        parent::__construct();
        $this->accountModel = new Account();
        $this->auditModel = new AuditLog();
    }
    
    public function index() {
        $user = AuthMiddleware::requireAuth();
        
        $orgId = $this->getCurrentOrgId();
        
        $accounts = $this->accountModel->getAccountsByOrg($orgId);
        $totalBalance = $this->accountModel->getTotalBalance($orgId);
        $balanceByType = $this->accountModel->getBalanceByAccountType($orgId);
        $balanceByPessoaTipo = $this->accountModel->getTotalBalanceByType($orgId);
        $accountTypes = $this->accountModel->getAccountTypes();
        
        $data = [
            'title' => 'Contas Bancárias - Sistema Financeiro',
            'page' => 'accounts',
            'user' => $user,
            'accounts' => $accounts,
            'totalBalance' => $totalBalance,
            'balanceByType' => $balanceByType,
            'balanceByPessoaTipo' => $balanceByPessoaTipo,
            'accountTypes' => $accountTypes
        ];
        
        $this->render('layout', $data);
    }
    
    public function create() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $pessoaTipo = $_POST['pessoa_tipo'] ?? 'PF';
            $nome = trim($_POST['nome'] ?? '');
            $razaoSocial = trim($_POST['razao_social'] ?? '');
            $cnpj = trim($_POST['cnpj'] ?? '');
            $cpf = trim($_POST['cpf'] ?? '');
            $inscricaoEstadual = trim($_POST['inscricao_estadual'] ?? '');
            $tipo = $_POST['tipo'] ?? '';
            $banco = trim($_POST['banco'] ?? '');
            $agencia = trim($_POST['agencia'] ?? '');
            $conta = trim($_POST['conta'] ?? '');
            $saldoInicial = floatval($_POST['saldo_inicial'] ?? 0);
            $descricao = trim($_POST['descricao'] ?? '');
            $cor = $_POST['cor'] ?? '';
            
            // Validações
            if (empty($nome)) {
                $this->json(['success' => false, 'message' => 'Nome da conta é obrigatório']);
            }
            
            if (empty($tipo)) {
                $this->json(['success' => false, 'message' => 'Tipo da conta é obrigatório']);
            }
            
            $accountTypes = $this->accountModel->getAccountTypes();
            if (!array_key_exists($tipo, $accountTypes)) {
                $this->json(['success' => false, 'message' => 'Tipo de conta inválido']);
            }
            
            // Validações específicas para PJ
            if ($pessoaTipo === 'PJ') {
                if (empty($razaoSocial)) {
                    $this->json(['success' => false, 'message' => 'Razão Social é obrigatória para PJ']);
                }
                if (!empty($cnpj) && !$this->validarCNPJ($cnpj)) {
                    $this->json(['success' => false, 'message' => 'CNPJ inválido']);
                }
            }
            
            // Validações específicas para PF
            if ($pessoaTipo === 'PF') {
                if (!empty($cpf) && !$this->validarCPF($cpf)) {
                    $this->json(['success' => false, 'message' => 'CPF inválido']);
                }
            }
            
            // Dados da conta
            $accountData = [
                'org_id' => $this->getCurrentOrgId(),
                'pessoa_tipo' => $pessoaTipo,
                'nome' => $nome,
                'razao_social' => $razaoSocial ?: null,
                'cnpj' => $cnpj ?: null,
                'cpf' => $cpf ?: null,
                'inscricao_estadual' => $inscricaoEstadual ?: null,
                'tipo' => $tipo,
                'banco' => $banco ?: null,
                'agencia' => $agencia ?: null,
                'conta' => $conta ?: null,
                'saldo_inicial' => $saldoInicial,
                'descricao' => $descricao ?: null,
                'cor' => $cor,
                'created_by' => $user['id']
            ];
            
            $accountId = $this->accountModel->createAccount($accountData);
            
            if ($accountId) {
                // Log da auditoria
                $this->auditModel->logUserAction(
                    $user['id'],
                    1,
                    'account',
                    'create',
                    $accountId,
                    null,
                    $accountData,
                    "Conta criada: {$nome}"
                );
                
                $this->json([
                    'success' => true,
                    'message' => 'Conta criada com sucesso!',
                    'account_id' => $accountId
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao criar conta']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao criar conta: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function update() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $accountId = $_POST['id'] ?? 0;
            $nome = trim($_POST['nome'] ?? '');
            $razaoSocial = trim($_POST['razao_social'] ?? '');
            $cnpj = trim($_POST['cnpj'] ?? '');
            $cpf = trim($_POST['cpf'] ?? '');
            $inscricaoEstadual = trim($_POST['inscricao_estadual'] ?? '');
            $banco = trim($_POST['banco'] ?? '');
            $agencia = trim($_POST['agencia'] ?? '');
            $conta = trim($_POST['conta'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $cor = $_POST['cor'] ?? '';
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            if (!$accountId) {
                $this->json(['success' => false, 'message' => 'ID da conta é obrigatório']);
            }
            
            if (empty($nome)) {
                $this->json(['success' => false, 'message' => 'Nome da conta é obrigatório']);
            }
            
            // Buscar dados atuais para auditoria
            $oldData = $this->accountModel->findById($accountId);
            if (!$oldData) {
                $this->json(['success' => false, 'message' => 'Conta não encontrada']);
            }
            
            $updateData = [
                'nome' => $nome,
                'razao_social' => $razaoSocial ?: null,
                'cnpj' => $cnpj ?: null,
                'cpf' => $cpf ?: null,
                'inscricao_estadual' => $inscricaoEstadual ?: null,
                'banco' => $banco ?: null,
                'agencia' => $agencia ?: null,
                'conta' => $conta ?: null,
                'descricao' => $descricao ?: null,
                'cor' => $cor,
                'ativo' => $ativo
            ];
            
            $success = $this->accountModel->update($accountId, $updateData);
            
            if ($success) {
                // Log da auditoria
                $this->auditModel->logUserAction(
                    $user['id'],
                    1,
                    'account',
                    'update',
                    $accountId,
                    $oldData,
                    $updateData,
                    "Conta atualizada: {$nome}"
                );
                
                $this->json(['success' => true, 'message' => 'Conta atualizada com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao atualizar conta']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar conta: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function delete() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $accountId = $_POST['id'] ?? 0;
            
            if (!$accountId) {
                $this->json(['success' => false, 'message' => 'ID da conta é obrigatório']);
            }
            
            // Verificar se não há transações vinculadas
            // TODO: Implementar verificação quando criar o sistema de transações
            
            $account = $this->accountModel->findById($accountId);
            if (!$account) {
                $this->json(['success' => false, 'message' => 'Conta não encontrada']);
            }
            
            $success = $this->accountModel->delete($accountId);
            
            if ($success) {
                // Log da auditoria
                $this->auditModel->logUserAction(
                    $user['id'],
                    1,
                    'account',
                    'delete',
                    $accountId,
                    $account,
                    null,
                    "Conta excluída: {$account['nome']}"
                );
                
                $this->json(['success' => true, 'message' => 'Conta excluída com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao excluir conta']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao excluir conta: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function getAccount() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $accountId = $_GET['id'] ?? 0;
            
            if (!$accountId) {
                $this->json(['success' => false, 'message' => 'ID da conta é obrigatório']);
            }
            
            $account = $this->accountModel->findById($accountId);
            
            if ($account) {
                $this->json(['success' => true, 'account' => $account]);
            } else {
                $this->json(['success' => false, 'message' => 'Conta não encontrada']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao buscar conta: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    private function validarCPF($cpf) {
        // Remove caracteres não numéricos
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // Verifica se tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }
        
        // Verifica se não é uma sequência de números iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        // Cálculo do primeiro dígito verificador
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += $cpf[$i] * (10 - $i);
        }
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : (11 - $resto);
        
        // Cálculo do segundo dígito verificador
        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += $cpf[$i] * (11 - $i);
        }
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : (11 - $resto);
        
        // Verifica se os dígitos estão corretos
        return ($cpf[9] == $digito1 && $cpf[10] == $digito2);
    }
    
    private function validarCNPJ($cnpj) {
        // Remove caracteres não numéricos
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        // Verifica se tem 14 dígitos
        if (strlen($cnpj) != 14) {
            return false;
        }
        
        // Verifica se não é uma sequência de números iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }
        
        // Cálculo do primeiro dígito verificador
        $soma = 0;
        $peso = 5;
        for ($i = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $peso;
            $peso = ($peso == 2) ? 9 : $peso - 1;
        }
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : (11 - $resto);
        
        // Cálculo do segundo dígito verificador
        $soma = 0;
        $peso = 6;
        for ($i = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $peso;
            $peso = ($peso == 2) ? 9 : $peso - 1;
        }
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : (11 - $resto);
        
        // Verifica se os dígitos estão corretos
        return ($cnpj[12] == $digito1 && $cnpj[13] == $digito2);
    }
    
    public function recalculateBalances() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            // Buscar todas as contas ativas da organização
            $accounts = $this->accountModel->getActiveAccountsByOrg(1);
            $recalculatedCount = 0;
            
            foreach ($accounts as $account) {
                if ($this->accountModel->recalculateBalance($account['id'])) {
                    $recalculatedCount++;
                }
            }
            
            // Log da auditoria
            $this->auditModel->logUserAction(
                $user['id'],
                1,
                'account',
                'recalculate',
                null,
                null,
                null,
                "Recálculo de saldos executado em {$recalculatedCount} contas"
            );
            
            $this->json([
                'success' => true, 
                'message' => "Saldos recalculados com sucesso! {$recalculatedCount} contas atualizadas.",
                'recalculated_count' => $recalculatedCount
            ]);
            
        } catch (Exception $e) {
            error_log("Erro ao recalcular saldos: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
}