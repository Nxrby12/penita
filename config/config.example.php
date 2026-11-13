<?php
/**
 * Copiaza acest fisier ca config.php si completeaza datele.
 * config.php NU se urca pe GitHub (e in .gitignore), pentru ca
 * contine parole si chei secrete.
 */
return [
    'app_name' => 'Penița',
    'timezone' => 'Europe/Bucharest',

    // Pe XAMPP site-ul e la http://localhost/biblioteca  -> '/biblioteca'
    // Pe hosting, daca site-ul e direct pe domeniu         -> ''
    'base_url' => '/biblioteca',

    // Adresa completa a site-ului (folosita de Stripe ca sa te trimita inapoi dupa plata)
    'app_url' => 'http://localhost/biblioteca',

    'payments' => [
        // 'stripe'   = plata reala prin Stripe (pe site-ul publicat)
        // 'simulare' = plata simulata, doar pentru teste pe laptop
        'driver'        => 'stripe',
        'stripe_secret' => 'sk_test_...',   // din dashboard.stripe.com -> Developers -> API keys
        'currency'      => 'ron',
    ],

    // Limitarea cererilor: cate cereri pe minut are voie un IP
    'rate_limit' => ['max' => 100],

    'db' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'biblioteca',
        'user'     => 'root',
        'password' => '',          // pe XAMPP parola implicita e goala
    ],
];
