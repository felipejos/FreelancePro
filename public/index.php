<?php
/**
 * FreelancePro - Ponto de entrada da aplicação
 * Plataforma Corporativa de Treinamentos + Gestão de Freelancers com IA
 */

// Definir constante do caminho raiz
define('ROOT_PATH', dirname(__DIR__));

// Carregar autoloader
require_once ROOT_PATH . '/app/Core/Autoloader.php';

// Inicializar autoloader
Autoloader::register();

// Carregar configurações
$config = require_once ROOT_PATH . '/config/app.php';

// Iniciar sessão
session_start();

// Inicializar aplicação (ANTES de carregar as rotas)
$app = new App\Core\Application();

// Carregar rotas
require_once ROOT_PATH . '/routes/web.php';

// Executar aplicação
$app->run();
