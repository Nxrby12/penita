<?php
declare(strict_types=1);

namespace App\Exceptions;

/** Aruncata cand plata nu poate fi pornita sau verificata (Stripe indisponibil, chei lipsa etc.). */
class PaymentException extends \Exception
{
}
