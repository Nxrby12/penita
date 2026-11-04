<?php
/**
 * Copiaza acest fisier ca config.php si completeaza datele.
 * config.php NU se urca pe GitHub (e in .gitignore), pentru ca
 * contine parole si chei secrete.
 */
return [
    'app_name' => 'Penița',

    // Pe XAMPP site-ul e la http://localhost/biblioteca  -> '/biblioteca'
    // Pe hosting, daca site-ul e direct pe domeniu         -> ''
    'base_url' => '/biblioteca',

    'db' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'biblioteca',
        'user'     => 'root',
        'password' => '',          // pe XAMPP parola implicita e goala
    ],
];
