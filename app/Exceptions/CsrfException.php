<?php
declare(strict_types=1);

namespace App\Exceptions;

/** Aruncata cand un formular este trimis fara token CSRF valid (raspuns 419). */
class CsrfException extends \Exception
{
}
