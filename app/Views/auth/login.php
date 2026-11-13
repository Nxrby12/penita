<?php
/**
 * Variabile primite de la AuthController::showLogin()
 * @var bool $needsCaptcha  au fost prea multe incercari gresite
 */
?>
<section class="section">
    <div class="container auth-wrap">
        <div class="auth-card">
            <h1>Intră în cont</h1>
            <p class="auth-sub">Nu ai cont? <a href="<?= url('/inregistrare') ?>">Creează unul gratuit</a>.</p>

            <form action="<?= url('/autentificare') ?>" method="post" class="form" novalidate>
                <?= csrf_field() ?>

                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= e(old('email')) ?>"
                           autocomplete="email" required <?= error('email') ? 'aria-invalid="true"' : '' ?>>
                    <?php if ($msg = error('email')): ?><p class="field-error"><?= e($msg) ?></p><?php endif; ?>
                </div>

                <div class="field">
                    <label for="password">Parolă</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                    <?php if ($msg = error('password')): ?><p class="field-error"><?= e($msg) ?></p><?php endif; ?>
                </div>

                <?php if ($needsCaptcha): ?>
                    <p class="notice notice-error">Au fost mai multe încercări greșite. Confirmă că nu ești robot.</p>
                    <?= \App\Core\View::render('partials/captcha', [], null) ?>
                <?php endif; ?>

                <button type="submit" class="btn btn-block">Intră în cont</button>
            </form>
        </div>
    </div>
</section>
