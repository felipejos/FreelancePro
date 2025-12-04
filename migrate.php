<?php
/**
 * CLI para gerenciamento de migrations
 * 
 * Uso:
 *   php migrate.php create NomeDaMigration  - Criar nova migration
 *   php migrate.php run                      - Executar migrations pendentes
 *   php migrate.php rollback                 - Reverter última batch
 *   php migrate.php status                   - Ver status das migrations
 *   php migrate.php mark arquivo.sql         - Marcar como executada
 */

define('ROOT_PATH', __DIR__);

require_once ROOT_PATH . '/app/Core/Autoloader.php';
Autoloader::register();

use App\Core\Migration;

// Verificar argumentos
if ($argc < 2) {
    showHelp();
    exit(1);
}

$command = $argv[1];
$migration = new Migration();

switch ($command) {
    case 'create':
        if (!isset($argv[2])) {
            echo "Erro: Informe o nome da migration\n";
            echo "Uso: php migrate.php create NomeDaMigration\n";
            exit(1);
        }
        $filename = $migration->create($argv[2]);
        echo "Migration criada: database/migrations/{$filename}\n";
        echo "Edite o arquivo SQL e depois execute manualmente no banco.\n";
        echo "Após executar, rode: php migrate.php mark {$filename}\n";
        break;

    case 'run':
        $result = $migration->runPending();
        echo $result['message'] . "\n";
        foreach ($result['executed'] as $m) {
            echo "  ✓ {$m}\n";
        }
        break;

    case 'rollback':
        $result = $migration->rollback();
        echo $result['message'] . "\n";
        foreach ($result['reverted'] as $m) {
            echo "  ✗ {$m}\n";
        }
        break;

    case 'status':
        $status = $migration->status();
        if (empty($status)) {
            echo "Nenhuma migration encontrada.\n";
        } else {
            echo "Status das migrations:\n";
            echo str_repeat('-', 60) . "\n";
            foreach ($status as $s) {
                $icon = $s['status'] === 'Executada' ? '✓' : '○';
                echo "  {$icon} {$s['migration']} - {$s['status']}\n";
            }
        }
        break;

    case 'mark':
        if (!isset($argv[2])) {
            echo "Erro: Informe o arquivo da migration\n";
            echo "Uso: php migrate.php mark arquivo.sql\n";
            exit(1);
        }
        if ($migration->mark($argv[2])) {
            echo "Migration marcada como executada: {$argv[2]}\n";
        } else {
            echo "Erro ao marcar migration.\n";
        }
        break;

    default:
        showHelp();
        exit(1);
}

function showHelp()
{
    echo "FreelancePro - Gerenciador de Migrations\n";
    echo str_repeat('=', 50) . "\n\n";
    echo "Comandos disponíveis:\n";
    echo "  create <nome>   Criar nova migration\n";
    echo "  run             Registrar migrations pendentes\n";
    echo "  rollback        Reverter última batch\n";
    echo "  status          Ver status das migrations\n";
    echo "  mark <arquivo>  Marcar migration como executada\n";
    echo "\n";
    echo "Exemplo:\n";
    echo "  php migrate.php create AddPhoneToUsers\n";
    echo "  php migrate.php mark 2024_01_15_120000_add_phone_to_users.sql\n";
}
