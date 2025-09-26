<?php
/**
 * Debug - Sistema Aduaneiro
 */

echo "<h1>Sistema Aduaneiro - Debug</h1>";

// Iniciar sessão
session_start();

// Carregar configurações
require_once '../config/config.php';

echo "<h2>Configurações</h2>";
echo "BASE_URL: " . BASE_URL . "<br>";
echo "APP_PATH: " . APP_PATH . "<br>";
echo "CONFIG_PATH: " . CONFIG_PATH . "<br>";

echo "<h2>Autoloader Test</h2>";

// Autoloader simples
spl_autoload_register(function ($class) {
    echo "Autoloader tentando carregar: " . $class . "<br>";
    $class = str_replace('\\', '/', $class);
    $class = str_replace('App/', '', $class);
    $file = APP_PATH . $class . '.php';

    echo "Caminho do arquivo: " . $file . "<br>";

    if (file_exists($file)) {
        echo "✅ Arquivo encontrado!<br>";
        require_once $file;
        return true;
    } else {
        echo "❌ Arquivo NÃO encontrado!<br>";
        return false;
    }
});

echo "<h2>Router Test</h2>";

try {
    // Criar instância do router
    $router = new App\Core\Router();
    echo "✅ Router instance created!<br>";

    // Definir rotas
    echo "<h3>Loading routes...</h3>";
    require_once '../routes/web.php';
    echo "✅ Routes loaded!<br>";

    // Testar URL
    echo "<h3>URL Debug</h3>";
    echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "<br>";
    echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
    echo "URL param: " . ($_GET['url'] ?? 'não definido') . "<br>";

    // Teste de rotas funcionando
    echo "✅ Sistema de rotas carregado com sucesso!<br>";

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?>