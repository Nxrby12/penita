<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Router;
use App\Exceptions\NotFoundException;
use PHPUnit\Framework\TestCase;

/** Un controller fals, ca sa vedem ce apeleaza Router-ul. */
class FakeController
{
    public static array $calls = [];

    public function index(): void
    {
        self::$calls[] = 'index';
    }

    public function show(string $slug): void
    {
        self::$calls[] = "show:{$slug}";
    }
}

/** Potrivirea adreselor cu rutele (partea "C" din MVC). */
class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        FakeController::$calls = [];
        $this->router = new Router('/biblioteca');
        $this->router->add('GET', '/', [FakeController::class, 'index']);
        $this->router->add('GET', '/carte/{slug}', [FakeController::class, 'show']);
    }

    public function testPrefixulSiParametriiDinAdresaSuntIgnorati(): void
    {
        $this->assertSame('/carti', $this->router->normalize('/biblioteca/carti?gen=2'));
        $this->assertSame('/', $this->router->normalize('/biblioteca/'));
    }

    public function testRutaCuParametruPrimesteValoareaDinAdresa(): void
    {
        $this->router->dispatch('GET', '/biblioteca/carte/ce-ascunde-ceata');
        $this->assertSame(['show:ce-ascunde-ceata'], FakeController::$calls);
    }

    public function testAdresaInexistentaAruncaNotFound(): void
    {
        $this->expectException(NotFoundException::class);
        $this->router->dispatch('GET', '/biblioteca/nu-exista');
    }

    public function testMetodaHttpGresitaNuSePotriveste(): void
    {
        $this->expectException(NotFoundException::class);
        $this->router->dispatch('POST', '/biblioteca/');   // ruta / exista doar pentru GET
    }
}
