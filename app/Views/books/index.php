<?php
use App\Core\View;

$pageUrl = fn(int $p) => url('/carti') . '?' . http_build_query(array_filter(['gen' => $genreId, 'pagina' => $p > 1 ? $p : null]));
?>
<section class="section">
    <div class="container">
        <p class="eyebrow">Catalog</p>
        <h1><?= e($title) ?></h1>
        <p class="lead"><?= $total === 1 ? '1 carte publicată' : $total . ' cărți publicate' ?> de autori independenți.</p>

        <nav class="filter-row" aria-label="Filtrează după gen">
            <a href="<?= url('/carti') ?>" class="chip-link <?= $genreId === null ? 'is-active' : '' ?>">Toate</a>
            <?php foreach ($genres as $genre): ?>
                <a href="<?= url('/carti') ?>?gen=<?= $genre->id ?>"
                   class="chip-link <?= $genreId === $genre->id ? 'is-active' : '' ?>"><?= e($genre->name) ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if (empty($books)): ?>
            <p class="notice">Nu am găsit cărți aici încă. <a href="<?= url('/carti') ?>">Vezi toate cărțile</a>.</p>
        <?php else: ?>
            <div class="book-grid">
                <?php foreach ($books as $book): ?>
                    <?= View::render('partials/book-card', ['book' => $book], null) ?>
                <?php endforeach; ?>
            </div>

            <?php if ($pages > 1): ?>
                <nav class="pagination" aria-label="Pagini">
                    <?php for ($p = 1; $p <= $pages; $p++): ?>
                        <a href="<?= e($pageUrl($p)) ?>" <?= $p === $page ? 'aria-current="page" class="is-active"' : '' ?>><?= $p ?></a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
