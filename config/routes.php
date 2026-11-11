<?php
/**
 * Lista rutelor aplicatiei: [metoda HTTP, adresa, [Controller, actiune]]
 * {slug} sau {id} = parametru preluat din adresa.
 */
use App\Controllers\AccountController;
use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;

return [
    ['GET',  '/',              [HomeController::class, 'index']],

    // Autentificare
    ['GET',  '/autentificare', [AuthController::class, 'showLogin']],
    ['POST', '/autentificare', [AuthController::class, 'login']],
    ['GET',  '/inregistrare',  [AuthController::class, 'showRegister']],
    ['POST', '/inregistrare',  [AuthController::class, 'register']],
    ['POST', '/iesire',        [AuthController::class, 'logout']],

    // Zone protejate
    ['GET',  '/cont',          [AccountController::class, 'index']],
    ['GET',  '/admin',         [AdminController::class, 'index']],
];
