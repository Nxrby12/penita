<?php
declare(strict_types=1);

namespace App\Exceptions;

/** Aruncata cand datele dintr-un formular nu trec validarea. Pastreaza erorile pe campuri. */
class ValidationException extends \Exception
{
    /** @param array<string, string> $errors camp => mesaj */
    public function __construct(private array $errors)
    {
        parent::__construct('Date invalide.');
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }
}
