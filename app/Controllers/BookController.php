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

        // Adresa canonica: doar parametrii care schimba continutul (genul si pagina)
        $query = http_build_query(array_filter(['gen' => $genreId, 'pagina' => $page > 1 ? $page : null]));

        $this->view('books/index', [
            'canonical'   => absolute_url('/carti') . ($query ? '?' . $query : ''),
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

        $url = absolute_url('/carte/' . $book->slug);
        $cover = absolute_url($book->coverUrl());

        $this->view('books/show', [
            'canonical'      => $url,
            'ogType'         => 'book',
            'ogImage'        => $cover,
            // Date structurate schema.org: Google poate afisa cartea cu pret si autor in rezultate
            'structuredData' => [
                '@context'    => 'https://schema.org',
                '@type'       => 'Book',
                'name'        => $book->title,
                'author'      => ['@type' => 'Person', 'name' => $book->authorName],
                'description' => $book->excerpt(300),
                'image'       => $cover,
                'url'         => $url,
                'genre'       => $book->genres,
                'inLanguage'  => 'ro',
                'bookFormat'  => 'https://schema.org/EBook',
                'offers'      => [
                    '@type'         => 'Offer',
                    'price'         => number_format($book->price, 2, '.', ''),
                    'priceCurrency' => 'RON',
                    'availability'  => 'https://schema.org/InStock',
                    'url'           => $url,
                ],
            ],
            'owned'       => $owned,
            'loggedIn'    => $user !== null,
            'title'       => $book->title,
            'description' => $book->excerpt(155),
            'book'        => $book,
        ]);
    }
}
