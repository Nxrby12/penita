<?php
/**
 * Variabile primite de la AccountController::index()
 * @var \App\Models\User                                                        $user
 * @var array<int, array{book: \App\Models\Book, paidAt: string, price: float}> $library
 */
?>
<section class="section">
    <div class="container">
        <p class="eyebrow"><?= e($user->roleLabel()) ?></p>
        <h1>Salut, <?= e($user->username) ?>!</h1>
        <p class="lead">Cont creat pe <?= e(date('d.m.Y', strtotime($user->createdAt))) ?> · <?= e($user->email) ?></p>

        <h2 id="biblioteca" class="section-title">Biblioteca mea</h2>
        <?php if (empty($library)): ?>
            <p class="notice">
                Nu ai cumpărat încă nicio carte. <a href="<?= url('/carti') ?>">Caută în catalog</a>
                sau <a href="<?= url('/blind-date') ?>">încearcă un Blind Date</a>.
            </p>
        <?php else: ?>
            <div class="library-grid">
                <?php foreach ($library as $entry): $book = $entry['book']; ?>
                    <article class="library-item <?= $book->isBlindDate ? 'is-revealed' : '' ?>">
                        <img src="<?= asset($book->coverUrl()) ?>" alt="Coperta cărții <?= e($book->title) ?>" width="120" height="180">
                        <div>
                            <?php if ($book->isBlindDate): ?>
                                <p class="eyebrow">Blind Date dezvăluit</p>
                            <?php endif; ?>
                            <h3><?= e($book->title) ?></h3>
                            <p class="book-author">de <?= e($book->authorName) ?></p>
                            <p class="library-meta">Cumpărată pe <?= e(date('d.m.Y', strtotime($entry['paidAt']))) ?>
                                · <?= e(number_format($entry['price'], 2, ',', '.')) ?> lei</p>
                            <a href="<?= url('/descarca/' . $book->id) ?>" class="btn btn-small">Descarcă PDF</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="panel-grid">
            <?php if ($user->isAuthor()): ?>
                <article class="panel panel-accent">
                    <h2>Publică o carte</h2>
                    <p>Încarcă manuscrisul (PDF) și coperta. Un administrator o verifică înainte de publicare.</p>
                    <a href="<?= url('/autor/carti/noua') ?>">Publică o carte →</a> ·
                    <a href="<?= url('/autor/carti') ?>">Cărțile mele →</a>
                </article>
            <?php endif; ?>

            <?php if ($user->isAdmin()): ?>
                <article class="panel panel-accent">
                    <h2>Administrare</h2>
                    <p>Aprobă cărțile trimise, gestionează Blind Date-urile și utilizatorii.</p>
                    <a href="<?= url('/admin/carti') ?>">Deschide panoul →</a>
                </article>
            <?php endif; ?>

            <article class="panel">
                <h2>Blind Date</h2>
                <p>Alege o carte doar după indicii și află titlul după ce o cumperi.</p>
                <a href="<?= url('/blind-date') ?>">Încearcă un Blind Date →</a>
            </article>
        </div>
    </div>
</section>
