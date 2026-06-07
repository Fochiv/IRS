<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$search = $conn->real_escape_string($_GET['search'] ?? '');
$where = $search ? "WHERE al.action LIKE '%$search%' OR al.description LIKE '%$search%' OR u.email LIKE '%$search%'" : '';
$logs = $conn->query("SELECT al.*, u.first_name, u.last_name, u.email, a.full_name as admin_name FROM activity_log al LEFT JOIN users u ON al.user_id=u.id LEFT JOIN admins a ON al.admin_id=a.id $where ORDER BY al.created_at DESC LIMIT 200");

$lang = getLang(); $theme = getTheme();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal d'Activité - Admin IRS</title>
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
                <div><h1 class="page-title">Journal d'Activité</h1><p class="page-subtitle">Historique complet des actions système</p></div>
            </div>
        </div>

        <div class="irs-card mb-3">
            <div class="irs-card-body py-2">
                <form method="GET" class="d-flex gap-2">
                    <div class="search-box flex-grow-1" style="max-width:400px;">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" placeholder="Rechercher dans les logs..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-sm" style="background:var(--irs-blue);color:white;border-radius:8px;">Rechercher</button>
                    <?php if ($search): ?><a href="/admin/activity-log.php" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">Réinitialiser</a><?php endif; ?>
                </form>
            </div>
        </div>

        <div class="irs-card">
            <div class="table-wrap">
                <?php if ($logs && $logs->num_rows > 0): ?>
                <table class="irs-table w-100">
                    <thead><tr><th>#</th><th>Action</th><th>Description</th><th>Acteur</th><th>IP</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php $n=1; while ($log = $logs->fetch_assoc()):
                            $is_admin = !empty($log['admin_name']);
                        ?>
                        <tr>
                            <td><?= $n++ ?></td>
                            <td>
                                <span class="badge" style="background:rgba(255,165,0,0.15);color:#ffa500;font-size:0.75rem;">
                                    <i class="bi bi-activity me-1"></i><?= htmlspecialchars($log['action']) ?>
                                </span>
                            </td>
                            <td style="font-size:0.87rem;max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($log['description']) ?>">
                                <?= htmlspecialchars($log['description']) ?>
                            </td>
                            <td>
                                <?php if ($is_admin): ?>
                                <span class="badge" style="background:rgba(255,165,0,0.15);color:#ffa500;font-size:0.75rem;"><i class="bi bi-shield-fill me-1"></i><?= htmlspecialchars($log['admin_name']) ?></span>
                                <?php elseif ($log['first_name']): ?>
                                <span style="font-size:0.85rem;"><?= htmlspecialchars($log['first_name'].' '.$log['last_name']) ?></span>
                                <?php else: ?>
                                <span style="color:var(--irs-text-muted);font-size:0.82rem;">Système</span>
                                <?php endif; ?>
                            </td>
                            <td><code style="font-size:0.78rem;"><?= htmlspecialchars($log['ip_address'] ?: '-') ?></code></td>
                            <td style="font-size:0.82rem;white-space:nowrap;"><?= formatDateTime($log['created_at']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state"><i class="bi bi-journal-x"></i><p>Aucune activité enregistrée.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
</body></html>
