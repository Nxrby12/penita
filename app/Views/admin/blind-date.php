<?php $on = old('is_blind_date', $book->isBlindDate ? '1' : '') !== ''; ?>
<section class="section">
    <div class="container narrow">
        <p class="eyebrow">Administrare · Blind Date</p>
        <h1><?= e($book->title) ?></h1>
        <p class="lead">de <?= e($book->authorName) ?> · <?= e($book->formattedPrice()) ?></p>

        <form action="<?= url('/admin/carti/' . $book->id . '/blind-date') ?>" method="post" class="form form-card">
            <?= csrf_field() ?>

            <label class="role-option">
                <input type="checkbox" name="is_blind_date" value="1" <?= $on ? 'checked' : '' ?>>
                <span><strong>Vinde ca Blind Date.</strong> Cartea dispare din catalog și apare împachetată, doar cu indiciile de mai jos.</span>
            </label>

            <?php foreach ([0, 1, 2] as $i): ?>
                <div class="field">
                    <label for="clue<?= $i ?>">Indiciul <?= $i + 1 ?></label>
                    <input type="text" id="clue<?= $i ?>" name="clues[]" maxlength="80"
                           value="<?= e(old("clue{$i}", $clues[$i] ?? '')) ?>"
                           placeholder="<?= ['ex: un dragon care și-a pierdut memoria', 'ex: munții Apuseni', 'ex: final care te lasă fără cuvinte'][$i] ?>"
                           <?= error("clue{$i}") ? 'aria-invalid="true"' : '' ?>>
                    <?php if ($msg = error("clue{$i}")): ?><p class="field-error"><?= e($msg) ?></p><?php endif; ?>
                </div>
            <?php endforeach; ?>
            <p class="field-hint">Indiciile nu trebuie să dezvăluie titlul sau numele autorului.</p>

            <div class="hero-actions">
                <button type="submit" class="btn">Salvează</button>
                <a href="<?= url('/admin/carti') ?>" class="btn btn-ghost">Înapoi</a>
            </div>
        </form>
    </div>
</section>
