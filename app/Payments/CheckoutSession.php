<?php
declare(strict_types=1);

namespace App\Payments;

/** Rezultatul pornirii unei plati: ID-ul sesiunii si adresa la care e trimis cumparatorul. */
final class CheckoutSession
{
    public function __construct(
        public readonly string $id,
        public readonly string $url,
    ) {}
}
