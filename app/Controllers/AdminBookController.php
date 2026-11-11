<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Exceptions\NotFoundException;
use App\Repositories\BookRepository;
use App\Services\BookStorage;

/** Moderarea cartilor: aprobare, respingere, stergere. Doar pentru admin. */
class AdminBookController extends Controller
{
    private BookRepository $books;

    public function __construct()
    {
        $this->books = new BookRepository();
    }

    public function index(): void
    {
        $this->guard();
        $status = $_GET['status'] ?? null;
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $status = null;
        }

        $this->view('admin/books', [
            'title'        => 'Cărți trimise',
            'books'        => $this->books->forAdmin($status),
            'status'       => $status,
            'pendingCount' => $this->books->countByStatus('pending'),
        ]);
    }

    public function approve(string $id): void
    {
        $this->changeStatus((int) $id, 'approved', 'a fost aprobată și apare acum în catalog.');
    }

    public function reject(string $id): void
    {
        $this->changeStatus((int) $id, 'rejected', 'a fost respinsă.');
    }

    public function destroy(string $id): void
    {
        $this->guard();
        $book = $this->findOrFail((int) $id);
        try {
            $this->books->delete($book->id);
            BookStorage::deleteFiles($book);
            Session::flash('success', "„{$book->title}” a fost ștearsă.");
        } catch (\PDOException) {
            Session::flash('error', "„{$book->title}” a fost deja cumpărată, așa că nu poate fi ștearsă. O poți respinge.");
        }
        $this->redirect('/admin/carti');
    }

    private function changeStatus(int $id, string $status, string $message): void
    {
        $this->guard();
        $book = $this->findOrFail($id);
        $this->books->setStatus($book->id, $status, Auth::user()->id);
        Session::flash('success', "„{$book->title}” {$message}");
        $this->redirect('/admin/carti');
    }

    private function findOrFail(int $id): \App\Models\Book
    {
        return $this->books->findById($id) ?? throw new NotFoundException('Carte inexistenta');
    }

    private function guard(): void
    {
        $this->requireLogin();
        Auth::requireRole('admin');
    }
}
