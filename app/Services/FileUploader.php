<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\UploadException;

/**
 * Incarcarea sigura a fisierelor (coperti si carti PDF).
 *
 * Verificari, in ordine:
 *  1. dimensiune maxima 12 MB
 *  2. extensie din lista aprobata (.jpg, .jpeg, .png, .pdf)
 *  3. tipul REAL al continutului (finfo), nu doar extensia
 *  4. anti-steganografie:
 *       - imaginile sunt re-desenate pixel cu pixel si salvate din nou (GD):
 *         datele ascunse, metadatele si orice cod lipit dupa imagine dispar
 *       - PDF-urile sunt respinse daca contin JavaScript, fisiere atasate
 *         sau date ascunse dupa sfarsitul documentului
 *  5. fisierul primeste un nume aleatoriu (numele original nu se foloseste)
 */
class FileUploader
{
    public const MAX_BYTES = 12 * 1024 * 1024;   // 12 MB

    private const IMAGE_TYPES = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
    ];

    private const PDF_TYPES = [
        'pdf' => 'application/pdf',
    ];

    /** Elemente PDF care pot ascunde sau rula cod (expresii regulate). */
    private const PDF_FORBIDDEN = [
        '#/JavaScript#',            // cod JavaScript
        '#/JS(?![A-Za-z])#',        // forma scurta pentru JavaScript (dar nu /JSON etc.)
        '#/Launch#',                // porneste un program
        '#/EmbeddedFiles?#',        // fisiere atasate: /EmbeddedFile si /EmbeddedFiles
        '#/RichMedia#',             // Flash / video incorporat
    ];

    private const MAX_COVER_WIDTH = 900;

    /**
     * Valideaza si salveaza o coperta. Intoarce numele fisierului salvat.
     * @throws UploadException
     */
    public function storeImage(array $file, string $targetDir): string
    {
        [$ext, $mime] = $this->check($file, self::IMAGE_TYPES, 'Coperta');
        $this->ensureDir($targetDir);

        $image = @imagecreatefromstring((string) file_get_contents($file['tmp_name']));
        if ($image === false) {
            throw new UploadException('Coperta nu este o imagine validă.');
        }

        $clean = $this->redraw($image);
        $name = $this->randomName($mime === 'image/png' ? 'png' : 'jpg');
        $path = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $name;

        $saved = $mime === 'image/png'
            ? imagepng($clean, $path, 6)
            : imagejpeg($clean, $path, 85);

        imagedestroy($image);
        imagedestroy($clean);

        if (!$saved) {
            throw new UploadException('Coperta nu a putut fi salvată.');
        }
        return $name;
    }

    /**
     * Valideaza si salveaza un PDF. Intoarce numele fisierului salvat.
     * @throws UploadException
     */
    public function storePdf(array $file, string $targetDir): string
    {
        $this->check($file, self::PDF_TYPES, 'Cartea');

        $content = (string) file_get_contents($file['tmp_name']);
        $this->assertSafePdf($content);

        $this->ensureDir($targetDir);
        $name = $this->randomName('pdf');
        if (!move_uploaded_file($file['tmp_name'], rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $name)) {
            throw new UploadException('Cartea nu a putut fi salvată.');
        }
        return $name;
    }

    /**
     * Verificarile comune: eroare de upload, dimensiune, extensie, tip real.
     * @return array{0:string,1:string} [extensie, tip MIME]
     * @throws UploadException
     */
    public function check(array $file, array $allowed, string $label): array
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            throw new UploadException("{$label}: alege un fișier.");
        }
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new UploadException("{$label}: fișierul depășește limita de 12 MB.");
        }
        if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
            throw new UploadException("{$label}: încărcarea a eșuat. Încearcă din nou.");
        }

        self::assertSize((int) $file['size'], $label);
        $ext = self::assertExtension((string) $file['name'], array_keys($allowed), $label);

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if ($mime !== $allowed[$ext]) {
            throw new UploadException("{$label}: conținutul fișierului nu corespunde extensiei .{$ext}.");
        }

        return [$ext, $mime];
    }

    /** @throws UploadException */
    public static function assertSize(int $bytes, string $label = 'Fișierul'): void
    {
        if ($bytes <= 0) {
            throw new UploadException("{$label}: fișierul este gol.");
        }
        if ($bytes > self::MAX_BYTES) {
            throw new UploadException("{$label}: fișierul depășește limita de 12 MB.");
        }
    }

    /**
     * @param string[] $allowed
     * @throws UploadException
     */
    public static function assertExtension(string $filename, array $allowed, string $label = 'Fișierul'): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            $list = implode(', ', array_map(fn($e) => ".{$e}", $allowed));
            throw new UploadException("{$label}: sunt acceptate doar fișiere {$list}.");
        }
        return $ext;
    }

    /** @throws UploadException */
    public static function assertSafePdf(string $content): void
    {
        if (!str_starts_with($content, '%PDF-')) {
            throw new UploadException('Cartea nu este un PDF valid.');
        }
        foreach (self::PDF_FORBIDDEN as $pattern) {
            if (preg_match($pattern, $content)) {
                throw new UploadException('Cartea conține elemente nepermise (cod sau fișiere atașate).');
            }
        }
        $eof = strrpos($content, '%%EOF');
        if ($eof === false || trim(substr($content, $eof + 5)) !== '') {
            throw new UploadException('Cartea conține date ascunse după sfârșitul documentului.');
        }
    }

    /** Copiaza doar pixelii intr-o imagine noua (si o micsoreaza daca e prea lata). */
    private function redraw(\GdImage $src): \GdImage
    {
        $w = imagesx($src);
        $h = imagesy($src);
        $newW = min($w, self::MAX_COVER_WIDTH);
        $newH = (int) round($h * $newW / $w);

        $dst = imagecreatetruecolor($newW, $newH);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
        return $dst;
    }

    private function randomName(string $ext): string
    {
        return bin2hex(random_bytes(16)) . '.' . $ext;
    }

    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new UploadException('Folderul de încărcare nu poate fi creat.');
        }
    }
}
