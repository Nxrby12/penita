<section class="section">
    <div class="container">
        <p class="eyebrow">Blind Date with a Book</p>
        <h1>Judecă o carte după indicii, nu după copertă.</h1>
        <p class="lead">
            Fiecare pachet ascunde o carte de la un autor independent. Vezi doar genul și trei indicii.
            Titlul și coperta apar în biblioteca ta imediat după cumpărare.
        </p>

        <?php if (empty($items)): ?>
            <p class="notice">Momentan nu avem nicio carte împachetată. Revino curând!</p>
        <?php else: ?>
            <div class="blind-grid">
                <?php foreach ($items as $i => $item): $book = $item['book']; ?>
                    <article class="blind-package" style="--tilt: <?= $i % 2 ? '1.5deg' : '-1.5deg' ?>">
                        <div class="blind-paper" aria-hidden="true">
                            <span class="blind-ribbon"></span>
                            <span class="blind-number">#<?= $book->id ?></span>
                        </div>
                        <div class="blind-info">
                            <?php if ($book->genres): ?>
                                <p class="eyebrow"><?= e(implode(' · ', $book->genres)) ?></p>
                            <?php endif; ?>
                            <h2 class="visually-hidden">Carte surpriză #<?= $book->id ?></h2>
                            <ul class="clue-list" aria-label="Indicii">
                                <?php foreach ($item['clues'] as $clue): ?>
                                    <li><?= e($clue) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="blind-buy">
                                <span class="book-price"><?= e($book->formattedPrice()) ?></span>
                                <?php if (!empty($owned[$book->id])): ?>
                                    <a href="<?= url('/cont') ?>" class="btn btn-small btn-ghost">E în biblioteca ta</a>
                                <?php elseif ($loggedIn): ?>
                                    <form action="<?= url('/cumpara/' . $book->id) ?>" method="post">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-small">Despachetează</button>
                                    </form>
                                <?php else: ?>
                                    <a href="<?= url('/autentificare') ?>" class="btn btn-small">Intră în cont ca să cumperi</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
