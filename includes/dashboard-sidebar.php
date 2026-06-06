<?php
$notif_count = getUserNotificationsCount($conn, $_SESSION['user_id']);
$current = basename($_SERVER['PHP_SELF']);
$dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="/logo.png" alt="IRS">
        <div>
            <div class="brand-text">IRS</div>
            <span class="brand-sub">International Registration Server</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-section-title">Principal</div>

        <a href="/dashboard/index.php" class="sidebar-link <?= $current === 'index.php' && $dir === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i>
            <?= t('dashboard') ?>
        </a>

        <a href="/dashboard/submit.php" class="sidebar-link <?= $current === 'submit.php' ? 'active' : '' ?>">
            <i class="bi bi-upload"></i>
            <?= t('submit_doc') ?>
        </a>

        <a href="/dashboard/documents.php" class="sidebar-link <?= $current === 'documents.php' ? 'active' : '' ?>">
            <i class="bi bi-folder2-open"></i>
            <?= t('my_documents') ?>
        </a>

        <div class="sidebar-section-title">Compte</div>

        <a href="/dashboard/notifications.php" class="sidebar-link <?= $current === 'notifications.php' ? 'active' : '' ?>">
            <i class="bi bi-bell"></i>
            <?= t('notifications') ?>
            <?php if ($notif_count > 0): ?>
            <span class="badge-count"><?= $notif_count ?></span>
            <?php endif; ?>
        </a>

        <a href="/dashboard/profile.php" class="sidebar-link <?= $current === 'profile.php' ? 'active' : '' ?>">
            <i class="bi bi-person-circle"></i>
            <?= t('profile') ?>
        </a>

        <a href="/dashboard/settings.php" class="sidebar-link <?= $current === 'settings.php' ? 'active' : '' ?>">
            <i class="bi bi-gear"></i>
            <?= t('settings') ?>
        </a>

        <div class="sidebar-section-title">Navigation</div>
        <a href="/index.php" class="sidebar-link">
            <i class="bi bi-house"></i>
            Retour au site
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar">
                <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
            </div>
            <div>
                <div class="sidebar-user-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
                <div class="sidebar-user-email"><?= htmlspecialchars($user['email']) ?></div>
            </div>
        </div>
        <a href="/logout.php" class="btn-logout">
            <i class="bi bi-box-arrow-right"></i>
            <?= t('logout') ?>
        </a>
    </div>
</div>
<div class="overlay" id="sidebarOverlay"></div>
