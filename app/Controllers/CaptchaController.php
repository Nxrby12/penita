<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Captcha;
use App\Core\Controller;

/** Imaginea CAPTCHA. La fiecare cerere se genereaza un cod nou. */
class CaptchaController extends Controller
{
    public function image(): void
    {
        $png = Captcha::render(Captcha::generate());

        header('Content-Type: image/png');
        header('Content-Length: ' . strlen($png));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo $png;
    }
}
