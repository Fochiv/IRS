<?php
require_once __DIR__ . '/../includes/functions.php';
$lang = getLang();
$theme = getTheme();
$current_page = basename($_SERVER['PHP_SELF']);
$user = isUserLoggedIn() ? getUserById($conn, $_SESSION['user_id']) : null;
$notif_count = ($user) ? getUserNotificationsCount($conn, $_SESSION['user_id']) : 0;
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?><?= t('site_name') ?></title>
    <meta name="description" content="<?= t('tagline') ?>">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <?= isset($extra_css) ? $extra_css : '' ?>
</head>
<body>

<nav class="irs-navbar navbar navbar-expand-lg">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand" href="/index.php">
            <img src="/logo.png" alt="IRS Logo">
            <div class="brand-text">IRS <span class="brand-sub d-none d-md-inline">International Registration Server</span></div>
        </a>

        <!-- Collapsible nav links -->
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto ms-2">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>" href="/index.php">
                        <i class="bi bi-house me-1"></i><span data-i18n="home"><?= t('home') ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/index.php#verify-section">
                        <i class="bi bi-search me-1"></i><span data-i18n="verify"><?= t('verify') ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/index.php#about-section">
                        <i class="bi bi-info-circle me-1"></i><span data-i18n="about"><?= t('about') ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/index.php#faq-section">
                        <i class="bi bi-question-circle me-1"></i><span data-i18n="faq"><?= t('faq') ?></span>
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav align-items-center gap-1">
                <?php if ($user): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($user['first_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="background:var(--irs-card-bg);border-color:var(--irs-border);">
                            <li><a class="dropdown-item" href="/dashboard/index.php" style="color:var(--irs-text);">
                                <i class="bi bi-speedometer2 me-2"></i><span data-i18n="dashboard"><?= t('dashboard') ?></span>
                            </a></li>
                            <li><a class="dropdown-item" href="/dashboard/documents.php" style="color:var(--irs-text);">
                                <i class="bi bi-folder me-2"></i><span data-i18n="submitted_docs"><?= t('submitted_docs') ?></span>
                            </a></li>
                            <li><a class="dropdown-item" href="/dashboard/profile.php" style="color:var(--irs-text);">
                                <i class="bi bi-person me-2"></i><span data-i18n="profile"><?= t('profile') ?></span>
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i><span data-i18n="logout"><?= t('logout') ?></span>
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link btn-nav-login" href="/login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i><span data-i18n="login"><?= t('login') ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-nav-register" href="/register.php">
                            <i class="bi bi-person-plus me-1"></i><span data-i18n="register"><?= t('register') ?></span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- ====== ALWAYS VISIBLE: Lang + Theme + Bell + Toggler ====== -->
        <div class="navbar-controls ms-auto d-flex align-items-center gap-2">
            <!-- Sélecteur de langue -->
            <select class="lang-selector nav-lang-selector" aria-label="<?= t('language') ?>" title="<?= t('language') ?>">
                <option value="fr" <?= $lang === 'fr' ? 'selected' : '' ?>>🇫🇷 FR</option>
                <option value="en" <?= $lang === 'en' ? 'selected' : '' ?>>🇬🇧 EN</option>
            </select>
            <!-- Toggle thème -->
            <button class="theme-toggle nav-theme-toggle" title="<?= t('theme') ?>" data-i18n-title="theme">
                <i class="bi <?= $theme === 'dark' ? 'bi-sun-fill' : 'bi-moon-fill' ?>"></i>
            </button>
            <!-- Cloche notification (toujours visible si connecté) -->
            <?php if ($user): ?>
            <a href="/dashboard/notifications.php" class="nav-bell-btn" title="<?= t('notifications') ?>">
                <i class="bi bi-bell-fill"></i>
                <?php if ($notif_count > 0): ?>
                <span class="nav-bell-count"><?= $notif_count > 99 ? '99+' : $notif_count ?></span>
                <?php endif; ?>
            </a>
            <?php endif; ?>
            <!-- Toggler mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-expanded="false">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </div>
</nav>
