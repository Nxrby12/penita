<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;
use App\Repositories\BookRepository;
use PDOException;

class HomeController extends Controller
{
    public function index(): void
    {
        $books = [];
        $dbError = false;

        try {
            $books = (new BookRepository())->latestApproved(8);
        } catch (PDOException $e) {
            // Baza de date nu raspunde: pagina se afiseaza oricum, fara carti
            Logger::error('error.database', ['error' => $e->getMessage()]);
            $dbError = true;
        }

        $this->view('home/index', [
            'canonical'      => absolute_url('/'),
            'structuredData' => [
                '@context'    => 'https://schema.org',
                '@type'       => 'WebSite',
                'name'        => \App\Core\Config::get('app_name', 'Penița'),
                'url'         => absolute_url('/'),
                'inLanguage'  => 'ro',
            ],
            'title'       => 'Cărți scrise de autori independenți',
            'description' => 'Penița: descoperă și cumpără cărți publicate direct de autori independenți sau încearcă un Blind Date with a Book.',
            'books'       => $books,
            'dbError'     => $dbError,
        ]);
    }
}
