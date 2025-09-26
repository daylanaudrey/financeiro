<?php

/**
 * Classe Database
 * Gerencia conexão com o banco de dados
 */
class Database
{
    private static ?PDO $connection = null;

    /**
     * Obter conexão com o banco de dados
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            // Verificar se PDO está disponível
            if (!extension_loaded('pdo')) {
                self::showDatabaseError("A extensão PDO não está instalada no servidor.");
            }

            // Verificar se o driver MySQL está disponível
            if (!extension_loaded('pdo_mysql')) {
                self::showDatabaseError("O driver PDO MySQL não está instalado no servidor.");
            }

            $config = require CONFIG_PATH . '/database.php';

            try {
                $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";

                self::$connection = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                $errorMessage = $e->getMessage();

                // Mensagem mais clara para erros comuns
                if (strpos($errorMessage, 'could not find driver') !== false) {
                    self::showDatabaseError("Driver PDO MySQL não encontrado. Por favor, instale a extensão php-mysql.");
                } elseif (strpos($errorMessage, 'Access denied') !== false) {
                    self::showDatabaseError("Acesso negado ao banco de dados. Verifique as credenciais.");
                } elseif (strpos($errorMessage, 'Connection refused') !== false) {
                    self::showDatabaseError("Conexão recusada. Verifique se o MySQL está rodando.");
                } elseif (strpos($errorMessage, 'Unknown database') !== false) {
                    self::showDatabaseError("Banco de dados não encontrado. Verifique o nome do banco.");
                } else {
                    if (ENVIRONMENT === 'development') {
                        self::showDatabaseError("Erro de conexão: " . $errorMessage);
                    } else {
                        self::showDatabaseError("Erro ao conectar com o banco de dados.");
                    }
                }
            }
        }

        return self::$connection;
    }

    /**
     * Exibir erro de banco de dados de forma amigável
     */
    private static function showDatabaseError(string $message): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Erro de Configuração</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background: #f5f5f5;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    margin: 0;
                }
                .error-container {
                    background: white;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    max-width: 600px;
                    text-align: center;
                }
                h1 {
                    color: #dc3545;
                    margin-bottom: 20px;
                }
                .message {
                    background: #f8d7da;
                    color: #721c24;
                    padding: 15px;
                    border-radius: 5px;
                    margin: 20px 0;
                    border: 1px solid #f5c6cb;
                }
                .help-link {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    background: #007bff;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                }
                .help-link:hover {
                    background: #0056b3;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1>⚠️ Erro de Configuração</h1>
                <div class="message">
                    <?= htmlspecialchars($message) ?>
                </div>
                <p>O sistema não pode ser iniciado devido a um problema de configuração.</p>
                <?php
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $path = str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
                $path = rtrim($path, '/') . '/';
                $checkUrl = $protocol . $host . $path . 'public/check_requirements.php';
                ?>
                <a href="<?= $checkUrl ?>" class="help-link">
                    Verificar Requisitos do Sistema
                </a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    /**
     * Executar query com prepared statement
     */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Iniciar transação
     */
    public static function beginTransaction(): void
    {
        self::getConnection()->beginTransaction();
    }

    /**
     * Confirmar transação
     */
    public static function commit(): void
    {
        self::getConnection()->commit();
    }

    /**
     * Reverter transação
     */
    public static function rollback(): void
    {
        self::getConnection()->rollBack();
    }
}