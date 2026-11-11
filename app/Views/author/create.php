<?php $checked = old_list('genres'); ?>
<section class="section">
    <div class="container narrow">
        <p class="eyebrow">Autor</p>
        <h1>Publică o carte</h1>
        <p class="lead">Cartea apare în catalog după ce o aprobă un administrator.</p>

        <form action="<?= url('/autor/carti') ?>" method="post" enctype="multipart/form-data" class="form form-card" novalidate>
            <?= csrf_field() ?>
            <!-- limita de 12 MB, verificata si de browser, si de PHP -->
            <input type="hidden" name="MAX_FILE_SIZE" value="<?= \App\Services\FileUploader::MAX_BYTES ?>">

            <div class="field">
                <label for="title">Titlu</label>
                <input type="text" id="title" name="title" value="<?= e(old('title')) ?>" maxlength="200" required
                       <?= error('title') ? 'aria-invalid="true"' : '' ?>>
                <?php if ($msg = error('title')): ?><p class="field-error"><?= e($msg) ?></p><?php endif; ?>
            </div>

            <div class="field">
                <label for="description">Descriere</label>
                <textarea id="description" name="description" rows="6" maxlength="5000" required
                          <?= error('description') ? 'aria-invalid="true"' : '' ?>><?= e(old('description')) ?></textarea>
                <p class="field-hint">Minimum 30 de caractere. Apare pe pagina cărții.</p>
                <?php if ($msg = error('description')): ?><p class="field-error"><?= e($msg) ?></p><?php endif; ?>
            </div>

            <div class="field field-short">
                <label for="price">Preț (lei)</label>
                <input type="text" id="price" name="price" inputmode="decimal" value="<?= e(old('price')) ?>" placeholder="24,99"
                       <?= error('price') ? 'aria-invalid="true"' : '' ?>>
                <?php if ($msg = error('price')): ?><p class="field-error"><?= e($msg) ?></p><?php endif; ?>
            </div>

            <fieldset class="field">
                <legend>Genuri <span class="field-hint-inline">(între 1 și 3)</span></legend>
                <div class="chip-group">
                    <?php foreach ($genres as $genre): ?>
                        <label class="chip">
                            <input type="checkbox" name="genres[]" value="<?= $genre->id ?>"
                                   <?= in_array((string) $genre->id, $checked, true) ? 'checked' : '' ?>>
                            <span><?= e($genre->name) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php if ($msg = error('genres')): ?><p class="field-error"><?= e($msg) ?></p><?php endif; ?>
            </fieldset>

            <div class="upload-grid">
                <div class="field">
                    <label for="cover">Copertă</label>
                    <input type="file" id="cover" name="cover" accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                           data-max-bytes="<?= \App\Services\FileUploader::MAX_BYTES ?>">
                    <p class="field-hint">JPG sau PNG, maximum 12 MB.</p>
                    <?php if ($msg = error('cover')): ?><p class="field-error"><?= e($msg) ?></p><?php endif; ?>
                </div>
                <div class="field">
                    <label for="pdf">Cartea (PDF)</label>
                    <input type="file" id="pdf" name="pdf" accept=".pdf,application/pdf"
                           data-max-bytes="<?= \App\Services\FileUploader::MAX_BYTES ?>">
                    <p class="field-hint">Doar PDF, maximum 12 MB.</p>
                    <?php if ($msg = error('pdf')): ?><p class="field-error"><?= e($msg) ?></p><?php endif; ?>
                </div>
            </div>

            <button type="submit" class="btn">Trimite spre aprobare</button>
        </form>
    </div>
</section>
