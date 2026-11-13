<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Antetele HTTP de securitate, trimise la fiecare raspuns.
 * Aceleasi antete sunt setate si din .htaccess (Apache le aplica si fisierelor CSS, JS, imagini);
 * aici sunt pentru serverele fara mod_headers.
 */
class SecurityHeaders
{
    /** @return array<string, string> */
    public static function all(bool $https): array
    {
        $headers = [
            // De unde are voie pagina sa incarce resurse: doar de pe propriul site.
            // form-action permite si trimiterea catre pagina de plata Stripe.
            'Content-Security-Policy' => implode('; ', [
                "default-src 'self'",
                "script-src 'self'",
                "style-src 'self'",
                "img-src 'self' data:",
                "font-src 'self'",
                "connect-src 'self'",
                "form-action 'self' https://checkout.stripe.com",
                "frame-ancestors 'none'",
                "base-uri 'self'",
                "object-src 'none'",
            ]),
            'X-Frame-Options'           => 'DENY',                                  // nu poate fi pus intr-un iframe (clickjacking)
            'X-Content-Type-Options'    => 'nosniff',                               // browserul nu ghiceste tipul fisierelor
            'Referrer-Policy'           => 'strict-origin-when-cross-origin',
            'Permissions-Policy'        => 'camera=(), microphone=(), geolocation=(), payment=(self "https://checkout.stripe.com")',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];
        if ($https) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';   // doar HTTPS, un an
        }
        return $headers;
    }

    public static function send(): void
    {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        foreach (self::all($https) as $name => $value) {
            header("{$name}: {$value}");
        }
        header_remove('X-Powered-By');
    }
}
