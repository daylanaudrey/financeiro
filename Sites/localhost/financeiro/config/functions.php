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