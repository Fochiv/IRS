<?php
require_once __DIR__ . '/../includes/functions.php';
requireUserLogin();
$user = getUserById($conn, $_SESSION['user_id']);
$lang = getLang(); $theme = getTheme();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('settings') ?> - <?= t('site_name') ?></title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
    <div class="main-content">
        <div class="top-bar">
            <div class="d-flex align-items-center gap-3">
                <button class="sidebar-toggle-btn" id="sidebarToggle"><i class="bi bi-list"></i></button>
                <div>
                    <h1 class="page-title"><?= t('settings') ?></h1>
                    <p class="page-subtitle">Personnalisez votre expérience</p>
                </div>
            </div>
            <?php $notif_count = getUserNotificationsCount($conn, $_SESSION['user_id']); ?>
            <a href="/dashboard/notifications.php" class="nav-bell-btn" title="Notifications">
                <i class="bi bi-bell-fill"></i>
                <?php if ($notif_count > 0): ?><span class="nav-bell-count"><?= $notif_count > 99 ? '99+' : $notif_count ?></span><?php endif; ?>
            </a>
        </div>
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="irs-card">
                    <div class="irs-card-header">
                        <h5 class="irs-card-title"><i class="bi bi-palette text-irs-blue"></i>Apparence</h5>
                    </div>
                    <div class="irs-card-body">
                        <div class="d-flex align-items-center justify-content-between p-3" style="background:var(--irs-gray);border-radius:10px;margin-bottom:1rem;">
                            <div>
                                <div style="font-weight:600;color:var(--irs-text);">Mode sombre</div>
                                <div style="font-size:0.85rem;color:var(--irs-text-muted);">Activer le thème sombre pour réduire la fatigue oculaire</div>
                            </div>
                            <button class="theme-toggle" style="background:var(--irs-blue);border-color:var(--irs-blue);">
                                <i class="bi bi-moon-fill"></i>
                                <span class="theme-label">Sombre</span>
                            </button>
                        </div>
                        <div class="d-flex align-items-center justify-content-between p-3" style="background:var(--irs-gray);border-radius:10px;">
                            <div>
                                <div style="font-weight:600;color:var(--irs-text);">Langue</div>
                                <div style="font-size:0.85rem;color:var(--irs-text-muted);">Choisissez la langue de l'interface</div>
                            </div>
                            <select class="lang-selector">
                                <option value="fr" <?= $lang === 'fr' ? 'selected' : '' ?>>🇫🇷 Français</option>
                                <option value="en" <?= $lang === 'en' ? 'selected' : '' ?>>🇬🇧 English</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="irs-card">
                    <div class="irs-card-header">
                        <h5 class="irs-card-title"><i class="bi bi-bell text-irs-blue"></i>Notifications</h5>
                    </div>
                    <div class="irs-card-body">
                        <?php
                        $items = [
                            ['Notifications email', 'Recevoir des emails pour les mises à jour de vos documents', true],
                            ['Notifications sur le site', 'Afficher les notifications dans l\'interface', true],
                            ['Alertes de validation', 'Être notifié lors de la validation d\'un document', true],
                        ];
                        foreach ($items as $item): ?>
                        <div class="d-flex align-items-center justify-content-between p-3 mb-2" style="background:var(--irs-gray);border-radius:10px;">
                            <div>
                                <div style="font-weight:600;color:var(--irs-text);font-size:0.9rem;"><?= $item[0] ?></div>
                                <div style="font-size:0.8rem;color:var(--irs-text-muted);"><?= $item[1] ?></div>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" <?= $item[2] ? 'checked' : '' ?> style="width:2.5rem;height:1.3rem;">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
</body>
</html>
