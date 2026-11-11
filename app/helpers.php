<?php
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;
use App\Models\User;

/**
 * Escape pentru afisarea in HTML -> protectie XSS.
 * Orice text venit de la utilizator se afiseaza DOAR prin e().
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Construieste un link intern, tinand cont de prefix (ex: /biblioteca). */
function url(string $path = '/'): string
{
    return View::baseUrl() . '/' . ltrim($path, '/');
}

/** Link catre un fisier din public/ (CSS, JS, imagini). */
function asset(string $path): string
{
    return url($path);
}

/** Campul ascuns cu token-ul CSRF, pentru fiecare formular POST. */
function csrf_field(): string
{
    return Csrf::field();
}

/**
 * Valoarea completata anterior intr-un camp (dupa o eroare de validare).
 * Citeste o singura data mesajele flash pe cerere si le pastreaza in memorie.
 */
function old(string $field, string $default = ''): string
{
    static $old = null;
    $old ??= Session::pull('old', []);
    return (string) ($old[$field] ?? $default);
}

/** Mesajul de eroare pentru un camp, daca exista. */
function error(string $field): ?string
{
    static $errors = null;
    $errors ??= Session::pull('errors', []);
    return $errors[$field] ?? null;
}

/** Utilizatorul autentificat sau null (fara sa opreasca pagina daca baza de date nu raspunde). */
function auth_user(): ?User
{
    try {
        return Auth::user();
    } catch (\PDOException) {
        return null;
    }
}
