<?php
require_once __DIR__ . '/includes/functions.php';

if (isUserLoggedIn()) redirect('/dashboard/index.php');

$error = '';
$countries = getCountriesList();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($conn->real_escape_string($_POST['first_name'] ?? ''));
    $last_name   = trim($conn->real_escape_string($_POST['last_name'] ?? ''));
    $email       = trim($conn->real_escape_string($_POST['email'] ?? ''));
    $phone       = trim($conn->real_escape_string($_POST['phone'] ?? ''));
    $country     = trim($conn->real_escape_string($_POST['country'] ?? ''));
    $password    = trim($_POST['password'] ?? '');
    $confirm     = trim($_POST['confirm_password'] ?? '');

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif ($password !== $confirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        $check = $conn->query("SELECT id FROM users WHERE email = '$email' LIMIT 1");
        if ($check && $check->num_rows > 0) {
            $error = 'Cette adresse email est déjà utilisée.';
        } else {
            $pass_esc = $conn->real_escape_string($password);
            $conn->query("INSERT INTO users (first_name, last_name, email, phone, country, password) VALUES ('$first_name', '$last_name', '$email', '$phone', '$country', '$pass_esc')");
            $new_id = $conn->insert_id;
            logActivity($conn, 'user_register', 'Nouvel utilisateur: ' . $email, $new_id);
            redirect('/login.php?msg=registered');
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
    <title><?= t('register') ?> - <?= t('site_name') ?></title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card" style="max-width:520px;">
        <div class="auth-header">
            <img src="/logo.png" alt="IRS Logo">
            <h4><?= t('create_your_account') ?></h4>
            <p><?= t('site_name') ?></p>
        </div>
        <div class="auth-body">
            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label"><?= t('first_name') ?> *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label"><?= t('last_name') ?> *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label"><?= t('email') ?> *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label"><?= t('phone') ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label"><?= t('country') ?></label>
                        <select name="country" class="form-select">
                            <option value="">-- Sélectionner --</option>
                            <?php foreach ($countries as $code => $name): ?>
                            <option value="<?= $name ?>" <?= (($_POST['country'] ?? '') === $name) ? 'selected' : '' ?>><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label"><?= t('password') ?> *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="pass1" class="form-control" placeholder="Min. 6 caractères" required>
                            <button type="button" class="btn" style="border:1px solid var(--irs-input-border);background:var(--irs-input-bg);color:var(--irs-text-muted);" onclick="togglePass('pass1',this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label"><?= t('confirm_password') ?> *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="confirm_password" id="pass2" class="form-control" placeholder="Répétez le mot de passe" required>
                            <button type="button" class="btn" style="border:1px solid var(--irs-input-border);background:var(--irs-input-bg);color:var(--irs-text-muted);" onclick="togglePass('pass2',this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms" style="font-size:0.85rem;color:var(--irs-text-muted);">
                                J'accepte les <a href="/terms.php" target="_blank" style="color:var(--irs-blue);">conditions d'utilisation</a> et la <a href="/privacy.php" target="_blank" style="color:var(--irs-blue);">politique de confidentialité</a>.
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn-irs-primary btn">
                            <i class="bi bi-person-plus me-2"></i><?= t('sign_up') ?>
                        </button>
                    </div>
                </div>
            </form>

            <div class="text-center mt-3" style="font-size:0.9rem;color:var(--irs-text-muted);">
                <?= t('have_account') ?>
                <a href="/login.php" style="color:var(--irs-blue);text-decoration:none;font-weight:600;"><?= t('sign_in') ?></a>
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
const t2 = document.cookie.match('(^|;)\\s*theme\\s*=\\s*([^;]+)');
document.documentElement.setAttribute('data-theme', t2 ? t2[2] : 'light');
</script>
</body>
</html>
