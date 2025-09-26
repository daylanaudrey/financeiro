<?php
/**
 * Sistema Aduaneiro
 * Definição de Rotas
 */

// Página inicial
$router->get('/', 'HomeController@index');
$router->get('/home', 'HomeController@index');

// Autenticação
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@doLogin');
$router->get('/logout', 'AuthController@logout');

// Dashboard (requer autenticação)
$router->get('/dashboard', 'DashboardController@index');

// Produtos
$router->get('/produtos', 'ProductController@index');
$router->get('/produtos/criar', 'ProductController@create');
$router->post('/produtos/criar', 'ProductController@store');
$router->get('/produtos/{id}', 'ProductController@show');
$router->get('/produtos/{id}/editar', 'ProductController@edit');
$router->post('/produtos/{id}/editar', 'ProductController@update');
$router->post('/produtos/{id}/excluir', 'ProductController@destroy');

// Clientes
$router->get('/clientes', 'ClientController@index');
$router->get('/clientes/criar', 'ClientController@create');
$router->post('/clientes/criar', 'ClientController@store');
$router->get('/clientes/{id}', 'ClientController@show');
$router->get('/clientes/{id}/editar', 'ClientController@edit');
$router->post('/clientes/{id}/editar', 'ClientController@update');
$router->post('/clientes/{id}/excluir', 'ClientController@destroy');

// Processos
$router->get('/processos', 'ProcessController@index');
$router->get('/processos/criar', 'ProcessController@create');
$router->post('/processos/criar', 'ProcessController@store');
$router->get('/processos/{id}', 'ProcessController@show');
$router->get('/processos/{id}/editar', 'ProcessController@edit');
$router->post('/processos/{id}/editar', 'ProcessController@update');
$router->post('/processos/{id}/excluir', 'ProcessController@destroy');