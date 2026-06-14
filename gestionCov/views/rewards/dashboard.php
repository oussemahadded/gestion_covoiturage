<?php
/**
 * views/rewards/dashboard.php
 * Conducteur personal reward dashboard
 */
$pageTitle = 'Mes Récompenses';
require_once ROOT_PATH . '/views/layouts/header.php';

$eligible      = $rewardData['eligible'];
$points        = (int)  $rewardData['points_total'];
$current       = $rewardData['current_level'];
$next          = $rewardData['next_level'];
$remise        = (float) $rewardData['remise_percent'];
$remaining     = (int)  $rewardData['remaining_points'];
$pct           = (float) $rewardData['progress_pct'];
$history       = $rewardData['history'];

$levelLabel    = $current ? $current['label']          : null;
$levelColor    = $current ? $current['badge_color']    : '#6B7280';
$levelRemise   = $current ? (float)$current['remise_percent'] : 0.0;
$nextLabel     = $next    ? $next['label']             : null;
$nextMin       = $next    ? (int)$next['min_points']   : null;

// Per-tier SVG icons — uses the same ui_icon() helper already loaded by header.php
$badgeIcons = [
    'Bronze'   => ui_icon('award',  'icon icon-sm rw-tier-icon'),
    'Silver'   => ui_icon('award',  'icon icon-sm rw-tier-icon'),
    'Gold'     => ui_icon('award',  'icon icon-sm rw-tier-icon'),
    'Platinum' => ui_icon('trophy', 'icon icon-sm rw-tier-icon'),
];
$currentIcon = $levelLabel ? ($badgeIcons[$levelLabel] ?? ui_icon('award',  'icon icon-sm rw-tier-icon')) : ui_icon('award', 'icon icon-sm rw-tier-icon');
$nextIcon    = $nextLabel  ? ($badgeIcons[$nextLabel]  ?? ui_icon('award',  'icon icon-sm rw-tier-icon')) : null;
// Keep $badgeEmojis as alias so history loop below uses $badgeIcons without renaming the var
$badgeEmojis = $badgeIcons;
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/rewards.css">

