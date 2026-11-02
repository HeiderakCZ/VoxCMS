<?php

use Sunlight\Core;
use Sunlight\Database\Database as DB;
use Sunlight\InstallationLock;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$autoload = require __DIR__ . '/../vendor/autoload.php';
Core::$classLoader = $autoload;
Core::init([
    'minimal_mode' => true,
    'config_file' => false,
    'debug' => true,
]);

$configPath = SL_ROOT . 'config.php';
$config = is_file($configPath) ? @include $configPath : false;
$command = $argv[1] ?? '';
$supportedCommands = ['seal', 'unlock', 'resume'];
$projectRoot = rtrim(SL_ROOT, DIRECTORY_SEPARATOR);
$databaseTarget = is_array($config) && is_string($config['db.name'] ?? null)
    ? sprintf('%s:%s/%s', $config['db.server'] ?? '(unknown host)', $config['db.port'] ?? 'default', $config['db.name'])
    : '(not configured)';

fwrite(STDOUT, "Project root: {$projectRoot}\nTarget database: {$databaseTarget}\n");

if (!in_array($command, $supportedCommands, true)) {
    fwrite(STDERR, "Usage: php tools/installation-lock.php <seal|unlock|resume> --confirm=<action|database|project-root>\n");
    exit(2);
}

if (!is_array($config) || empty($config['secret'])) {
    fwrite(STDERR, "No configured installation exists. A fresh installation is not a recovery operation.\n");
    exit(1);
}

$expectedConfirmation = '--confirm=' . $command . '|' . $databaseTarget . '|' . $projectRoot;

if (($argv[2] ?? '') !== $expectedConfirmation) {
    fwrite(STDERR, "Refusing to continue without the exact confirmation argument:\n{$expectedConfirmation}\n");
    exit(2);
}

try {
    DB::connect(
        $config['db.server'],
        $config['db.user'],
        $config['db.password'],
        $config['db.name'],
        $config['db.port'],
        $config['db.prefix'],
        $config['db.engine']
    );

    $databaseState = InstallationLock::inspectDatabase();

    if ($databaseState['status'] === 'unavailable') {
        throw new RuntimeException($databaseState['message']);
    }

    if ($command === 'seal') {
        if ($databaseState['status'] !== 'complete') {
            throw new RuntimeException('Refusing to seal an incomplete installation: ' . $databaseState['message']);
        }

        InstallationLock::sealExisting((string) $config['secret']);
        fwrite(STDOUT, "The installed system was verified and locked.\n");
    } elseif ($command === 'unlock') {
        if ($databaseState['status'] !== 'complete') {
            throw new RuntimeException('Refusing to unlock an incomplete installation: ' . $databaseState['message']);
        }

        InstallationLock::unlock((string) $config['secret']);
        fwrite(STDOUT, "Installer unlocked for localhost access. Keep Apache restricted during reinstalls.\n");
    } else {
        if ($databaseState['status'] === 'complete') {
            throw new RuntimeException('The database is complete; use the explicitly confirmed seal action.');
        }

        InstallationLock::recoverInterrupted((string) $config['secret']);
        fwrite(STDOUT, "Interrupted installation is resumable from localhost. No database changes were made.\n");
    }
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
