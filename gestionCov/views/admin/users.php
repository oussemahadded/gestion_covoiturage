<?php $pageTitle = 'Admin — Utilisateurs'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="page-header-row">
        <h1 class="page-title">👥 Gestion des utilisateurs</h1>
        <a href="<?= BASE_URL ?>/index.php?page=admin" class="btn btn-outline">← Dashboard</a>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Rôle</th><th>Inscrit le</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['telephone'] ?? '—') ?></td>
                    <td><span class="badge-role badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php if ($u['id'] !== $_SESSION['user']['id']): ?>
                        <form action="<?= BASE_URL ?>/index.php?page=admin&action=deleteUser" method="POST"
                              class="inline-form"
                              onsubmit="return confirm('Supprimer cet utilisateur et toutes ses données ?')">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-xs">🗑 Supprimer</button>
                        </form>
                        <?php else: ?>
                            <span class="text-muted">Vous</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
