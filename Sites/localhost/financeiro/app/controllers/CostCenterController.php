<?php
require_once 'BaseController.php';

class CostCenterController extends BaseController {
    private $costCenterModel;
    
    public function __construct() {
        parent::__construct();
        $this->costCenterModel = new CostCenter();
    }
    
    public function index() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            // Por enquanto, usar org_id = 1
            $orgId = 1;
            
            $costCenters = $this->costCenterModel->getCostCentersByOrg($orgId);
            $hierarchy = $this->costCenterModel->getCostCenterHierarchy($orgId);
            
            $data = [
                'title' => 'Centros de Custo - Sistema Financeiro',
                'page' => 'cost_centers',
                'user' => $user,
                'costCenters' => $costCenters,
                'hierarchy' => $hierarchy,
                'pageTitle' => 'Centros de Custo'
            ];
            
            $this->render('layout', $data);
            
        } catch (Exception $e) {
            $this->handleError($e, 'Erro ao carregar centros de custo');
        }
    }
    
    public function create() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido');
            }
            
            $data = $_POST;
            $data['org_id'] = 1; // Por enquanto fixo
            $data['created_by'] = $_SESSION['user_id'] ?? null;
            
            $costCenterId = $this->costCenterModel->createCostCenter($data);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Centro de custo criado com sucesso!',
                'cost_center_id' => $costCenterId
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
                throw new Exception('ID do centro de custo não informado');
            }
            
            $data = $_POST;
            unset($data['id']);
            
            $this->costCenterModel->updateCostCenter($id, $data);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Centro de custo atualizado com sucesso!'
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
                throw new Exception('ID do centro de custo não informado');
            }
            
            // Verificar se pode ser deletado
            $canDelete = $this->costCenterModel->canDelete($id);
            if (!$canDelete['can_delete']) {
                throw new Exception($canDelete['reason']);
            }
            
            $this->costCenterModel->delete($id);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Centro de custo excluído com sucesso!'
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function getCostCenter() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID do centro de custo não informado');
            }
            
            $costCenter = $this->costCenterModel->findById($id);
            if (!$costCenter) {
                throw new Exception('Centro de custo não encontrado');
            }
            
            // Buscar opções de parent (excluindo o próprio item e seus filhos)
            // Por enquanto, usar org_id = 1
            $orgId = 1;
            $parentOptions = $this->costCenterModel->getParentOptions($orgId, $id);
            
            // Buscar estatísticas de uso
            $usage = $this->costCenterModel->getCostCenterUsage($id);
            
            $this->jsonResponse([
                'success' => true,
                'costCenter' => $costCenter,
                'parentOptions' => $parentOptions,
                'usage' => $usage
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function getActiveCostCenters() {
        try {
            // Por enquanto, usar org_id = 1
            $orgId = 1;
            $costCenters = $this->costCenterModel->getActiveCostCenters($orgId);
            
            $this->jsonResponse([
                'success' => true,
                'costCenters' => $costCenters
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function getParentOptions() {
        try {
            $excludeId = $_GET['exclude_id'] ?? null;
            // Por enquanto, usar org_id = 1
            $orgId = 1;
            $parentOptions = $this->costCenterModel->getParentOptions($orgId, $excludeId);
            
            $this->jsonResponse([
                'success' => true,
                'options' => $parentOptions
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function getReport() {
        try {
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');
            
            // Por enquanto, usar org_id = 1
            $orgId = 1;
            $report = $this->costCenterModel->getCostCenterReport($orgId, $startDate, $endDate);
            
            $this->jsonResponse([
                'success' => true,
                'report' => $report,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}