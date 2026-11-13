<?php
/**
 * Variabile primite de la AdminBookController::index()
 * @var \App\Models\Book[] $books
 * @var string|null        $status  filtrul ales
 * @var int                $pendingCount
 */
use App\Core\View;
use App\Models\Book;

$filters = [null => 'Toate'] + Book::STATUS_LABELS;
?>
<section class="section">
    <div class="container">
        <p class="eyebrow">Administrare</p>
        <h1>Cărți</h1>
        <?= View::render('admin/_nav', ['active' => 'books', 'pendingCount' => $pendingCount], null) ?>

        <div class="filter-row" role="group" aria-label="Filtrează după stadiu">
            <?php foreach ($filters as $key => $label): ?>
                <a href="<?= url('/admin/carti' . ($key ? '?status=' . $key : '')) ?>"
                   class="chip-link <?= $status === ($key ?: null) ? 'is-active' : '' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($books)): ?>
            <p class="notice">Nicio carte în această categorie.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <caption class="visually-hidden">Cărțile trimise de autori</caption>
                    <thead>
                        <tr>
                            <th scope="col">Copertă</th>
                            <th scope="col">Titlu</th>
                            <th scope="col">Autor</th>
                            <th scope="col">Genuri</th>
                            <th scope="col">Preț</th>
                            <th scope="col">Stadiu</th>
                            <th scope="col">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <td><img src="<?= asset($book->coverUrl()) ?>" alt="" class="thumb" width="40" height="60"></td>
                                <td>
                                    <strong><?= e($book->title) ?></strong>
                                    <?php if ($book->isBlindDate): ?><span class="badge badge-blind">Blind Date</span><?php endif; ?>
                                </td>
                                <td><?= e($book->authorName) ?></td>
                                <td><?= e(implode(', ', $book->genres)) ?></td>
                                <td><?= e($book->formattedPrice()) ?></td>
                                <td><span class="badge badge-<?= e($book->status) ?>"><?= e($book->statusLabel()) ?></span></td>
                                <td class="actions">
                                    <?php if ($book->status !== 'approved'): ?>
                                        <form action="<?= url('/admin/carti/' . $book->id . '/aproba') ?>" method="post">
                                            <?= csrf_field() ?><button type="submit" class="btn btn-small">Aprobă</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($book->status !== 'rejected'): ?>
                                        <form action="<?= url('/admin/carti/' . $book->id . '/respinge') ?>" method="post">
                                            <?= csrf_field() ?><button type="submit" class="btn btn-small btn-ghost">Respinge</button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="<?= url('/admin/carti/' . $book->id . '/blind-date') ?>" class="btn btn-small btn-ghost">Blind Date</a>
                                    <form action="<?= url('/admin/carti/' . $book->id . '/sterge') ?>" method="post"
                                          data-confirm="Sigur ștergi definitiv „<?= e($book->title) ?>”?">
                                        <?= csrf_field() ?><button type="submit" class="link-danger">Șterge</button>
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
