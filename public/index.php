<?php
/**
 * FRONT CONTROLLER
 * Singurul punct de intrare al aplicatiei. Incarca configurarea,
 * inregistreaza rutele si trimite cererea catre controller-ul potrivit.
 */
declare(strict_types=1);

use App\Core\Router;
use App\Core\View;
use App\Exceptions\NotFoundException;

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

$config = require ROOT . '/config/config.php';
View::setBaseUrl($config['base_url']);
View::share('appName', $config['app_name']);

$router = new Router($config['base_url']);
foreach (require ROOT . '/config/routes.php' as [$method, $path, $handler]) {
    $router->add($method, $path, $handler);
}

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (NotFoundException $e) {
    http_response_code(404);
    echo View::render('errors/404', ['title' => 'Pagina nu există']);
} catch (Throwable $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo View::render('errors/500', ['title' => 'Eroare de server']);
}
