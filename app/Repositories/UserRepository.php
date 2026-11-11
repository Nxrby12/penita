<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;

/** Accesul la tabelele `users`, `roles` si `author_profiles`. */
class UserRepository extends BaseRepository
{
    private const SELECT = 'SELECT u.*, r.name AS role FROM users u JOIN roles r ON r.id = u.role_id';

    protected function table(): string
    {
        return 'users';
    }

    protected function map(array $row): User
    {
        return User::fromRow($row);
    }

    // Suprascrie metodele din BaseRepository ca sa includa si numele rolului (JOIN)
    public function findAll(): array
    {
        $rows = $this->db->query(self::SELECT . ' ORDER BY u.id DESC')->fetchAll();
        return array_map(fn(array $row) => $this->map($row), $rows);
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare(self::SELECT . ' WHERE u.id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->map($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare(self::SELECT . ' WHERE u.email = :email');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ? $this->map($row) : null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        return (bool) $stmt->fetchColumn();
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Creeaza contul. Pentru autori creeaza si profilul de autor, in aceeasi tranzactie:
     * ori se salveaza amandoua, ori niciunul.
     */
    public function create(string $username, string $email, string $password, string $role, ?string $penName = null): User
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO users (role_id, username, email, password_hash)
                 VALUES ((SELECT id FROM roles WHERE name = :role), :username, :email, :hash)'
            );
            $stmt->execute([
                'role'     => $role,
                'username' => $username,
                'email'    => $email,
                'hash'     => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $id = (int) $this->db->lastInsertId();

            if ($role === 'autor') {
                $stmt = $this->db->prepare('INSERT INTO author_profiles (user_id, pen_name) VALUES (:id, :pen)');
                $stmt->execute(['id' => $id, 'pen' => $penName ?: $username]);
            }

            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $this->findById($id);
    }
}
