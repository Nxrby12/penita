<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Config;
use App\Core\SecurityHeaders;
use App\Models\Book;
use App\Models\User;
use App\Support\Str;
use PHPUnit\Framework\TestCase;

/** Modelele (Book, User) si clasele ajutatoare (Str, Config, SecurityHeaders). */
class ModelsTest extends TestCase
{
    private function book(array $overrides = []): Book
    {
        return Book::fromRow($overrides + [
            'id' => 7, 'author_id' => 2, 'title' => 'Ce ascunde ceața', 'slug' => 'ce-ascunde-ceata',
            'description' => str_repeat('Ceața acoperă satul. ', 20), 'price' => '27.5',
            'cover_path' => 'abc.jpg', 'pdf_path' => 'abc.pdf', 'status' => 'approved',
            'is_blind_date' => 0, 'created_at' => '2026-11-01 10:00:00',
            'author_name' => 'Mihai Radu', 'genre_list' => 'Mister, Thriller',
        ]);
    }

    public function testCarteaSeConstruiesteDinRandulDinBazaDeDate(): void
    {
        $book = $this->book();
        $this->assertSame(27.5, $book->price);
        $this->assertSame('27,50 lei', $book->formattedPrice());
        $this->assertSame(['Mister', 'Thriller'], $book->genres);
        $this->assertSame('uploads/covers/abc.jpg', $book->coverUrl());
        $this->assertTrue($book->isApproved());
        $this->assertFalse($book->isBlindDate);
    }

    public function testRezumatulDescrieriiEsteScurtatCuPuncteDeSuspensie(): void
    {
        $excerpt = $this->book()->excerpt(50);
        $this->assertLessThanOrEqual(50, mb_strlen($excerpt));
        $this->assertStringEndsWith('…', $excerpt);
    }

    public function testUtilizatorulVerificaParolaCriptata(): void
    {
        $user = User::fromRow([
            'id' => 1, 'username' => 'ana', 'email' => 'ana@t.ro', 'role' => 'autor',
            'password_hash' => password_hash('parola123', PASSWORD_DEFAULT), 'created_at' => '2026-11-01 10:00:00',
        ]);

        $this->assertTrue($user->verifyPassword('parola123'));
        $this->assertFalse($user->verifyPassword('Parola123'));
        $this->assertTrue($user->isAuthor());
        $this->assertFalse($user->isAdmin());
        $this->assertSame('Autor', $user->roleLabel());
    }

    public function testSlugulTransformaDiacriticeleSiSemnele(): void
    {
        $this->assertSame('ultima-statie-sulina', Str::slug('Ultima stație: Sulina!'));
        $this->assertSame('radacini-de-oras', Str::slug('Rădăcini de oraș'));
        $this->assertSame('carte', Str::slug('???'));
    }

    public function testConfigCitesteValoriCuPunct(): void
    {
        Config::load(['payments' => ['driver' => 'stripe']]);
        $this->assertSame('stripe', Config::get('payments.driver'));
        $this->assertSame('ron', Config::get('payments.currency', 'ron'));
        $this->assertNull(Config::get('nu.exista'));
    }

    public function testAnteteleDeSecuritate(): void
    {
        $http = SecurityHeaders::all(false);
        $https = SecurityHeaders::all(true);

        $this->assertStringContainsString("default-src 'self'", $http['Content-Security-Policy']);
        $this->assertSame('DENY', $http['X-Frame-Options']);
        $this->assertSame('nosniff', $http['X-Content-Type-Options']);
        $this->assertArrayNotHasKey('Strict-Transport-Security', $http);   // HSTS doar pe HTTPS
        $this->assertArrayHasKey('Strict-Transport-Security', $https);
    }
}
