<?php
/**
 * Variabile primite de la celelalte pagini de admin
 * @var string   $active  tab-ul activ: books, users, log
 * @var int|null $pendingCount
 */
?>
<nav class="tabs" aria-label="Secțiuni de administrare">
    <a href="<?= url('/admin/carti') ?>" class="<?= $active === 'books' ? 'is-active' : '' ?>">
        Cărți<?php if (!empty($pendingCount)): ?> <span class="count"><?= (int) $pendingCount ?></span><?php endif; ?>
    </a>
    <a href="<?= url('/admin') ?>" class="<?= $active === 'users' ? 'is-active' : '' ?>">Utilizatori</a>
    <a href="<?= url('/admin/jurnal') ?>" class="<?= $active === 'log' ? 'is-active' : '' ?>">Jurnal</a>
</nav>
