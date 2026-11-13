<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Jurnalul de activitate: fiecare actiune importanta a utilizatorilor
 * se scrie ca un rand JSON in logs/activity.log.
 *
 *   Logger::info('auth.login', ['email' => $email]);
 *
 * De ce JSON: caracterele speciale (de ex. un "Enter" intr-un nume de utilizator)
 * sunt escapate, deci nimeni nu poate falsifica randuri in jurnal (log injection).
 * Folderul logs/ este blocat din browser prin .htaccess; jurnalul se citeste
 * doar din panoul de admin.
 */
class Logger
{
    private const MAX_BYTES = 5 * 1024 * 1024;   // peste 5 MB, fisierul e arhivat si se incepe altul

    public static function info(string $action, array $details = []): void
    {
        self::write('info', $action, $details);
    }

    public static function warning(string $action, array $details = []): void
    {
        self::write('warning', $action, $details);
    }

    public static function error(string $action, array $details = []): void
    {
        self::write('error', $action, $details);
    }

    public static function path(): string
    {
        return ROOT . '/logs/activity.log';
    }

    /**
     * Ultimele intrari, cele mai noi primele, optional filtrate.
     * @return array<int, array<string, mixed>>
     */
    public static function recent(int $limit = 200, ?string $actionPrefix = null, ?string $level = null): array
    {
        $file = self::path();
        if (!is_file($file)) {
            return [];
        }

        $entries = [];
        foreach (array_reverse(file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []) as $line) {
            $entry = json_decode($line, true);
            if (!is_array($entry)) {
                continue;
            }
            if ($actionPrefix && !str_starts_with((string) $entry['action'], $actionPrefix)) {
                continue;
            }
            if ($level && $entry['level'] !== $level) {
                continue;
            }
            $entries[] = $entry;
            if (count($entries) >= $limit) {
                break;
            }
        }
        return $entries;
    }

    private static function write(string $level, string $action, array $details): void
    {
        $entry = [
            'time'    => date('Y-m-d H:i:s'),
            'level'   => $level,
            'action'  => $action,
            'user'    => self::currentUser(),
            'ip'      => $_SERVER['REMOTE_ADDR'] ?? 'cli',
            'method'  => $_SERVER['REQUEST_METHOD'] ?? '',
            'url'     => $_SERVER['REQUEST_URI'] ?? '',
            'details' => $details,
        ];

        $file = self::path();
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (is_file($file) && filesize($file) > self::MAX_BYTES) {
            @rename($file, $dir . '/activity-' . date('Ymd-His') . '.log');
        }

        $line = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        @file_put_contents($file, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private static function currentUser(): ?string
    {
        try {
            $user = Auth::user();
            return $user ? "{$user->username} (#{$user->id})" : null;
        } catch (\Throwable) {
            return null;   // jurnalul nu are voie sa opreasca aplicatia
        }
    }
}
