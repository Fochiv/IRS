<?php
require_once __DIR__ . '/../includes/functions.php';
requireUserLogin();
$user = getUserById($conn, $_SESSION['user_id']);

$error = ''; $success = '';
$countries = getCountriesList();

$doc_types = ['Diplôme', 'Baccalauréat', 'Passeport', 'Carte d\'identité', 'Acte de naissance', 'Acte de mariage', 'Attestation de travail', 'Certificat médical', 'Relevé de notes', 'Contrat', 'Permis de conduire', 'Visa', 'Autre'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doc_name  = trim($conn->real_escape_string($_POST['doc_name'] ?? ''));
    $doc_number = trim($conn->real_escape_string($_POST['doc_number'] ?? ''));
    $doc_type  = trim($conn->real_escape_string($_POST['doc_type'] ?? ''));
    $issuing   = trim($conn->real_escape_string($_POST['issuing_org'] ?? ''));
    $country   = trim($conn->real_escape_string($_POST['country'] ?? ''));
    $desc      = trim($conn->real_escape_string($_POST['description'] ?? ''));
    $uid       = (int)$_SESSION['user_id'];

    if (empty($doc_name) || empty($doc_number) || empty($doc_type) || empty($issuing)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        $file_path = ''; $img_path = '';

        if (!empty($_FILES['doc_pdf']['name'])) {
            $allowed_pdf = ['pdf'];
            $ext = strtolower(pathinfo($_FILES['doc_pdf']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_pdf)) {
                $error = 'Le fichier PDF doit être au format .pdf';
            } else {
                $fname = uniqid() . '_' . time() . '.pdf';
                $dest = __DIR__ . '/../uploads/documents/' . $fname;
                if (move_uploaded_file($_FILES['doc_pdf']['tmp_name'], $dest)) {
                    $file_path = 'uploads/documents/' . $fname;
                }
            }
        }

        if (empty($error) && !empty($_FILES['doc_image']['name'])) {
            $allowed_img = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES['doc_image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_img)) {
                $error = 'L\'image doit être au format jpg, jpeg, png ou gif.';
            } else {
                $fname = uniqid() . '_' . time() . '.' . $ext;
                $dest = __DIR__ . '/../uploads/images/' . $fname;
                if (move_uploaded_file($_FILES['doc_image']['tmp_name'], $dest)) {
                    $img_path = 'uploads/images/' . $fname;
                }
            }
        }

        if (empty($error)) {
            $conn->query("INSERT INTO submitted_documents (user_id, document_name, document_number, document_type, issuing_organization, country_of_origin, description, file_path, image_path) VALUES ($uid, '$doc_name', '$doc_number', '$doc_type', '$issuing', '$country', '$desc', '$file_path', '$img_path')");
            $new_id = $conn->insert_id;
            createNotification($conn, $uid, 'Document reçu', "Votre document \"$doc_name\" a été reçu et est en cours d'analyse.", 'info');
            logActivity($conn, 'document_submit', "Document soumis: $doc_name ($doc_number)", $uid);
            redirect('/dashboard/documents.php?msg=submitted');
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
    <title>Soumettre un document - <?= t('site_name') ?></title>
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
                    <h1 class="page-title">Soumettre un Document</h1>
                    <p class="page-subtitle">Demandez une vérification approfondie de votre document</p>
                </div>
            </div>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="irs-card">
                    <div class="irs-card-header">
                        <h5 class="irs-card-title"><i class="bi bi-upload text-irs-blue"></i>Informations du document</h5>
                    </div>
                    <div class="irs-card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label"><i class="bi bi-file-text me-1"></i><?= t('doc_name') ?> *</label>
                                    <input type="text" name="doc_name" class="form-control" placeholder="Nom du document" required value="<?= htmlspecialchars($_POST['doc_name'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><i class="bi bi-hash me-1"></i><?= t('doc_number') ?> *</label>
                                    <input type="text" name="doc_number" class="form-control" placeholder="Numéro unique du document" required value="<?= htmlspecialchars($_POST['doc_number'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><i class="bi bi-tag me-1"></i><?= t('doc_type') ?> *</label>
                                    <select name="doc_type" class="form-select" required>
                                        <option value="">-- Sélectionner le type --</option>
                                        <?php foreach ($doc_types as $dt): ?>
                                        <option value="<?= $dt ?>" <?= (($_POST['doc_type'] ?? '') === $dt) ? 'selected' : '' ?>><?= $dt ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><i class="bi bi-building me-1"></i><?= t('issuing_org') ?> *</label>
                                    <input type="text" name="issuing_org" class="form-control" placeholder="Organisme émetteur" required value="<?= htmlspecialchars($_POST['issuing_org'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><i class="bi bi-geo-alt me-1"></i><?= t('country_origin') ?></label>
                                    <select name="country" class="form-select">
                                        <option value="">-- Sélectionner --</option>
                                        <?php foreach ($countries as $code => $name): ?>
                                        <option value="<?= $name ?>" <?= (($_POST['country'] ?? '') === $name) ? 'selected' : '' ?>><?= $name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label"><i class="bi bi-text-paragraph me-1"></i><?= t('description') ?></label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="Description du document (optionnel)"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><i class="bi bi-file-pdf me-1"></i><?= t('upload_pdf') ?></label>
                                    <input type="file" name="doc_pdf" class="form-control" accept=".pdf">
                                    <div class="form-text">Format PDF uniquement. Max 10 Mo.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><i class="bi bi-image me-1"></i><?= t('upload_image') ?></label>
                                    <input type="file" name="doc_image" class="form-control" accept=".jpg,.jpeg,.png,.gif">
                                    <div class="form-text">Format JPG, PNG. Max 10 Mo.</div>
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-info" style="font-size:0.9rem;">
                                        <i class="bi bi-info-circle-fill me-2"></i>
                                        Votre document sera analysé par notre système IA et nos experts qualifiés. Vous serez notifié à chaque étape du processus.
                                    </div>
                                </div>
                                <div class="col-12 d-flex gap-3">
                                    <button type="submit" class="btn" style="background:var(--irs-blue);color:white;border-radius:8px;padding:0.65rem 1.5rem;font-weight:600;">
                                        <i class="bi bi-send me-2"></i><?= t('submit_verification') ?>
                                    </button>
                                    <a href="/dashboard/index.php" class="btn btn-outline-secondary" style="border-radius:8px;">
                                        <i class="bi bi-x me-1"></i>Annuler
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
</body>
</html>
