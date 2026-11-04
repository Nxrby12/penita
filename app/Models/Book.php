<?php
declare(strict_types=1);

namespace App\Models;

/** Modelul unei carti (partea "M" din MVC). */
class Book
{
    public function __construct(
        public readonly int $id,
        public readonly int $authorId,
        public readonly string $title,
        public readonly string $slug,
        public readonly string $description,
        public readonly float $price,
        public readonly string $coverPath,
        public readonly string $status,
        public readonly bool $isBlindDate,
        public readonly ?string $authorName = null,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id:          (int) $row['id'],
            authorId:    (int) $row['author_id'],
            title:       $row['title'],
            slug:        $row['slug'],
            description: $row['description'],
            price:       (float) $row['price'],
            coverPath:   $row['cover_path'],
            status:      $row['status'],
            isBlindDate: (bool) $row['is_blind_date'],
            authorName:  $row['author_name'] ?? null,
        );
    }

    public function formattedPrice(): string
    {
        return number_format($this->price, 2, ',', '.') . ' lei';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
