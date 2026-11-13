<section class="section">
    <div class="container auth-wrap">
        <div class="auth-card">
            <p class="eyebrow">Mod de test</p>
            <h1>Plată simulată</h1>
            <p class="auth-sub">
                Această pagină înlocuiește Stripe cât timp <code>payments.driver</code> este <code>simulare</code>.
                Nu se fac plăți reale.
            </p>

            <table class="facts">
                <tr><th scope="row">Produs</th><td><?= e($session['name']) ?></td></tr>
                <tr><th scope="row">Sumă</th><td><?= e(number_format($session['amount'] / 100, 2, ',', '.')) ?> lei</td></tr>
            </table>

            <form action="<?= url('/comanda/simulare') ?>?sesiune=<?= e($sessionId) ?>" method="post" class="form simulate-actions">
                <?= csrf_field() ?>
                <button type="submit" name="action" value="pay" class="btn btn-block">Plătește (simulare)</button>
                <button type="submit" name="action" value="cancel" class="btn btn-ghost btn-block">Renunță</button>
            </form>
        </div>
    </div>
</section>
