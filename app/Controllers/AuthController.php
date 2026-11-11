<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;

/** Inregistrare, autentificare si iesire din cont. */
class AuthController extends Controller
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/cont');
        }
        $this->view('auth/login', [
            'title'       => 'Intră în cont',
            'description' => 'Autentifică-te în contul tău Penița.',
        ]);
    }

    public function login(): void
    {
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
            $this->backWithErrors('/autentificare', ['email' => 'Email sau parolă greșită.'], $_POST);
        }

        Auth::login($user);
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
        try {
            $data = Validator::validate($_POST, [
                'username'              => 'required|alpha_dash|min:3|max:50',
                'email'                 => 'required|email|max:255',
                'password'              => 'required|min:8|max:72',
                'password_confirmation' => 'required|same:password',
                'role'                  => 'required|in:cititor,autor',   // adminul NU se poate alege
                'pen_name'              => 'max:100',
            ]);

            $errors = [];
            if ($this->users->usernameExists($data['username'])) {
                $errors['username'] = 'Numele de utilizator este deja folosit.';
            }
            if ($this->users->emailExists($data['email'])) {
                $errors['email'] = 'Există deja un cont cu acest email.';
            }
            if ($errors !== []) {
                throw new ValidationException($errors);
            }
        } catch (ValidationException $e) {
            $this->backWithErrors('/inregistrare', $e, $_POST);
        }

        $user = $this->users->create(
            $data['username'],
            $data['email'],
            $_POST['password'],         // parola exacta, nu cea curatata de spatii
            $data['role'],
            $data['pen_name'] ?: null,
        );

        Auth::login($user);
        Session::flash('success', 'Cont creat. Bun venit, ' . $user->username . '!');
        $this->redirect('/cont');
    }

    public function logout(): void
    {
        Auth::logout();
        Session::start();
        Session::regenerate();   // sesiune noua, fara legatura cu cea veche
        Session::flash('success', 'Ai ieșit din cont.');
        $this->redirect('/');
    }
}
