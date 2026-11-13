<?php
/**
 * Variabile primite de la AuthorBookController::index()
 * @var \App\Models\Book[] $books  cartile autorului, cu orice status
 */
?>
<section class="section">
    <div class="container">
        <div class="page-head">
            <div>
                <p class="eyebrow">Autor</p>
                <h1>Cărțile mele</h1>
            </div>
            <a href="<?= url('/autor/carti/noua') ?>" class="btn">Publică o carte</a>
        </div>

        <?php if (empty($books)): ?>
            <p class="notice">Nu ai publicat încă nicio carte. Începe cu prima!</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <caption class="visually-hidden">Cărțile tale și stadiul lor</caption>
                    <thead>
                        <tr>
                            <th scope="col">Copertă</th>
                            <th scope="col">Titlu</th>
                            <th scope="col">Preț</th>
                            <th scope="col">Stadiu</th>
                            <th scope="col">Trimisă</th>
                            <th scope="col"><span class="visually-hidden">Acțiuni</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <td><img src="<?= asset($book->coverUrl()) ?>" alt="" class="thumb" width="40" height="60"></td>
                                <td>
                                    <?php if ($book->isApproved() && !$book->isBlindDate): ?>
                                        <a href="<?= url('/carte/' . $book->slug) ?>"><?= e($book->title) ?></a>
                                    <?php else: ?>
                                        <?= e($book->title) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($book->formattedPrice()) ?></td>
                                <td>
                                    <span class="badge badge-<?= e($book->status) ?>"><?= e($book->statusLabel()) ?></span>
                                    <?php if ($book->isBlindDate): ?><span class="badge badge-blind">Blind Date</span><?php endif; ?>
                                </td>
                                <td><?= e(date('d.m.Y', strtotime($book->createdAt))) ?></td>
                                <td>
                                    <form action="<?= url('/autor/carti/' . $book->id . '/sterge') ?>" method="post"
                                          data-confirm="Sigur ștergi „<?= e($book->title) ?>”?">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="link-danger">Șterge</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
