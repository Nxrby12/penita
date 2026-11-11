<?php
/**
 * Lista rutelor aplicatiei: [metoda HTTP, adresa, [Controller, actiune]]
 * {slug} sau {id} = parametru preluat din adresa.
 */
use App\Controllers\AccountController;
use App\Controllers\AdminBookController;
use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\AuthorBookController;
use App\Controllers\BookController;
use App\Controllers\HomeController;

return [
    ['GET',  '/',                          [HomeController::class, 'index']],

    // Catalog public
    ['GET',  '/carti',                     [BookController::class, 'index']],
    ['GET',  '/carte/{slug}',              [BookController::class, 'show']],

    // Autentificare
    ['GET',  '/autentificare',             [AuthController::class, 'showLogin']],
    ['POST', '/autentificare',             [AuthController::class, 'login']],
    ['GET',  '/inregistrare',              [AuthController::class, 'showRegister']],
    ['POST', '/inregistrare',              [AuthController::class, 'register']],
    ['POST', '/iesire',                    [AuthController::class, 'logout']],

    // Contul utilizatorului
    ['GET',  '/cont',                      [AccountController::class, 'index']],

    // Autor
    ['GET',  '/autor/carti',               [AuthorBookController::class, 'index']],
    ['GET',  '/autor/carti/noua',          [AuthorBookController::class, 'create']],
    ['POST', '/autor/carti',               [AuthorBookController::class, 'store']],
    ['POST', '/autor/carti/{id}/sterge',   [AuthorBookController::class, 'destroy']],

    // Administrare
    ['GET',  '/admin',                     [AdminController::class, 'index']],
    ['GET',  '/admin/carti',               [AdminBookController::class, 'index']],
    ['POST', '/admin/carti/{id}/aproba',   [AdminBookController::class, 'approve']],
    ['POST', '/admin/carti/{id}/respinge', [AdminBookController::class, 'reject']],
    ['POST', '/admin/carti/{id}/sterge',   [AdminBookController::class, 'destroy']],
];
