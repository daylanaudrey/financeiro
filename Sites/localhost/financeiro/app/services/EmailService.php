<?php
require_once __DIR__ . '/../models/AuditLog.php';

class EmailService {
    private $apiKey;
    private $fromEmail;
    private $fromName;
    private $auditModel;
    private $db;
    
    public function __construct() {
        $this->apiKey = $this->getSystemConfig('mailersend_api_key');
        $this->fromEmail = $this->getSystemConfig('mailersend_from_email') ?: 'noreply@dagsolucaodigital.com.br';
        $this->fromName = $this->getSystemConfig('mailersend_from_name') ?: 'DAG Sistema Financeiro';
        
        // Inicializar audit log
        try {
            require_once __DIR__ . '/../../config/database.php';
            $database = new Database();
            $this->db = $database->getConnection();
            $this->auditModel = new AuditLog($this->db);
        } catch (Exception $e) {
            error_log("Erro ao inicializar EmailService audit: " . $e->getMessage());
        }
    }
    
    public function sendEmail($to, $subject, $content, $templateId = null) {
        $startTime = microtime(true);
        
        if (!$this->apiKey) {
            $error = 'MailerSend API key not configured';
            error_log($error);
            $this->logEmailAttempt($to, $subject, 'error', $error);
            return false;
        }
        
        $data = [
            'from' => [
                'email' => $this->fromEmail,
                'name' => $this->fromName
            ],
            'to' => [
                [
                    'email' => $to,
                    'name' => explode('@', $to)[0]
                ]
            ],
            'subject' => $subject
        ];
        
        if ($templateId) {
            $data['template_id'] = $templateId;
            $data['variables'] = [
                [
                    'email' => $to,
                    'substitutions' => [
                        [
                            'var' => 'content',
                            'value' => $content
                        ]
                    ]
                ]
            ];
        } else {
            $data['html'] = $this->wrapContentInTemplate($content, $subject);
            $data['text'] = strip_tags($content);
        }
        
        $result = $this->makeApiCall($data);
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        if ($result['success']) {
            $this->logEmailAttempt($to, $subject, 'success', 'Email enviado com sucesso', $duration);
        } else {
            $this->logEmailAttempt($to, $subject, 'error', $result['error'], $duration);
        }
        
        return $result['success'];
    }
    
    public function sendWelcomeEmail($userEmail, $userName, $orgName) {
        $subject = "Bem-vindo ao $orgName - Sistema Financeiro";
        $content = "
            <h2>Bem-vindo, $userName!</h2>
            <p>Sua conta foi criada com sucesso na organização <strong>$orgName</strong>.</p>
            <p>Você agora pode acessar o sistema financeiro e começar a gerenciar suas transações.</p>
            <p>Se você tiver alguma dúvida, entre em contato com o administrador da sua organização.</p>
        ";
        
        return $this->sendEmail($userEmail, $subject, $content);
    }
    
    public function sendInvitationEmail($email, $orgName, $inviteLink) {
        $subject = "Convite para $orgName - Sistema Financeiro";
        $content = "
            <h2>Você foi convidado!</h2>
            <p>Você foi convidado para participar da organização <strong>$orgName</strong> no Sistema Financeiro.</p>
            <p>Para aceitar o convite e criar sua conta, clique no link abaixo:</p>
            <p><a href=\"$inviteLink\" style=\"background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;\">Aceitar Convite</a></p>
            <p>Este convite é válido por 7 dias.</p>
        ";
        
        return $this->sendEmail($email, $subject, $content);
    }
    
    public function sendTrialExpiringEmail($userEmail, $userName, $orgName, $daysLeft) {
        $subject = "Trial expirando em $daysLeft dias - $orgName";
        $content = "
            <h2>Olá, $userName!</h2>
            <p>Seu período de trial na organização <strong>$orgName</strong> expira em <strong>$daysLeft dias</strong>.</p>
            <p>Para continuar usando o sistema, escolha um dos nossos planos:</p>
            <ul>
                <li><strong>Starter</strong> - R$ 29,90/mês</li>
                <li><strong>Professional</strong> - R$ 59,90/mês</li>
                <li><strong>Enterprise</strong> - R$ 99,90/mês</li>
            </ul>
            <p>Acesse o sistema para fazer o upgrade.</p>
        ";
        
        return $this->sendEmail($userEmail, $subject, $content);
    }
    
