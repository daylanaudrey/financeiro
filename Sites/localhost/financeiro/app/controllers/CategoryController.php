<?php
require_once 'BaseController.php';
require_once 'AuthMiddleware.php';

class CategoryController extends BaseController {
    private $categoryModel;
    private $auditModel;
    
    public function __construct() {
        parent::__construct();
        $this->categoryModel = new Category();
        $this->auditModel = new AuditLog();
    }
    
    public function index() {
        $user = AuthMiddleware::requireAuth();
        
        $orgId = $this->getCurrentOrgId();
        
        $categories = $this->categoryModel->getCategoriesByOrg($orgId);
        $typeOptions = $this->categoryModel->getTypeOptions();
        
        $data = [
            'title' => 'Categorias - Sistema Financeiro',
            'page' => 'categories',
            'user' => $user,
            'categories' => $categories,
            'typeOptions' => $typeOptions
        ];
        
        $this->render('layout', $data);
    }
    
    public function create() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $nome = trim($_POST['nome'] ?? '');
            $tipo = $_POST['tipo'] ?? '';
            $cor = $_POST['cor'] ?? '#007bff';
            $icone = trim($_POST['icone'] ?? 'fas fa-tag');
            $descricao = trim($_POST['descricao'] ?? '') ?: null;
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
            
            $categoryData = [
                'org_id' => $this->getCurrentOrgId(),
                'nome' => $nome,
                'tipo' => $tipo,
                'cor' => $cor,
                'icone' => $icone,
                'descricao' => $descricao,
                'ativo' => $ativo,
                'created_by' => $user['id']
            ];
            
            $categoryId = $this->categoryModel->createCategory($categoryData);
            
            if ($categoryId) {
                $this->json([
                    'success' => true,
                    'message' => 'Categoria criada com sucesso!',
                    'category_id' => $categoryId
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao criar categoria']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao criar categoria: " . $e->getMessage());
            
            // Capturar mensagens específicas de erro do banco
            $errorMessage = $e->getMessage();
            $userMessage = 'Erro interno do servidor';
            
            // Verificar tipos específicos de erro
            if (strpos($errorMessage, 'Duplicate entry') !== false) {
                if (strpos($errorMessage, 'nome') !== false) {
                    $userMessage = 'Já existe uma categoria com este nome';
                } else {
                    $userMessage = 'Categoria com dados duplicados';
                }
            } elseif (strpos($errorMessage, 'Data too long') !== false) {
                $userMessage = 'Um dos campos excede o tamanho máximo permitido';
            } elseif (strpos($errorMessage, 'cannot be null') !== false) {
                $userMessage = 'Campo obrigatório não informado';
            } elseif (strpos($errorMessage, 'foreign key constraint') !== false) {
                $userMessage = 'Referência inválida - verifique os dados informados';
            } elseif (strpos($errorMessage, 'Invalid argument') !== false) {
                $userMessage = $errorMessage; // Usar a mensagem específica do modelo
            }
            
            $this->json(['success' => false, 'message' => $userMessage]);
        }
    }
    
    public function update() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $categoryId = (int)($_POST['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $tipo = $_POST['tipo'] ?? '';
            $cor = $_POST['cor'] ?? '#007bff';
            $icone = trim($_POST['icone'] ?? 'fas fa-tag');
            $descricao = trim($_POST['descricao'] ?? '') ?: null;
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            if (!$categoryId) {
                $this->json(['success' => false, 'message' => 'ID da categoria é obrigatório']);
                return;
            }
            
            // Buscar dados atuais para auditoria
            $oldData = $this->categoryModel->findById($categoryId);
            if (!$oldData) {
                $this->json(['success' => false, 'message' => 'Categoria não encontrada']);
                return;
            }
            
            $updateData = [
                'nome' => $nome,
                'tipo' => $tipo,
                'cor' => $cor,
                'icone' => $icone,
                'descricao' => $descricao,
                'ativo' => $ativo
            ];
            
            $success = $this->categoryModel->updateCategory($categoryId, $updateData);
            
            if ($success) {
                // Log da auditoria
                $this->auditModel->logUserAction(
                    $user['id'],
                    1,
                    'category',
                    'update',
                    $categoryId,
                    $oldData,
                    $updateData,
                    "Categoria atualizada: {$nome}"
                );
                
                $this->json(['success' => true, 'message' => 'Categoria atualizada com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao atualizar categoria']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar categoria: " . $e->getMessage());
            
            // Capturar mensagens específicas de erro do banco
            $errorMessage = $e->getMessage();
            $userMessage = 'Erro interno do servidor';
            
            // Verificar tipos específicos de erro
            if (strpos($errorMessage, 'Duplicate entry') !== false) {
                if (strpos($errorMessage, 'nome') !== false) {
                    $userMessage = 'Já existe uma categoria com este nome';
                } else {
                    $userMessage = 'Categoria com dados duplicados';
                }
            } elseif (strpos($errorMessage, 'Data too long') !== false) {
                $userMessage = 'Um dos campos excede o tamanho máximo permitido';
            } elseif (strpos($errorMessage, 'cannot be null') !== false) {
                $userMessage = 'Campo obrigatório não informado';
            } elseif (strpos($errorMessage, 'foreign key constraint') !== false) {
                $userMessage = 'Referência inválida - verifique os dados informados';
            } elseif (strpos($errorMessage, 'Invalid argument') !== false) {
                $userMessage = $errorMessage; // Usar a mensagem específica do modelo
            }
            
            $this->json(['success' => false, 'message' => $userMessage]);
        }
    }
    
    public function delete() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $categoryId = (int)($_POST['id'] ?? 0);
            
            if (!$categoryId) {
                $this->json(['success' => false, 'message' => 'ID da categoria é obrigatório']);
                return;
            }
            
            $category = $this->categoryModel->findById($categoryId);
            if (!$category) {
                $this->json(['success' => false, 'message' => 'Categoria não encontrada']);
                return;
            }
            
            $success = $this->categoryModel->deleteCategory($categoryId);
            
            if ($success) {
                // Log da auditoria
                $this->auditModel->logUserAction(
                    $user['id'],
                    1,
                    'category',
                    'delete',
                    $categoryId,
                    $category,
                    null,
                    "Categoria excluída: {$category['nome']}"
                );
                
                $this->json(['success' => true, 'message' => 'Categoria excluída com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao excluir categoria']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao excluir categoria: " . $e->getMessage());
            if (strpos($e->getMessage(), 'lançamentos vinculados') !== false) {
                $this->json(['success' => false, 'message' => $e->getMessage()]);
            } else {
                $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
            }
        }
    }
    
    public function getCategory() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $categoryId = (int)($_GET['id'] ?? 0);
            
            if (!$categoryId) {
                $this->json(['success' => false, 'message' => 'ID da categoria é obrigatório']);
                return;
            }
            
            $category = $this->categoryModel->findById($categoryId);
            
            if ($category) {
                $this->json(['success' => true, 'category' => $category]);
            } else {
                $this->json(['success' => false, 'message' => 'Categoria não encontrada']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao buscar categoria: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
}