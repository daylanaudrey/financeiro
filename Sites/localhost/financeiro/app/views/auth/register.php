<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Cadastro - Sistema Financeiro' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
        }
        
        .register-header {
            background: #28a745;
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .register-body {
            padding: 2rem;
        }
        
        .form-floating > label {
            color: #6c757d;
        }
        
        .btn-register {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .password-strength {
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="register-card">
                    <div class="register-header">
                        <h3 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>
                            Criar Conta
                        </h3>
                        <p class="mb-0 mt-2 opacity-75">Cadastre-se no sistema</p>
                    </div>
                    
                    <div class="register-body">
                        <form id="registerForm">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="nome" name="nome" placeholder="Seu Nome" required>
                                <label for="nome">
                                    <i class="fas fa-user me-2"></i>
                                    Nome Completo
                                </label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" placeholder="seu@email.com" required>
                                <label for="email">
                                    <i class="fas fa-envelope me-2"></i>
                                    Email
                                </label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="organization_name" name="organization_name" placeholder="Nome da Organização" required>
                                <label for="organization_name">
                                    <i class="fas fa-building me-2"></i>
                                    Nome da Organização
                                </label>
                                <div class="form-text mt-1">
                                    <small class="text-muted">Ex: Minha Empresa, João Silva ME, etc.</small>
                                </div>
                            </div>
                            
                            <div class="form-floating mb-3 position-relative">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Senha" required minlength="6">
                                <label for="password">
                                    <i class="fas fa-lock me-2"></i>
                                    Senha
                                </label>
                                <button type="button" class="btn btn-link position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); z-index: 10; border: none; background: none; color: #6c757d;" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="password-eye"></i>
                                </button>
                                <div id="passwordStrength" class="password-strength"></div>
                            </div>
                            
                            <div class="form-floating mb-3 position-relative">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirmar Senha" required>
                                <label for="confirm_password">
                                    <i class="fas fa-lock me-2"></i>
                                    Confirmar Senha
                                </label>
                                <button type="button" class="btn btn-link position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); z-index: 10; border: none; background: none; color: #6c757d;" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye" id="confirm_password-eye"></i>
                                </button>
                                <div id="passwordMatch" class="password-strength"></div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    Aceito os <a href="#" class="text-decoration-none">termos de uso</a>
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-success btn-register w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>
                                Criar Conta
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="text-muted mb-2">Já tem uma conta?</p>
                            <a href="<?= url('/login') ?>" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Fazer Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.all.min.js"></script>
    
    <script>
        // Função para mostrar/ocultar senha
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const eye = document.getElementById(fieldId + '-eye');
            
            if (field.type === 'password') {
                field.type = 'text';
                eye.classList.remove('fa-eye');
                eye.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                eye.classList.remove('fa-eye-slash');
                eye.classList.add('fa-eye');
            }
        }
        
        // Verificação de força da senha
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.innerHTML = '';
                return;
            }
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^A-Za-z0-9]/)) strength++;
            
            if (strength < 3) {
                strengthDiv.innerHTML = '<span class="strength-weak">Senha fraca</span>';
            } else if (strength < 4) {
                strengthDiv.innerHTML = '<span class="strength-medium">Senha média</span>';
            } else {
                strengthDiv.innerHTML = '<span class="strength-strong">Senha forte</span>';
            }
        });
        
        // Verificação de confirmação de senha
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword.length === 0) {
                matchDiv.innerHTML = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchDiv.innerHTML = '<span class="strength-strong">Senhas coincidem</span>';
            } else {
                matchDiv.innerHTML = '<span class="strength-weak">Senhas não coincidem</span>';
            }
        });
        
        // Submit do formulário
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            
            // Validar senhas
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'As senhas não coincidem'
                });
                return;
            }
            
            // Loading state
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Criando conta...';
            
            try {
                const response = await fetch('<?= url('/auth/register') ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: result.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = result.redirect;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: result.message
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro de conexão. Tente novamente.'
                });
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        });
    </script>
</body>
</html>