    public function sendPasswordResetEmail($userEmail, $userName, $resetLink) {
        $subject = "Recuperação de Senha - Sistema Financeiro";
        $content = "
            <h2>Recuperação de Senha</h2>
            <p>Olá, $userName!</p>
            <p>Recebemos uma solicitação para redefinir sua senha. Clique no link abaixo para criar uma nova senha:</p>
            <p><a href=\"$resetLink\" style=\"background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;\">Redefinir Senha</a></p>
            <p>Se você não solicitou esta alteração, ignore este email.</p>
            <p>Este link é válido por 1 hora.</p>
        ";

        return $this->sendEmail($userEmail, $subject, $content);
    }

    public function sendDueDateReminderEmail($userEmail, $userName, $transactions, $daysAhead) {
        $subject = "⏰ Lembretes de Vencimento - {$daysAhead} dia(s)";

        $timeLabel = $daysAhead == 0 ? "hoje" : ($daysAhead == 1 ? "amanhã" : "em {$daysAhead} dias");
        $urgencyColor = $daysAhead <= 1 ? "#dc3545" : ($daysAhead <= 3 ? "#fd7e14" : "#17a2b8");
        $urgencyIcon = $daysAhead <= 1 ? "🚨" : ($daysAhead <= 3 ? "⚠️" : "📅");

        $content = "
            <div style='text-align: center; margin-bottom: 30px;'>
                <div style='display: inline-block; background: {$urgencyColor}; color: white; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; margin-bottom: 15px;'>
                    {$urgencyIcon} Vencimento {$timeLabel}
                </div>
                <h2 style='color: #2c3e50; margin: 0;'>Olá, {$userName}!</h2>
                <p style='font-size: 18px; color: #5a6c7d; margin: 10px 0 0;'>
                    Você tem <strong style='color: {$urgencyColor};'>" . count($transactions) . " transação(ões)</strong> que vencem {$timeLabel}
                </p>
            </div>

            <div style='background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; padding: 24px; margin: 24px 0; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
                <table style='width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
                    <thead>
                        <tr style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;'>
                            <th style='padding: 16px 12px; text-align: left; font-weight: 600; font-size: 14px;'>Tipo</th>
                            <th style='padding: 16px 12px; text-align: left; font-weight: 600; font-size: 14px;'>Descrição</th>
                            <th style='padding: 16px 12px; text-align: right; font-weight: 600; font-size: 14px;'>Valor</th>
                            <th style='padding: 16px 12px; text-align: center; font-weight: 600; font-size: 14px;'>Vencimento</th>
                        </tr>
                    </thead>
                    <tbody>
        ";

        $totalValue = 0;
        $rowIndex = 0;
        foreach ($transactions as $transaction) {
            $valor = number_format($transaction['valor'], 2, ',', '.');
            $isExpense = $transaction['kind'] === 'saida';
            $tipo = $isExpense ? '<span style="color: #dc3545; font-weight: 600;">🔴 Despesa</span>' : '<span style="color: #28a745; font-weight: 600;">🟢 Receita</span>';
            $totalValue += ($isExpense ? $transaction['valor'] : -$transaction['valor']);

            $rowBg = $rowIndex % 2 === 0 ? '#ffffff' : '#f8f9fa';
            $valueColor = $isExpense ? '#dc3545' : '#28a745';

            $content .= "
                <tr style='background-color: {$rowBg}; border-bottom: 1px solid #e9ecef;'>
                    <td style='padding: 12px; font-size: 14px;'>{$tipo}</td>
                    <td style='padding: 12px; font-size: 14px; color: #2c3e50; font-weight: 500;'>{$transaction['descricao']}</td>
                    <td style='padding: 12px; text-align: right; font-size: 14px; font-weight: 600; color: {$valueColor};'>R$ {$valor}</td>
                    <td style='padding: 12px; text-align: center; font-size: 14px; color: #5a6c7d;'>" . date('d/m/Y', strtotime($transaction['data_competencia'])) . "</td>
                </tr>
            ";
            $rowIndex++;
        }

        $totalFormatted = number_format(abs($totalValue), 2, ',', '.');
        $totalLabel = $totalValue > 0 ? 'Total em Despesas' : 'Total em Receitas';
        $totalColor = $totalValue > 0 ? '#dc3545' : '#28a745';
        $totalIcon = $totalValue > 0 ? '💸' : '💰';

        $content .= "
                    </tbody>
                </table>
            </div>

            <div style='background: linear-gradient(135deg, {$totalColor}15 0%, {$totalColor}05 100%); border: 2px solid {$totalColor}20; border-radius: 12px; padding: 20px; margin: 24px 0; text-align: center;'>
                <div style='font-size: 18px; font-weight: 700; color: {$totalColor}; margin-bottom: 8px;'>
                    {$totalIcon} {$totalLabel}
                </div>
                <div style='font-size: 28px; font-weight: 800; color: {$totalColor};'>
                    R$ {$totalFormatted}
                </div>
            </div>

            <div style='background: linear-gradient(135deg, #e3f2fd 0%, #f0f4ff 100%); border-left: 4px solid #2196f3; padding: 20px; border-radius: 0 8px 8px 0; margin: 24px 0;'>
                <p style='margin: 0; color: #1976d2; font-weight: 600; font-size: 16px;'>
                    💡 <strong>Dica Importante:</strong>
                </p>
                <p style='margin: 8px 0 0; color: #424242; font-size: 15px; line-height: 1.6;'>
                    Acesse o sistema para confirmar pagamentos, reagendar transações ou atualizar informações das suas contas.
                </p>
            </div>

            <div style='text-align: center; margin: 32px 0;'>
                <a href='" . absoluteUrl('transactions') . "' class='btn' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white !important; padding: 16px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); transition: all 0.3s ease;'>
                    📊 Ver Todas as Transações
                </a>
            </div>

