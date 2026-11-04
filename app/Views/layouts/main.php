<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? '') ?> | <?= e($appName) ?></title>
    <meta name="description" content="<?= e($description ?? 'Platformă de self-publishing pentru autori independenți.') ?>">
    <link rel="stylesheet" href="<?= asset('css/base.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/layout.css') ?>">
    <script src="<?= asset('js/app.js') ?>" defer></script>
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
                <a href="<?= url('/autentificare') ?>" class="btn btn-small">Intră în cont</a>
            </nav>
        </div>
    </header>

    <main>
        <?= $content ?>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= e($appName) ?>. Cărți scrise de autori independenți.</p>
        </div>
    </footer>
</body>
</html>
