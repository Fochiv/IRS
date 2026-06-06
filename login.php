<?php
require_once __DIR__ . '/includes/functions.php';

if (isUserLoggedIn()) redirect('/dashboard/index.php');

$error = '';
$success = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'registered') {
    $success = 'Compte créé avec succès. Vous pouvez maintenant vous connecter.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($conn->real_escape_string($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $result = $conn->query("SELECT * FROM users WHERE email = '$email' LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($user['password'] === $password) {
                if ($user['status'] === 'suspended') {
                    $error = 'Votre compte a été suspendu. Contactez l\'administrateur.';
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $conn->query("UPDATE users SET last_login = NOW() WHERE id = {$user['id']}");
                    logActivity($conn, 'user_login', 'Connexion utilisateur: ' . $email, $user['id']);
                    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '/dashboard/index.php';
                    if (!preg_match('/^\//', $redirect)) $redirect = '/dashboard/index.php';
                    redirect($redirect);
                }
            } else {
                $error = 'Email ou mot de passe incorrect.';
            }
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}

$page_title = t('login');
$lang = getLang();
$theme = getTheme();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('login') ?> - <?= t('site_name') ?></title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <img src="/logo.png" alt="IRS Logo">
            <h4><?= t('welcome_back') ?></h4>
            <p><?= t('sign_in') ?> - <?= t('site_name') ?></p>
        </div>
        <div class="auth-body">
            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-envelope me-1"></i><?= t('email') ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="votre@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-lock me-1"></i><?= t('password') ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="passField" class="form-control" placeholder="••••••••" required>
                        <button type="button" class="btn" style="border:1px solid var(--irs-input-border);background:var(--irs-input-bg);color:var(--irs-text-muted);" onclick="togglePass('passField',this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember" style="font-size:0.85rem;color:var(--irs-text-muted);">Se souvenir de moi</label>
                    </div>
                    <a href="/forgot-password.php" style="font-size:0.85rem;color:var(--irs-blue);text-decoration:none;"><?= t('forgot_password') ?></a>
                </div>
                <button type="submit" class="btn-irs-primary btn">
                    <i class="bi bi-box-arrow-in-right me-2"></i><?= t('sign_in') ?>
                </button>
            </form>

            <div class="text-center mt-4" style="font-size:0.9rem;color:var(--irs-text-muted);">
                <?= t('no_account') ?>
                <a href="/register.php" style="color:var(--irs-blue);text-decoration:none;font-weight:600;"><?= t('sign_up') ?></a>
            </div>
            <div class="text-center mt-2">
                <a href="/index.php" style="font-size:0.85rem;color:var(--irs-text-muted);text-decoration:none;">
                    <i class="bi bi-arrow-left me-1"></i>Retour à l'accueil
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
const t = document.documentElement.getAttribute('data-theme') || (document.cookie.match('(^|;)\\s*theme\\s*=\\s*([^;]+)')||[])[2] || 'light';
document.documentElement.setAttribute('data-theme', t);
</script>
</body>
</html>
