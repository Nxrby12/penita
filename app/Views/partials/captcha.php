<?php
/**
 * Variabile primite de la formularele de autentificare
 * (nu primeste date; foloseste doar functiile din helpers.php)
 */
?>
<div class="field captcha-field">
    <label for="captcha">Scrie codul din imagine</label>
    <div class="captcha-row">
        <img src="<?= url('/captcha') ?>?t=<?= time() ?>" alt="Cod de verificare CAPTCHA" width="200" height="64" class="captcha-img">
        <button type="button" class="captcha-refresh" data-captcha-refresh aria-label="Alt cod">↻</button>
    </div>
    <input type="text" id="captcha" name="captcha" autocomplete="off" autocapitalize="characters"
           spellcheck="false" maxlength="5" required <?= error('captcha') ? 'aria-invalid="true"' : '' ?>>
    <p class="field-hint">Protecție împotriva roboților. Nu contează literele mari sau mici.</p>
    <?php if ($msg = error('captcha')): ?><p class="field-error"><?= e($msg) ?></p><?php endif; ?>
</div>
