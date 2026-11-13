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

/**
 * Adresa completa (cu http:// si domeniu), necesara pentru Stripe.
 * Se ia din config ('app_url'); daca lipseste, se deduce din cererea curenta.
 */
function absolute_url(string $path = '/'): string
{
    $base = rtrim((string) \App\Core\Config::get('app_url', ''), '/');
    if ($base === '') {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url($path);
    }
    return $base . '/' . ltrim($path, '/');
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
    $value = $old[$field] ?? $default;
    return is_scalar($value) ? (string) $value : $default;
}

/** Lista completata anterior (ex: genurile bifate). @return string[] */
function old_list(string $field): array
{
    $value = old_raw($field);
    return is_array($value) ? array_map('strval', $value) : [];
}

/** Valoarea bruta din formularul anterior (text sau lista). */
function old_raw(string $field): mixed
{
    static $old = null;
    $old ??= Session::pull('old_raw', []);
    return $old[$field] ?? null;
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
