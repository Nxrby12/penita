<?php
/**
 * FRONT CONTROLLER
 * Singurul punct de intrare al aplicatiei. Incarca configurarea,
 * porneste sesiunea, verifica token-ul CSRF si trimite cererea
 * catre controller-ul potrivit.
 */
declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Logger;
use App\Core\RateLimiter;
use App\Core\SecurityHeaders;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use App\Exceptions\CsrfException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

$config = require ROOT . '/config/config.php';
Config::load($config);
date_default_timezone_set($config['timezone'] ?? 'Europe/Bucharest');   // orele din jurnal si din pagini
View::setBaseUrl($config['base_url']);
View::share('appName', $config['app_name']);

SecurityHeaders::send();
Session::start();

// ---------- Rate limiting: maximum 100 de cereri pe minut de la acelasi IP ----------
try {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $limiter = new RateLimiter(Database::connection(), (int) Config::get('rate_limit.max', 100), 60);
    $count = $limiter->hit($ip);
    if ($count > $limiter->maxRequests()) {
        if ($count === $limiter->maxRequests() + 1) {
            Logger::warning('security.rate_limited', ['requests' => $count]);   // o singura data pe minut
        }
        http_response_code(429);
        header('Retry-After: ' . max(1, $limiter->retryAfter($ip)));
        echo View::render('errors/429', ['title' => 'Prea multe cereri']);
        exit;
    }
} catch (PDOException $e) {
    // daca baza de date nu raspunde, site-ul merge mai departe (pagina de acasa afiseaza un mesaj)
}

$router = new Router($config['base_url']);
foreach (require ROOT . '/config/routes.php' as [$method, $path, $handler]) {
    $router->add($method, $path, $handler);
}

try {
    // Orice formular trimis (POST) trebuie sa aiba token-ul CSRF corect
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        Csrf::verify($_POST['_csrf'] ?? null);
    }

    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (NotFoundException $e) {
    http_response_code(404);
    View::share('noindex', true);
    echo View::render('errors/404', ['title' => 'Pagina nu există']);
} catch (ForbiddenException $e) {
    Logger::warning('security.forbidden', ['reason' => $e->getMessage()]);
    http_response_code(403);
    View::share('noindex', true);
    echo View::render('errors/403', ['title' => 'Acces interzis']);
} catch (CsrfException $e) {
    Logger::warning('security.csrf', ['reason' => $e->getMessage()]);
    http_response_code(419);
    View::share('noindex', true);
    echo View::render('errors/419', ['title' => 'Sesiune expirată']);
} catch (Throwable $e) {
    http_response_code(500);
    Logger::error('error.server', ['error' => $e->getMessage(), 'file' => basename($e->getFile()) . ':' . $e->getLine()]);
    View::share('noindex', true);
    echo View::render('errors/500', ['title' => 'Eroare de server']);
}
