<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\BookRepository;
use App\Repositories\OrderRepository;

/** Sectiunea "Blind Date with a Book": carti vandute doar dupa indicii. */
class BlindDateController extends Controller
{
    public function index(): void
    {
        $user = auth_user();
        $items = (new BookRepository())->blindDates();

        // Ce carti are deja utilizatorul (ca sa nu le cumpere de doua ori)
        $owned = [];
        if ($user !== null) {
            $orders = new OrderRepository();
            foreach ($items as $item) {
                $book = $item['book'];
                if ($book->authorId === $user->id || $orders->userOwnsBook($user->id, $book->id)) {
                    $owned[$book->id] = true;
                }
            }
        }

        $this->view('blind/index', [
            'title'       => 'Blind Date with a Book',
            'description' => 'Alege o carte doar după câteva indicii. Titlul și coperta se dezvăluie după cumpărare.',
            'items'       => $items,
            'owned'       => $owned,
            'loggedIn'    => $user !== null,
        ]);
    }
}
