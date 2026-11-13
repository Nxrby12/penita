<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Logger;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Repositories\BookRepository;
use App\Repositories\OrderRepository;
use App\Services\BookStorage;

/**
 * Descarcarea PDF-ului. PDF-urile stau in storage/, in afara folderului public,
 * deci singura cale de a le obtine e aceasta, cu verificarea dreptului de acces:
 * cumparatorul, autorul cartii sau un admin.
 */
class DownloadController extends Controller
{
    public function download(string $id): void
    {
        $this->requireLogin();
        $user = Auth::user();

        $book = (new BookRepository())->findById((int) $id)
            ?? throw new NotFoundException('Carte inexistenta');

        $allowed = $user->isAdmin()
            || $book->authorId === $user->id
            || (new OrderRepository())->userOwnsBook($user->id, $book->id);

        if (!$allowed) {
            throw new ForbiddenException('Cartea nu a fost cumparata');
        }

        $path = BookStorage::pdfDir() . '/' . basename($book->pdfPath);
        if (!is_file($path)) {
            throw new NotFoundException('Fisierul PDF lipseste');
        }

        Logger::info('book.downloaded', ['book_id' => $book->id, 'title' => $book->title]);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $book->slug . '.pdf"');
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        readfile($path);
        exit;
    }
}
