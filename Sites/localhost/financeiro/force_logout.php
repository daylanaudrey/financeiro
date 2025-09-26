<?php
// Script para forçar logout sem erros
session_start();

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Se cookies de sessão estiverem ativos, destrua-os
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir sessão
session_destroy();

// Remover cookies de lembrar
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

echo "<h2>✅ Logout realizado com sucesso!</h2>";
echo "<p>Todas as sessões foram limpas.</p>";
echo "<p><a href='/login' style='background: blue; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔑 IR PARA LOGIN</a></p>";

// Redirecionar após 2 segundos
echo "<script>
setTimeout(function() {
    window.location.href = '/login';
}, 2000);
</script>";
?>