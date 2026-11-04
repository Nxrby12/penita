<?php
/**
 * Lista rutelor aplicatiei: [metoda HTTP, adresa, [Controller, actiune]]
 * {slug} sau {id} = parametru preluat din adresa.
 */
use App\Controllers\HomeController;

return [
    ['GET', '/', [HomeController::class, 'index']],
];
