<?php
/**
 * Gera URL correta para o projeto, considerando se está rodando
 * no servidor built-in PHP ou no Apache/MAMP
 */
function url($path = '') {
    // Remove a barra inicial se presente
    $path = ltrim($path, '/');
    
    // Se estiver rodando no servidor built-in PHP
    if (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Development Server') !== false) {
        return '/' . $path;
    }
    
    // Se estiver rodando no Apache/MAMP
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    if ($basePath === '/') {
        return '/' . $path;
    }
    
    return $basePath . '/' . $path;
}

/**
 * Obtém o caminho base do projeto
 */
function getBasePath() {
    if (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Development Server') !== false) {
        return '';
    }
    
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    return ($basePath === '/') ? '' : $basePath;
}

/**
 * Verifica se está rodando no MAMP/Apache
 */
function isRunningOnMamp() {
    return !isset($_SERVER['SERVER_SOFTWARE']) || strpos($_SERVER['SERVER_SOFTWARE'], 'Development Server') === false;
}

/**
 * Gera URL absoluta para emails e links externos
 */
function absoluteUrl($path = '') {
    // Remove a barra inicial se presente
    $path = ltrim($path, '/');

    // Detectar protocolo (HTTP ou HTTPS)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';

    // Obter host
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Se estiver rodando no servidor built-in PHP
    if (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Development Server') !== false) {
        return $protocol . '://' . $host . '/' . $path;
    }

    // Se estiver rodando no Apache/MAMP
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    if ($basePath === '/') {
        return $protocol . '://' . $host . '/' . $path;
    }

    return $protocol . '://' . $host . $basePath . '/' . $path;
}