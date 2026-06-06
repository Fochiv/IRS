<?php
require_once __DIR__ . '/includes/functions.php';
if (isUserLoggedIn()) redirect('/dashboard/index.php');

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($conn->real_escape_string($_POST['email'] ?? ''));
    if (empty($email)) {
        $error = 'Veuillez saisir votre adresse email.';
    } else {
        $result = $conn->query("SELECT id, first_name FROM users WHERE email = '$email' LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $success = 'Si cet email existe dans notre système, vous recevrez un lien de réinitialisation. Vérifiez votre boite mail.';
        } else {
            $success = 'Si cet email existe dans notre système, vous recevrez un lien de réinitialisation. Vérifiez votre boite mail.';
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
    <title>Mot de passe oublié - <?= t('site_name') ?></title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <img src="/logo.png" alt="IRS">
            <h4>Mot de passe oublié</h4>
            <p>Saisissez votre email pour recevoir un lien de réinitialisation.</p>
        </div>
        <div class="auth-body">
            <?php if ($error): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?= $success ?></div>
            <?php else: ?>
            <form method="POST">
                <div class="mb-4">
                    <label class="form-label">Adresse Email *</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="votre@email.com" required>
                    </div>
                </div>
                <button type="submit" class="btn-irs-primary btn">
                    <i class="bi bi-send me-2"></i>Envoyer le lien
                </button>
            </form>
            <?php endif; ?>
            <div class="text-center mt-3">
                <a href="/login.php" style="font-size:0.85rem;color:var(--irs-text-muted);text-decoration:none;">
                    <i class="bi bi-arrow-left me-1"></i>Retour à la connexion
                </a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const t = document.cookie.match('(^|;)\\s*theme\\s*=\\s*([^;]+)');
document.documentElement.setAttribute('data-theme', t ? t[2] : 'light');
</script>
</body>
</html>
