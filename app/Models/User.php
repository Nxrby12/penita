<?php
declare(strict_types=1);

namespace App\Models;

/** Modelul unui utilizator. Rolul vine din tabelul `roles` (cititor, autor, admin). */
class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly string $email,
        public readonly string $role,
        public readonly string $passwordHash,
        public readonly string $createdAt,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id:           (int) $row['id'],
            username:     $row['username'],
            email:        $row['email'],
            role:         $row['role'],
            passwordHash: $row['password_hash'],
            createdAt:    $row['created_at'],
        );
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAuthor(): bool
    {
        return $this->role === 'autor';
    }

    /** Numele rolului, pentru afisare. */
    public function roleLabel(): string
    {
        return match ($this->role) {
            'admin' => 'Administrator',
            'autor' => 'Autor',
            default => 'Cititor',
        };
    }
}