            <div style='border-top: 1px solid #e1e8ed; padding: 20px 0; text-align: center; margin-top: 32px;'>
                <p style='font-size: 13px; color: #8898aa; margin: 0; line-height: 1.6;'>
                    Este é um lembrete automático do Sistema Financeiro.<br>
                    Para alterar suas preferências de notificação, <a href='" . absoluteUrl('profile') . "' style='color: #667eea; text-decoration: none; font-weight: 500;'>acesse seu perfil</a>.
                </p>
            </div>
        ";

        return $this->sendEmail($userEmail, $subject, $content);
    }
    
    private function wrapContentInTemplate($content, $subject) {
        return "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>$subject</title>
            <style>
                /* Reset CSS para compatibilidade entre clientes de email */
                * { margin: 0; padding: 0; box-sizing: border-box; }

                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                    line-height: 1.6;
                    color: #2c3e50;
                    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                    margin: 0;
                    padding: 20px 0;
                }

                .email-wrapper {
                    width: 100%;
                    max-width: 650px;
                    margin: 0 auto;
                    background: #ffffff;
                    border-radius: 16px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                    overflow: hidden;
                }

                .header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 40px 30px;
                    text-align: center;
                    position: relative;
                }

                .header::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\"><defs><pattern id=\"grain\" width=\"100\" height=\"100\" patternUnits=\"userSpaceOnUse\"><circle cx=\"50\" cy=\"50\" r=\"1\" fill=\"white\" opacity=\"0.1\"/></pattern></defs><rect width=\"100\" height=\"100\" fill=\"url(%23grain)\"/></svg>');
                    opacity: 0.3;
                }

                .header-content {
                    position: relative;
                    z-index: 1;
                }

                .logo {
                    width: 60px;
                    height: 60px;
                    background: rgba(255,255,255,0.2);
                    border-radius: 12px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 20px;
                    font-size: 24px;
                    font-weight: bold;
                }

                .header h1 {
                    font-size: 28px;
                    font-weight: 700;
                    margin: 0 0 8px;
                    letter-spacing: -0.5px;
                }

                .header p {
                    font-size: 16px;
                    opacity: 0.9;
                    margin: 0;
                    font-weight: 300;
                }

                .content {
                    padding: 40px 30px;
                    background: #ffffff;
                }

                .content h2 {
                    color: #2c3e50;
                    font-size: 24px;
                    font-weight: 600;
                    margin-bottom: 20px;
                    text-align: center;
                }

                .content p {
                    font-size: 16px;
                    line-height: 1.7;
                    margin-bottom: 16px;
                    color: #5a6c7d;
                }

                .content a {
                    color: #667eea;
                    text-decoration: none;
                    font-weight: 500;
                }

                .content a:hover {
                    text-decoration: underline;
                }

                .btn {
                    display: inline-block;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white !important;
                    padding: 14px 28px;
                    text-decoration: none;
                    border-radius: 8px;
                    font-weight: 600;
                    font-size: 16px;
                    text-align: center;
                    margin: 20px 0;
                    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
                    transition: all 0.3s ease;
                }

                .divider {
                    height: 1px;
                    background: linear-gradient(90deg, transparent, #e1e8ed, transparent);
                    margin: 30px 0;
                }

                .footer {
                    background: #f8fafb;
                    padding: 30px;
                    text-align: center;
                    border-top: 1px solid #e1e8ed;
                }

                .footer-content {
                    color: #8898aa;
                    font-size: 14px;
                    line-height: 1.6;
                }

                .footer-content p {
                    margin: 8px 0;
                }

                .footer-links {
                    margin: 20px 0 0;
                }

                .footer-links a {
                    color: #667eea;
                    text-decoration: none;
                    font-size: 13px;
                    margin: 0 10px;
                }

                .social-links {
                    margin: 20px 0 0;
                }

                .social-link {
                    display: inline-block;
                    width: 36px;
                    height: 36px;
                    background: #667eea;
                    color: white;
                    text-decoration: none;
                    border-radius: 50%;
                    line-height: 36px;
                    text-align: center;
                    margin: 0 5px;
                    font-size: 16px;
                }

                /* Responsividade */
                @media (max-width: 600px) {
                    body { padding: 10px 0; }
                    .email-wrapper { margin: 0 10px; border-radius: 12px; }
                    .header { padding: 30px 20px; }
                    .content { padding: 30px 20px; }
                    .footer { padding: 20px; }
                    .header h1 { font-size: 24px; }
                    .content h2 { font-size: 20px; }
                }

                /* Modo escuro */
                @media (prefers-color-scheme: dark) {
                    .content { background: #1a1a1a; }
                    .content h2 { color: #ffffff; }
                    .content p { color: #b0b0b0; }
                    .footer { background: #2a2a2a; }
                    .footer-content { color: #888; }
                }
            </style>
        </head>
        <body>
            <div class='email-wrapper'>
                <div class='header'>
                    <div class='header-content'>
                        <div class='logo'>DF</div>
                        <h1>Sistema Financeiro</h1>
                        <p>DAG Solução Digital</p>
                    </div>
                </div>
                <div class='content'>
                    $content
                </div>
                <div class='divider'></div>
                <div class='footer'>
                    <div class='footer-content'>
                        <p><strong>&copy; 2024 DAG Solução Digital</strong></p>
                        <p>Todos os direitos reservados.</p>
                        <div class='footer-links'>
                            <a href='#'>Política de Privacidade</a>
                            <a href='#'>Termos de Uso</a>
                            <a href='#'>Suporte</a>
                        </div>
                        <div class='social-links'>
                            <a href='#' class='social-link'>📧</a>
                            <a href='#' class='social-link'>💬</a>
                            <a href='#' class='social-link'>🌐</a>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function makeApiCall($data) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.mailersend.com/v1/email',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
                'X-Requested-With: XMLHttpRequest'
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            $errorMsg = "MailerSend cURL error: $error";
            error_log($errorMsg);
            return ['success' => false, 'error' => $errorMsg];
        }
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'response' => $response];
        } else {
            $errorMsg = "MailerSend API error (HTTP $httpCode): $response";
            error_log($errorMsg);
            return ['success' => false, 'error' => $errorMsg];
        }
    }
    
    private function logEmailAttempt($to, $subject, $status, $message, $duration = null) {
        try {
            if (!$this->auditModel) {
                return;
            }
            
            $userId = $_SESSION['user_id'] ?? null;
            $orgId = $_SESSION['current_org_id'] ?? null;
            
            $context = [
                'to' => $to,
                'subject' => $subject,
                'from' => $this->fromEmail,
                'status' => $status,
                'message' => $message
            ];
            
            if ($duration !== null) {
                $context['duration_ms'] = $duration;
            }
            
            $action = $status === 'success' ? 'email_sent' : 'email_failed';
            $description = $status === 'success' 
                ? "Email enviado para $to: $subject" 
                : "Falha ao enviar email para $to: $message";
            
            $this->auditModel->logUserAction(
                $userId,
                $orgId,
                'email',
                $action,
                null,
                null,
                json_encode($context),
                $description
            );
        } catch (Exception $e) {
            error_log("Erro ao logar email na auditoria: " . $e->getMessage());
        }
    }
    
    private function getSystemConfig($key) {
        try {
            require_once __DIR__ . '/../../config/database.php';
            $database = new Database();
            $pdo = $database->getConnection();

            $stmt = $pdo->prepare("SELECT key_value FROM system_configs WHERE key_name = ?");
            $stmt->execute([$key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['key_value'] : null;
        } catch (PDOException $e) {
            error_log("Error fetching system config: " . $e->getMessage());
            return null;
        }
    }

    private function getSystemUrl($path = '') {
        // Remove a barra inicial se presente
        $path = ltrim($path, '/');

        // Tentar obter a URL base das configurações do sistema
        $baseUrl = $this->getSystemConfig('app_url');

        if ($baseUrl) {
            return rtrim($baseUrl, '/') . '/' . $path;
        }

        // Fallback: tentar detectar baseado no ambiente
        if (isset($_SERVER['HTTP_HOST'])) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];

            // Se estiver no localhost com MAMP, incluir /financeiro
            if (strpos($host, 'localhost') !== false) {
                return $protocol . '://' . $host . '/financeiro/' . $path;
            }

            return $protocol . '://' . $host . '/' . $path;
        }

        // Fallback final: URL relativa
        return '/' . $path;
    }
    
    public function testConnection() {
        if (!$this->apiKey) {
            return ['success' => false, 'message' => 'API Key do MailerSend não configurada'];
        }
        
        $testData = [
            'from' => [
                'email' => $this->fromEmail,
                'name' => $this->fromName
            ],
            'to' => [
                [
                    'email' => 'test@example.com',
                    'name' => 'Test User'
                ]
            ],
            'subject' => 'Teste de Configuração MailerSend',
            'html' => '<p>Este é um teste de configuração do MailerSend.</p>'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.mailersend.com/v1/email',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
                'X-Requested-With: XMLHttpRequest'
            ],
            CURLOPT_POSTFIELDS => json_encode($testData),
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['success' => false, 'message' => "Erro de conexão: $error"];
        }
        
        if ($httpCode === 422) {
            return ['success' => true, 'message' => 'Configuração válida (email de teste não foi enviado)'];
        } elseif ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'message' => 'Configuração válida e email de teste enviado'];
        } else {
            return ['success' => false, 'message' => "Erro na API (HTTP $httpCode): $response"];
        }
    }
}