<?php $current = basename($_SERVER['PHP_SELF']); ?>
<div class="sidebar admin-sidebar" id="sidebar">
    <div class="sidebar-header" style="background:#0d1117;border-bottom:1px solid rgba(255,165,0,0.15);">
        <img src="/logo.png" alt="IRS">
        <div>
            <div class="brand-text">IRS Admin</div>
            <span class="brand-sub" style="color:rgba(255,165,0,0.7);">Super Administrateur</span>
        </div>
    </div>

    <nav class="sidebar-nav" style="background:#0d1117;">
        <div class="sidebar-section-title">Tableau de Bord</div>

        <a href="/admin/index.php" class="sidebar-link <?= $current === 'index.php' ? 'active' : '' ?>" style="<?= $current === 'index.php' ? 'border-left-color:#ffa500;background:rgba(255,165,0,0.1);' : '' ?>">
            <i class="bi bi-speedometer2"></i>Vue d'ensemble
        </a>

        <div class="sidebar-section-title">Gestion</div>

        <a href="/admin/users.php" class="sidebar-link <?= $current === 'users.php' ? 'active' : '' ?>" style="<?= $current === 'users.php' ? 'border-left-color:#ffa500;background:rgba(255,165,0,0.1);' : '' ?>">
            <i class="bi bi-people"></i>Utilisateurs
        </a>

        <a href="/admin/documents.php" class="sidebar-link <?= $current === 'documents.php' ? 'active' : '' ?>" style="<?= $current === 'documents.php' ? 'border-left-color:#ffa500;background:rgba(255,165,0,0.1);' : '' ?>">
            <i class="bi bi-file-earmark-text"></i>Documents officiels
        </a>

        <a href="/admin/verifications.php" class="sidebar-link <?= $current === 'verifications.php' ? 'active' : '' ?>" style="<?= $current === 'verifications.php' ? 'border-left-color:#ffa500;background:rgba(255,165,0,0.1);' : '' ?>">
            <i class="bi bi-clipboard-check"></i>Vérifications soumises
        </a>

        <div class="sidebar-section-title">Communication</div>

        <a href="/admin/notifications.php" class="sidebar-link <?= $current === 'notifications.php' ? 'active' : '' ?>" style="<?= $current === 'notifications.php' ? 'border-left-color:#ffa500;background:rgba(255,165,0,0.1);' : '' ?>">
            <i class="bi bi-bell"></i>Notifications
        </a>

        <div class="sidebar-section-title">Journaux</div>

        <a href="/admin/activity-log.php" class="sidebar-link <?= $current === 'activity-log.php' ? 'active' : '' ?>" style="<?= $current === 'activity-log.php' ? 'border-left-color:#ffa500;background:rgba(255,165,0,0.1);' : '' ?>">
            <i class="bi bi-journal-text"></i>Journal d'activité
        </a>

        <a href="/admin/search.php" class="sidebar-link <?= $current === 'search.php' ? 'active' : '' ?>" style="<?= $current === 'search.php' ? 'border-left-color:#ffa500;background:rgba(255,165,0,0.1);' : '' ?>">
            <i class="bi bi-search"></i>Recherche avancée
        </a>

        <div class="sidebar-section-title">Navigation</div>
        <a href="/index.php" class="sidebar-link" target="_blank">
            <i class="bi bi-globe"></i>Voir le site
        </a>
    </nav>

    <div class="sidebar-footer" style="background:#0d1117;border-top:1px solid rgba(255,165,0,0.1);">
        <div class="sidebar-user">
            <div class="sidebar-avatar" style="background:#ffa500;color:#0d1117;">
                <?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 2)) ?>
            </div>
            <div>
                <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></div>
                <div class="sidebar-user-email"><?= htmlspecialchars($_SESSION['admin_email'] ?? '') ?></div>
            </div>
        </div>
        <a href="/admin/logout.php" class="btn-logout" style="border-color:rgba(255,165,0,0.3);color:#ffa500;background:rgba(255,165,0,0.08);">
            <i class="bi bi-box-arrow-right"></i>Déconnexion Admin
        </a>
    </div>
</div>
<div class="overlay" id="sidebarOverlay"></div>
