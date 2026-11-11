<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UploadException;
use App\Exceptions\ValidationException;
use App\Repositories\BookRepository;
use App\Repositories\GenreRepository;
use App\Services\BookStorage;
use App\Services\FileUploader;

/** Cartile autorului: lista, publicare (upload), stergere. Doar pentru rolul "autor". */
class AuthorBookController extends Controller
{
    private BookRepository $books;

    public function __construct()
    {
        $this->books = new BookRepository();
    }

    public function index(): void
    {
        $this->guard();
        $this->view('author/books', [
            'title' => 'Cărțile mele publicate',
            'books' => $this->books->byAuthor(Auth::user()->id),
        ]);
    }

    public function create(): void
    {
        $this->guard();
        $this->view('author/create', [
            'title'  => 'Publică o carte',
            'genres' => (new GenreRepository())->findAll(),
        ]);
    }

    public function store(): void
    {
        $this->guard();
        $back = '/autor/carti/noua';

        // 1. Datele text
        // toate erorile se aduna si se afiseaza deodata
        $errors = [];
        try {
            $data = Validator::validate($_POST, [
                'title'       => 'required|min:2|max:200',
                'description' => 'required|min:30|max:5000',
                'price'       => 'required|numeric',
            ]);
            $price = round((float) str_replace(',', '.', $data['price']), 2);
            if ($price < 0 || $price > 500) {
                $errors['price'] = 'Prețul trebuie să fie între 0 și 500 lei.';
            }
        } catch (ValidationException $e) {
            $errors = $e->errors();
        }

        $genreIds = (new GenreRepository())->filterValidIds((array) ($_POST['genres'] ?? []));
        if ($genreIds === [] || count($genreIds) > 3) {
            $errors['genres'] = 'Alege între 1 și 3 genuri.';
        }
        if ($errors !== []) {
            $this->backWithErrors($back, $errors, $_POST);
        }

        // 2. Fisierele (fiecare cu mesajul lui de eroare)
        $uploader = new FileUploader();
        $cover = null;
        try {
            $cover = $uploader->storeImage($_FILES['cover'] ?? [], BookStorage::coverDir());
        } catch (UploadException $e) {
            $this->backWithErrors($back, ['cover' => $e->getMessage()], $_POST);
        }
        try {
            $pdf = $uploader->storePdf($_FILES['pdf'] ?? [], BookStorage::pdfDir());
        } catch (UploadException $e) {
            BookStorage::deleteCover($cover);   // nu lasam coperta orfana
            $this->backWithErrors($back, ['pdf' => $e->getMessage()], $_POST);
        }

        // 3. Salvarea in baza de date
        try {
            $book = $this->books->create(
                Auth::user()->id, $data['title'], $data['description'], $price, $cover, $pdf, $genreIds
            );
        } catch (\PDOException $e) {
            BookStorage::deleteCover($cover);
            BookStorage::deletePdf($pdf);
            throw $e;
        }

        Session::flash('success', "„{$book->title}” a fost trimisă. O publicăm după ce o verifică un administrator.");
        $this->redirect('/autor/carti');
    }

    public function destroy(string $id): void
    {
        $this->guard();
        $book = $this->books->findById((int) $id);
        if ($book === null) {
            throw new NotFoundException('Carte inexistenta');
        }
        if ($book->authorId !== Auth::user()->id) {
            throw new ForbiddenException('Cartea apartine altui autor');   // un autor sterge doar cartile LUI
        }

        try {
            $this->books->delete($book->id);
            BookStorage::deleteFiles($book);
            Session::flash('success', "„{$book->title}” a fost ștearsă.");
        } catch (\PDOException) {
            // cheia straina din order_items impiedica stergerea cartilor deja cumparate
            Session::flash('error', "„{$book->title}” are deja cititori care au cumpărat-o, așa că nu poate fi ștearsă.");
        }
        $this->redirect('/autor/carti');
    }

    private function guard(): void
    {
        $this->requireLogin();
        Auth::requireRole('autor');
    }
}
