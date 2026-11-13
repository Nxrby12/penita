<?php
/**
 * Variabile primite de la BookController::show()
 * @var \App\Models\Book $book
 * @var bool             $owned  utilizatorul are deja cartea
 * @var bool             $loggedIn
 */
?>
<section class="section">
    <div class="container">
        <nav class="breadcrumb" aria-label="Unde te afli">
            <a href="<?= url('/') ?>">Acasă</a> / <a href="<?= url('/carti') ?>">Cărți</a> / <span><?= e($book->title) ?></span>
        </nav>

        <article class="book-detail">
            <img src="<?= asset($book->coverUrl()) ?>" alt="Coperta cărții <?= e($book->title) ?>"
                 class="book-detail-cover" width="360" height="540">

            <div class="book-detail-body">
                <?php if ($book->genres): ?>
                    <p class="eyebrow"><?= e(implode(' · ', $book->genres)) ?></p>
                <?php endif; ?>
                <h1><?= e($book->title) ?></h1>
                <p class="book-detail-author">de <strong><?= e($book->authorName) ?></strong></p>

                <div class="book-detail-buy">
                    <span class="book-detail-price"><?= e($book->formattedPrice()) ?></span>
                    <?php if ($owned): ?>
                        <a href="<?= url('/descarca/' . $book->id) ?>" class="btn">Descarcă PDF</a>
                    <?php elseif ($loggedIn): ?>
                        <form action="<?= url('/cumpara/' . $book->id) ?>" method="post">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn"><?= $book->price > 0 ? 'Cumpără' : 'Adaugă gratuit' ?></button>
                        </form>
                    <?php else: ?>
                        <a href="<?= url('/autentificare') ?>" class="btn">Intră în cont ca să cumperi</a>
                    <?php endif; ?>
                </div>

                <h2 class="visually-hidden">Despre carte</h2>
                <div class="book-detail-description">
                    <?php foreach (preg_split('/\R{2,}/u', trim($book->description)) as $paragraph): ?>
                        <p><?= nl2br(e($paragraph)) ?></p>
                    <?php endforeach; ?>
                </div>

                <table class="facts">
                    <caption class="visually-hidden">Detalii despre carte</caption>
                    <tr><th scope="row">Autor</th><td><?= e($book->authorName) ?></td></tr>
                    <tr><th scope="row">Genuri</th><td><?= e(implode(', ', $book->genres) ?: '—') ?></td></tr>
                    <tr><th scope="row">Format</th><td>PDF, descărcabil după cumpărare</td></tr>
                    <tr><th scope="row">Plată</th><td>Card, prin Stripe (datele cardului nu ajung la noi)</td></tr>
                    <tr><th scope="row">Publicată</th><td><?= e(date('d.m.Y', strtotime($book->createdAt))) ?></td></tr>
                </table>
            </div>
        </article>
    </div>
</section>
