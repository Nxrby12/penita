<?php
use App\Controllers\AdminLogController;
use App\Core\View;

$labels = [
    'auth.register'         => 'Cont nou',
    'auth.login'            => 'Autentificare',
    'auth.login_failed'     => 'Autentificare eșuată',
    'auth.logout'           => 'Ieșire din cont',
    'book.submitted'        => 'Carte trimisă',
    'book.upload_rejected'  => 'Fișier respins',
    'book.deleted'          => 'Carte ștearsă de autor',
    'book.downloaded'       => 'PDF descărcat',
    'admin.book_approved'   => 'Carte aprobată',
    'admin.book_rejected'   => 'Carte respinsă',
    'admin.book_deleted'    => 'Carte ștearsă de admin',
    'admin.blind_date'      => 'Blind Date modificat',
    'order.started'         => 'Plată pornită',
    'order.paid'            => 'Plată reușită',
    'order.cancelled'       => 'Plată anulată',
    'order.not_confirmed'   => 'Plată neconfirmată',
    'order.payment_error'   => 'Eroare la plată',
    'security.csrf'         => 'Token CSRF invalid',
    'security.forbidden'    => 'Acces interzis',
    'security.rate_limited' => 'Prea multe cereri',
    'security.captcha_failed' => 'CAPTCHA greșit',
    'error.server'          => 'Eroare de server',
    'error.database'        => 'Eroare bază de date',
];
$levels = ['info' => 'Info', 'warning' => 'Avertisment', 'error' => 'Eroare'];
$filterUrl = fn(?string $c, ?string $l) => url('/admin/jurnal') . (($q = http_build_query(array_filter(['categorie' => $c, 'nivel' => $l]))) ? '?' . $q : '');
?>
<section class="section">
    <div class="container">
        <p class="eyebrow">Administrare</p>
        <div class="page-head">
            <h1>Jurnal de activitate</h1>
            <a href="<?= url('/admin/jurnal/descarca') ?>" class="btn btn-small btn-ghost">Descarcă fișierul jurnalului</a>
        </div>
        <?= View::render('admin/_nav', ['active' => 'log', 'pendingCount' => $pendingCount], null) ?>

        <nav class="filter-row" aria-label="Filtrează după categorie">
            <a href="<?= $filterUrl(null, $level) ?>" class="chip-link <?= $category === null ? 'is-active' : '' ?>">Toate</a>
            <?php foreach (AdminLogController::CATEGORIES as $key => $name): ?>
                <a href="<?= $filterUrl($key, $level) ?>" class="chip-link <?= $category === $key ? 'is-active' : '' ?>"><?= e($name) ?></a>
            <?php endforeach; ?>
            <span class="filter-sep" aria-hidden="true"></span>
            <?php foreach ($levels as $key => $name): ?>
                <a href="<?= $filterUrl($category, $level === $key ? null : $key) ?>"
                   class="chip-link <?= $level === $key ? 'is-active' : '' ?>"><?= e($name) ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if (empty($entries)): ?>
            <p class="notice">Nicio înregistrare pentru acest filtru.</p>
        <?php else: ?>
            <p class="field-hint">Ultimele <?= count($entries) ?> înregistrări, cele mai noi primele.</p>
            <div class="table-wrap">
                <table class="data-table log-table">
                    <caption class="visually-hidden">Jurnalul de activitate</caption>
                    <thead>
                        <tr>
                            <th scope="col">Data și ora</th>
                            <th scope="col">Nivel</th>
                            <th scope="col">Acțiune</th>
                            <th scope="col">Utilizator</th>
                            <th scope="col">IP</th>
                            <th scope="col">Detalii</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $entry): ?>
                            <tr>
                                <td><?= e($entry['time']) ?></td>
                                <td><span class="badge badge-level-<?= e($entry['level']) ?>"><?= e($levels[$entry['level']] ?? $entry['level']) ?></span></td>
                                <td><?= e($labels[$entry['action']] ?? $entry['action']) ?></td>
                                <td><?= e($entry['user'] ?? 'vizitator') ?></td>
                                <td><?= e($entry['ip']) ?></td>
                                <td class="log-details">
                                    <?php foreach ((array) ($entry['details'] ?? []) as $key => $value): ?>
                                        <span><strong><?= e((string) $key) ?>:</strong> <?= e(is_scalar($value) ? var_export($value, true) : json_encode($value)) ?></span>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
