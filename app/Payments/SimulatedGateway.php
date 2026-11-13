<?php
declare(strict_types=1);

namespace App\Payments;

use App\Core\Session;

/**
 * Plata SIMULATA, doar pentru dezvoltare si teste, cand nu sunt configurate cheile Stripe.
 * Se activeaza din config: 'payments' => ['driver' => 'simulare'].
 * Pe site-ul publicat se foloseste 'stripe'.
 */
class SimulatedGateway implements PaymentGatewayInterface
{
    private const KEY = '_simulated_payments';

    public function createCheckout(int $orderId, string $productName, int $amountInBani,
                                   string $successUrl, string $cancelUrl): CheckoutSession
    {
        $id = 'sim_' . bin2hex(random_bytes(12));
        $sessions = Session::get(self::KEY, []);
        $sessions[$id] = ['order' => $orderId, 'name' => $productName, 'amount' => $amountInBani,
                          'success' => $successUrl . '&session_id=' . $id, 'cancel' => $cancelUrl, 'paid' => false];
        Session::set(self::KEY, $sessions);

        return new CheckoutSession($id, url('/comanda/simulare?sesiune=' . $id));
    }

    public function isPaid(string $sessionId, int $orderId): bool
    {
        $s = Session::get(self::KEY, [])[$sessionId] ?? null;
        return $s !== null && $s['paid'] === true && $s['order'] === $orderId;
    }

    /** Datele sesiunii simulate (pentru pagina de plata simulata). */
    public static function find(string $sessionId): ?array
    {
        return Session::get(self::KEY, [])[$sessionId] ?? null;
    }

    public static function markPaid(string $sessionId): void
    {
        $sessions = Session::get(self::KEY, []);
        if (isset($sessions[$sessionId])) {
            $sessions[$sessionId]['paid'] = true;
            Session::set(self::KEY, $sessions);
        }
    }
}
