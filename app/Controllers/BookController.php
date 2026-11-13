<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Exceptions\NotFoundException;
use App\Repositories\BookRepository;
use App\Repositories\GenreRepository;
use App\Repositories\OrderRepository;

/** Partea publica: catalogul de carti si pagina fiecarei carti. */
class BookController extends Controller
{
    private const PER_PAGE = 12;

    public function index(): void
    {
        $genres = (new GenreRepository())->findAll();

        $genreId = filter_var($_GET['gen'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
        $page = filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;

        $result = (new BookRepository())->catalog($genreId, $page, self::PER_PAGE);
        $pages = max(1, (int) ceil($result['total'] / self::PER_PAGE));

        $current = null;
        foreach ($genres as $g) {
            if ($g->id === $genreId) {
                $current = $g;
            }
        }

        $this->view('books/index', [
            'title'       => $current ? "Cărți {$current->name}" : 'Toate cărțile',
            'description' => 'Catalogul de cărți publicate de autori independenți pe Penița. Filtrează după gen și descoperă povești noi.',
            'books'       => $result['books'],
            'total'       => $result['total'],
            'genres'      => $genres,
            'genreId'     => $genreId,
            'page'        => min($page, $pages),
            'pages'       => $pages,
        ]);
    }

    public function show(string $slug): void
    {
        $book = (new BookRepository())->findPublishedBySlug($slug)
            ?? throw new NotFoundException("Carte inexistenta: {$slug}");

        $user = auth_user();
        $owned = $user !== null
            && ($book->authorId === $user->id || (new OrderRepository())->userOwnsBook($user->id, $book->id));

        $this->view('books/show', [
            'owned'       => $owned,
            'loggedIn'    => $user !== null,
            'title'       => $book->title,
            'description' => $book->excerpt(155),
            'book'        => $book,
        ]);
    }
}
