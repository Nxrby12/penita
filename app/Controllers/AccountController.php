<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

/** Pagina "Contul meu": continutul difera in functie de rol. */
class AccountController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $this->view('account/index', [
            'title' => 'Contul meu',
            'user'  => Auth::user(),
        ]);
    }
}
