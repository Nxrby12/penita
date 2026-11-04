<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Randeaza fisierele din app/Views (partea "V" din MVC).
 * Fiecare pagina se afiseaza in interiorul unui layout comun.
 */
class View
{
    private static string $baseUrl = '';
    private static array $shared = [];

    public static function setBaseUrl(string $baseUrl): void
    {
        self::$baseUrl = rtrim($baseUrl, '/');
    }

    public static function baseUrl(): string
    {
        return self::$baseUrl;
    }

    /** Date disponibile in toate view-urile (ex: numele aplicatiei). */
    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): string
    {
        $data = array_merge(self::$shared, $data);
        $content = self::capture($view, $data);

        if ($layout === null) {
            return $content;
        }
        return self::capture($layout, $data + ['content' => $content]);
    }

    private static function capture(string $view, array $data): string
    {
        $file = ROOT . '/app/Views/' . $view . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View inexistent: {$view}");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}
