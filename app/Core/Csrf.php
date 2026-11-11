<?php
declare(strict_types=1);

namespace App\Core;

use App\Exceptions\CsrfException;

/**
 * Protectie CSRF: fiecare sesiune primeste un token aleator, pus ca
 * camp ascuns in fiecare formular. O cerere POST fara token-ul corect
 * (de exemplu trimisa de pe alt site) este respinsa.
 */
class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        $token = Session::get(self::KEY);
        if (!is_string($token)) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::KEY, $token);
        }
        return $token;
    }

    /** Campul ascuns care se pune in fiecare <form method="post">. */
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . self::token() . '">';
    }

    /**
     * @throws CsrfException daca token-ul lipseste sau nu se potriveste
     */
    public static function verify(?string $submitted): void
    {
        $expected = Session::get(self::KEY);
        if (!is_string($expected) || !is_string($submitted) || !hash_equals($expected, $submitted)) {
            throw new CsrfException('Token CSRF invalid sau lipsa.');
        }
    }
}
