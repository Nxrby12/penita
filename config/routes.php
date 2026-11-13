<?php
/**
 * Lista rutelor aplicatiei: [metoda HTTP, adresa, [Controller, actiune]]
 * {slug} sau {id} = parametru preluat din adresa.
 */
use App\Controllers\AccountController;
use App\Controllers\AdminBookController;
use App\Controllers\AdminController;
use App\Controllers\AdminLogController;
use App\Controllers\CaptchaController;
use App\Controllers\AuthController;
use App\Controllers\AuthorBookController;
use App\Controllers\BlindDateController;
use App\Controllers\BookController;
use App\Controllers\DownloadController;
use App\Controllers\OrderController;
use App\Controllers\SeoController;
use App\Controllers\HomeController;

return [
    ['GET',  '/',                          [HomeController::class, 'index']],

    // Pentru motoarele de cautare
    ['GET',  '/robots.txt',                [SeoController::class, 'robots']],
    ['GET',  '/sitemap.xml',               [SeoController::class, 'sitemap']],

    // Catalog public
    ['GET',  '/carti',                     [BookController::class, 'index']],
    ['GET',  '/carte/{slug}',              [BookController::class, 'show']],
    ['GET',  '/blind-date',                [BlindDateController::class, 'index']],

    // Cumparare si descarcare
    ['POST', '/cumpara/{id}',              [OrderController::class, 'buy']],
    ['GET',  '/comanda/succes',            [OrderController::class, 'success']],
    ['GET',  '/comanda/anulata',           [OrderController::class, 'cancel']],
    ['GET',  '/comanda/simulare',          [OrderController::class, 'simulate']],
    ['POST', '/comanda/simulare',          [OrderController::class, 'simulateConfirm']],
    ['GET',  '/descarca/{id}',             [DownloadController::class, 'download']],

    // Autentificare
    ['GET',  '/captcha',                   [CaptchaController::class, 'image']],
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
    ['GET',  '/admin/jurnal',              [AdminLogController::class, 'index']],
    ['GET',  '/admin/jurnal/descarca',     [AdminLogController::class, 'download']],
    ['POST', '/admin/carti/{id}/aproba',   [AdminBookController::class, 'approve']],
    ['POST', '/admin/carti/{id}/respinge', [AdminBookController::class, 'reject']],
    ['POST', '/admin/carti/{id}/sterge',   [AdminBookController::class, 'destroy']],
    ['GET',  '/admin/carti/{id}/blind-date', [AdminBookController::class, 'editBlindDate']],
    ['POST', '/admin/carti/{id}/blind-date', [AdminBookController::class, 'updateBlindDate']],
];
