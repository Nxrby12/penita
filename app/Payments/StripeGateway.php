<?php
declare(strict_types=1);

namespace App\Payments;

use App\Exceptions\PaymentException;

/**
 * Plata reala prin Stripe Checkout (biblioteca oficiala stripe/stripe-php).
 * Cumparatorul introduce cardul pe pagina Stripe, nu pe site-ul nostru:
 * datele cardului nu trec niciodata prin serverul aplicatiei.
 *
 * In modul test, cardul 4242 4242 4242 4242 (orice data viitoare, orice CVC) trece.
 */
class StripeGateway implements PaymentGatewayInterface
{
    private \Stripe\StripeClient $client;

    public function __construct(string $secretKey, private string $currency = 'ron')
    {
        if (!class_exists(\Stripe\StripeClient::class)) {
            throw new PaymentException('Biblioteca Stripe nu este instalată. Rulează: composer require stripe/stripe-php');
        }
        if (!str_starts_with($secretKey, 'sk_')) {
            throw new PaymentException('Cheia secretă Stripe lipsește din config/config.php.');
        }
        $this->client = new \Stripe\StripeClient($secretKey);
    }

    public function createCheckout(int $orderId, string $productName, int $amountInBani,
                                   string $successUrl, string $cancelUrl): CheckoutSession
    {
        try {
            $session = $this->client->checkout->sessions->create([
                'mode'                => 'payment',
                'client_reference_id' => (string) $orderId,
                'metadata'            => ['order_id' => (string) $orderId],
                'line_items'          => [[
                    'quantity'   => 1,
                    'price_data' => [
                        'currency'     => $this->currency,
                        'unit_amount'  => $amountInBani,
                        'product_data' => ['name' => $productName],
                    ],
                ]],
                // Stripe inlocuieste {CHECKOUT_SESSION_ID} cu ID-ul real al sesiunii
                'success_url' => $successUrl . '&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => $cancelUrl,
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new PaymentException('Stripe: ' . $e->getMessage(), 0, $e);
        }

        return new CheckoutSession($session->id, $session->url);
    }

    public function isPaid(string $sessionId, int $orderId): bool
    {
        try {
            $session = $this->client->checkout->sessions->retrieve($sessionId);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new PaymentException('Stripe: ' . $e->getMessage(), 0, $e);
        }

        return $session->payment_status === 'paid'
            && (string) $session->client_reference_id === (string) $orderId;
    }
}
