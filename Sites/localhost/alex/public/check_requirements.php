<?php
/**
 * Script de Diagnóstico - Sistema Aduaneiro
 * Verifica requisitos do sistema
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico do Sistema</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
        }
        .check {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 5px solid #ddd;
        }
        .check.success {
            border-left-color: #28a745;
        }
        .check.error {
            border-left-color: #dc3545;
        }
        .check.warning {
            border-left-color: #ffc107;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 200px;
        }
        .status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
        .status.ok {
            background: #28a745;
        }
        .status.fail {
            background: #dc3545;
        }
        .status.warn {
            background: #ffc107;
            color: #333;
        }
        .info {
            background: #e7f3ff;
            border-left: 5px solid #007bff;
            padding: 10px;
            margin: 20px 0;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
        }
        .commands {
            background: #fff;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .command {
            background: #f8f9fa;
            padding: 8px;
            margin: 5px 0;
            font-family: monospace;
            border-left: 3px solid #007bff;
        }
    </style>
</head>
<body>
    <h1>🔍 Diagnóstico do Sistema Aduaneiro</h1>

    <div class="info">
        <strong>Servidor:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Não identificado' ?><br>
        <strong>PHP:</strong> <?= PHP_VERSION ?><br>
        <strong>Sistema:</strong> <?= PHP_OS ?><br>
        <strong>Data/Hora:</strong> <?= date('d/m/Y H:i:s') ?>
    </div>

    <h2>1. Extensões PHP Necessárias</h2>

    <?php
    $extensions = [
        'pdo' => ['nome' => 'PDO', 'critico' => true],
        'pdo_mysql' => ['nome' => 'PDO MySQL', 'critico' => true],
        'mysqli' => ['nome' => 'MySQLi', 'critico' => false],
        'mbstring' => ['nome' => 'Multibyte String', 'critico' => true],
        'json' => ['nome' => 'JSON', 'critico' => true],
        'session' => ['nome' => 'Session', 'critico' => true],
        'fileinfo' => ['nome' => 'FileInfo', 'critico' => false],
        'gd' => ['nome' => 'GD (Imagens)', 'critico' => false],
        'zip' => ['nome' => 'ZIP', 'critico' => false],
        'curl' => ['nome' => 'cURL', 'critico' => false]
    ];

    $hasErrors = false;
    foreach ($extensions as $ext => $info) {
        $loaded = extension_loaded($ext);
        $class = $loaded ? 'success' : ($info['critico'] ? 'error' : 'warning');
        if (!$loaded && $info['critico']) {
            $hasErrors = true;
        }
        ?>
        <div class="check <?= $class ?>">
            <span class="label"><?= $info['nome'] ?>:</span>
            <?php if ($loaded): ?>
                <span class="status ok">INSTALADO</span>
                <?php if ($ext === 'pdo_mysql'): ?>
                    <?php
                    $drivers = PDO::getAvailableDrivers();
                    echo " - Drivers: " . implode(', ', $drivers);
                    ?>
                <?php endif; ?>
            <?php else: ?>
                <span class="status <?= $info['critico'] ? 'fail' : 'warn' ?>">
                    <?= $info['critico'] ? 'NÃO INSTALADO (CRÍTICO)' : 'NÃO INSTALADO' ?>
                </span>
            <?php endif; ?>
        </div>
        <?php
    }
    ?>

    <?php if ($hasErrors): ?>
        <div class="commands">
            <h3>⚠️ Comandos para instalar extensões faltantes:</h3>
            <p><strong>Ubuntu/Debian:</strong></p>
            <div class="command">sudo apt-get update</div>
            <div class="command">sudo apt-get install php-pdo php-mysql php-mbstring php-json php-gd php-zip php-curl</div>
            <div class="command">sudo systemctl restart apache2</div>

            <p><strong>CentOS/RHEL:</strong></p>
            <div class="command">sudo yum install php-pdo php-mysqlnd php-mbstring php-json php-gd php-zip php-curl</div>
            <div class="command">sudo systemctl restart httpd</div>

            <p><strong>cPanel/WHM:</strong></p>
            <div class="command">Acesse WHM → EasyApache 4 → PHP Extensions → Selecione as extensões necessárias</div>
        </div>
    <?php endif; ?>

    <h2>2. Teste de Conexão com Banco de Dados</h2>

    <?php
    // Carregar configuração
    $configFile = dirname(__DIR__) . '/config/database.php';
    if (file_exists($configFile)) {
        $config = include $configFile;
        ?>
        <div class="check">
            <span class="label">Arquivo de configuração:</span>
            <span class="status ok">ENCONTRADO</span>
        </div>

        <pre>Host: <?= $config['host'] ?? 'não definido' ?>

Porta: <?= $config['port'] ?? 'não definido' ?>

Database: <?= $config['database'] ?? 'não definido' ?>

Usuário: <?= $config['username'] ?? 'não definido' ?></pre>

        <?php
        // Tentar conectar
        if (extension_loaded('pdo_mysql')) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    $config['host'],
                    $config['port'],
                    $config['database']
                );

                $pdo = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
                ?>
                <div class="check success">
                    <span class="label">Conexão MySQL:</span>
                    <span class="status ok">CONECTADO COM SUCESSO</span>
                </div>

                <?php
                // Verificar versão do MySQL
                $version = $pdo->query("SELECT VERSION()")->fetchColumn();
                ?>
                <div class="check success">
                    <span class="label">MySQL Version:</span>
                    <?= $version ?>
                </div>

                <?php
                // Verificar tabelas
                $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                ?>
                <div class="check success">
                    <span class="label">Tabelas encontradas:</span>
                    <?= count($tables) ?> tabelas
                </div>

                <?php
            } catch (PDOException $e) {
                ?>
                <div class="check error">
                    <span class="label">Conexão MySQL:</span>
                    <span class="status fail">ERRO DE CONEXÃO</span>
                </div>
                <pre>Erro: <?= htmlspecialchars($e->getMessage()) ?></pre>

                <div class="commands">
                    <h3>🔧 Possíveis soluções:</h3>
                    <ol>
                        <li>Verifique se o MySQL está rodando</li>
                        <li>Confirme as credenciais no arquivo <code>config/database.php</code></li>
                        <li>Verifique se o usuário tem permissões para acessar o banco</li>
                        <li>Para criar o banco e usuário:</li>
                    </ol>
                    <div class="command">mysql -u root -p</div>
                    <div class="command">CREATE DATABASE IF NOT EXISTS alex CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</div>
                    <div class="command">CREATE USER IF NOT EXISTS 'seu_usuario'@'localhost' IDENTIFIED BY 'sua_senha';</div>
                    <div class="command">GRANT ALL PRIVILEGES ON alex.* TO 'seu_usuario'@'localhost';</div>
                    <div class="command">FLUSH PRIVILEGES;</div>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="check error">
                <span class="label">Teste de conexão:</span>
                <span class="status fail">NÃO PODE SER EXECUTADO - PDO_MYSQL NÃO INSTALADO</span>
            </div>
            <?php
        }
    } else {
        ?>
        <div class="check error">
            <span class="label">Arquivo de configuração:</span>
            <span class="status fail">NÃO ENCONTRADO</span>
        </div>
        <?php
    }
    ?>

    <h2>3. Permissões de Diretórios</h2>

    <?php
    $directories = [
        '../temp' => 'Diretório temporário',
        '../public/uploads' => 'Diretório de uploads',
        '../logs' => 'Diretório de logs'
    ];

    foreach ($directories as $dir => $name) {
        $path = __DIR__ . '/' . $dir;
        $exists = file_exists($path);
        $writable = $exists ? is_writable($path) : false;

        $class = $exists && $writable ? 'success' : ($exists ? 'warning' : 'warning');
        ?>
        <div class="check <?= $class ?>">
            <span class="label"><?= $name ?>:</span>
            <?php if ($exists): ?>
                <?php if ($writable): ?>
                    <span class="status ok">EXISTE E GRAVÁVEL</span>
                <?php else: ?>
                    <span class="status warn">EXISTE MAS NÃO GRAVÁVEL</span>
                <?php endif; ?>
            <?php else: ?>
                <span class="status warn">NÃO EXISTE</span>
            <?php endif; ?>
            <small>(<?= $dir ?>)</small>
        </div>
        <?php
    }
    ?>

    <h2>4. Informações PHP</h2>

    <div class="check <?= version_compare(PHP_VERSION, '7.4.0', '>=') ? 'success' : 'error' ?>">
        <span class="label">Versão PHP:</span>
        <?= PHP_VERSION ?>
        <?php if (version_compare(PHP_VERSION, '7.4.0', '>=')): ?>
            <span class="status ok">OK (>= 7.4)</span>
        <?php else: ?>
            <span class="status fail">ATUALIZAR (Requer >= 7.4)</span>
        <?php endif; ?>
    </div>

    <div class="check">
        <span class="label">Memory Limit:</span>
        <?= ini_get('memory_limit') ?>
    </div>

    <div class="check">
        <span class="label">Max Execution Time:</span>
        <?= ini_get('max_execution_time') ?> segundos
    </div>

    <div class="check">
        <span class="label">Upload Max Filesize:</span>
        <?= ini_get('upload_max_filesize') ?>
    </div>

    <div class="check">
        <span class="label">Post Max Size:</span>
        <?= ini_get('post_max_size') ?>
    </div>

    <h2>5. Resumo</h2>

    <?php if ($hasErrors): ?>
        <div class="check error">
            <strong>❌ O sistema NÃO está pronto para funcionar.</strong><br>
            Por favor, instale as extensões PHP necessárias listadas acima.
        </div>
    <?php else: ?>
        <div class="check success">
            <strong>✅ Todas as extensões PHP necessárias estão instaladas!</strong><br>
            Verifique a conexão com o banco de dados se houver problemas.
        </div>
    <?php endif; ?>

    <div class="info" style="margin-top: 30px;">
        <strong>Próximos passos:</strong>
        <ol>
            <li>Corrija todos os itens marcados como CRÍTICO</li>
            <li>Configure o banco de dados em <code>config/database.php</code></li>
            <li>Execute as migrações do banco de dados</li>
            <li>Teste o login no sistema</li>
        </ol>
    </div>
</body>
</html>