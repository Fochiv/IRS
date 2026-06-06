<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$total_users   = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_docs    = $conn->query("SELECT COUNT(*) as c FROM documents")->fetch_assoc()['c'];
$pending_verif = $conn->query("SELECT COUNT(*) as c FROM submitted_documents WHERE status='pending'")->fetch_assoc()['c'];
$total_verif   = $conn->query("SELECT COUNT(*) as c FROM submitted_documents")->fetch_assoc()['c'];
$total_checks  = $conn->query("SELECT COUNT(*) as c FROM verifications")->fetch_assoc()['c'];

$recent_submissions = $conn->query("SELECT sd.*, u.first_name, u.last_name, u.email FROM submitted_documents sd LEFT JOIN users u ON sd.user_id=u.id ORDER BY sd.submitted_at DESC LIMIT 8");
$recent_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$recent_activity = $conn->query("SELECT al.*, u.first_name, u.last_name FROM activity_log al LEFT JOIN users u ON al.user_id=u.id ORDER BY al.created_at DESC LIMIT 8");

$lang = getLang(); $theme = getTheme();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('admin_panel') ?> - IRS</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>

    <div class="main-content">
        <div class="top-bar">
            <div class="d-flex align-items-center gap-3">
                <button class="sidebar-toggle-btn" id="sidebarToggle"><i class="bi bi-list"></i></button>
                <div>
                    <h1 class="page-title"><?= $lang === 'fr' ? 'Tableau de bord' : 'Dashboard' ?></h1>
                    <p class="page-subtitle"><?= $lang === 'fr' ? 'Vue d\'ensemble du système IRS' : 'IRS system overview' ?></p>
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
                <span style="font-size:0.85rem;color:var(--irs-text-muted);">
                    <i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i') ?>
                </span>
            </div>
        </div>

        <!-- STATS -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-lg-2-4">
                <div class="stat-card">
                    <div class="stat-card-icon blue"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="stat-card-value"><?= number_format($total_users) ?></div>
                        <div class="stat-card-label"><?= t('total_users') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2-4">
                <div class="stat-card">
                    <div class="stat-card-icon green"><i class="bi bi-file-earmark-check"></i></div>
                    <div>
                        <div class="stat-card-value"><?= number_format($total_docs) ?></div>
                        <div class="stat-card-label"><?= t('total_documents') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2-4">
                <div class="stat-card">
                    <div class="stat-card-icon orange"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <div class="stat-card-value"><?= number_format($pending_verif) ?></div>
                        <div class="stat-card-label"><?= t('pending_verif') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2-4">
                <div class="stat-card">
                    <div class="stat-card-icon purple"><i class="bi bi-clipboard-data"></i></div>
                    <div>
                        <div class="stat-card-value"><?= number_format($total_verif) ?></div>
                        <div class="stat-card-label"><?= $lang === 'fr' ? 'Soumissions totales' : 'Total submissions' ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2-4">
                <div class="stat-card">
                    <div class="stat-card-icon blue"><i class="bi bi-search"></i></div>
                    <div>
                        <div class="stat-card-value"><?= number_format($total_checks) ?></div>
                        <div class="stat-card-label"><?= t('total_verif') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- RECENT SUBMISSIONS -->
            <div class="col-lg-8">
                <div class="irs-card">
                    <div class="irs-card-header">
                        <h5 class="irs-card-title"><i class="bi bi-clipboard-check" style="color:#ffa500;"></i><?= $lang === 'fr' ? 'Vérifications récentes' : 'Recent verifications' ?></h5>
                        <a href="/admin/verifications.php" style="font-size:0.85rem;color:#ffa500;text-decoration:none;"><?= t('view_all') ?> <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="table-wrap">
                        <?php if ($recent_submissions && $recent_submissions->num_rows > 0): ?>
                        <table class="irs-table w-100">
                            <thead>
                                <tr>
                                    <th><?= t('doc_name') ?></th>
                                    <th><?= $lang === 'fr' ? 'Utilisateur' : 'User' ?></th>
                                    <th><?= t('date') ?></th>
                                    <th><?= t('status') ?></th>
                                    <th><?= t('action') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $recent_submissions->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:600;font-size:0.9rem;"><?= htmlspecialchars($row['document_name']) ?></div>
                                        <code style="font-size:0.78rem;color:var(--irs-text-muted);"><?= htmlspecialchars($row['document_number']) ?></code>
                                    </td>
                                    <td>
                                        <div style="font-size:0.85rem;"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></div>
                                        <div style="font-size:0.78rem;color:var(--irs-text-muted);"><?= htmlspecialchars($row['email']) ?></div>
                                    </td>
                                    <td style="font-size:0.85rem;white-space:nowrap;"><?= formatDateTime($row['submitted_at']) ?></td>
                                    <td><?= getStatusBadge($row['status']) ?></td>
                                    <td>
                                        <a href="/admin/verifications.php?id=<?= $row['id'] ?>" class="btn btn-sm" style="background:#ffa500;color:#0d1117;border-radius:6px;font-size:0.78rem;">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="empty-state"><i class="bi bi-inbox"></i><p><?= $lang === 'fr' ? 'Aucune vérification soumise.' : 'No verifications submitted.' ?></p></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- RECENT USERS + ACTIVITY -->
            <div class="col-lg-4">
                <div class="irs-card mb-4">
                    <div class="irs-card-header">
                        <h5 class="irs-card-title"><i class="bi bi-person-plus" style="color:#ffa500;"></i><?= $lang === 'fr' ? 'Nouveaux utilisateurs' : 'New users' ?></h5>
                        <a href="/admin/users.php" style="font-size:0.82rem;color:#ffa500;text-decoration:none;"><?= t('view_all') ?></a>
                    </div>
                    <div class="irs-card-body py-2">
                        <?php if ($recent_users && $recent_users->num_rows > 0): ?>
                            <?php while ($u = $recent_users->fetch_assoc()): ?>
                            <div class="d-flex align-items-center gap-2 py-2" style="border-bottom:1px solid var(--irs-border);">
                                <div style="width:36px;height:36px;background:rgba(46,134,222,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;color:var(--irs-blue);flex-shrink:0;">
                                    <?= strtoupper(substr($u['first_name'],0,1).substr($u['last_name'],0,1)) ?>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-weight:600;font-size:0.85rem;color:var(--irs-text);"><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></div>
                                    <div style="font-size:0.75rem;color:var(--irs-text-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($u['email']) ?></div>
                                </div>
                                <span class="badge <?= $u['status']==='active' ? 'bg-success' : 'bg-danger' ?>" style="font-size:0.65rem;"><?= $u['status'] ?></span>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                        <p style="color:var(--irs-text-muted);font-size:0.9rem;padding:1rem 0;text-align:center;"><?= $lang === 'fr' ? 'Aucun utilisateur.' : 'No users.' ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="irs-card">
                    <div class="irs-card-header">
                        <h5 class="irs-card-title"><i class="bi bi-activity" style="color:#ffa500;"></i><?= $lang === 'fr' ? 'Activité récente' : 'Recent activity' ?></h5>
                    </div>
                    <div class="irs-card-body py-1" style="max-height:250px;overflow-y:auto;">
                        <?php if ($recent_activity && $recent_activity->num_rows > 0): ?>
                            <?php while ($a = $recent_activity->fetch_assoc()): ?>
                            <div style="padding:0.5rem 0;border-bottom:1px solid var(--irs-border);font-size:0.82rem;">
                                <div style="color:var(--irs-text);font-weight:500;"><?= htmlspecialchars($a['action']) ?></div>
                                <div style="color:var(--irs-text-muted);"><?= formatDateTime($a['created_at']) ?></div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                        <p style="color:var(--irs-text-muted);font-size:0.85rem;text-align:center;padding:1rem;"><?= $lang === 'fr' ? 'Aucune activité.' : 'No activity.' ?></p>
                        <?php endif; ?>
                    </div>
                </div>
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
    theme_light: '<?= $lang === "fr" ? "Clair" : "Light" ?>',
    theme_dark: '<?= $lang === "fr" ? "Sombre" : "Dark" ?>',
};
</script>
<script src="/assets/js/main.js"></script>
</body>
</html>
