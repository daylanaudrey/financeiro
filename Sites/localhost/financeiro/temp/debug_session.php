<?php
session_start();

echo "=== DEBUG DA SESSÃO ===\n\n";
echo "Status da sessão: " . session_status() . "\n";
echo "ID da sessão: " . session_id() . "\n\n";

echo "=== DADOS DA SESSÃO ===\n";
if (!empty($_SESSION)) {
    foreach ($_SESSION as $key => $value) {
        if (is_array($value)) {
            echo "$key: " . json_encode($value) . "\n";
        } else {
            echo "$key: $value\n";
        }
    }
} else {
    echo "Sessão vazia\n";
}

echo "\n=== VERIFICAÇÃO SUPERADMIN ===\n";
echo "Possui is_super_admin: " . (isset($_SESSION['is_super_admin']) ? 'SIM' : 'NÃO') . "\n";

if (isset($_SESSION['is_super_admin'])) {
    echo "Valor is_super_admin: " . ($_SESSION['is_super_admin'] ? 'TRUE' : 'FALSE') . "\n";
}

echo "Email do usuário: " . ($_SESSION['user_email'] ?? 'Não definido') . "\n";
echo "Role do usuário: " . ($_SESSION['user_role'] ?? 'Não definido') . "\n";

echo "\n=== TESTE ACESSO INTEGRAÇÕES ===\n";
$temAcesso = isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] === true;
echo "Tem acesso às integrações: " . ($temAcesso ? 'SIM' : 'NÃO') . "\n";
?>