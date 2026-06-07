<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$results = [];
$searched = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($_GET['q'])) {
    $searched = true;
    $q       = $conn->real_escape_string(trim($_POST['q'] ?? $_GET['q'] ?? ''));
    $type    = $conn->real_escape_string($_POST['type'] ?? $_GET['type'] ?? 'all');
    $status  = $conn->real_escape_string($_POST['status'] ?? $_GET['status'] ?? '');
    $country = $conn->real_escape_string($_POST['country'] ?? $_GET['country'] ?? '');
    $date_from = $conn->real_escape_string($_POST['date_from'] ?? $_GET['date_from'] ?? '');
    $date_to   = $conn->real_escape_string($_POST['date_to'] ?? $_GET['date_to'] ?? '');

    if ($q || $status || $country || $date_from) {
        // Search documents
        if ($type === 'all' || $type === 'documents') {
            $where = "1=1";
            if ($q) $where .= " AND (document_number LIKE '%$q%' OR holder_name LIKE '%$q%' OR document_type LIKE '%$q%')";
            if ($status) $where .= " AND status='$status'";
            if ($country) $where .= " AND country_of_origin LIKE '%$country%'";
            if ($date_from) $where .= " AND created_at >= '$date_from'";
            if ($date_to) $where .= " AND created_at <= '$date_to 23:59:59'";
            $res = $conn->query("SELECT *, 'official' as doc_source FROM documents WHERE $where LIMIT 50");
            if ($res) while ($r = $res->fetch_assoc()) $results['documents'][] = $r;
        }

        // Search submitted
        if ($type === 'all' || $type === 'submitted') {
            $where2 = "1=1";
            if ($q) $where2 .= " AND (sd.document_number LIKE '%$q%' OR sd.document_name LIKE '%$q%' OR u.first_name LIKE '%$q%' OR u.last_name LIKE '%$q%')";
            if ($status) $where2 .= " AND sd.status='$status'";
            if ($country) $where2 .= " AND sd.country_of_origin LIKE '%$country%'";
            if ($date_from) $where2 .= " AND sd.submitted_at >= '$date_from'";
            if ($date_to) $where2 .= " AND sd.submitted_at <= '$date_to 23:59:59'";
            $res2 = $conn->query("SELECT sd.*, u.first_name, u.last_name, 'submitted' as doc_source FROM submitted_documents sd LEFT JOIN users u ON sd.user_id=u.id WHERE $where2 LIMIT 50");
            if ($res2) while ($r = $res2->fetch_assoc()) $results['submitted'][] = $r;
        }

        // Search users
        if ($type === 'all' || $type === 'users') {
            $where3 = "1=1";
            if ($q) $where3 .= " AND (first_name LIKE '%$q%' OR last_name LIKE '%$q%' OR email LIKE '%$q%')";
            if ($country) $where3 .= " AND country LIKE '%$country%'";
            if ($date_from) $where3 .= " AND created_at >= '$date_from'";
            $res3 = $conn->query("SELECT * FROM users WHERE $where3 LIMIT 50");
            if ($res3) while ($r = $res3->fetch_assoc()) $results['users'][] = $r;
        }
    }
}

