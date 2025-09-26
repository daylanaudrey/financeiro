<?php

/**
 * ProfileController
 * Gerencia perfil do usuário
 */
class ProfileController
{
    /**
     * Exibir perfil do usuário
     */
    public function index(): void
    {
        // Verificar se está logado
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Buscar dados do usuário atual pelo ID (mais seguro)
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            $_SESSION['error'] = 'Sessão inválida.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $user = $this->findUserById($userId);

        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado.';
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        $data = [
            'title' => 'Meu Perfil',
            'user' => $user
        ];

        $this->render('profile/index', $data);
    }

    /**
     * Atualizar perfil
     */
    public function update(): void
    {
        // Verificar se está logado
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'profile');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validações básicas
        if (empty($name) || empty($email)) {
            $_SESSION['error'] = 'Nome e email são obrigatórios.';
            header('Location: ' . BASE_URL . 'profile');
            exit;
        }

        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Email inválido.';
            header('Location: ' . BASE_URL . 'profile');
            exit;
        }

        try {
            // Buscar usuário atual
            $user = User::findByEmail($_SESSION['user_email']);

            if (!$user) {
                throw new Exception('Usuário não encontrado.');
            }

            // Verificar se o email não está sendo usado por outro usuário
            if ($email !== $user['email']) {
                $existingUser = User::findByEmail($email);
                if ($existingUser && $existingUser['id'] !== $userId) {
                    throw new Exception('Este email já está sendo usado por outro usuário.');
                }
            }

            $updateData = [
                'name' => $name,
                'email' => $email
            ];

            // Se uma nova senha foi fornecida
            if (!empty($newPassword)) {
                // Verificar senha atual
                if (!password_verify($currentPassword, $user['password'])) {
                    throw new Exception('Senha atual incorreta.');
                }

                // Verificar se as senhas coincidem
                if ($newPassword !== $confirmPassword) {
                    throw new Exception('As senhas não coincidem.');
                }

                // Validar tamanho da senha
                if (strlen($newPassword) < 6) {
                    throw new Exception('A nova senha deve ter pelo menos 6 caracteres.');
                }

                $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            // Atualizar dados
            User::update($userId, $updateData);

            // Log de atualização de perfil
            AuditLog::log('UPDATE', 'users', $userId, $user, $updateData);

            // Atualizar dados da sessão se necessário
            if ($name !== $_SESSION['user_name']) {
                $_SESSION['user_name'] = $name;
            }
            if ($email !== $_SESSION['user_email']) {
                $_SESSION['user_email'] = $email;
            }

            $_SESSION['success'] = 'Perfil atualizado com sucesso!';
            header('Location: ' . BASE_URL . 'profile');

        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar perfil: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'profile');
        }
        exit;
    }

    /**
     * Buscar usuário por ID (sem restrição de ativo)
     */
    private function findUserById(int $id): ?array
    {
        $sql = "SELECT * FROM users WHERE id = :id AND deleted = 0";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Renderizar view
     */
    private function render(string $view, array $data = []): void
    {
        extract($data);

        // Capturar o conteúdo da view
        ob_start();
        include APP_PATH . '/Views/' . $view . '.php';
        $content = ob_get_clean();

        // Incluir layout
        include APP_PATH . '/Views/layouts/main.php';
    }
}