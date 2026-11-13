<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Captcha;
use App\Core\Controller;
use App\Core\Logger;
use App\Core\Session;
use App\Core\Validator;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;

/** Inregistrare, autentificare si iesire din cont. */
class AuthController extends Controller
{
    /** Dupa atatea incercari gresite, la login se cere si CAPTCHA. */
    public const CAPTCHA_AFTER_FAILURES = 3;

    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public static function loginNeedsCaptcha(): bool
    {
        return (int) Session::get('failed_logins', 0) >= self::CAPTCHA_AFTER_FAILURES;
    }

    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/cont');
        }
        $this->view('auth/login', [
            'title'        => 'Intră în cont',
            'description'  => 'Autentifică-te în contul tău Penița.',
            'needsCaptcha' => self::loginNeedsCaptcha(),
        ]);
    }

    public function login(): void
    {
        if (self::loginNeedsCaptcha() && !Captcha::verify($_POST['captcha'] ?? null)) {
            Logger::warning('security.captcha_failed', ['form' => 'login']);
            $this->backWithErrors('/autentificare', ['captcha' => 'Codul din imagine nu este corect.'], $_POST);
        }

        try {
            $data = Validator::validate($_POST, [
                'email'    => 'required|email',
                'password' => 'required',
            ]);
        } catch (ValidationException $e) {
            $this->backWithErrors('/autentificare', $e, $_POST);
        }

        $user = $this->users->findByEmail($data['email']);

        // Acelasi mesaj daca emailul nu exista sau parola e gresita:
        // un atacator nu poate afla ce adrese de email au cont.
        if ($user === null || !$user->verifyPassword($_POST['password'])) {
            Session::set('failed_logins', (int) Session::get('failed_logins', 0) + 1);
            Logger::warning('auth.login_failed', ['email' => $data['email'], 'attempts' => Session::get('failed_logins')]);
            $this->backWithErrors('/autentificare', ['email' => 'Email sau parolă greșită.'], $_POST);
        }

        Session::forget('failed_logins');
        Auth::login($user);
        Logger::info('auth.login');
        Session::flash('success', 'Bine ai revenit, ' . $user->username . '!');
        $this->redirect('/cont');
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            $this->redirect('/cont');
        }
        $this->view('auth/register', [
            'title'       => 'Creează cont',
            'description' => 'Creează un cont Penița de cititor sau de autor.',
        ]);
    }

    public function register(): void
    {
        $errors = [];
        if (!Captcha::verify($_POST['captcha'] ?? null)) {
            Logger::warning('security.captcha_failed', ['form' => 'register']);
            $errors['captcha'] = 'Codul din imagine nu este corect.';
        }

        try {
            $data = Validator::validate($_POST, [
                'username'              => 'required|alpha_dash|min:3|max:50',
                'email'                 => 'required|email|max:255',
                'password'              => 'required|min:8|max:72',
                'password_confirmation' => 'required|same:password',
                'role'                  => 'required|in:cititor,autor',   // adminul NU se poate alege
                'pen_name'              => 'max:100',
            ]);
            if ($this->users->usernameExists($data['username'])) {
                $errors['username'] = 'Numele de utilizator este deja folosit.';
            }
            if ($this->users->emailExists($data['email'])) {
                $errors['email'] = 'Există deja un cont cu acest email.';
            }
        } catch (ValidationException $e) {
            $errors = $e->errors() + $errors;
        }

        if ($errors !== []) {
            $this->backWithErrors('/inregistrare', $errors, $_POST);
        }

        $user = $this->users->create(
            $data['username'],
            $data['email'],
            $_POST['password'],         // parola exacta, nu cea curatata de spatii
            $data['role'],
            $data['pen_name'] ?: null,
        );

        Auth::login($user);
        Logger::info('auth.register', ['role' => $user->role]);
        Session::flash('success', 'Cont creat. Bun venit, ' . $user->username . '!');
        $this->redirect('/cont');
    }

    public function logout(): void
    {
        Logger::info('auth.logout');
        Auth::logout();
        Session::start();
        Session::regenerate();   // sesiune noua, fara legatura cu cea veche
        Session::flash('success', 'Ai ieșit din cont.');
        $this->redirect('/');
    }
}
