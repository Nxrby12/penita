<?php
declare(strict_types=1);

namespace App\Payments;

use App\Core\Config;
use App\Exceptions\PaymentException;

/** Alege procesatorul de plati dupa setarea 'payments.driver' din config. */
class PaymentGatewayFactory
{
    /** @throws PaymentException */
    public static function make(): PaymentGatewayInterface
    {
        return match (Config::get('payments.driver', 'stripe')) {
            'stripe'   => new StripeGateway(
                (string) Config::get('payments.stripe_secret', ''),
                (string) Config::get('payments.currency', 'ron'),
            ),
            'simulare' => new SimulatedGateway(),
            default    => throw new PaymentException('Setarea payments.driver trebuie să fie "stripe" sau "simulare".'),
        };
    }

    public static function isSimulated(): bool
    {
        return Config::get('payments.driver', 'stripe') === 'simulare';
    }
}