$countries = getCountriesList();
$lang = getLang(); $theme = getTheme();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche Avancée - Admin IRS</title>
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
                <div><h1 class="page-title">Recherche Avancée</h1><p class="page-subtitle">Filtrez par numéro, pays, type, date, statut</p></div>
            </div>
        </div>

        <div class="irs-card mb-4">
            <div class="irs-card-header"><h5 class="irs-card-title"><i class="bi bi-funnel" style="color:#ffa500;"></i>Filtres de recherche</h5></div>
            <div class="irs-card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Mot-clé</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="q" class="form-control" placeholder="Numéro, nom, email..." value="<?= htmlspecialchars($_POST['q'] ?? $_GET['q'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Type de recherche</label>
                            <select name="type" class="form-select">
                                <option value="all">Tout</option>
                                <option value="documents" <?= ($_POST['type'] ?? '') === 'documents' ? 'selected' : '' ?>>Documents officiels</option>
                                <option value="submitted" <?= ($_POST['type'] ?? '') === 'submitted' ? 'selected' : '' ?>>Documents soumis</option>
                                <option value="users" <?= ($_POST['type'] ?? '') === 'users' ? 'selected' : '' ?>>Utilisateurs</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Statut</label>
                            <select name="status" class="form-select">
                                <option value="">Tous</option>
                                <option value="verified">Vérifié</option>
                                <option value="pending">En attente</option>
                                <option value="validated">Validé</option>
                                <option value="rejected">Rejeté</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Pays</label>
                            <input type="text" name="country" class="form-control" placeholder="ex: France" value="<?= htmlspecialchars($_POST['country'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date du</label>
                            <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($_POST['date_from'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date au</label>
                            <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($_POST['date_to'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 d-flex align-items-end gap-2">
                            <button type="submit" class="btn" style="background:#ffa500;color:#0d1117;border-radius:8px;font-weight:600;padding:0.6rem 1.5rem;">
                                <i class="bi bi-search me-2"></i>Rechercher
                            </button>
                            <a href="/admin/search.php" class="btn btn-outline-secondary" style="border-radius:8px;">
                                <i class="bi bi-x me-1"></i>Réinitialiser
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($searched): ?>

        <?php if (!empty($results['documents'])): ?>
        <div class="irs-card mb-4">
            <div class="irs-card-header"><h5 class="irs-card-title"><i class="bi bi-file-earmark-check" style="color:#198754;"></i>Documents officiels (<?= count($results['documents']) ?>)</h5></div>
            <div class="table-wrap">
                <table class="irs-table w-100">
                    <thead><tr><th>Numéro</th><th>Titulaire</th><th>Type</th><th>Pays</th><th>Statut</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php foreach ($results['documents'] as $d): ?>
                    <tr>
                        <td><code style="font-size:0.82rem;"><?= htmlspecialchars($d['document_number']) ?></code></td>
                        <td><?= htmlspecialchars($d['holder_name']) ?></td>
                        <td><?= htmlspecialchars($d['document_type']) ?></td>
                        <td><?= htmlspecialchars($d['country_of_origin']) ?></td>
                        <td><?php echo $d['status']==='verified' ? '<span class="badge bg-success">Vérifié</span>' : '<span class="badge bg-danger">Rejeté</span>'; ?></td>
                        <td><?= formatDate($d['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($results['submitted'])): ?>
        <div class="irs-card mb-4">
            <div class="irs-card-header"><h5 class="irs-card-title"><i class="bi bi-clipboard-data" style="color:var(--irs-blue);"></i>Documents soumis (<?= count($results['submitted']) ?>)</h5></div>
            <div class="table-wrap">
                <table class="irs-table w-100">
                    <thead><tr><th>Document</th><th>Numéro</th><th>Utilisateur</th><th>Type</th><th>Statut</th><th>Soumis</th></tr></thead>
                    <tbody>
                    <?php foreach ($results['submitted'] as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['document_name']) ?></td>
                        <td><code style="font-size:0.82rem;"><?= htmlspecialchars($s['document_number']) ?></code></td>
                        <td><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></td>
                        <td><?= htmlspecialchars($s['document_type']) ?></td>
                        <td><?= getStatusBadge($s['status']) ?></td>
                        <td><?= formatDateTime($s['submitted_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($results['users'])): ?>
        <div class="irs-card">
            <div class="irs-card-header"><h5 class="irs-card-title"><i class="bi bi-people" style="color:var(--irs-blue);"></i>Utilisateurs (<?= count($results['users']) ?>)</h5></div>
            <div class="table-wrap">
                <table class="irs-table w-100">
                    <thead><tr><th>Nom</th><th>Email</th><th>Pays</th><th>Statut</th><th>Inscrit</th></tr></thead>
                    <tbody>
                    <?php foreach ($results['users'] as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['country'] ?: '-') ?></td>
                        <td><span class="badge <?= $u['status']==='active'?'bg-success':'bg-warning text-dark' ?>"><?= $u['status'] ?></span></td>
                        <td><?= formatDate($u['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($results)): ?>
        <div class="empty-state"><i class="bi bi-search"></i><p>Aucun résultat pour cette recherche.</p></div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
</body></html>
