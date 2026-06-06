<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$lang = getLang(); $theme = getTheme();
$success = ''; $error = '';

$admin_id = (int)$_SESSION['admin_id'];

// Récupérer l'admin actuel
$admin = $conn->query("SELECT * FROM admins WHERE id = $admin_id LIMIT 1")->fetch_assoc();

// Récupérer les paramètres du site
function getSetting($conn, $key, $default = '') {
    $key = $conn->real_escape_string($key);
    $r = $conn->query("SELECT setting_value FROM settings WHERE setting_key = '$key' LIMIT 1");
    if ($r && $r->num_rows > 0) return $r->fetch_assoc()['setting_value'];
    return $default;
}
function setSetting($conn, $key, $value) {
    $key = $conn->real_escape_string($key);
    $value = $conn->real_escape_string($value);
    $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$value') ON DUPLICATE KEY UPDATE setting_value = '$value'");
}

$site_email = getSetting($conn, 'site_email', 'internationalregistrationserve@gmail.com');
$site_name  = getSetting($conn, 'site_name', 'IRS - International Registration Server');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- Changer mot de passe ---
    if ($action === 'change_password') {
        $current  = $_POST['current_password'] ?? '';
        $new_pass = trim($_POST['new_password'] ?? '');
        $confirm  = trim($_POST['confirm_password'] ?? '');

        if (empty($current) || empty($new_pass) || empty($confirm)) {
            $error = 'Veuillez remplir tous les champs.';
        } elseif ($admin['password'] !== $current) {
            $error = 'Mot de passe actuel incorrect.';
        } elseif ($new_pass !== $confirm) {
            $error = 'Les nouveaux mots de passe ne correspondent pas.';
        } elseif (strlen($new_pass) < 6) {
            $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
        } else {
            $safe = $conn->real_escape_string($new_pass);
            $conn->query("UPDATE admins SET password = '$safe' WHERE id = $admin_id");
            logActivity($conn, 'admin_change_password', 'Changement de mot de passe admin', null, $admin_id);
            $success = 'Mot de passe mis à jour avec succès.';
            $admin['password'] = $new_pass;
        }
    }

    // --- Changer email de connexion ---
    if ($action === 'change_email') {
        $new_email = trim($conn->real_escape_string($_POST['new_email'] ?? ''));
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Adresse email invalide.';
        } else {
            $exists = $conn->query("SELECT id FROM admins WHERE email = '$new_email' AND id != $admin_id LIMIT 1");
            if ($exists && $exists->num_rows > 0) {
                $error = 'Cet email est déjà utilisé par un autre compte.';
            } else {
                $conn->query("UPDATE admins SET email = '$new_email' WHERE id = $admin_id");
                $_SESSION['admin_email'] = $new_email;
                $admin['email'] = $new_email;
                logActivity($conn, 'admin_change_email', 'Changement email admin: ' . $new_email, null, $admin_id);
                $success = 'Email de connexion mis à jour.';
            }
        }
    }

    // --- Paramètres du site ---
    if ($action === 'save_site_settings') {
        $new_site_email = trim($_POST['site_email'] ?? '');
        $new_site_name  = trim($_POST['site_name'] ?? '');
        if (!empty($new_site_email) && filter_var($new_site_email, FILTER_VALIDATE_EMAIL)) {
            setSetting($conn, 'site_email', $new_site_email);
            $site_email = $new_site_email;
        }
        if (!empty($new_site_name)) {
            setSetting($conn, 'site_name', $new_site_name);
            $site_name = $new_site_name;
        }
        logActivity($conn, 'admin_update_settings', 'Mise à jour des paramètres du site', null, $admin_id);
        $success = 'Paramètres du site mis à jour.';
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres Admin - IRS</title>
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
                    <h1 class="page-title"><i class="bi bi-gear me-2" style="color:#ffa500;"></i>Paramètres Admin</h1>
                    <p class="page-subtitle">Gérez votre compte et les paramètres du site</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="theme-toggle topbar-control">
                    <i class="bi bi-<?= $theme === 'dark' ? 'sun-fill' : 'moon-fill' ?>"></i>
                    <span class="theme-label"><?= $theme === 'dark' ? 'Clair' : 'Sombre' ?></span>
                </button>
            </div>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row g-4">

            <!-- Changer mot de passe -->
            <div class="col-lg-6">
                <div class="irs-card">
                    <div class="irs-card-header">
                        <h5 class="irs-card-title">
                            <i class="bi bi-key" style="color:#ffa500;"></i>
                            Changer le mot de passe
                        </h5>
                    </div>
                    <div class="irs-card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            <div class="mb-3">
                                <label class="form-label">Mot de passe actuel</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="current_password" id="curPass" class="form-control" placeholder="••••••••" required>
                                    <button type="button" class="btn" style="border:1px solid var(--irs-input-border);background:var(--irs-input-bg);color:var(--irs-text-muted);" onclick="togglePass('curPass',this)"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nouveau mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                                    <input type="password" name="new_password" id="newPass" class="form-control" placeholder="••••••••" required>
                                    <button type="button" class="btn" style="border:1px solid var(--irs-input-border);background:var(--irs-input-bg);color:var(--irs-text-muted);" onclick="togglePass('newPass',this)"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Confirmer le nouveau mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                    <input type="password" name="confirm_password" id="confPass" class="form-control" placeholder="••••••••" required>
                                    <button type="button" class="btn" style="border:1px solid var(--irs-input-border);background:var(--irs-input-bg);color:var(--irs-text-muted);" onclick="togglePass('confPass',this)"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                            <button type="submit" class="btn w-100" style="background:#ffa500;color:#0d1117;font-weight:700;border-radius:8px;padding:0.75rem;">
                                <i class="bi bi-save me-2"></i>Mettre à jour le mot de passe
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Changer email de connexion -->
            <div class="col-lg-6">
                <div class="irs-card">
                    <div class="irs-card-header">
                        <h5 class="irs-card-title">
                            <i class="bi bi-envelope" style="color:#ffa500;"></i>
                            Email de connexion
                        </h5>
                    </div>
                    <div class="irs-card-body">
                        <div class="mb-3 p-3" style="background:var(--irs-gray);border-radius:8px;">
                            <div style="font-size:0.8rem;color:var(--irs-text-muted);">Email actuel</div>
                            <div style="font-weight:600;color:var(--irs-text);"><?= htmlspecialchars($admin['email']) ?></div>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="action" value="change_email">
                            <div class="mb-4">
                                <label class="form-label">Nouvel email de connexion</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="new_email" class="form-control" placeholder="admin@exemple.com" required>
                                </div>
                            </div>
                            <button type="submit" class="btn w-100" style="background:#ffa500;color:#0d1117;font-weight:700;border-radius:8px;padding:0.75rem;">
                                <i class="bi bi-save me-2"></i>Mettre à jour l'email
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Paramètres du site -->
            <div class="col-12">
                <div class="irs-card">
                    <div class="irs-card-header">
                        <h5 class="irs-card-title">
                            <i class="bi bi-globe" style="color:#ffa500;"></i>
                            Paramètres du site
                        </h5>
                    </div>
                    <div class="irs-card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="save_site_settings">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nom du site</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                                        <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($site_name) ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email de contact du site</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope-at"></i></span>
                                        <input type="email" name="site_email" class="form-control" value="<?= htmlspecialchars($site_email) ?>">
                                    </div>
                                    <div class="form-text">Affiché dans le footer et les emails automatiques.</div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn" style="background:#ffa500;color:#0d1117;font-weight:700;border-radius:8px;padding:0.6rem 1.5rem;">
                                    <i class="bi bi-save me-2"></i>Enregistrer les paramètres
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Infos du compte -->
            <div class="col-12">
                <div class="irs-card">
                    <div class="irs-card-header">
                        <h5 class="irs-card-title">
                            <i class="bi bi-person-badge" style="color:#ffa500;"></i>
                            Informations du compte
                        </h5>
                    </div>
                    <div class="irs-card-body">
                        <div class="row g-3">
                            <div class="col-md-3 text-center">
                                <div style="width:80px;height:80px;background:#ffa500;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;color:#0d1117;margin:0 auto 0.75rem;">
                                    <?= strtoupper(substr($admin['full_name'], 0, 2)) ?>
                                </div>
                                <div style="font-weight:700;color:var(--irs-text);"><?= htmlspecialchars($admin['full_name']) ?></div>
                                <span class="badge bg-warning text-dark mt-1">Administrateur</span>
                            </div>
                            <div class="col-md-9">
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <div class="p-3" style="background:var(--irs-gray);border-radius:8px;">
                                            <div style="font-size:0.75rem;color:var(--irs-text-muted);">Username</div>
                                            <div style="font-weight:600;color:var(--irs-text);"><?= htmlspecialchars($admin['username']) ?></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="p-3" style="background:var(--irs-gray);border-radius:8px;">
                                            <div style="font-size:0.75rem;color:var(--irs-text-muted);">Email</div>
                                            <div style="font-weight:600;color:var(--irs-text);"><?= htmlspecialchars($admin['email']) ?></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="p-3" style="background:var(--irs-gray);border-radius:8px;">
                                            <div style="font-size:0.75rem;color:var(--irs-text-muted);">Statut</div>
                                            <span class="badge bg-success"><?= $admin['status'] ?></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="p-3" style="background:var(--irs-gray);border-radius:8px;">
                                            <div style="font-size:0.75rem;color:var(--irs-text-muted);">Dernière connexion</div>
                                            <div style="font-weight:600;color:var(--irs-text);"><?= $admin['last_login'] ? formatDateTime($admin['last_login']) : '-' ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePass(id, btn) {
    const f = document.getElementById(id);
    if (f.type === 'password') { f.type = 'text'; btn.innerHTML = '<i class="bi bi-eye-slash"></i>'; }
    else { f.type = 'password'; btn.innerHTML = '<i class="bi bi-eye"></i>'; }
}
</script>
<script src="/assets/js/main.js"></script>
</body>
</html>
