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

<section class="section">
    <div class="container">
        <h2>Cele mai noi cărți</h2>

        <?php if ($dbError): ?>
            <p class="notice notice-error">Nu ne putem conecta momentan la baza de date. Încearcă din nou puțin mai târziu.</p>
        <?php elseif (empty($books)): ?>
            <p class="notice">Încă nu a fost publicată nicio carte. Primii autori sunt pe drum.</p>
        <?php else: ?>
            <div class="book-grid">
                <?php foreach ($books as $book): ?>
                    <article class="book-card">
                        <img src="<?= asset($book->coverPath) ?>" alt="Coperta cărții <?= e($book->title) ?>" loading="lazy">
                        <h3><a href="<?= url('/carte/' . $book->slug) ?>"><?= e($book->title) ?></a></h3>
                        <p class="book-author"><?= e($book->authorName) ?></p>
                        <p class="book-price"><?= e($book->formattedPrice()) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
