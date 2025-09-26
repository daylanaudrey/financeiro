<?php
echo "Sistema funcionando!<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current Directory: " . __DIR__ . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";

if (isset($_GET['url'])) {
    echo "URL parameter: " . $_GET['url'] . "<br>";
}

// Testar autoloader
require_once '../config/config.php';

spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    $class = str_replace('App/', '', $class);
    $file = APP_PATH . $class . '.php';

    echo "Tentando carregar: " . $file . "<br>";

    if (file_exists($file)) {
        echo "Arquivo encontrado!<br>";
        require_once $file;
        return true;
    } else {
        echo "Arquivo NÃO encontrado!<br>";
        return false;
    }
});

// Testar se Router carrega
try {
    $router = new App\Core\Router();
    echo "Router carregado com sucesso!<br>";
} catch (Exception $e) {
    echo "Erro ao carregar Router: " . $e->getMessage() . "<br>";
}
?>