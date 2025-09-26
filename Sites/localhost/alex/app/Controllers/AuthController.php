<?php

/**
 * AuthController
 * Gerencia autenticação do sistema
 */
class AuthController
{
    /**
     * Exibir formulário de login
     */
    public function showLogin(): void
    {
        // Se já estiver logado, redirecionar
        if ($this->isLoggedIn()) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        $data = [
            'title' => 'Login',
            'error' => $_SESSION['login_error'] ?? null
        ];

        // Limpar erro da sessão
        unset($_SESSION['login_error']);

        $this->render('auth/login', $data);
    }

    /**
     * Processar login
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validar campos
        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Por favor, preencha todos os campos.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Validar credenciais
        $user = User::validateLogin($email, $password);

        if (!$user) {
            $_SESSION['login_error'] = 'Email ou senha inválidos.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Verificar se usuário está ativo
        if (!$user['is_active']) {
            $_SESSION['login_error'] = 'Sua conta está inativa. Contate o administrador.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Criar sessão
        $this->createSession($user);

        // Log de login
        AuditLog::log('LOGIN', 'users', $user['id'], null, [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);

        // Redirecionar para dashboard
        header('Location: ' . BASE_URL . 'dashboard');
        exit;
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        // Log de logout (antes de destruir a sessão)
        if (isset($_SESSION['user_id'])) {
            AuditLog::log('LOGOUT', 'users', $_SESSION['user_id'], null, [
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        }

        // Destruir sessão
        session_destroy();

        // Redirecionar para login
        header('Location: ' . BASE_URL . 'login');
        exit;
    }

    /**
     * Criar sessão do usuário
     */
    private function createSession(array $user): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();

        // Regenerar ID da sessão por segurança
        session_regenerate_id(true);
    }

    /**
     * Verificar se está logado
     */
    private function isLoggedIn(): bool
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
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