<?php
/**
 * Sistema Aduaneiro
 * Configurações Gerais
 */

// Ambiente (development, production)
define('ENVIRONMENT', 'development');

// URLs - Detecção dinâmica
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$path = str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$path = rtrim($path, '/') . '/';

define('BASE_URL', $protocol . $host . $path);
define('PUBLIC_URL', BASE_URL . 'public/');

// Paths
define('ROOT_PATH', dirname(__DIR__) . '/');
define('APP_PATH', ROOT_PATH . 'app/');
define('CONFIG_PATH', ROOT_PATH . 'config/');
define('PUBLIC_PATH', ROOT_PATH . 'public/');
define('TEMP_PATH', ROOT_PATH . 'temp/');

// Sessão
define('SESSION_TIMEOUT', 120); // minutos

// Segurança
define('CSRF_TOKEN_NAME', 'csrf_token');

// Configurações de Debug
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', TEMP_PATH . 'error.log');
}

// Timezone
date_default_timezone_set('America/Sao_Paulo');