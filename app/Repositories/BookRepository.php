<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Book;

/** Accesul la tabelul `books`. Toate interogarile folosesc prepared statements. */
class BookRepository extends BaseRepository
{
    protected function table(): string
    {
        return 'books';
    }

    protected function map(array $row): Book
    {
        return Book::fromRow($row);
    }

    /** Cele mai noi carti aprobate (fara cartile "Blind Date", care sunt secrete). */
    public function latestApproved(int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            "SELECT b.*, a.pen_name AS author_name
               FROM books b
               JOIN author_profiles a ON a.user_id = b.author_id
              WHERE b.status = 'approved' AND b.is_blind_date = FALSE
              ORDER BY b.created_at DESC
              LIMIT :limit"
        );
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return array_map(fn(array $row) => $this->map($row), $stmt->fetchAll());
    }
}
