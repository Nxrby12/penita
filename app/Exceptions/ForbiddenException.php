<?php
declare(strict_types=1);

namespace App\Exceptions;

/** Aruncata cand utilizatorul nu are dreptul sa acceseze o pagina (raspuns 403). */
class ForbiddenException extends \Exception
{
}
