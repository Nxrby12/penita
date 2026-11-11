<?php
use App\Core\View;

$slides = array_slice($books, 0, 5);
?>
<section class="hero">
    <div class="container hero-inner">
        <div class="hero-text">
            <p class="eyebrow">Self-publishing pentru autori independenți</p>
            <h1>Povești care n-au trecut prin nicio editură.</h1>
            <p class="lead">
                Autorii își publică direct cărțile, iar tu le citești primul.
                Sau lasă-te surprins: cumpără o carte doar după câteva indicii.
            </p>
            <div class="hero-actions">
                <a href="<?= url('/carti') ?>" class="btn">Explorează cărțile</a>
                <a href="<?= url('/blind-date') ?>" class="btn btn-ghost">Încearcă un Blind Date</a>
            </div>
        </div>

        <a href="<?= url('/blind-date') ?>" class="blind-card" aria-label="Descoperă o carte surpriză">
            <span class="blind-ribbon" aria-hidden="true"></span>
            <span class="blind-label">Blind Date<br>with a Book</span>
            <span class="blind-clues">
                <span>#dragoni</span><span>#trădare</span><span>#final-neașteptat</span>
            </span>
        </a>
    </div>
</section>

<?php if (count($slides) > 0): ?>
<section class="section section-band" aria-labelledby="featured-title">
    <div class="container">
        <h2 id="featured-title">Proaspăt publicate</h2>

        <div class="carousel" data-carousel aria-roledescription="carusel" aria-label="Cărți proaspăt publicate">
            <div class="carousel-track">
                <?php foreach ($slides as $i => $book): ?>
                    <div class="carousel-slide <?= $i === 0 ? 'is-active' : '' ?>" role="group"
                         aria-roledescription="slide" aria-label="<?= $i + 1 ?> din <?= count($slides) ?>"
                         <?= $i === 0 ? '' : 'aria-hidden="true"' ?>>
                        <img src="<?= asset($book->coverUrl()) ?>" alt="Coperta cărții <?= e($book->title) ?>"
                             width="300" height="450" <?= $i === 0 ? '' : 'loading="lazy"' ?>>
                        <div class="carousel-text">
                            <p class="eyebrow"><?= e(implode(' · ', $book->genres)) ?></p>
                            <h3><?= e($book->title) ?></h3>
                            <p class="book-author">de <?= e($book->authorName) ?></p>
                            <p><?= e($book->excerpt(180)) ?></p>
                            <a href="<?= url('/carte/' . $book->slug) ?>" class="btn btn-small"
                               <?= $i === 0 ? '' : 'tabindex="-1"' ?>>Vezi cartea</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($slides) > 1): ?>
                <button type="button" class="carousel-btn carousel-prev" aria-label="Cartea anterioară">‹</button>
                <button type="button" class="carousel-btn carousel-next" aria-label="Cartea următoare">›</button>
                <div class="carousel-dots">
                    <?php foreach ($slides as $i => $book): ?>
                        <button type="button" class="<?= $i === 0 ? 'is-active' : '' ?>"
                                aria-label="Arată cartea <?= $i + 1 ?>" <?= $i === 0 ? 'aria-current="true"' : '' ?>></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="container">
        <div class="page-head">
            <h2>Cele mai noi cărți</h2>
            <?php if (!empty($books)): ?><a href="<?= url('/carti') ?>">Toate cărțile →</a><?php endif; ?>
        </div>

        <?php if ($dbError): ?>
            <p class="notice notice-error">Nu ne putem conecta momentan la baza de date. Încearcă din nou puțin mai târziu.</p>
        <?php elseif (empty($books)): ?>
            <p class="notice">Încă nu a fost publicată nicio carte. Primii autori sunt pe drum.</p>
        <?php else: ?>
            <div class="book-grid">
                <?php foreach ($books as $book): ?>
                    <?= View::render('partials/book-card', ['book' => $book], null) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
