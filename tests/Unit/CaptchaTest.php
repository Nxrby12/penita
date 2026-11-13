<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Captcha;
use PHPUnit\Framework\TestCase;

/** CAPTCHA: cod corect, cod gresit, folosire o singura data, expirare. */
class CaptchaTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];   // sesiune goala pentru fiecare test
    }

    public function testCodulGeneratAre5CaractereFaraCeleUsorDeConfundat(): void
    {
        $code = Captcha::generate();
        $this->assertMatchesRegularExpression('/^[A-HJ-NP-Z2-9]{5}$/', $code);
    }

    public function testCodulCorectEsteAcceptatSiCuLitereMici(): void
    {
        $code = Captcha::generate();
        $this->assertTrue(Captcha::verify(' ' . strtolower($code) . ' '));
    }

    public function testCodulGresitEsteRespins(): void
    {
        Captcha::generate();
        $this->assertFalse(Captcha::verify('11111'));   // cifra 1 nu apare niciodata in coduri
    }

    public function testCodulSePoateFolosiOSinguraData(): void
    {
        $code = Captcha::generate();
        $this->assertTrue(Captcha::verify($code));
        $this->assertFalse(Captcha::verify($code));    // a doua oara nu mai merge
    }

    public function testCodulExpiratEsteRespins(): void
    {
        $code = Captcha::generate();
        $_SESSION['_captcha']['expires'] = time() - 1;
        $this->assertFalse(Captcha::verify($code));
    }

    public function testInSesiuneNuSePastreazaCodulInClar(): void
    {
        $code = Captcha::generate();
        $this->assertStringNotContainsString($code, serialize($_SESSION));
    }

    public function testImagineaEsteUnPngValid(): void
    {
        $png = Captcha::render('AB2CD');
        $this->assertStringStartsWith("\x89PNG", $png);
        [$width, $height] = getimagesizefromstring($png);
        $this->assertSame([200, 64], [$width, $height]);
    }
}
