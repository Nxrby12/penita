<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Book;
use App\Support\Str;
use PDO;

/** Accesul la tabelele `books` si `book_genres`. Toate interogarile folosesc prepared statements. */
class BookRepository extends BaseRepository
{
    /** Cartea + pseudonimul autorului + genurile ei, intr-o singura interogare. */
    private const SELECT = "SELECT b.*, a.pen_name AS author_name,
                                   (SELECT GROUP_CONCAT(g.name ORDER BY g.name SEPARATOR ', ')
                                      FROM book_genres bg JOIN genres g ON g.id = bg.genre_id
                                     WHERE bg.book_id = b.id) AS genre_list
                              FROM books b
                              JOIN author_profiles a ON a.user_id = b.author_id";

    protected function table(): string
    {
        return 'books';
    }

    protected function map(array $row): Book
    {
        return Book::fromRow($row);
    }

    public function findById(int $id): ?Book
    {
        $stmt = $this->db->prepare(self::SELECT . ' WHERE b.id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->map($row) : null;
    }

    /** O carte aprobata, dupa adresa ei (slug). Cartile Blind Date nu au pagina publica. */
    public function findPublishedBySlug(string $slug): ?Book
    {
        $stmt = $this->db->prepare(
            self::SELECT . " WHERE b.slug = :slug AND b.status = 'approved' AND b.is_blind_date = FALSE"
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ? $this->map($row) : null;
    }

    /** Cele mai noi carti aprobate (fara cele Blind Date, care sunt secrete). */
    public function latestApproved(int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            self::SELECT . " WHERE b.status = 'approved' AND b.is_blind_date = FALSE
                             ORDER BY b.created_at DESC, b.id DESC LIMIT :limit"
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return array_map(fn(array $row) => $this->map($row), $stmt->fetchAll());
    }

    /**
     * Catalogul public, optional filtrat pe gen, pe pagini.
     * @return array{books: Book[], total: int}
     */
    public function catalog(?int $genreId, int $page, int $perPage): array
    {
        $where = "WHERE b.status = 'approved' AND b.is_blind_date = FALSE";
        $params = [];
        if ($genreId !== null) {
            $where .= ' AND EXISTS (SELECT 1 FROM book_genres bg WHERE bg.book_id = b.id AND bg.genre_id = :genre)';
            $params['genre'] = $genreId;
        }

        $count = $this->db->prepare("SELECT COUNT(*) FROM books b {$where}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $stmt = $this->db->prepare(self::SELECT . " {$where} ORDER BY b.created_at DESC, b.id DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'books' => array_map(fn(array $row) => $this->map($row), $stmt->fetchAll()),
            'total' => $total,
        ];
    }

    /** Toate cartile unui autor, indiferent de status. */
    public function byAuthor(int $authorId): array
    {
        $stmt = $this->db->prepare(self::SELECT . ' WHERE b.author_id = :author ORDER BY b.created_at DESC, b.id DESC');
        $stmt->execute(['author' => $authorId]);
        return array_map(fn(array $row) => $this->map($row), $stmt->fetchAll());
    }

    /** Lista pentru admin: cele in asteptare primele. */
    public function forAdmin(?string $status = null): array
    {
        $sql = self::SELECT;
        $params = [];
        if ($status !== null) {
            $sql .= ' WHERE b.status = :status';
            $params['status'] = $status;
        }
        $sql .= " ORDER BY FIELD(b.status, 'pending', 'approved', 'rejected'), b.created_at DESC, b.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return array_map(fn(array $row) => $this->map($row), $stmt->fetchAll());
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM books WHERE status = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    /** Salveaza cartea si genurile ei, in aceeasi tranzactie. */
    public function create(int $authorId, string $title, string $description, float $price,
                           string $cover, string $pdf, array $genreIds): Book
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO books (author_id, title, slug, description, price, cover_path, pdf_path)
                 VALUES (:author, :title, :slug, :description, :price, :cover, :pdf)'
            );
            $stmt->execute([
                'author'      => $authorId,
                'title'       => $title,
                'slug'        => $this->uniqueSlug($title),
                'description' => $description,
                'price'       => $price,
                'cover'       => $cover,
                'pdf'         => $pdf,
            ]);
            $id = (int) $this->db->lastInsertId();

            $link = $this->db->prepare('INSERT INTO book_genres (book_id, genre_id) VALUES (:book, :genre)');
            foreach ($genreIds as $genreId) {
                $link->execute(['book' => $id, 'genre' => $genreId]);
            }

            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
        return $this->findById($id);
    }

    public function setStatus(int $id, string $status, int $adminId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE books SET status = :status, reviewed_by = :admin, reviewed_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['status' => $status, 'admin' => $adminId, 'id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Cartile Blind Date aprobate, cu indiciile lor (fara titlu sau coperta in pagina publica).
     * @return array<int, array{book: Book, clues: string[]}>
     */
    public function blindDates(): array
    {
        $stmt = $this->db->query(
            self::SELECT . " WHERE b.status = 'approved' AND b.is_blind_date = TRUE ORDER BY b.created_at DESC, b.id DESC"
        );
        return array_map(fn(array $row) => [
            'book'  => $this->map($row),
            'clues' => $this->cluesFor((int) $row['id']),
        ], $stmt->fetchAll());
    }

    /** @return string[] indiciile, in ordine */
    public function cluesFor(int $bookId): array
    {
        $stmt = $this->db->prepare('SELECT clue FROM blind_date_clues WHERE book_id = :book ORDER BY position');
        $stmt->execute(['book' => $bookId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Marcheaza / demarcheaza o carte ca Blind Date si ii salveaza indiciile. */
    public function saveBlindDate(int $bookId, bool $enabled, array $clues): void
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare('UPDATE books SET is_blind_date = :on WHERE id = :id')
                     ->execute(['on' => (int) $enabled, 'id' => $bookId]);
            $this->db->prepare('DELETE FROM blind_date_clues WHERE book_id = :id')->execute(['id' => $bookId]);

            $insert = $this->db->prepare('INSERT INTO blind_date_clues (book_id, position, clue) VALUES (:b, :p, :c)');
            foreach (array_values($clues) as $i => $clue) {
                $insert->execute(['b' => $bookId, 'p' => $i + 1, 'c' => $clue]);
            }
            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** @return string[] */
    public function genreNames(int $bookId): array
    {
        $stmt = $this->db->prepare(
            'SELECT g.name FROM genres g JOIN book_genres bg ON bg.genre_id = g.id
              WHERE bg.book_id = :book ORDER BY g.name'
        );
        $stmt->execute(['book' => $bookId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** "titlu", "titlu-2", "titlu-3"... pana gaseste o adresa libera. */
    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $check = $this->db->prepare('SELECT 1 FROM books WHERE slug = :slug');
        for ($i = 2; ; $i++) {
            $check->execute(['slug' => $slug]);
            if (!$check->fetchColumn()) {
                return $slug;
            }
            $slug = "{$base}-{$i}";
        }
    }
}
