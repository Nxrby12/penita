<?php
/** Camp de formular cu eticheta si mesaj de eroare. */
$field = function (string $name, string $label, string $type = 'text', string $autocomplete = '', string $hint = ''): string {
    $msg = error($name);
    $value = $type === 'password' ? '' : e(old($name));
    $html  = '<div class="field">';
    $html .= '<label for="' . $name . '">' . e($label) . '</label>';
    $html .= '<input type="' . $type . '" id="' . $name . '" name="' . $name . '" value="' . $value . '"'
           . ($autocomplete ? ' autocomplete="' . $autocomplete . '"' : '')
           . ($msg ? ' aria-invalid="true"' : '') . '>';
    if ($hint) {
        $html .= '<p class="field-hint">' . e($hint) . '</p>';
    }
    if ($msg) {
        $html .= '<p class="field-error">' . e($msg) . '</p>';
    }
    return $html . '</div>';
};
$role = old('role', 'cititor');
?>
<section class="section">
    <div class="container auth-wrap">
        <div class="auth-card">
            <h1>Creează cont</h1>
            <p class="auth-sub">Ai deja cont? <a href="<?= url('/autentificare') ?>">Intră în cont</a>.</p>

            <form action="<?= url('/inregistrare') ?>" method="post" class="form" novalidate>
                <?= csrf_field() ?>

                <fieldset class="role-picker">
                    <legend>Vreau să</legend>
                    <label class="role-option">
                        <input type="radio" name="role" value="cititor" <?= $role === 'cititor' ? 'checked' : '' ?>>
                        <span><strong>Citesc</strong> cărți și încerc Blind Date-uri</span>
                    </label>
                    <label class="role-option">
                        <input type="radio" name="role" value="autor" <?= $role === 'autor' ? 'checked' : '' ?>>
                        <span><strong>Public</strong> cărțile mele ca autor</span>
                    </label>
                    <?php if ($msg = error('role')): ?><p class="field-error"><?= e($msg) ?></p><?php endif; ?>
                </fieldset>

                <?= $field('username', 'Nume de utilizator', 'text', 'username', 'Litere, cifre, - și _, minimum 3 caractere.') ?>
                <?= $field('email', 'Email', 'email', 'email') ?>
                <div class="author-only">
                    <?= $field('pen_name', 'Pseudonim de autor (opțional)', 'text', '', 'Numele afișat pe cărțile tale. Dacă îl lași gol, folosim numele de utilizator.') ?>
                </div>
                <?= $field('password', 'Parolă', 'password', 'new-password', 'Minimum 8 caractere.') ?>
                <?= $field('password_confirmation', 'Confirmă parola', 'password', 'new-password') ?>

                <?= \App\Core\View::render('partials/captcha', [], null) ?>

                <button type="submit" class="btn btn-block">Creează cont</button>
            </form>
        </div>
    </div>
</section>
