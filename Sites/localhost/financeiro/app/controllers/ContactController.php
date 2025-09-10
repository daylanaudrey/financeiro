<?php
require_once 'BaseController.php';
require_once 'AuthMiddleware.php';

class ContactController extends BaseController {
    private $contactModel;
    private $auditModel;
    
    public function __construct() {
        parent::__construct();
        $this->contactModel = new Contact();
        $this->auditModel = new AuditLog();
    }
    
    public function index() {
        $user = AuthMiddleware::requireAuth();
        
        // Por enquanto, usar org_id = 1
        $orgId = 1;
        
        $contacts = $this->contactModel->getContactsByOrg($orgId);
        $typeOptions = $this->contactModel->getTypeOptions();
        
        $data = [
            'title' => 'Contatos - Sistema Financeiro',
            'page' => 'contacts',
            'user' => $user,
            'contacts' => $contacts,
            'typeOptions' => $typeOptions
        ];
        
        $this->render('layout', $data);
    }
    
    public function create() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $nome = trim($_POST['nome'] ?? '');
            $tipo = $_POST['tipo'] ?? '';
            $documento = trim($_POST['documento'] ?? '') ?: null;
            $email = trim($_POST['email'] ?? '') ?: null;
            $telefone = trim($_POST['telefone'] ?? '') ?: null;
            $endereco = trim($_POST['endereco'] ?? '') ?: null;
            $observacoes = trim($_POST['observacoes'] ?? '') ?: null;
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            // Validações
            if (empty($nome)) {
                $this->json(['success' => false, 'message' => 'Nome é obrigatório']);
                return;
            }
            
            if (empty($tipo)) {
                $this->json(['success' => false, 'message' => 'Tipo é obrigatório']);
                return;
            }
            
            // Validar email se fornecido
            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->json(['success' => false, 'message' => 'Email inválido']);
                return;
            }
            
            $contactData = [
                'org_id' => 1, // Por enquanto fixo
                'nome' => $nome,
                'tipo' => $tipo,
                'documento' => $documento,
                'email' => $email,
                'telefone' => $telefone,
                'endereco' => $endereco,
                'observacoes' => $observacoes,
                'ativo' => $ativo,
                'created_by' => $user['id']
            ];
            
            $contactId = $this->contactModel->createContact($contactData);
            
            if ($contactId) {
                // Log da auditoria
                $this->auditModel->logUserAction(
                    $user['id'],
                    1,
                    'contact',
                    'create',
                    $contactId,
                    null,
                    $contactData,
                    "Contato criado: {$nome}"
                );
                
                $this->json([
                    'success' => true,
                    'message' => 'Contato criado com sucesso!',
                    'contact_id' => $contactId
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao criar contato']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao criar contato: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function update() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $contactId = (int)($_POST['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $tipo = $_POST['tipo'] ?? '';
            $documento = trim($_POST['documento'] ?? '') ?: null;
            $email = trim($_POST['email'] ?? '') ?: null;
            $telefone = trim($_POST['telefone'] ?? '') ?: null;
            $endereco = trim($_POST['endereco'] ?? '') ?: null;
            $observacoes = trim($_POST['observacoes'] ?? '') ?: null;
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            if (!$contactId) {
                $this->json(['success' => false, 'message' => 'ID do contato é obrigatório']);
                return;
            }
            
            // Validar email se fornecido
            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->json(['success' => false, 'message' => 'Email inválido']);
                return;
            }
            
            // Buscar dados atuais para auditoria
            $oldData = $this->contactModel->findById($contactId);
            if (!$oldData) {
                $this->json(['success' => false, 'message' => 'Contato não encontrado']);
                return;
            }
            
            $updateData = [
                'nome' => $nome,
                'tipo' => $tipo,
                'documento' => $documento,
                'email' => $email,
                'telefone' => $telefone,
                'endereco' => $endereco,
                'observacoes' => $observacoes,
                'ativo' => $ativo
            ];
            
            $success = $this->contactModel->updateContact($contactId, $updateData);
            
            if ($success) {
                // Log da auditoria
                $this->auditModel->logUserAction(
                    $user['id'],
                    1,
                    'contact',
                    'update',
                    $contactId,
                    $oldData,
                    $updateData,
                    "Contato atualizado: {$nome}"
                );
                
                $this->json(['success' => true, 'message' => 'Contato atualizado com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao atualizar contato']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar contato: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function delete() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $contactId = (int)($_POST['id'] ?? 0);
            
            if (!$contactId) {
                $this->json(['success' => false, 'message' => 'ID do contato é obrigatório']);
                return;
            }
            
            $contact = $this->contactModel->findById($contactId);
            if (!$contact) {
                $this->json(['success' => false, 'message' => 'Contato não encontrado']);
                return;
            }
            
            $success = $this->contactModel->deleteContact($contactId);
            
            if ($success) {
                // Log da auditoria
                $this->auditModel->logUserAction(
                    $user['id'],
                    1,
                    'contact',
                    'delete',
                    $contactId,
                    $contact,
                    null,
                    "Contato excluído: {$contact['nome']}"
                );
                
                $this->json(['success' => true, 'message' => 'Contato excluído com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao excluir contato']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao excluir contato: " . $e->getMessage());
            if (strpos($e->getMessage(), 'lançamentos vinculados') !== false) {
                $this->json(['success' => false, 'message' => $e->getMessage()]);
            } else {
                $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
            }
        }
    }
    
    public function getContact() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $contactId = (int)($_GET['id'] ?? 0);
            
            if (!$contactId) {
                $this->json(['success' => false, 'message' => 'ID do contato é obrigatório']);
                return;
            }
            
            $contact = $this->contactModel->findById($contactId);
            
            if ($contact) {
                $this->json(['success' => true, 'contact' => $contact]);
            } else {
                $this->json(['success' => false, 'message' => 'Contato não encontrado']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao buscar contato: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
}