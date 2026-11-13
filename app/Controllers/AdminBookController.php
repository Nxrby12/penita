<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Logger;
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

    /** Formularul Blind Date: bifa + 3 indicii. */
    public function editBlindDate(string $id): void
    {
        $this->guard();
        $book = $this->findOrFail((int) $id);
        $this->view('admin/blind-date', [
            'title' => 'Blind Date: ' . $book->title,
            'book'  => $book,
            'clues' => $this->books->cluesFor($book->id),
        ]);
    }

    public function updateBlindDate(string $id): void
    {
        $this->guard();
        $book = $this->findOrFail((int) $id);
        $enabled = isset($_POST['is_blind_date']);
        $clues = array_map(fn($c) => trim((string) $c), array_slice((array) ($_POST['clues'] ?? []), 0, 3));

        $errors = [];
        foreach ([0, 1, 2] as $i) {
            $clue = $clues[$i] ?? '';
            if ($enabled && $clue === '') {
                $errors["clue{$i}"] = 'Scrie indiciul.';
            } elseif (mb_strlen($clue) > 80) {
                $errors["clue{$i}"] = 'Maximum 80 de caractere.';
            }
        }
        if ($errors !== []) {
            $old = $_POST;
            foreach ([0, 1, 2] as $i) {
                $old["clue{$i}"] = $clues[$i] ?? '';
            }
            $this->backWithErrors("/admin/carti/{$book->id}/blind-date", $errors, $old);
        }

        $this->books->saveBlindDate($book->id, $enabled, $enabled ? $clues : []);
        Logger::info('admin.blind_date', ['book_id' => $book->id, 'title' => $book->title, 'enabled' => $enabled]);
        Session::flash('success', $enabled
            ? "„{$book->title}” este acum un Blind Date."
            : "„{$book->title}” nu mai este Blind Date.");
        $this->redirect('/admin/carti');
    }

    public function destroy(string $id): void
    {
        $this->guard();
        $book = $this->findOrFail((int) $id);
        try {
            $this->books->delete($book->id);
            BookStorage::deleteFiles($book);
            Logger::info('admin.book_deleted', ['book_id' => $book->id, 'title' => $book->title]);
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
        Logger::info('admin.book_' . $status, ['book_id' => $book->id, 'title' => $book->title]);
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
