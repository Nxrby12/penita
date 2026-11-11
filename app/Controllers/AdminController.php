<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Repositories\UserRepository;

/** Panoul de administrare: accesibil DOAR rolului admin. */
class AdminController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        Auth::requireRole('admin');   // altfel: ForbiddenException -> pagina 403

        $this->view('admin/index', [
            'title' => 'Administrare',
            'users' => (new UserRepository())->findAll(),
        ]);
    }
}
