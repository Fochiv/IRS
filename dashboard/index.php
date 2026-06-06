<?php
require_once __DIR__ . '/../includes/functions.php';
requireUserLogin();
$user = getUserById($conn, $_SESSION['user_id']);
$stats = getDocumentStats($conn, $_SESSION['user_id']);

$recent = $conn->query("SELECT * FROM submitted_documents WHERE user_id = {$_SESSION['user_id']} ORDER BY submitted_at DESC LIMIT 5");

$lang = getLang(); $theme = getTheme();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('dashboard') ?> - <?= t('site_name') ?></title>
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
                <button class="sidebar-toggle-btn" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <h1 class="page-title"><?= t('dashboard') ?></h1>
                    <p class="page-subtitle"><?= $lang === 'fr' ? 'Bienvenue' : 'Welcome' ?>, <?= htmlspecialchars($user['first_name']) ?> !</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <select class="lang-selector topbar-control" style="padding:0.3rem 0.6rem;font-size:0.82rem;border-radius:6px;">
                    <option value="fr" <?= $lang === 'fr' ? 'selected' : '' ?>>🇫🇷 FR</option>
                    <option value="en" <?= $lang === 'en' ? 'selected' : '' ?>>🇬🇧 EN</option>
                </select>
                <button class="theme-toggle topbar-control" title="<?= t('theme') ?>">
                    <i class="bi <?= $theme === 'dark' ? 'bi-sun-fill' : 'bi-moon-fill' ?>"></i>
                    <span class="theme-label"><?= $theme === 'dark' ? 'Clair' : 'Sombre' ?></span>
                </button>
                <a href="/dashboard/submit.php" class="btn btn-sm" style="background:var(--irs-blue);color:white;border-radius:8px;">
                    <i class="bi bi-upload me-1"></i><?= t('submit_doc') ?>
                </a>
            </div>
        </div>

        <!-- STATS -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-card-icon blue"><i class="bi bi-folder2-open"></i></div>
                    <div>
                        <div class="stat-card-value"><?= $stats['total'] ?></div>
                        <div class="stat-card-label"><?= t('documents_submitted') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-card-icon green"><i class="bi bi-check-circle"></i></div>
                    <div>
                        <div class="stat-card-value"><?= $stats['validated'] ?></div>
                        <div class="stat-card-label"><?= t('documents_validated') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-card-icon red"><i class="bi bi-x-circle"></i></div>
                    <div>
                        <div class="stat-card-value"><?= $stats['rejected'] ?></div>
                        <div class="stat-card-label"><?= t('documents_rejected') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-card-icon orange"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <div class="stat-card-value"><?= $stats['pending'] ?></div>
                        <div class="stat-card-label"><?= t('documents_pending') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RECENT DOCUMENTS -->
        <div class="irs-card">
            <div class="irs-card-header">
                <h5 class="irs-card-title"><i class="bi bi-clock-history text-irs-blue"></i><?= $lang === 'fr' ? 'Documents récents' : 'Recent Documents' ?></h5>
                <a href="/dashboard/documents.php" style="font-size:0.85rem;color:var(--irs-blue);text-decoration:none;">
                    <?= t('view_all') ?> <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="table-wrap">
                <?php if ($recent && $recent->num_rows > 0): ?>
                <table class="irs-table w-100">
                    <thead>
                        <tr>
                            <th><?= t('doc_name') ?></th>
                            <th><?= t('doc_number') ?></th>
                            <th><?= t('doc_type') ?></th>
                            <th><?= t('date') ?></th>
                            <th><?= t('status') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($doc = $recent->fetch_assoc()): ?>
                        <tr>
                            <td><span class="fw-600"><?= htmlspecialchars($doc['document_name']) ?></span></td>
                            <td><code style="font-size:0.82rem;"><?= htmlspecialchars($doc['document_number']) ?></code></td>
                            <td><?= htmlspecialchars($doc['document_type']) ?></td>
                            <td><?= formatDateTime($doc['submitted_at']) ?></td>
                            <td><?= getStatusBadge($doc['status']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-folder-x"></i>
                    <p><?= $lang === 'fr' ? 'Aucun document soumis pour l\'instant.' : 'No documents submitted yet.' ?></p>
                    <a href="/dashboard/submit.php" class="btn btn-sm" style="background:var(--irs-blue);color:white;border-radius:8px;margin-top:0.75rem;">
                        <i class="bi bi-upload me-1"></i><?= t('submit_doc') ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- QUICK VERIFY -->
        <div class="irs-card">
            <div class="irs-card-header">
                <h5 class="irs-card-title"><i class="bi bi-search text-irs-blue"></i><?= $lang === 'fr' ? 'Vérification rapide' : 'Quick Verification' ?></h5>
            </div>
            <div class="irs-card-body">
                <form id="verifyForm">
                    <div class="d-flex gap-2 flex-wrap">
                        <input type="text" id="docNumber" class="form-control" placeholder="<?= t('enter_doc_number') ?>" style="max-width:380px;">
                        <button type="submit" class="btn" style="background:var(--irs-blue);color:white;border-radius:8px;padding:0.5rem 1.25rem;">
                            <i class="bi bi-search me-1"></i><?= t('verify_now') ?>
                        </button>
                    </div>
                </form>
                <div id="verifyResult" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<!-- Bouton retour en haut -->
<button id="backToTop" title="<?= $lang === 'fr' ? 'Retour en haut' : 'Back to top' ?>">
    <i class="bi bi-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.IRS_LANG = {
    verified_badge: '<?= addslashes(t("verified_badge")) ?>',
    not_verified_badge: '<?= addslashes(t("not_verified_badge")) ?>',
    doc_authenticated: '<?= addslashes(t("doc_authenticated")) ?>',
    doc_not_found: '<?= addslashes(t("doc_not_found")) ?>',
    submit_for_analysis: '<?= addslashes(t("submit_for_analysis")) ?>',
    download_doc: '<?= addslashes(t("download_doc")) ?>',
    download_certificate: '<?= $lang === "fr" ? "Télécharger le certificat officiel" : "Download official certificate" ?>',
    doc_received: '<?= addslashes(t("doc_received")) ?>',
    analyzing_structure: '<?= addslashes(t("analyzing_structure")) ?>',
    analyzing_data: '<?= addslashes(t("analyzing_data")) ?>',
    ocr_analysis: '<?= addslashes(t("ocr_analysis")) ?>',
    security_analysis: '<?= addslashes(t("security_analysis")) ?>',
    ai_verification: '<?= addslashes(t("ai_verification")) ?>',
    expert_verification: '<?= addslashes(t("expert_verification")) ?>',
    final_validation: '<?= addslashes(t("final_validation")) ?>',
    holder_name: '<?= addslashes(t("holder_name")) ?>',
    doc_type: '<?= addslashes(t("doc_type")) ?>',
    issuing_org: '<?= addslashes(t("issuing_org")) ?>',
    issue_date: '<?= addslashes(t("issue_date")) ?>',
    country_origin: '<?= addslashes(t("country_origin")) ?>',
    verify_date: '<?= addslashes(t("verify_date")) ?>',
    status: '<?= addslashes(t("status")) ?>',
    theme_light: '<?= $lang === "fr" ? "Clair" : "Light" ?>',
    theme_dark: '<?= $lang === "fr" ? "Sombre" : "Dark" ?>',
};
</script>
<script src="/assets/js/main.js"></script>
</body>
</html>
