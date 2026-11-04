<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Clasa de baza pentru toate controller-ele.
 * Controller-ele concrete o MOSTENESC (ex: HomeController extends Controller).
 */
abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        echo View::render($view, $data);
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . url($path));
        exit;
    }
}
