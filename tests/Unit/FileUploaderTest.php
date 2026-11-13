<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\UploadException;
use App\Services\FileUploader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Verificarile de securitate la incarcarea fisierelor (12 MB, extensii, PDF-uri suspecte). */
class FileUploaderTest extends TestCase
{
    private const ALLOWED = ['jpg', 'jpeg', 'png'];

    public function testFisierulDeExact12MbEsteAcceptat(): void
    {
        FileUploader::assertSize(12 * 1024 * 1024);
        $this->addToAssertionCount(1);   // nu a aruncat exceptie
    }

    public function testFisierulMaiMareDe12MbEsteRespins(): void
    {
        $this->expectException(UploadException::class);
        $this->expectExceptionMessage('12 MB');
        FileUploader::assertSize(12 * 1024 * 1024 + 1);
    }

    public function testFisierulGolEsteRespins(): void
    {
        $this->expectException(UploadException::class);
        FileUploader::assertSize(0);
    }

    public function testExtensiaEsteVerificataFaraDiferentaDeLitere(): void
    {
        $this->assertSame('jpg', FileUploader::assertExtension('Coperta.JPG', self::ALLOWED));
        $this->assertSame('png', FileUploader::assertExtension('coperta.png', self::ALLOWED));
    }

    /** @dataProvider extensiiInterzise */    #[DataProvider('extensiiInterzise')]
    public function testExtensiileNeaprobateSuntRespinse(string $filename): void
    {
        $this->expectException(UploadException::class);
        FileUploader::assertExtension($filename, self::ALLOWED);
    }

    public static function extensiiInterzise(): array
    {
        return [
            'program'           => ['virus.exe'],
            'cod PHP'           => ['shell.php'],
            'extensie ascunsa'  => ['coperta.jpg.php'],
            'fara extensie'     => ['coperta'],
            'svg cu JavaScript' => ['coperta.svg'],
        ];
    }

    public function testUnPdfCuratEsteAcceptat(): void
    {
        FileUploader::assertSafePdf($this->pdf());
        $this->addToAssertionCount(1);
    }

    public function testUnFisierCareNuEPdfEsteRespins(): void
    {
        $this->expectException(UploadException::class);
        FileUploader::assertSafePdf('nu sunt un PDF');
    }

    public function testPdfulCuJavaScriptEsteRespins(): void
    {
        $this->expectException(UploadException::class);
        $this->expectExceptionMessage('elemente nepermise');
        FileUploader::assertSafePdf($this->pdf('/OpenAction << /S /JavaScript /JS (app.alert(1)) >>'));
    }

    public function testPdfulCuFisiereAtasateEsteRespins(): void
    {
        $this->expectException(UploadException::class);
        FileUploader::assertSafePdf($this->pdf('/Names << /EmbeddedFiles 9 0 R >>'));
    }

    public function testPdfulCuDateAscunseDupaSfarsitEsteRespins(): void
    {
        // steganografie: o arhiva lipita dupa %%EOF
        $this->expectException(UploadException::class);
        $this->expectExceptionMessage('date ascunse');
        FileUploader::assertSafePdf($this->pdf() . "PK\x03\x04arhiva ascunsa");
    }

    /** Un PDF minimal de o pagina, optional cu ceva in plus in catalog. */
    private function pdf(string $extra = ''): string
    {
        return "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R {$extra} >>\nendobj\n"
             . "2 0 obj\n<< /Type /Pages /Kids [] /Count 0 >>\nendobj\n"
             . "trailer\n<< /Root 1 0 R >>\n%%EOF\n";
    }
}
