<?php

namespace Sunlight;

use Sunlight\Database\Database as DB;

/**
 * Manages the filesystem state used to protect the installer.
 */
class InstallationLock
{
    private const FORMAT = 1;
    private const INSTALLED_FILE = '.voxcms-installed';
    private const PENDING_FILE = '.voxcms-installing';
    private const CORE_TABLES = [
        'article', 'box', 'gallery_image', 'iplog', 'log', 'page', 'pm', 'poll', 'post',
        'redirect', 'setting', 'shoutbox', 'user', 'user_activation', 'user_group',
    ];

    /**
     * Inspect the connected database without changing it.
     *
     * @return array{status: 'complete'|'incomplete'|'unavailable', message: string}
     */
    static function inspectDatabase(): array
    {
        try {
            $caseInsensitiveNames = (int) (DB::queryRow('SELECT @@lower_case_table_names AS value')['value'] ?? 0) > 0;
            $normalizeTableName = static function (string $name) use ($caseInsensitiveNames): string {
                return $caseInsensitiveNames ? strtolower($name) : $name;
            };
            $actualTables = array_flip(array_map($normalizeTableName, DB::getTablesByPrefix()));
            $missingTables = [];

            foreach (self::CORE_TABLES as $baseTable) {
                $tableName = DB::$prefix . $baseTable;

                if (!isset($actualTables[$normalizeTableName($tableName)])) {
                    $missingTables[] = $tableName;
                }
            }

            if ($missingTables !== []) {
                return [
                    'status' => 'incomplete',
                    'message' => 'The database is missing core tables: ' . implode(', ', $missingTables),
                ];
            }

            $databaseVersion = DB::queryRow(
                'SELECT val FROM ' . DB::escIdt(DB::table('setting')) . ' WHERE var=' . DB::val('dbversion')
            );
            $admin = DB::queryRow(
                'SELECT id, username, password FROM ' . DB::escIdt(DB::table('user')) . ' WHERE id=1'
            );

            if (($databaseVersion['val'] ?? null) !== Core::DB_VERSION) {
                return ['status' => 'incomplete', 'message' => 'The database version does not match this system.'];
            }

            if (empty($admin['username']) || empty($admin['password'])) {
                return ['status' => 'incomplete', 'message' => 'The administrator account is missing or invalid.'];
            }

            return ['status' => 'complete', 'message' => 'The core database schema and administrator account are valid.'];
        } catch (\Throwable $e) {
            return ['status' => 'unavailable', 'message' => 'The database could not be validated.'];
        }
    }

    /**
     * Check whether the installed marker exists, including an invalid marker.
     */
    static function hasInstalledMarker(): bool
    {
        return file_exists(SL_ROOT . self::INSTALLED_FILE);
    }

    /**
     * Validate the installed marker using the configured secret.
     */
    static function isInstalled(string $secret): bool
    {
        return self::verify(SL_ROOT . self::INSTALLED_FILE, 'installed', $secret);
    }

    /**
     * Check whether the current request may use the installer.
     */
    static function canRunInstaller(string $secret, bool $configExists, ?string $remoteAddress): bool
    {
        if (!self::isLoopback($remoteAddress) || self::hasInstalledMarker()) {
            return false;
        }

        $pendingPath = SL_ROOT . self::PENDING_FILE;

        if (file_exists($pendingPath)) {
            return self::verify($pendingPath, 'installing', $secret);
        }

        // A config file without a signed progress marker may be a completed or
        // interrupted installation. Fail closed instead of reopening setup.
        return !$configExists;
    }

    /**
     * Record that a local installation has passed configuration setup.
     */
    static function begin(string $secret, ?string $previousSecret = null): void
    {
        $path = SL_ROOT . self::PENDING_FILE;

        if (file_exists($path)) {
            if (self::verify($path, 'installing', $secret)) {
                return;
            }

            if ($previousSecret !== null && self::verify($path, 'installing', $previousSecret)) {
                $quarantinePath = self::quarantine($path, self::PENDING_FILE);

                try {
                    self::writeMarker($path, 'installing', $secret);
                } catch (\Throwable $e) {
                    @rename($quarantinePath, $path);
                    throw $e;
                }

                return;
            }

            throw new \RuntimeException('The installer progress marker is invalid.');
        }

        self::writeMarker($path, 'installing', $secret);
    }

    /**
     * Atomically mark installation complete and remove its progress marker.
     */
    static function complete(string $secret): void
    {
        $lockPath = SL_ROOT . self::INSTALLED_FILE;

        if (file_exists($lockPath)) {
            if (self::verify($lockPath, 'installed', $secret)) {
                return;
            }

            throw new \RuntimeException('The installed marker exists but is invalid.');
        }

        self::writeMarker($lockPath, 'installed', $secret);

        $pendingPath = SL_ROOT . self::PENDING_FILE;
        if (file_exists($pendingPath) && !@unlink($pendingPath)) {
            // The installed marker takes precedence; leave the progress file
            // for offline cleanup rather than weakening the completed lock.
        }
    }

