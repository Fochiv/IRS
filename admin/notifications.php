<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = $conn->real_escape_string(trim($_POST['title'] ?? ''));
    $message = $conn->real_escape_string(trim($_POST['message'] ?? ''));
    $type    = $conn->real_escape_string($_POST['type'] ?? 'info');
    $target  = $_POST['target'] ?? 'global';
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $admin_id = (int)$_SESSION['admin_id'];

    if ($title && $message) {
        if ($target === 'global') {
            $users = $conn->query("SELECT id FROM users WHERE status='active'");
            while ($u = $users->fetch_assoc()) {
                $uid = $u['id'];
                $conn->query("INSERT INTO notifications (user_id, admin_id, title, message, type) VALUES ($uid, $admin_id, '$title', '$message', '$type')");
            }
            $success = 'Notification globale envoyée à tous les utilisateurs actifs.';
        } elseif ($target === 'specific' && $user_id > 0) {
            $conn->query("INSERT INTO notifications (user_id, admin_id, title, message, type) VALUES ($user_id, $admin_id, '$title', '$message', '$type')");
            $success = 'Notification envoyée à l\'utilisateur.';
        }
        logActivity($conn, 'admin_send_notification', "Envoi notification: $title", null, $admin_id);
    }
}

$users_list = $conn->query("SELECT id, first_name, last_name, email FROM users WHERE status='active' ORDER BY first_name");
$recent_notifs = $conn->query("SELECT n.*, u.first_name, u.last_name FROM notifications n LEFT JOIN users u ON n.user_id=u.id ORDER BY n.created_at DESC LIMIT 30");
$lang = getLang(); $theme = getTheme();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Notifications - Admin IRS</title>
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
                <div><h1 class="page-title">Gestion Notifications</h1><p class="page-subtitle">Envoyez des notifications aux utilisateurs</p></div>
            </div>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- SEND FORM -->
            <div class="col-lg-5">
                <div class="irs-card">
                    <div class="irs-card-header">
                        <h5 class="irs-card-title"><i class="bi bi-send" style="color:#ffa500;"></i>Envoyer une notification</h5>
                    </div>
                    <div class="irs-card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Cible *</label>
                                <select name="target" class="form-select" id="targetSelect" onchange="toggleUserSelect(this.value)">
                                    <option value="global">Tous les utilisateurs actifs</option>
                                    <option value="specific">Utilisateur spécifique</option>
                                </select>
                            </div>
                            <div class="mb-3" id="userSelectDiv" style="display:none;">
                                <label class="form-label">Utilisateur *</label>
                                <select name="user_id" class="form-select">
                                    <option value="">-- Sélectionner --</option>
                                    <?php if ($users_list): $users_list->data_seek(0); while ($u = $users_list->fetch_assoc()): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['first_name'].' '.$u['last_name'].' ('.$u['email'].')') ?></option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="info">ℹ️ Information</option>
                                    <option value="success">✅ Succès</option>
                                    <option value="warning">⚠️ Avertissement</option>
                                    <option value="danger">❌ Alerte</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Titre *</label>
                                <input type="text" name="title" class="form-control" placeholder="Titre de la notification" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Message *</label>
                                <textarea name="message" class="form-control" rows="4" placeholder="Contenu du message..." required></textarea>
                            </div>
                            <button type="submit" class="btn w-100" style="background:#ffa500;color:#0d1117;border-radius:8px;font-weight:600;">
                                <i class="bi bi-send me-2"></i>Envoyer la notification
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- RECENT NOTIFICATIONS -->
            <div class="col-lg-7">
                <div class="irs-card">
                    <div class="irs-card-header">
                        <h5 class="irs-card-title"><i class="bi bi-bell" style="color:#ffa500;"></i>Notifications récentes</h5>
                    </div>
                    <?php
                    $type_icons = ['info' => 'bi-info-circle-fill', 'success' => 'bi-check-circle-fill', 'warning' => 'bi-exclamation-triangle-fill', 'danger' => 'bi-x-circle-fill'];
                    $type_colors = ['info' => '#0d6efd', 'success' => '#198754', 'warning' => '#ffc107', 'danger' => '#dc3545'];
                    if ($recent_notifs && $recent_notifs->num_rows > 0):
                        while ($n = $recent_notifs->fetch_assoc()):
                            $icon = $type_icons[$n['type']] ?? 'bi-bell-fill';
                            $color = $type_colors[$n['type']] ?? '#0d6efd';
                    ?>
                    <div class="notif-item">
                        <div class="notif-icon" style="background:<?= $color ?>20;color:<?= $color ?>;"><i class="bi <?= $icon ?>"></i></div>
                        <div style="flex:1;">
                            <div class="notif-title"><?= htmlspecialchars($n['title']) ?></div>
                            <div class="notif-msg"><?= htmlspecialchars($n['message']) ?></div>
                            <div class="notif-time">
                                <i class="bi bi-person me-1"></i><?= htmlspecialchars($n['first_name'].' '.$n['last_name']) ?>
                                &bull; <i class="bi bi-clock me-1"></i><?= formatDateTime($n['created_at']) ?>
                                &bull; <?= $n['is_read'] ? '<span class="text-success">Lu</span>' : '<span class="text-warning">Non lu</span>' ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; else: ?>
                    <div class="empty-state"><i class="bi bi-bell-slash"></i><p>Aucune notification envoyée.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
<script>
function toggleUserSelect(val) {
    document.getElementById('userSelectDiv').style.display = val === 'specific' ? 'block' : 'none';
}
</script>
</body></html>
