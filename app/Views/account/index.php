<section class="section">
    <div class="container">
        <p class="eyebrow"><?= e($user->roleLabel()) ?></p>
        <h1>Salut, <?= e($user->username) ?>!</h1>
        <p class="lead">Cont creat pe <?= e(date('d.m.Y', strtotime($user->createdAt))) ?> · <?= e($user->email) ?></p>

        <div class="panel-grid">
            <article class="panel">
                <h2>Cărțile mele</h2>
                <p>Aici vor apărea cărțile pe care le cumperi, gata de descărcat.</p>
                <a href="<?= url('/carti') ?>">Caută cărți →</a>
            </article>

            <article class="panel">
                <h2>Blind Date</h2>
                <p>Alege o carte doar după indicii și află titlul după ce o cumperi.</p>
                <a href="<?= url('/blind-date') ?>">Încearcă un Blind Date →</a>
            </article>

            <?php if ($user->isAuthor()): ?>
                <article class="panel panel-accent">
                    <h2>Publică o carte</h2>
                    <p>Încarcă manuscrisul (PDF) și coperta. Un administrator o verifică înainte de publicare.</p>
                    <a href="<?= url('/autor/carti') ?>">Cărțile mele publicate →</a>
                </article>
            <?php endif; ?>

            <?php if ($user->isAdmin()): ?>
                <article class="panel panel-accent">
                    <h2>Administrare</h2>
                    <p>Aprobă cărțile trimise, gestionează Blind Date-urile și utilizatorii.</p>
                    <a href="<?= url('/admin') ?>">Deschide panoul →</a>
                </article>
            <?php endif; ?>
        </div>
    </div>
</section>
