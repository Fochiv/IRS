<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isAdminLoggedIn()) {
    redirect('/admin/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($conn->real_escape_string($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $result = $conn->query("SELECT * FROM admins WHERE email = '$email' LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            if ($admin['password'] === $password) {
                if ($admin['status'] !== 'active') {
                    $error = 'Ce compte administrateur est désactivé.';
                } else {
                    $_SESSION['admin_id']   = $admin['id'];
                    $_SESSION['admin_name'] = $admin['full_name'];
                    $_SESSION['admin_email']= $admin['email'];
                    $conn->query("UPDATE admins SET last_login = NOW() WHERE id = {$admin['id']}");
                    logActivity($conn, 'admin_login', 'Connexion administrateur: ' . $email, null, $admin['id']);
                    redirect('/admin/index.php');
                }
            } else {
                $error = 'Identifiants incorrects.';
            }
        } else {
            $error = 'Identifiants incorrects.';
        }
    }
}

$lang = getLang(); $theme = getTheme();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - IRS</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .admin-auth-wrapper {
            min-height: 100vh;
            background: linear-gradient(135deg, #0d1117 0%, #161b22 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,165,0,0.15);
            border: 1px solid rgba(255,165,0,0.3);
            color: #ffa500;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
<div class="admin-auth-wrapper">
    <div class="auth-card">
        <div class="auth-header" style="background:#0d1117;border-bottom:1px solid rgba(255,165,0,0.2);">
            <img src="/logo.png" alt="IRS">
            <div class="admin-badge"><i class="bi bi-shield-lock-fill"></i>Accès Restreint - Administration</div>
            <h4>Panneau Administrateur</h4>
            <p style="color:rgba(255,165,0,0.7);">Connexion réservée aux administrateurs IRS</p>
        </div>
        <div class="auth-body">
            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-shield-x me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="alert" style="background:rgba(255,165,0,0.1);border:1px solid rgba(255,165,0,0.2);color:#ffa500;border-radius:8px;font-size:0.85rem;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Cet espace est exclusivement réservé aux administrateurs IRS autorisés.
            </div>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-envelope me-1"></i>Email Administrateur</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="admin@irs-server.com" required autocomplete="off" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label"><i class="bi bi-key me-1"></i>Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                        <input type="password" name="password" id="passField" class="form-control" placeholder="••••••••" required>
                        <button type="button" class="btn" style="border:1px solid var(--irs-input-border);background:var(--irs-input-bg);color:var(--irs-text-muted);" onclick="togglePass('passField',this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn w-100 fw-600" style="background:#ffa500;border:none;color:#0d1117;border-radius:8px;padding:0.75rem;font-weight:700;font-size:1rem;">
                    <i class="bi bi-shield-lock me-2"></i>Accéder au panneau admin
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="/index.php" style="font-size:0.85rem;color:var(--irs-text-muted);text-decoration:none;">
                    <i class="bi bi-globe me-1"></i>Retour au site public
                </a>
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
const th = document.cookie.match('(^|;)\\s*theme\\s*=\\s*([^;]+)');
document.documentElement.setAttribute('data-theme', th ? th[2] : 'light');
</script>
</body>
</html>
