<?php
/**
 * Pregateste mediul pentru teste: autoload-ul Composer si constanta ROOT.
 * Testele unitare nu au nevoie de baza de date sau de server web.
 */
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
require ROOT . '/vendor/autoload.php';

date_default_timezone_set('Europe/Bucharest');
