<?php
declare(strict_types=1);

namespace App\Core;

use App\Exceptions\ValidationException;

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

    /** Intoarce la formular cu erorile si datele completate (fara parole). */
    protected function backWithErrors(string $path, ValidationException|array $errors, array $old = []): never
    {
        unset($old['password'], $old['password_confirmation'], $old['_csrf']);
        Session::flash('errors', $errors instanceof ValidationException ? $errors->errors() : $errors);
        Session::flash('old', $old);
        $this->redirect($path);
    }

    /** Pagina e doar pentru utilizatorii autentificati. */
    protected function requireLogin(): void
    {
        if (!Auth::check()) {
            Session::flash('error', 'Intră în cont ca să vezi pagina asta.');
            $this->redirect('/autentificare');
        }
    }
}
