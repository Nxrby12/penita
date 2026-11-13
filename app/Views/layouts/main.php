<?php
/**
 * Layout-ul comun al tuturor paginilor.
 * @var string      $content         HTML-ul paginii, generat de view
 * @var string      $appName         numele aplicatiei (din config)
 * @var string|null $title           titlul paginii
 * @var string|null $description     descrierea pentru motoarele de cautare (meta description)
 * @var string|null $canonical       adresa canonica (fara parametri inutili)
 * @var string|null $ogImage         imaginea afisata cand pagina e distribuita pe retele sociale
 * @var string|null $ogType          tipul paginii pentru Open Graph (website, book)
 * @var array|null  $structuredData  date structurate schema.org (JSON-LD)
 * @var bool|null   $noindex         paginile private nu apar in Google
 */
use App\Core\Session;

$currentUser = auth_user();
$success = Session::pull('success');
$flashError = Session::pull('error');

$pageTitle   = ($title ?? '') !== '' ? "{$title} | {$appName}" : $appName;
$pageDesc    = $description ?? 'Platformă de self-publishing: cărți scrise de autori independenți, cumpărate direct de la ei, plus Blind Date with a Book.';
$pageUrl     = $canonical ?? absolute_url(current_path());
$pageImage   = $ogImage ?? absolute_url('images/og-default.png');
$isPrivate   = !empty($noindex);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDesc) ?>">
    <?php if ($isPrivate): ?>
        <meta name="robots" content="noindex, nofollow">
    <?php else: ?>
        <link rel="canonical" href="<?= e($pageUrl) ?>">
    <?php endif; ?>

    <!-- Cum arata pagina cand e distribuita (Facebook, WhatsApp, LinkedIn, X) -->
    <meta property="og:site_name" content="<?= e($appName) ?>">
    <meta property="og:locale" content="ro_RO">
    <meta property="og:type" content="<?= e($ogType ?? 'website') ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDesc) ?>">
    <meta property="og:url" content="<?= e($pageUrl) ?>">
    <meta property="og:image" content="<?= e($pageImage) ?>">
    <meta name="twitter:card" content="summary_large_image">

    <meta name="theme-color" content="#b8432f">
    <link rel="icon" href="<?= asset('favicon.ico') ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?= asset('images/apple-touch-icon.png') ?>">

    <link rel="stylesheet" href="<?= asset('css/base.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/layout.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/forms.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components.css') ?>">
    <script src="<?= asset('js/app.js') ?>" defer></script>

    <?php if (!empty($structuredData)): ?>
        <script type="application/ld+json"><?= json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
    <?php endif; ?>
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="<?= url('/') ?>" class="logo">
                <span class="logo-mark" aria-hidden="true">✒</span> <?= e($appName) ?>
            </a>

            <button class="nav-toggle" aria-label="Deschide meniul" aria-expanded="false" aria-controls="main-nav">
                <span></span><span></span><span></span>
            </button>

            <nav id="main-nav" class="main-nav" aria-label="Navigare principală">
                <a href="<?= url('/') ?>">Acasă</a>
                <a href="<?= url('/carti') ?>">Cărți</a>
                <a href="<?= url('/blind-date') ?>">Blind Date</a>

                <?php if ($currentUser): ?>
                    <?php if ($currentUser->isAdmin()): ?>
                        <a href="<?= url('/admin/carti') ?>">Administrare</a>
                    <?php endif; ?>
                    <a href="<?= url('/cont') ?>">Contul meu</a>
                    <form action="<?= url('/iesire') ?>" method="post" class="nav-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-small btn-ghost">Ieșire</button>
                    </form>
                <?php else: ?>
                    <a href="<?= url('/autentificare') ?>" class="btn btn-small">Intră în cont</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main>
        <?php if ($success || $flashError): ?>
            <div class="container flash-wrap">
                <?php if ($success): ?>
                    <p class="notice notice-success" role="status"><?= e($success) ?></p>
                <?php endif; ?>
                <?php if ($flashError): ?>
                    <p class="notice notice-error" role="alert"><?= e($flashError) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= e($appName) ?>. Cărți scrise de autori independenți.</p>
        </div>
    </footer>
</body>
</html>
