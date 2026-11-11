<?php
declare(strict_types=1);

namespace App\Core;

use App\Exceptions\ForbiddenException;
use App\Models\User;
use App\Repositories\UserRepository;

/**
 * Autentificare (cine esti) si autorizare (ce ai voie sa faci).
 * In sesiune se pastreaza doar ID-ul utilizatorului; restul se citeste din baza de date.
 */
class Auth
{
    private static ?User $user = null;
    private static bool $loaded = false;

    public static function login(User $user): void
    {
        Session::regenerate();
        Session::set('user_id', $user->id);
        self::$user = $user;
        self::$loaded = true;
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$user = null;
        self::$loaded = true;
    }

    public static function user(): ?User
    {
        if (!self::$loaded) {
            self::$loaded = true;
            $id = Session::get('user_id');
            if (is_int($id)) {
                self::$user = (new UserRepository())->findById($id);
            }
        }
        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function hasRole(string ...$roles): bool
    {
        $user = self::user();
        return $user !== null && in_array($user->role, $roles, true);
    }

    /**
     * Opreste accesul daca utilizatorul nu are unul dintre rolurile cerute.
     * @throws ForbiddenException
     */
    public static function requireRole(string ...$roles): void
    {
        if (!self::hasRole(...$roles)) {
            throw new ForbiddenException('Acces interzis pentru rolul curent.');
        }
    }
}
