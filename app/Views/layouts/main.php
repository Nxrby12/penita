<?php
use App\Core\Session;

$currentUser = auth_user();
$success = Session::pull('success');
$flashError = Session::pull('error');
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? '') ?> | <?= e($appName) ?></title>
    <meta name="description" content="<?= e($description ?? 'Platformă de self-publishing pentru autori independenți.') ?>">
    <link rel="stylesheet" href="<?= asset('css/base.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/layout.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/forms.css') ?>">
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

                <?php if ($currentUser): ?>
                    <?php if ($currentUser->isAdmin()): ?>
                        <a href="<?= url('/admin') ?>">Administrare</a>
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
