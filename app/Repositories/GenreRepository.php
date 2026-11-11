<?php
declare(strict_types=1);

namespace App\Repositories;

/** Accesul la tabelul `genres`. Genurile sunt intoarse ca obiecte simple {id, name}. */
class GenreRepository extends BaseRepository
{
    protected function table(): string
    {
        return 'genres';
    }

    protected function map(array $row): object
    {
        return (object) ['id' => (int) $row['id'], 'name' => $row['name']];
    }

    public function findAll(): array
    {
        $rows = $this->db->query('SELECT * FROM genres ORDER BY name')->fetchAll();
        return array_map(fn(array $row) => $this->map($row), $rows);
    }

    /** ID-urile valide dintr-o lista primita din formular. @return int[] */
    public function filterValidIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return [];
        }
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT id FROM genres WHERE id IN ({$marks})");
        $stmt->execute($ids);
        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }
}
