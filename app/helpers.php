<?php
declare(strict_types=1);

use App\Core\View;

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
