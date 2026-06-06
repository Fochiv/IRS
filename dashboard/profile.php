<?php
require_once __DIR__ . '/../includes/functions.php';
requireUserLogin();
$user = getUserById($conn, $_SESSION['user_id']);

$success = ''; $error = '';
$countries = getCountriesList();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first  = trim($conn->real_escape_string($_POST['first_name'] ?? ''));
    $last   = trim($conn->real_escape_string($_POST['last_name'] ?? ''));
    $phone  = trim($conn->real_escape_string($_POST['phone'] ?? ''));
    $country= trim($conn->real_escape_string($_POST['country'] ?? ''));
    $uid    = (int)$_SESSION['user_id'];

    if (empty($first) || empty($last)) {
        $error = 'Prénom et nom sont obligatoires.';
    } else {
        $conn->query("UPDATE users SET first_name='$first', last_name='$last', phone='$phone', country='$country' WHERE id=$uid");
        $_SESSION['user_name'] = $first . ' ' . $last;
        $user = getUserById($conn, $uid);
        $success = 'Profil mis à jour avec succès.';
    }

    if (!empty($_POST['new_password'])) {
        $curr = trim($_POST['current_password'] ?? '');
        $new  = trim($_POST['new_password'] ?? '');
        $conf = trim($_POST['confirm_password'] ?? '');
        if ($curr !== $user['password']) {
            $error = 'Mot de passe actuel incorrect.';
        } elseif ($new !== $conf) {
            $error = 'Les nouveaux mots de passe ne correspondent pas.';
        } elseif (strlen($new) < 6) {
            $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
        } else {
            $new_esc = $conn->real_escape_string($new);
            $uid = (int)$_SESSION['user_id'];
            $conn->query("UPDATE users SET password='$new_esc' WHERE id=$uid");
            $success = 'Profil et mot de passe mis à jour avec succès.';
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
    <title><?= t('profile') ?> - <?= t('site_name') ?></title>
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
                <button class="sidebar-toggle-btn" id="sidebarToggle"><i class="bi bi-list"></i></button>
                <div>
                    <h1 class="page-title"><?= t('profile') ?></h1>
                    <p class="page-subtitle">Gérez vos informations personnelles</p>
                </div>
            </div>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- PROFILE CARD -->
            <div class="col-lg-4">
                <div class="irs-card text-center">
                    <div class="irs-card-body">
                        <div style="width:90px;height:90px;background:linear-gradient(135deg,var(--irs-blue),#1a70c8);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:2rem;font-weight:800;color:white;">
                            <?= strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)) ?>
                        </div>
                        <h5 style="font-weight:700;color:var(--irs-text);"><?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></h5>
                        <p style="color:var(--irs-text-muted);font-size:0.9rem;"><?= htmlspecialchars($user['email']) ?></p>
                        <div class="divider"></div>
                        <div class="text-start" style="font-size:0.9rem;">
                            <div class="info-row"><span class="info-label"><i class="bi bi-telephone"></i>Téléphone</span><span class="info-value"><?= htmlspecialchars($user['phone'] ?: '-') ?></span></div>
                            <div class="info-row"><span class="info-label"><i class="bi bi-geo-alt"></i>Pays</span><span class="info-value"><?= htmlspecialchars($user['country'] ?: '-') ?></span></div>
                            <div class="info-row"><span class="info-label"><i class="bi bi-calendar"></i>Inscrit le</span><span class="info-value"><?= formatDate($user['created_at']) ?></span></div>
                            <div class="info-row"><span class="info-label"><i class="bi bi-clock"></i>Dernière co.</span><span class="info-value"><?= formatDateTime($user['last_login']) ?></span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EDIT FORM -->
            <div class="col-lg-8">
                <form method="POST">
                    <div class="irs-card mb-4">
                        <div class="irs-card-header">
                            <h5 class="irs-card-title"><i class="bi bi-person text-irs-blue"></i>Informations personnelles</h5>
                        </div>
                        <div class="irs-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label"><?= t('first_name') ?> *</label>
                                    <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($user['first_name']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= t('last_name') ?> *</label>
                                    <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($user['last_name']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= t('email') ?></label>
                                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                                    <div class="form-text">L'email ne peut pas être modifié.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= t('phone') ?></label>
                                    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label"><?= t('country') ?></label>
                                    <select name="country" class="form-select">
                                        <option value="">-- Sélectionner --</option>
                                        <?php foreach ($countries as $code => $name): ?>
                                        <option value="<?= $name ?>" <?= ($user['country'] === $name) ? 'selected' : '' ?>><?= $name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="irs-card mb-4">
                        <div class="irs-card-header">
                            <h5 class="irs-card-title"><i class="bi bi-lock text-irs-blue"></i>Changer le mot de passe</h5>
                        </div>
                        <div class="irs-card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label"><?= t('current_password') ?></label>
                                    <input type="password" name="current_password" class="form-control" placeholder="Mot de passe actuel">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= t('new_password') ?></label>
                                    <input type="password" name="new_password" class="form-control" placeholder="Nouveau mot de passe">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= t('confirm_password') ?></label>
                                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirmer le nouveau mot de passe">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn" style="background:var(--irs-blue);color:white;border-radius:8px;padding:0.65rem 2rem;font-weight:600;">
                        <i class="bi bi-check2 me-2"></i><?= t('save_changes') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
</body>
</html>
