<?php
declare(strict_types=1);

namespace App\Repositories;

/**
 * Contract comun pentru toate repository-urile (pattern-ul Repository).
 * Controller-ele lucreaza cu aceasta interfata, nu direct cu SQL-ul.
 */
interface RepositoryInterface
{
    /** @return object[] */
    public function findAll(): array;

    public function findById(int $id): ?object;

    public function delete(int $id): bool;
}
