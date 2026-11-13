<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Validator;
use App\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

/** Validarea datelor din formulare (inregistrare, publicare carte). */
class ValidatorTest extends TestCase
{
    public function testDateleCorecteTrecSiSuntCuratateDeSpatii(): void
    {
        $data = Validator::validate(
            ['username' => '  ana_pop ', 'email' => 'ana@exemplu.ro'],
            ['username' => 'required|alpha_dash|min:3', 'email' => 'required|email']
        );

        $this->assertSame(['username' => 'ana_pop', 'email' => 'ana@exemplu.ro'], $data);
    }

    public function testCampurileGresiteAuCateUnMesajDeEroare(): void
    {
        try {
            Validator::validate(
                ['email' => 'nu-e-email', 'password' => '123', 'role' => 'admin'],
                ['email' => 'required|email', 'password' => 'required|min:8', 'role' => 'required|in:cititor,autor']
            );
            $this->fail('Trebuia aruncata ValidationException');
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertSame('Adresa de email nu este validă.', $errors['email']);
            $this->assertSame('Trebuie să aibă cel puțin 8 caractere.', $errors['password']);
            $this->assertSame('Valoarea aleasă nu este permisă.', $errors['role']);   // rolul de admin nu se poate alege
        }
    }

    public function testCampulObligatoriuGolEsteRespins(): void
    {
        $this->expectException(ValidationException::class);
        Validator::validate(['title' => '   '], ['title' => 'required']);
    }

    public function testConfirmareaParoleiTrebuieSaCoincida(): void
    {
        $this->expectException(ValidationException::class);
        Validator::validate(
            ['password' => 'parola123', 'password_confirmation' => 'parola124'],
            ['password' => 'required', 'password_confirmation' => 'required|same:password']
        );
    }

    public function testPretulAcceptaVirgula(): void
    {
        $data = Validator::validate(['price' => '24,99'], ['price' => 'required|numeric']);
        $this->assertSame('24,99', $data['price']);
    }

    public function testListeleTrimiseInLocDeTextSuntRespinse(): void
    {
        // ex: un atacator trimite email[]=... ca sa provoace o eroare
        $this->expectException(ValidationException::class);
        Validator::validate(['email' => ['a@b.ro']], ['email' => 'required|email']);
    }

    public function testCampulOptionalGolNuSeMaiVerifica(): void
    {
        $data = Validator::validate(['pen_name' => ''], ['pen_name' => 'max:100']);
        $this->assertSame('', $data['pen_name']);
    }

    public function testRegulaNecunoscutaEsteOEroareDeProgramare(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Validator::validate(['x' => 'a'], ['x' => 'regula_inexistenta']);
    }
}
