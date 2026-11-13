<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Logger;
use App\Repositories\BookRepository;

/** Consultarea jurnalului de activitate (doar admin). */
class AdminLogController extends Controller
{
    public const CATEGORIES = [
        'auth'     => 'Conturi',
        'book'     => 'Cărți',
        'admin'    => 'Administrare',
        'order'    => 'Comenzi',
        'security' => 'Securitate',
        'error'    => 'Erori',
    ];

    public function index(): void
    {
        $this->guard();

        $category = $_GET['categorie'] ?? null;
        $category = array_key_exists((string) $category, self::CATEGORIES) ? $category : null;

        $level = $_GET['nivel'] ?? null;
        $level = in_array($level, ['info', 'warning', 'error'], true) ? $level : null;

        $this->view('admin/log', [
            'title'        => 'Jurnal de activitate',
            'entries'      => Logger::recent(300, $category ? $category . '.' : null, $level),
            'category'     => $category,
            'level'        => $level,
            'pendingCount' => (new BookRepository())->countByStatus('pending'),
        ]);
    }

    /** Descarca fisierul complet al jurnalului. */
    public function download(): void
    {
        $this->guard();
        $file = Logger::path();
        if (!is_file($file)) {
            $this->redirect('/admin/jurnal');
        }
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="activity-' . date('Y-m-d') . '.log"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }

    private function guard(): void
    {
        $this->requireLogin();
        Auth::requireRole('admin');
    }
}
