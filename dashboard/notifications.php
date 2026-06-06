<?php
require_once __DIR__ . '/../includes/functions.php';
requireUserLogin();
$user = getUserById($conn, $_SESSION['user_id']);
$uid = (int)$_SESSION['user_id'];

if (isset($_GET['mark_read'])) {
    $nid = (int)$_GET['mark_read'];
    $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $nid AND user_id = $uid");
    redirect('/dashboard/notifications.php');
}

if (isset($_GET['mark_all'])) {
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $uid");
    redirect('/dashboard/notifications.php');
}

$notifs = $conn->query("SELECT * FROM notifications WHERE user_id = $uid ORDER BY created_at DESC");
$lang = getLang(); $theme = getTheme();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('notifications') ?> - <?= t('site_name') ?></title>
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
                    <h1 class="page-title"><?= t('notifications') ?></h1>
                    <p class="page-subtitle">Vos dernières notifications</p>
                </div>
            </div>
            <a href="/dashboard/notifications.php?mark_all=1" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                <i class="bi bi-check2-all me-1"></i><?= t('mark_all_read') ?>
            </a>
        </div>

        <div class="irs-card">
            <?php if ($notifs && $notifs->num_rows > 0): ?>
                <?php
                $type_icons = ['info' => 'bi-info-circle-fill', 'success' => 'bi-check-circle-fill', 'warning' => 'bi-exclamation-triangle-fill', 'danger' => 'bi-x-circle-fill'];
                $type_colors = ['info' => '#0d6efd', 'success' => '#198754', 'warning' => '#ffc107', 'danger' => '#dc3545'];
                while ($n = $notifs->fetch_assoc()):
                    $icon = $type_icons[$n['type']] ?? 'bi-bell-fill';
                    $color = $type_colors[$n['type']] ?? '#0d6efd';
                ?>
                <div class="notif-item <?= !$n['is_read'] ? 'unread' : '' ?>">
                    <div class="notif-icon" style="background:<?= $color ?>20;color:<?= $color ?>;">
                        <i class="bi <?= $icon ?>"></i>
                    </div>
                    <div style="flex:1;">
                        <div class="notif-title"><?= htmlspecialchars($n['title']) ?></div>
                        <div class="notif-msg"><?= htmlspecialchars($n['message']) ?></div>
                        <div class="notif-time"><i class="bi bi-clock me-1"></i><?= formatDateTime($n['created_at']) ?></div>
                    </div>
                    <?php if (!$n['is_read']): ?>
                    <div>
                        <a href="/dashboard/notifications.php?mark_read=<?= $n['id'] ?>" class="btn btn-sm btn-outline-primary" style="border-radius:6px;font-size:0.78rem;">
                            <i class="bi bi-check"></i>
                        </a>
                    </div>
                    <?php else: ?>
                    <div><span style="width:8px;height:8px;background:#ccc;border-radius:50%;display:block;"></span></div>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-bell-slash"></i>
                <p><?= t('no_notifications') ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
</body>
</html>
