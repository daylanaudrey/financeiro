<?php
require_once 'BaseController.php';
require_once 'AuthMiddleware.php';

class OrganizationController extends BaseController {
    
    public function index() {
        // Verificar se está logado
        $user = AuthMiddleware::requireAuth();
        
        // Instanciar models
        $orgModel = new Organization();
        
        // Buscar organizações do usuário
        $organizations = $orgModel->getUserOrganizations($user['id']);
        $roles = $orgModel->getRoles();
        
        $data = [
            'title' => 'Organizações - Sistema Financeiro',
            'page' => 'organizations',
            'user' => $user,
            'organizations' => $organizations,
            'roles' => $roles
        ];
        
        $this->render('layout', $data);
    }
    
    public function create() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $nome = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '') ?: null;
            $pessoaTipo = $_POST['pessoa_tipo'] ?? 'PJ';
            
            // Validações
            if (empty($nome)) {
                $this->json(['success' => false, 'message' => 'Nome da organização é obrigatório']);
                return;
            }
            
            // Criar organização
            $orgModel = new Organization();
            $orgData = [
                'nome' => $nome,
                'descricao' => $descricao,
                'pessoa_tipo' => $pessoaTipo,
                'created_by' => $user['id']
            ];
            
            $orgId = $orgModel->createWithOwner($orgData, $user['id']);
            
            if ($orgId) {
                // Log da auditoria
                $auditModel = new AuditLog();
                $auditModel->logUserAction(
                    $user['id'],
                    $orgId,
                    'organization',
                    'create',
                    $orgId,
                    null,
                    $orgData,
                    "Organização criada: {$nome}"
                );
                
                $this->json([
                    'success' => true,
                    'message' => 'Organização criada com sucesso!',
                    'organization_id' => $orgId
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao criar organização']);
            }
            
        } catch (Exception $e) {
            error_log("EXCEPTION in OrganizationController::create(): " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function switchOrg() {
        try {
            $user = AuthMiddleware::requireAuth();
            $orgId = (int)($_POST['org_id'] ?? 0);
            
            if (!$orgId) {
                $this->json(['success' => false, 'message' => 'ID da organização é obrigatório']);
                return;
            }
            
            // Verificar se o usuário pertence à organização
            $orgModel = new Organization();
            $role = $orgModel->getUserRole($user['id'], $orgId);
            
            if (!$role) {
                $this->json(['success' => false, 'message' => 'Você não tem acesso a esta organização']);
                return;
            }
            
            // Salvar na sessão
            $_SESSION['current_org_id'] = $orgId;
            $_SESSION['current_org_role'] = $role;
            
            $this->json(['success' => true, 'message' => 'Organização alterada com sucesso!']);
            
        } catch (Exception $e) {
            error_log("EXCEPTION in OrganizationController::switchOrg(): " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function inviteUser() {
        try {
            $user = AuthMiddleware::requireAuth();
            $orgId = $_SESSION['current_org_id'] ?? 1;
            
            // Verificar se é admin
            $orgModel = new Organization();
            $userRole = $orgModel->getUserRole($user['id'], $orgId);
            
            if ($userRole !== 'admin') {
                $this->json(['success' => false, 'message' => 'Apenas administradores podem convidar usuários']);
                return;
            }
            
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'financeiro';
            
            if (empty($email)) {
                $this->json(['success' => false, 'message' => 'Email é obrigatório']);
                return;
            }
            
            // Verificar se o usuário existe
            $userModel = new User();
            $invitedUser = $userModel->findByEmail($email);
            
            if (!$invitedUser) {
                $this->json(['success' => false, 'message' => 'Usuário não encontrado. O usuário deve se cadastrar primeiro.']);
                return;
            }
            
            // Verificar se já está na organização
            $existingRole = $orgModel->getUserRole($invitedUser['id'], $orgId);
            if ($existingRole) {
                $this->json(['success' => false, 'message' => 'Usuário já faz parte desta organização']);
                return;
            }
            
            // Adicionar à organização
            $success = $orgModel->addUserToOrg($invitedUser['id'], $orgId, $role);
            
            if ($success) {
                // Log da auditoria
                $auditModel = new AuditLog();
                $auditModel->logUserAction(
                    $user['id'],
                    $orgId,
                    'user_org_roles',
                    'create',
                    $invitedUser['id'],
                    null,
                    ['email' => $email, 'role' => $role],
                    "Usuário convidado: {$email} como {$role}"
                );
                
                $this->json(['success' => true, 'message' => "Usuário {$email} adicionado com sucesso!"]);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao adicionar usuário à organização']);
            }
            
        } catch (Exception $e) {
            error_log("EXCEPTION in OrganizationController::inviteUser(): " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
}