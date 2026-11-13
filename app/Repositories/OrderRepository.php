<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Book;

/** Accesul la tabelele `orders` si `order_items`. */
class OrderRepository extends BaseRepository
{
    protected function table(): string
    {
        return 'orders';
    }

    protected function map(array $row): object
    {
        return (object) [
            'id'        => (int) $row['id'],
            'userId'    => (int) $row['user_id'],
            'sessionId' => $row['stripe_session_id'],
            'status'    => $row['status'],
        ];
    }

    /** Creeaza comanda (in asteptarea platii) cu pretul cartii din acest moment. */
    public function createPending(int $userId, Book $book): int
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare('INSERT INTO orders (user_id) VALUES (:user)')->execute(['user' => $userId]);
            $orderId = (int) $this->db->lastInsertId();
            $this->db->prepare('INSERT INTO order_items (order_id, book_id, unit_price) VALUES (:o, :b, :p)')
                     ->execute(['o' => $orderId, 'b' => $book->id, 'p' => $book->price]);
            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
        return $orderId;
    }

    public function attachSession(int $orderId, string $sessionId): void
    {
        $this->db->prepare('UPDATE orders SET stripe_session_id = :s WHERE id = :id')
                 ->execute(['s' => $sessionId, 'id' => $orderId]);
    }

    /** Comanda, doar daca apartine utilizatorului (nimeni nu poate confirma comanda altcuiva). */
    public function findForUser(int $orderId, int $userId): ?object
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = :id AND user_id = :user');
        $stmt->execute(['id' => $orderId, 'user' => $userId]);
        $row = $stmt->fetch();
        return $row ? $this->map($row) : null;
    }

    public function markPaid(int $orderId): void
    {
        $this->db->prepare("UPDATE orders SET status = 'paid', paid_at = NOW() WHERE id = :id AND status = 'pending'")
                 ->execute(['id' => $orderId]);
    }

    public function markFailed(int $orderId): void
    {
        $this->db->prepare("UPDATE orders SET status = 'failed' WHERE id = :id AND status = 'pending'")
                 ->execute(['id' => $orderId]);
    }

    /** Totalul comenzii se calculeaza din produse (nu e stocat: 3NF). */
    public function total(int $orderId): float
    {
        $stmt = $this->db->prepare('SELECT COALESCE(SUM(unit_price), 0) FROM order_items WHERE order_id = :id');
        $stmt->execute(['id' => $orderId]);
        return (float) $stmt->fetchColumn();
    }

    public function bookIdForOrder(int $orderId): ?int
    {
        $stmt = $this->db->prepare('SELECT book_id FROM order_items WHERE order_id = :id LIMIT 1');
        $stmt->execute(['id' => $orderId]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    public function userOwnsBook(int $userId, int $bookId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM orders o JOIN order_items oi ON oi.order_id = o.id
              WHERE o.user_id = :user AND oi.book_id = :book AND o.status = 'paid' LIMIT 1"
        );
        $stmt->execute(['user' => $userId, 'book' => $bookId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Cartile cumparate de utilizator (inclusiv cele Blind Date, acum dezvaluite).
     * @return array<int, array{book: Book, paidAt: string, price: float}>
     */
    public function library(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT b.*, a.pen_name AS author_name, o.paid_at, oi.unit_price
               FROM orders o
               JOIN order_items oi ON oi.order_id = o.id
               JOIN books b ON b.id = oi.book_id
               JOIN author_profiles a ON a.user_id = b.author_id
              WHERE o.user_id = :user AND o.status = 'paid'
              ORDER BY o.paid_at DESC"
        );
        $stmt->execute(['user' => $userId]);
        return array_map(fn(array $row) => [
            'book'   => Book::fromRow($row),
            'paidAt' => $row['paid_at'],
            'price'  => (float) $row['unit_price'],
        ], $stmt->fetchAll());
    }
}
