<?php
declare(strict_types=1);

namespace App\Core;

use App\Exceptions\NotFoundException;

/**
 * Router: potriveste adresa ceruta cu o ruta si apeleaza
 * metoda controller-ului corespunzator (partea "C" din MVC).
 */
class Router
{
    /** @var array<int, array{method:string, pattern:string, handler:array}> */
    private array $routes = [];

    public function __construct(private string $baseUrl = '')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function add(string $method, string $path, array $handler): void
    {
        // /carte/{slug} -> #^/carte/(?P<slug>[^/]+)$#
        $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path);
        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => '#^' . $pattern . '$#',
            'handler' => $handler,
        ];
    }

    /**
     * @throws NotFoundException daca nicio ruta nu se potriveste
     */
    public function dispatch(string $method, string $uri): void
    {
        $path = $this->normalize($uri);
        // HEAD = GET fara continut (folosit de motoare de cautare si monitoare de disponibilitate)
        $method = strtoupper($method) === 'HEAD' ? 'GET' : $method;

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }
            if (preg_match($route['pattern'], $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                [$class, $action] = $route['handler'];
                (new $class())->$action(...$params);
                return;
            }
        }

        throw new NotFoundException("Ruta inexistenta: {$method} {$path}");
    }

    /** Scoate query string-ul si prefixul de baza (ex: /biblioteca). */
    public function normalize(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        if ($this->baseUrl !== '' && str_starts_with($path, $this->baseUrl)) {
            $path = substr($path, strlen($this->baseUrl));
        }
        $path = '/' . trim($path, '/');
        return $path;
    }
}
