<?php
/**
 * Sistema Aduaneiro
 * Configuração do Banco de Dados
 */

return [
    'host' => '127.0.0.1',
    'port' => '3307',
    'database' => 'alex',
    'username' => 'root',
    'password' => 'root',
//    'port' => '3306',
//    'database' => 'dagsolucao_alex',
//    'username' => 'dag_daylan',
//    'password' => 'Viniso122514@A',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];