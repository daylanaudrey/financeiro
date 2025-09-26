<?php
require_once 'BaseController.php';
require_once 'AuthMiddleware.php';
require_once __DIR__ . '/../models/User.php';

class ProfileController extends BaseController {
    
    public function index() {
        // Verificar se está logado
        $sessionUser = AuthMiddleware::requireAuth();
        
        // Buscar dados atualizados do usuário no banco
        $userModel = new User();
        $user = $userModel->findById($sessionUser['id']);
        
        if (!$user) {
            header('Location: ' . url('/login'));
            exit;
        }
        
        $data = [
            'title' => 'Configurações de Perfil - Sistema Financeiro',
            'page' => 'profile',
            'user' => $user
        ];
        
        $this->render('layout', $data);
    }
    
    public function update() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'update_profile':
                    return $this->updateProfile($user);
                case 'change_password':
                    return $this->changePassword($user);
                default:
                    $this->json(['success' => false, 'message' => 'Ação inválida']);
            }
            
        } catch (Exception $e) {
            error_log("EXCEPTION in ProfileController::update(): " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    private function updateProfile($user) {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '') ?: null;
        $whatsappNumber = trim($_POST['whatsapp_number'] ?? '') ?: null;
        
        // Validações
        if (empty($nome) || empty($email)) {
            $this->json(['success' => false, 'message' => 'Nome e email são obrigatórios']);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'Email inválido']);
            return;
        }
        
        // Verificar se email já existe (exceto para o próprio usuário)
        $userModel = new User();
        $existingUser = $userModel->findByEmail($email);
        
        if ($existingUser && $existingUser['id'] != $user['id']) {
            $this->json(['success' => false, 'message' => 'Este email já está em uso por outro usuário']);
            return;
        }
        
        // Atualizar dados
        $updateData = [
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'whatsapp_number' => $whatsappNumber
        ];
        
        $success = $userModel->updateUser($user['id'], $updateData);
        
        if ($success) {
            // Atualizar dados na sessão
            $_SESSION['user']['nome'] = $nome;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['telefone'] = $telefone;
            $_SESSION['user']['whatsapp_number'] = $whatsappNumber;
            
            $this->json([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso!'
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Erro ao atualizar perfil']);
        }
    }
    
    private function changePassword($user) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validações
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $this->json(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
            return;
        }
        
        if ($newPassword !== $confirmPassword) {
            $this->json(['success' => false, 'message' => 'Nova senha e confirmação não coincidem']);
            return;
        }
        
        if (strlen($newPassword) < 6) {
            $this->json(['success' => false, 'message' => 'A nova senha deve ter pelo menos 6 caracteres']);
            return;
        }
        
        // Verificar senha atual
        $userModel = new User();
        if (!$userModel->verifyUserPassword($user['id'], $currentPassword)) {
            $this->json(['success' => false, 'message' => 'Senha atual incorreta']);
            return;
        }
        
        // Atualizar senha
        $success = $userModel->updatePassword($user['id'], $newPassword);
        
        if ($success) {
            $this->json([
                'success' => true,
                'message' => 'Senha alterada com sucesso!'
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Erro ao alterar senha']);
        }
    }
}