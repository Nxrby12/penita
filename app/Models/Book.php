<?php
declare(strict_types=1);

namespace App\Models;

/** Modelul unei carti (partea "M" din MVC). */
class Book
{
    public const STATUS_LABELS = [
        'pending'  => 'În așteptare',
        'approved' => 'Aprobată',
        'rejected' => 'Respinsă',
    ];

    /** @param string[] $genres */
    public function __construct(
        public readonly int $id,
        public readonly int $authorId,
        public readonly string $title,
        public readonly string $slug,
        public readonly string $description,
        public readonly float $price,
        public readonly string $coverPath,
        public readonly string $pdfPath,
        public readonly string $status,
        public readonly bool $isBlindDate,
        public readonly string $createdAt,
        public readonly ?string $authorName = null,
        public readonly array $genres = [],
    ) {}

    public static function fromRow(array $row, array $genres = []): self
    {
        return new self(
            id:          (int) $row['id'],
            authorId:    (int) $row['author_id'],
            title:       $row['title'],
            slug:        $row['slug'],
            description: $row['description'],
            price:       (float) $row['price'],
            coverPath:   $row['cover_path'],
            pdfPath:     $row['pdf_path'],
            status:      $row['status'],
            isBlindDate: (bool) $row['is_blind_date'],
            createdAt:   $row['created_at'],
            authorName:  $row['author_name'] ?? null,
            genres:      $genres ?: array_filter(explode(', ', (string) ($row['genre_list'] ?? ''))),
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

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /** Adresa publica a copertii (copertele sunt in public/uploads/covers). */
    public function coverUrl(): string
    {
        return 'uploads/covers/' . $this->coverPath;
    }

    /** Primele cuvinte din descriere, pentru carduri si meta description. */
    public function excerpt(int $length = 160): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $this->description));
        return mb_strlen($text) > $length ? rtrim(mb_substr($text, 0, $length - 1)) . '…' : $text;
    }
}
