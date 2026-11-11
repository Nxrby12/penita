<section class="section">
    <div class="container">
        <p class="eyebrow">Administrare</p>
        <h1>Utilizatori</h1>
        <?= \App\Core\View::render('admin/_nav', ['active' => 'users', 'pendingCount' => $pendingCount], null) ?>
        <p class="lead"><?= count($users) === 1 ? '1 cont înregistrat' : count($users) . ' conturi înregistrate' ?>.</p>

        <div class="table-wrap">
            <table class="data-table">
                <caption class="visually-hidden">Lista utilizatorilor</caption>
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nume de utilizator</th>
                        <th scope="col">Email</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Înregistrat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u->id ?></td>
                            <td><?= e($u->username) ?></td>
                            <td><?= e($u->email) ?></td>
                            <td><span class="badge badge-<?= e($u->role) ?>"><?= e($u->roleLabel()) ?></span></td>
                            <td><?= e(date('d.m.Y', strtotime($u->createdAt))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