    /**
     * Seal an already installed system from an explicit offline operation.
     */
    static function sealExisting(string $secret): void
    {
        $lockPath = SL_ROOT . self::INSTALLED_FILE;

        if (!file_exists($lockPath) || self::verify($lockPath, 'installed', $secret)) {
            self::complete($secret);
            return;
        }

        $quarantinePath = $lockPath . '.invalid-' . bin2hex(random_bytes(6));

        if (!@rename($lockPath, $quarantinePath)) {
            throw new \RuntimeException('Could not quarantine the invalid installed marker.');
        }

        try {
            self::complete($secret);
        } catch (\Throwable $e) {
            @rename($quarantinePath, $lockPath);
            throw $e;
        }
    }

    /**
     * Explicitly unlock a valid installation for an offline administrator.
     */
    static function unlock(string $secret): void
    {
        $lockPath = SL_ROOT . self::INSTALLED_FILE;

        if (!self::verify($lockPath, 'installed', $secret)) {
            throw new \RuntimeException('The installed marker is missing or invalid.');
        }

        self::begin($secret);

        if (!@unlink($lockPath)) {
            throw new \RuntimeException('Could not remove the installed marker.');
        }
    }

    /**
     * Restore local installer access after an explicit offline recovery.
     */
    static function recoverInterrupted(string $secret): void
    {
        $installedPath = SL_ROOT . self::INSTALLED_FILE;

        if (file_exists($installedPath)) {
            if (self::verify($installedPath, 'installed', $secret)) {
                throw new \RuntimeException('This installation is already complete.');
            }

            self::quarantine($installedPath, self::INSTALLED_FILE);
        }

        $pendingPath = SL_ROOT . self::PENDING_FILE;

        if (file_exists($pendingPath)) {
            if (self::verify($pendingPath, 'installing', $secret)) {
                return;
            }

            self::quarantine($pendingPath, self::PENDING_FILE);
        }

        self::writeMarker($pendingPath, 'installing', $secret);
    }

    /**
     * Validate the signed marker at the given path.
     */
    private static function verify(string $path, string $expectedState, string $secret): bool
    {
        if ($secret === '' || !is_file($path) || !is_readable($path)) {
            return false;
        }

        $data = json_decode((string) @file_get_contents($path), true);

        if (
            !is_array($data)
            || ($data['format'] ?? null) !== self::FORMAT
            || ($data['state'] ?? null) !== $expectedState
            || !is_int($data['created_at'] ?? null)
            || !is_string($data['signature'] ?? null)
        ) {
            return false;
        }

        return hash_equals(
            self::signature($data['state'], $data['created_at'], $secret),
            $data['signature']
        );
    }

    /**
     * Atomically write a signed marker without replacing an existing file.
     */
    private static function writeMarker(string $path, string $state, string $secret): void
    {
        if ($secret === '') {
            throw new \RuntimeException('The configured secret is empty.');
        }

        $createdAt = time();
        $data = [
            'format' => self::FORMAT,
            'state' => $state,
            'created_at' => $createdAt,
            'signature' => self::signature($state, $createdAt, $secret),
        ];
        $contents = json_encode($data, JSON_UNESCAPED_SLASHES) . "\n";
        $temporaryPath = @tempnam(SL_ROOT, '.voxcms-lock-');

        if ($temporaryPath === false) {
            throw new \RuntimeException('Could not create a temporary installer marker.');
        }

        try {
            @chmod($temporaryPath, 0600);

            if (@file_put_contents($temporaryPath, $contents, LOCK_EX) !== strlen($contents)) {
                throw new \RuntimeException('Could not write the installer marker.');
            }

            if (file_exists($path) || !@rename($temporaryPath, $path)) {
                throw new \RuntimeException('Could not atomically create the installer marker.');
            }
        } finally {
            if (is_file($temporaryPath)) {
                @unlink($temporaryPath);
            }
        }
    }

    private static function quarantine(string $path, string $name): string
    {
        $quarantinePath = SL_ROOT . $name . '.invalid-' . bin2hex(random_bytes(6));

        if (!@rename($path, $quarantinePath)) {
            throw new \RuntimeException('Could not quarantine an invalid installation marker.');
        }

        return $quarantinePath;
    }

    private static function signature(string $state, int $createdAt, string $secret): string
    {
        return hash_hmac('sha256', self::FORMAT . "\n" . $state . "\n" . $createdAt, $secret);
    }

    private static function isLoopback(?string $address): bool
    {
        return $address === '127.0.0.1' || $address === '::1';
    }
}
