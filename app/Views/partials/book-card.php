<?php
/**
 * Variabile primite de la view-urile cu liste de carti
 * @var \App\Models\Book $book
 */
?>
<article class="book-card">
    <a href="<?= url('/carte/' . $book->slug) ?>" class="book-cover-link" tabindex="-1" aria-hidden="true">
        <img src="<?= asset($book->coverUrl()) ?>" alt="" loading="lazy" width="300" height="450">
    </a>
    <h3><a href="<?= url('/carte/' . $book->slug) ?>"><?= e($book->title) ?></a></h3>
    <p class="book-author"><?= e($book->authorName) ?></p>
    <p class="book-price"><?= e($book->formattedPrice()) ?></p>
</article>