<div class="container rw-conducteur-wrap">

    <!-- Page Title -->
    <div class="rw-cond-header">
        <h1 class="page-title rw-title">
            <span class="rw-title-icon"><?= ui_icon('trophy', 'icon icon-md') ?></span>
            <span>Mes Récompenses</span>
        </h1>

    </div>

    <?php if (!$eligible): ?>
    <!-- Not eligible notice -->
    <div class="rw-ineligible-card">
        <div class="rw-ineligible-icon"><?= ui_icon('lock', 'icon icon-lg') ?></div>
        <h2>Programme réservé aux conducteurs Sésame</h2>
        <p>
            Le programme de récompenses est exclusivement disponible pour les conducteurs
            dont l'adresse email est <strong>@sesame.com.tn</strong>.
        </p>
        <p>Votre compte n'est pas éligible à ce programme.</p>
    </div>
    <?php else: ?>

    <div class="rw-cond-grid">

        <!-- LEFT: Main reward card -->
        <div class="rw-main-col">

            <!-- Points & Level Card -->
            <div class="rw-hero-card" style="--hero-color:<?= $levelColor ?>">
                <div class="rw-hero-bg-circles"></div>

                <div class="rw-hero-top">
                    <div class="rw-hero-badge" style="border-color:<?= $levelColor ?>">
                        <span class="rw-hero-emoji"><?= $currentIcon ?></span>
                        <?php if ($levelLabel): ?>
                            <span class="rw-hero-level-name" style="color:<?= $levelColor ?>"><?= htmlspecialchars($levelLabel) ?></span>
                        <?php else: ?>
                            <span class="rw-hero-level-name rw-hero-level-none">Aucun niveau</span>
                        <?php endif; ?>
                    </div>
                    <div class="rw-hero-points-block">
                        <span class="rw-hero-pts-num" id="conducteurPoints" data-target="<?= $points ?>">0</span>
                        <span class="rw-hero-pts-label">points accumulés</span>
                    </div>
                </div>

                <?php if ($remise > 0): ?>
                <div class="rw-remise-highlight" style="background:<?= $levelColor ?>">
                    <span class="rw-remise-label">Votre remise actuelle</span>
                    <span class="rw-remise-pct"><?= number_format($remise, 0) ?>%</span>
                </div>
                <?php else: ?>
                <div class="rw-remise-highlight rw-remise-highlight--none">
                    <span class="rw-remise-label">Aucune remise pour l'instant</span>
                    <span class="rw-remise-pct">0%</span>
                </div>
                <?php endif; ?>

                <!-- Progress to next level -->
                <div class="rw-progress-section">
                    <?php if ($nextLabel): ?>
                        <div class="rw-progress-labels">
                            <span class="rw-prog-from">
                                <?= $levelLabel ? htmlspecialchars($levelLabel) : 'Départ' ?>
                            </span>
                            <span class="rw-prog-pct-text"><?= $pct ?>%</span>
                            <span class="rw-prog-to">
                                <?= $nextIcon ?> <?= htmlspecialchars($nextLabel) ?>
                            </span>
                        </div>
                        <div class="rw-progress-track" role="progressbar"
                             aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"
                             aria-label="Progression vers <?= htmlspecialchars($nextLabel) ?>">
                            <div class="rw-progress-fill"
                                 id="mainProgressBar"
                                 data-pct="<?= $pct ?>"
                                 style="width:0%;background:<?= $levelColor ?>">
                            </div>
                        </div>
                        <p class="rw-remaining-hint">
                            <?= ui_icon('star', 'icon icon-xs') ?>
                            <strong><?= number_format($remaining, 0, ',', ' ') ?> points</strong>
                            encore avant le niveau <strong><?= htmlspecialchars($nextLabel) ?></strong>
                            (<?= number_format($nextMin, 0, ',', ' ') ?> pts requis)
                        </p>
                    <?php else: ?>
                        <div class="rw-progress-labels">
                            <span class="rw-prog-from"><?= ui_icon('star', 'icon icon-xs') ?> Niveau maximum atteint</span>
                            <span class="rw-prog-pct-text">100%</span>
                        </div>
                        <div class="rw-progress-track">
                            <div class="rw-progress-fill"
                                 id="mainProgressBar"
                                 data-pct="100"
                                 style="width:0%;background:<?= $levelColor ?>">
                            </div>
                        </div>
                        <p class="rw-remaining-hint rw-remaining-hint--max">
                            <?= ui_icon('trophy', 'icon icon-xs') ?> Vous avez atteint le sommet ! Félicitations
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reward History -->
            <?php if (!empty($history)): ?>
            <div class="rw-history-card">
                <h3 class="rw-history-title">
                    <?= ui_icon('traceability', 'icon icon-sm') ?>
                    Historique des niveaux
                </h3>
                <div class="rw-history-timeline">
                    <?php foreach ($history as $h): ?>
                    <?php
                        $hEmoji = $badgeEmojis[$h['new_level']] ?? ui_icon('award', 'icon icon-sm rw-tier-icon');
                        $hDate  = date('d/m/Y', strtotime($h['changed_at']));
                    ?>
                    <div class="rw-history-item">
                        <span class="rw-history-emoji" style="color:var(--hero-color,#6B7280)"><?= $hEmoji ?></span>
                        <div class="rw-history-info">
                            <span class="rw-history-new">Niveau <strong><?= htmlspecialchars($h['new_level']) ?></strong> atteint</span>
                            <?php if ($h['old_level']): ?>
                            <span class="rw-history-old">Depuis : <?= htmlspecialchars($h['old_level']) ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="rw-history-date"><?= $hDate ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: Level roadmap -->
        <div class="rw-side-col">
            <div class="rw-roadmap-card">
                <h3 class="rw-roadmap-title">
                    <?= ui_icon('route', 'icon icon-sm') ?> Carte des niveaux
                </h3>
                <div class="rw-roadmap-list">
                    <?php foreach ($allLevels as $lvl): ?>
                    <?php
                        $isReached   = $points >= (int)$lvl['min_points'];
                        $isCurrent   = $levelLabel === $lvl['label'];
                        $lIcon       = $badgeIcons[$lvl['label']] ?? ui_icon('award', 'icon icon-sm rw-tier-icon');
                        $lColor      = $lvl['badge_color'];
                        $isPlatinum  = $lvl['label'] === 'Platinum';
                        $platinumStyle = $isPlatinum
                            ? ' background:#eff6ff; border:1px solid #bfdbfe;'
                            : '';
                        $platinumColor = $isPlatinum ? '#1e3a8a' : $lColor;
                    ?>
                    <div class="rw-roadmap-item
                        <?= $isCurrent ? 'rw-roadmap-item--current' : '' ?>
                        <?= $isReached && !$isCurrent ? 'rw-roadmap-item--reached' : '' ?>
                        <?= !$isReached ? 'rw-roadmap-item--locked' : '' ?>"
                        style="--rm-color:<?= $platinumColor ?><?= $platinumStyle ?>">

                        <div class="rw-rm-icon" style="color:<?= $platinumColor ?>"><?= $lIcon ?></div>
                        <div class="rw-rm-info">
                            <span class="rw-rm-name" style="color:<?= $platinumColor ?>"><?= htmlspecialchars($lvl['label']) ?></span>
                            <span class="rw-rm-pts"><?= number_format((int)$lvl['min_points'], 0, ',', ' ') ?> pts</span>
                        </div>
                        <div class="rw-rm-remise">
                            <span class="rw-rm-pct" style="color:<?= $platinumColor ?>"><?= (int)$lvl['remise_percent'] ?>%</span>
                            <span class="rw-rm-off">remise</span>
                        </div>
                        <?php if ($isCurrent): ?>
                            <span class="rw-rm-badge-current">Actuel</span>
                        <?php elseif ($isReached): ?>
                            <?= ui_icon('check', 'icon icon-xs rw-rm-check') ?>
                        <?php else: ?>
                            <?= ui_icon('lock', 'icon icon-xs rw-rm-lock') ?>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

    <?php endif; ?>
</div>

<script src="<?= BASE_URL ?>/public/js/rewards.js" defer></script>
<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
