<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Implementare comuna pentru operatiile de baza.
 * Repository-urile concrete o MOSTENESC si spun doar tabelul si modelul.
 */
abstract class BaseRepository implements RepositoryInterface
{
    protected PDO $db;

    /** Numele tabelului din baza de date. */
    abstract protected function table(): string;

    /** Transforma un rand din baza de date intr-un obiect model. */
    abstract protected function map(array $row): object;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table()} ORDER BY id DESC");
        return array_map(fn(array $row) => $this->map($row), $stmt->fetchAll());
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table()} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->map($row) : null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table()} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
