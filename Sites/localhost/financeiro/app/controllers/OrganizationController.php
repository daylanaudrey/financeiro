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

        // Para tela de equipes, buscar membros da organização atual
        $currentOrgId = $_SESSION['current_org_id'] ?? 1;
        $teamMembers = $orgModel->getTeamMembers($currentOrgId);
        $roles = $orgModel->getRoles();
        
        $data = [
            'title' => 'Organizações - Sistema Financeiro',
            'page' => 'organizations',
            'user' => $user,
            'organizations' => $organizations,
            'teamMembers' => $teamMembers,
            'roles' => $roles
        ];
        
        $this->render('layout', $data);
    }
    
    public function create() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $nome = trim($_POST['nome'] ?? '');
            $pessoaTipo = $_POST['pessoa_tipo'] ?? 'PJ';
            $cnpj = trim($_POST['cnpj'] ?? '') ?: null;
            $email = trim($_POST['email'] ?? '') ?: null;
            $telefone = trim($_POST['telefone'] ?? '') ?: null;
            $endereco = trim($_POST['endereco'] ?? '') ?: null;
            
            // Validações
            if (empty($nome)) {
                $this->json(['success' => false, 'message' => 'Nome da organização é obrigatório']);
                return;
            }
            
            // Criar organização
            $orgModel = new Organization();
            $orgData = [
                'nome' => $nome,
                'pessoa_tipo' => $pessoaTipo,
                'cnpj' => $cnpj,
                'email' => $email,
                'telefone' => $telefone,
                'endereco' => $endereco,
                'status' => 'ativa'
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
                $this->json(['success' => false, 'message' => 'Apenas administradores podem convidar pessoas']);
                return;
            }
            
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '') ?: null;
            $role = $_POST['role'] ?? 'operador';
            
            // Validações
            if (empty($nome) || empty($email) || empty($role)) {
                $this->json(['success' => false, 'message' => 'Nome, email e papel são obrigatórios']);
                return;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->json(['success' => false, 'message' => 'Email inválido']);
                return;
            }
            
            $userModel = new User();
            
            // Verificar se o usuário já existe
            $existingUser = $userModel->findByEmail($email);
            
            if ($existingUser) {
                // Se usuário existe, verificar se já está na organização
                $existingRole = $orgModel->getUserRole($existingUser['id'], $orgId);
                if ($existingRole) {
                    $this->json(['success' => false, 'message' => 'Esta pessoa já faz parte da equipe']);
                    return;
                }
                
                // Adicionar usuário existente à organização
                $success = $orgModel->addUserToOrg($existingUser['id'], $orgId, $role);
                
                if ($success) {
                    $this->json(['success' => true, 'message' => "{$nome} foi adicionado à equipe com sucesso!"]);
                } else {
                    $this->json(['success' => false, 'message' => 'Erro ao adicionar pessoa à equipe']);
                }
                return;
            }
            
            // Usuário não existe - criar novo usuário
            $senha = $this->generateRandomPassword();
            
            $userData = [
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone,
                'password' => $senha,
                'role' => $role,
                'status' => 'ativo', // Já ativo, não precisa verificar email
                'email_verified_at' => date('Y-m-d H:i:s') // Já considerado verificado
            ];
            
            $newUserId = $userModel->createUser($userData);
            
            if ($newUserId) {
                // Adicionar à organização
                $success = $orgModel->addUserToOrg($newUserId, $orgId, $role);
                
                if ($success) {
                    // Enviar email com credenciais
                    $emailSent = $this->sendWelcomeEmail($nome, $email, $senha, $user['nome']);
                    
                    // Log da auditoria
                    $auditModel = new AuditLog();
                    $auditModel->logUserAction(
                        $user['id'],
                        $orgId,
                        'user_org_roles',
                        'create',
                        $newUserId,
                        null,
                        ['nome' => $nome, 'email' => $email, 'role' => $role],
                        "Pessoa criada e adicionada à equipe: {$nome} ({$email}) como {$role}"
                    );
                    
                    $message = "{$nome} foi criado e adicionado à equipe com sucesso!";
                    if ($emailSent) {
                        $message .= " Um email com as credenciais foi enviado.";
                    } else {
                        $message .= " ATENÇÃO: Houve problema ao enviar o email. Senha temporária: {$senha}";
                    }
                    
                    $this->json(['success' => true, 'message' => $message]);
                } else {
                    $this->json(['success' => false, 'message' => 'Usuário criado, mas erro ao adicionar à equipe']);
                }
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao criar usuário']);
            }
            
        } catch (Exception $e) {
            error_log("EXCEPTION in OrganizationController::inviteUser(): " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    public function updateMember() {
        try {
            $user = AuthMiddleware::requireAuth();
            $orgId = $_SESSION['current_org_id'] ?? 1;

            // Verificar se é admin
            $orgModel = new Organization();
            $userRole = $orgModel->getUserRole($user['id'], $orgId);

            if ($userRole !== 'admin') {
                $this->json(['success' => false, 'message' => 'Apenas administradores podem editar membros']);
                return;
            }

            $userId = $_POST['user_id'] ?? '';
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '') ?: null;
            $role = $_POST['role'] ?? '';
            $newPassword = trim($_POST['new_password'] ?? '');

            // Validações
            if (empty($userId) || empty($nome) || empty($email) || empty($role)) {
                $this->json(['success' => false, 'message' => 'Todos os campos obrigatórios devem ser preenchidos']);
                return;
            }

            // Verificar se o usuário a ser editado pertence à organização
            $currentMemberRole = $orgModel->getUserRole($userId, $orgId);
            if (!$currentMemberRole) {
                $this->json(['success' => false, 'message' => 'Usuário não encontrado nesta organização']);
                return;
            }

            // Não permitir editar outro admin (apenas super admin pode)
            if ($currentMemberRole === 'admin' && !$this->isSuperAdmin()) {
                $this->json(['success' => false, 'message' => 'Não é possível editar outro proprietário']);
                return;
            }

            // Validar email único
            $userModel = new User();
            $existingUser = $userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] != $userId) {
                $this->json(['success' => false, 'message' => 'Este email já está sendo usado por outro usuário']);
                return;
            }

            // Atualizar dados do usuário
            $updateData = [
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone
            ];

            // Atualizar senha se fornecida
            if (!empty($newPassword)) {
                if (strlen($newPassword) < 6) {
                    $this->json(['success' => false, 'message' => 'A nova senha deve ter pelo menos 6 caracteres']);
                    return;
                }
                $updateData['password'] = $userModel->hashPassword($newPassword);
            }

            // Atualizar usuário
            $success = $userModel->updateUser($userId, $updateData);
            if (!$success) {
                $this->json(['success' => false, 'message' => 'Erro ao atualizar dados do usuário']);
                return;
            }

            // Atualizar papel se mudou
            if ($role !== $currentMemberRole) {
                $stmt = $orgModel->db->prepare("
                    UPDATE user_org_roles
                    SET role = ?
                    WHERE user_id = ? AND org_id = ?
                ");
                $stmt->execute([$role, $userId, $orgId]);
            }

            // Log da ação
            $userModel->logActivity(
                $user['id'],
                'member_update',
                "Membro {$nome} ({$email}) foi atualizado na organização",
                $userId,
                'user'
            );

            $this->json([
                'success' => true,
                'message' => 'Membro atualizado com sucesso!'
            ]);

        } catch (Exception $e) {
            error_log("Erro ao atualizar membro: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    
    private function generateRandomPassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        return substr(str_shuffle($chars), 0, $length);
    }
    
    private function sendWelcomeEmail($nome, $email, $senha, $ownerName) {
        try {
            require_once __DIR__ . '/../services/EmailService.php';
            $emailService = new EmailService();
            
            $loginUrl = url('/login');
            $subject = 'Bem-vindo ao Sistema Financeiro - Credenciais de Acesso';
            
            $content = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #007bff;'>Bem-vindo ao Sistema Financeiro!</h2>
                    
                    <p>Olá <strong>{$nome}</strong>,</p>
                    
                    <p>Você foi convidado por <strong>{$ownerName}</strong> para fazer parte da equipe financeira.</p>
                    
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <h3 style='margin-top: 0; color: #28a745;'>Suas credenciais de acesso:</h3>
                        <p><strong>Email:</strong> {$email}</p>
                        <p><strong>Senha temporária:</strong> <code style='background: #e9ecef; padding: 4px 8px; border-radius: 4px;'>{$senha}</code></p>
                    </div>
                    
                    <p style='margin: 20px 0;'>
                        <a href='{$loginUrl}' style='background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            Acessar Sistema
                        </a>
                    </p>
                    
                    <div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <h4 style='margin-top: 0; color: #856404;'>Importante:</h4>
                        <p style='margin: 0; color: #856404;'>Por segurança, recomendamos que você altere sua senha após o primeiro acesso através das configurações do perfil.</p>
                    </div>
                    
                    <p>Se você não solicitou este acesso ou tem dúvidas, entre em contato conosco.</p>
                    
                    <hr style='margin: 30px 0; border: none; border-top: 1px solid #dee2e6;'>
                    <p style='font-size: 12px; color: #6c757d; margin: 0;'>
                        Este é um email automático. Por favor, não responda.
                    </p>
                </div>
            ";
            
            return $emailService->sendEmail($email, $subject, $content);
        } catch (Exception $e) {
            error_log("Erro ao enviar email de boas-vindas: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Endpoint temporário para debug de permissões
     * Usar apenas em produção para diagnosticar problemas
     */
    public function debugUserRole() {
        // Verificar token de segurança
        if (($_GET['secret'] ?? '') !== 'dag_debug_2025') {
            http_response_code(403);
            die('Acesso negado');
        }

        try {
            $user = AuthMiddleware::requireAuth();
            $orgId = $_SESSION['current_org_id'] ?? 1;
            $orgModel = new Organization();
            $userRole = $orgModel->getUserRole($user['id'], $orgId);

            // Buscar informações adicionais
            $database = new Database();
            $pdo = $database->getConnection();

            // Todas as organizações do usuário
            $stmt = $pdo->prepare("
                SELECT
                    uor.org_id,
                    uor.role,
                    o.nome as org_name
                FROM user_org_roles uor
                JOIN organizations o ON uor.org_id = o.id
                WHERE uor.user_id = ?
                ORDER BY uor.org_id
            ");
            $stmt->execute([$user['id']]);
            $allRoles = $stmt->fetchAll();

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'timestamp' => date('Y-m-d H:i:s'),
                'user_id' => $user['id'],
                'user_name' => $user['nome'],
                'user_email' => $user['email'],
                'current_org_id' => $orgId,
                'session_org_role' => $_SESSION['current_org_role'] ?? null,
                'db_user_role' => $userRole,
                'can_invite' => $userRole === 'admin',
                'condition_check' => [
                    'userRole' => $userRole,
                    'is_admin' => $userRole === 'admin',
                    'not_admin' => $userRole !== 'admin',
                    'would_block' => $userRole !== 'admin'
                ],
                'all_organizations' => $allRoles,
                'session_data' => [
                    'user_id' => $_SESSION['user_id'] ?? null,
                    'current_org_id' => $_SESSION['current_org_id'] ?? null,
                    'current_org_role' => $_SESSION['current_org_role'] ?? null
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }
}