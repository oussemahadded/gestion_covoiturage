<?php
/**
 * views/admin/rewards.php
 * Admin — Conducteurs Récompensés
 */
$pageTitle = 'Conducteurs Récompensés';
require_once ROOT_PATH . '/views/layouts/header.php';

// Badge icon helper — uses the same ui_icon() SVG system as the rest of the app
function rewardBadgeIcon(string $level, string $size = '1.5rem'): string
{
    $icon  = ($level === 'Platinum') ? 'trophy' : 'award';
    $svg   = ui_icon($icon, 'icon icon-sm');
    return '<span class="rw-badge-emoji" style="font-size:' . $size . '" aria-label="' . htmlspecialchars($level) . '">' . $svg . '</span>';
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/rewards.css">

<div class="container rw-admin-wrap">

    <!-- Page Header -->
    <div class="page-header rw-page-header" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <h1 class="page-title rw-title">
            <span class="rw-title-icon"><?= ui_icon('trophy', 'icon icon-md') ?></span>
            <span>Conducteurs Récompensés</span>
        </h1>
        <a href="<?= BASE_URL ?>/index.php?page=admin" class="btn btn-outline">
            <?= ui_icon('arrow-left', 'icon icon-sm') ?>
            <span>Retour au dashboard</span>
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="rw-stats-grid">

        <article class="rw-stat-card rw-stat-card--blue">
            <div class="rw-stat-icon"><?= ui_icon('users', 'icon icon-lg') ?></div>
            <div class="rw-stat-body">
                <span class="rw-stat-num"><?= number_format($rewardStats['total_eligible']) ?></span>
                <span class="rw-stat-label">Conducteurs éligibles</span>
            </div>
        </article>

        <article class="rw-stat-card rw-stat-card--green">
            <div class="rw-stat-icon"><?= ui_icon('car', 'icon icon-lg') ?></div>
            <div class="rw-stat-body">
                <span class="rw-stat-num"><?= number_format($rewardStats['total_conducteurs']) ?></span>
                <span class="rw-stat-label">Total conducteurs</span>
            </div>
        </article>

        <article class="rw-stat-card rw-stat-card--gold">
            <div class="rw-stat-icon">
                <?php
                $topIcon = ($rewardStats['highest_level'] === 'Platinum')
                    ? ui_icon('trophy', 'icon icon-lg')
                    : ui_icon('award',  'icon icon-lg');
                echo $topIcon;
                ?>
            </div>
            <div class="rw-stat-body">
                <span class="rw-stat-num" style="color:<?= htmlspecialchars($rewardStats['highest_color']) ?>">
                    <?= htmlspecialchars($rewardStats['highest_level']) ?>
                </span>
                <span class="rw-stat-label">Niveau le plus élevé</span>
            </div>
        </article>

        <article class="rw-stat-card rw-stat-card--purple">
            <div class="rw-stat-icon"><?= ui_icon('star', 'icon icon-lg') ?></div>
            <div class="rw-stat-body">
                <span class="rw-stat-num"><?= number_format($rewardStats['avg_points'], 0, ',', ' ') ?></span>
                <span class="rw-stat-label">Points moyens</span>
            </div>
        </article>

    </div>

    <!-- Level Distribution Pills -->
    <?php if (!empty($rewardStats['level_distribution'])): ?>
    <div class="rw-dist-bar">
        <span class="rw-dist-title">Répartition par niveau :</span>
        <?php foreach ($rewardStats['level_distribution'] as $lvl => $cnt): ?>
            <?php
            $lvlColors = ['Bronze'=>'#CD7F32','Silver'=>'#A8A9AD','Gold'=>'#FFD700','Platinum'=>'#8B5CF6','Aucun'=>'#6B7280'];
            $col = $lvlColors[$lvl] ?? '#6B7280';
            ?>
            <span class="rw-dist-pill" style="border-color:<?= $col ?>;color:<?= $col ?>">
                <?= htmlspecialchars($lvl) ?> <strong><?= $cnt ?></strong>
            </span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Search & Filter Bar -->
    <div class="rw-filter-bar">
        <form method="GET" action="<?= BASE_URL ?>/index.php" class="rw-filter-form" id="rewardFilterForm">
            <input type="hidden" name="page"   value="reward">
            <input type="hidden" name="action" value="admin">

            <div class="rw-filter-group">
                <?= ui_icon('search', 'icon icon-sm rw-search-icon') ?>
                <input type="text"
                       id="rewardSearch"
                       name="search"
                       placeholder="Rechercher par nom, prénom, email…"
                       value="<?= htmlspecialchars($search) ?>"
                       class="rw-search-input"
                       autocomplete="off">
            </div>

            <div class="rw-filter-group">
                <select name="level_filter" class="rw-select" onchange="document.getElementById('rewardFilterForm').submit()">
                    <option value="">Tous les niveaux</option>
                    <?php foreach ($allLevels as $lvl): ?>
                        <option value="<?= htmlspecialchars($lvl['label']) ?>"
                            <?= $levelFilter === $lvl['label'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($lvl['label']) ?> — <?= (int)$lvl['remise_percent'] ?>%
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">
                <?= ui_icon('search', 'icon icon-xs') ?>
                <span>Filtrer</span>
            </button>

            <?php if ($search !== '' || $levelFilter !== ''): ?>
                <a href="<?= BASE_URL ?>/index.php?page=reward&action=admin" class="btn btn-outline btn-sm">
                    <?= ui_icon('x', 'icon icon-xs') ?>
                    <span>Réinitialiser</span>
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Results Table -->
    <div class="rw-table-card">
        <?php if (empty($conducteurs)): ?>
            <div class="rw-empty-state">
                <div class="rw-empty-icon"><?= ui_icon('trophy', 'icon icon-lg') ?></div>
                <h3>Aucun conducteur trouvé</h3>
                <p>
                    <?php if ($search !== '' || $levelFilter !== ''): ?>
                        Aucun résultat pour ces critères. <a href="<?= BASE_URL ?>/index.php?page=reward&action=admin">Réinitialiser les filtres</a>
                    <?php else: ?>
                        Aucun conducteur Sésame éligible pour le moment.
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <div class="rw-table-responsive">
                <table class="rw-table" id="rewardsTable">
                    <thead>
                        <tr>
                            <th>Conducteur</th>
                            <th>Email</th>
                            <th>Points</th>
                            <th>Niveau actuel</th>
                            <th>Remise</th>
                            <th>Éligibilité</th>
                            <th>Progression</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($conducteurs as $c): ?>
                        <?php
                        $pct        = (float) ($c['progress_pct'] ?? 0);
                        $hasLevel   = !empty($c['current_level']);
                        $levelLabel = $hasLevel ? $c['current_level'] : 'Aucun';
                        $color      = $hasLevel ? ($c['badge_color'] ?? '#6B7280') : '#6B7280';
                        $remise     = $hasLevel ? number_format((float)$c['current_remise'], 0) . '%' : '0%';
                        ?>
                        <tr class="rw-table-row" data-level="<?= htmlspecialchars($levelLabel) ?>">
                            <td class="rw-td-name">
                                <div class="rw-avatar">
                                    <?= strtoupper(substr($c['prenom'], 0, 1)) ?>
                                </div>
                                <div class="rw-name-block">
                                    <span class="rw-name"><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></span>
                                </div>
                            </td>

                            <td class="rw-td-email">
                                <span class="rw-email"><?= htmlspecialchars($c['email']) ?></span>
                            </td>

                            <td class="rw-td-points">
                                <span class="rw-points-val"><?= number_format((int)$c['points_total'], 0, ',', ' ') ?></span>
                                <span class="rw-points-unit">pts</span>
                            </td>

                            <td class="rw-td-level">
                                <?php if ($hasLevel): ?>
                                    <span class="rw-level-badge" style="--level-color:<?= $color ?>">
                                        <?= rewardBadgeIcon($levelLabel, '1rem') ?>
                                        <?= htmlspecialchars($levelLabel) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="rw-no-level">—</span>
                                <?php endif; ?>
                            </td>

                            <td class="rw-td-remise">
                                <span class="rw-remise-badge <?= $hasLevel ? 'rw-remise-badge--active' : '' ?>">
                                    <?= $remise ?>
                                </span>
                            </td>

                            <td class="rw-td-eligible">
                                <span class="rw-eligible-dot rw-eligible-dot--yes" title="Éligible Sésame">
                                    <?= ui_icon('check', 'icon icon-xs') ?>
                                    Éligible
                                </span>
                            </td>

                            <td class="rw-td-progress">
                                <div class="rw-mini-progress">
                                    <div class="rw-mini-bar-track">
                                        <div class="rw-mini-bar-fill"
                                             style="width:<?= $pct ?>%;background:<?= $color ?>;"
                                             data-pct="<?= $pct ?>">
                                        </div>
                                    </div>
                                    <span class="rw-mini-pct"><?= $pct ?>%</span>
                                </div>
                                <?php if (!empty($c['next_level'])): ?>
                                    <span class="rw-next-hint">→ <?= htmlspecialchars($c['next_level']) ?></span>
                                <?php else: ?>
                                    <span class="rw-next-hint rw-next-hint--max"><?= ui_icon('star', 'icon icon-xs') ?> Niveau max</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?= $pagination->render() ?>
        <?php endif; ?>
    </div>

</div>

<script src="<?= BASE_URL ?>/public/js/rewards.js" defer></script>
<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
