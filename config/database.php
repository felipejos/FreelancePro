<?php
/**
 * Configuração do Banco de Dados
 * 
 * IMPORTANTE: Altere a variável $environment para mudar entre
 * banco de dados de desenvolvimento e produção
 * 
 * Opções: 'development' ou 'production'
 */

// ============================================
// ALTERE AQUI PARA TROCAR O AMBIENTE
// ============================================
$environment = 'development'; // 'development' ou 'production'
// ============================================
$appConfig = require ROOT_PATH . '/config/app.php';
if (!empty($appConfig['environment'])) {
    $environment = $appConfig['environment'];
}

$databases = [
    
    // Configurações de DESENVOLVIMENTO
    'development' => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'port'      => '3306',
        'database'  => 'freelancepro_dev',
        'username'  => 'root',
        'password'  => '',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => '',
        'options'   => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ],
    ],
    
    // Configurações de PRODUÇÃO
    'production' => [
        'driver'    => 'mysql',
        'host'      => 'seu-servidor-producao.com',
        'port'      => '3306',
        'database'  => 'freelancepro_prod',
        'username'  => 'usuario_producao',
        'password'  => 'senha_segura_producao',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => '',
        'options'   => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ],
    ],
];

// Retornar configuração do ambiente selecionado
return [
    'environment' => $environment,
    'connection'  => $databases[$environment],
    
    // Configurações de migração
    'migrations' => [
        'path'  => ROOT_PATH . '/database/migrations',
        'table' => 'migrations',
    ],
];
