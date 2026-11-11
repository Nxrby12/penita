<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Book;

/**
 * Unde stau fisierele cartilor:
 *  - copertele: public/uploads/covers (se vad in browser)
 *  - PDF-urile: storage/books, IN AFARA folderului public: nu pot fi
 *    descarcate direct, ci doar prin aplicatie, dupa cumparare.
 */
class BookStorage
{
    public static function coverDir(): string
    {
        return ROOT . '/public/uploads/covers';
    }

    public static function pdfDir(): string
    {
        return ROOT . '/storage/books';
    }

    public static function deleteFiles(Book $book): void
    {
        self::deleteCover($book->coverPath);
        self::deletePdf($book->pdfPath);
    }

    public static function deleteCover(?string $name): void
    {
        self::delete(self::coverDir(), $name);
    }

    public static function deletePdf(?string $name): void
    {
        self::delete(self::pdfDir(), $name);
    }

    private static function delete(string $dir, ?string $name): void
    {
        // basename(): numele nu poate iesi din folder (ex: "../../config.php")
        if ($name && is_file($path = $dir . '/' . basename($name))) {
            unlink($path);
        }
    }
}
