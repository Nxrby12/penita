<?php
declare(strict_types=1);

namespace App\Payments;

use App\Exceptions\PaymentException;

/**
 * Contractul oricarui procesator de plati.
 * Aplicatia lucreaza doar cu aceasta interfata, deci Stripe poate fi
 * inlocuit (de ex. cu plata simulata, la dezvoltare) fara alte modificari.
 */
interface PaymentGatewayInterface
{
    /**
     * Porneste plata si intoarce adresa paginii de plata.
     * @throws PaymentException
     */
    public function createCheckout(int $orderId, string $productName, int $amountInBani,
                                   string $successUrl, string $cancelUrl): CheckoutSession;

    /**
     * Verifica, direct la procesator, daca sesiunea a fost platita pentru comanda data.
     * @throws PaymentException
     */
    public function isPaid(string $sessionId, int $orderId): bool;
}
