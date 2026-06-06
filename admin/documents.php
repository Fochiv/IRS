<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$success = ''; $error = '';
$countries = getCountriesList();
$doc_types = ['Diplôme','Baccalauréat','Passeport','Carte d\'identité','Acte de naissance','Acte de mariage','Attestation de travail','Certificat médical','Relevé de notes','Contrat','Permis de conduire','Visa','Autre'];

if (isset($_GET['action']) && isset($_GET['id'])) {
    $did = (int)$_GET['id'];
    if ($_GET['action'] === 'delete') {
        $conn->query("DELETE FROM documents WHERE id=$did");
        $success = 'Document supprimé.';
        logActivity($conn, 'admin_delete_doc', "Suppression document ID: $did", null, $_SESSION['admin_id']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_document'])) {
    $dn  = $conn->real_escape_string($_POST['document_number'] ?? '');
    $hn  = $conn->real_escape_string($_POST['holder_name'] ?? '');
    $dt  = $conn->real_escape_string($_POST['document_type'] ?? '');
    $io  = $conn->real_escape_string($_POST['issuing_organization'] ?? '');
    $id_date = $conn->real_escape_string($_POST['issue_date'] ?? '');
    $co  = $conn->real_escape_string($_POST['country_of_origin'] ?? '');
    $desc= $conn->real_escape_string($_POST['description'] ?? '');
    $st  = $conn->real_escape_string($_POST['status'] ?? 'verified');
    $aid = (int)$_SESSION['admin_id'];

    if ($dn && $hn && $dt && $io) {
        $check = $conn->query("SELECT id FROM documents WHERE document_number='$dn'");
        if ($check && $check->num_rows > 0) {
            $error = 'Ce numéro de document existe déjà.';
        } else {
            $file_path = '';
            if (!empty($_FILES['doc_file']['name'])) {
                $ext = strtolower(pathinfo($_FILES['doc_file']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['pdf','jpg','jpeg','png'])) {
                    $fname = uniqid().'_'.time().'.'.$ext;
                    $dest = __DIR__ . '/../uploads/documents/' . $fname;
                    if (move_uploaded_file($_FILES['doc_file']['tmp_name'], $dest)) {
                        $file_path = 'uploads/documents/' . $fname;
                    }
                }
            }
            $fp = $conn->real_escape_string($file_path);
            $conn->query("INSERT INTO documents (document_number,holder_name,document_type,issuing_organization,issue_date,country_of_origin,description,status,file_path,added_by) VALUES ('$dn','$hn','$dt','$io','$id_date','$co','$desc','$st','$fp',$aid)");
            $success = 'Document officiel ajouté.';
            logActivity($conn, 'admin_add_doc', "Ajout document: $dn", null, $aid);
        }
    } else {
        $error = 'Champs obligatoires manquants.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_document'])) {
    $did = (int)$_POST['doc_id'];
    $hn = $conn->real_escape_string($_POST['holder_name'] ?? '');
    $dt = $conn->real_escape_string($_POST['document_type'] ?? '');
    $io = $conn->real_escape_string($_POST['issuing_organization'] ?? '');
    $id_date = $conn->real_escape_string($_POST['issue_date'] ?? '');
    $co = $conn->real_escape_string($_POST['country_of_origin'] ?? '');
    $st = $conn->real_escape_string($_POST['status'] ?? 'verified');
    $desc = $conn->real_escape_string($_POST['description'] ?? '');
    $conn->query("UPDATE documents SET holder_name='$hn',document_type='$dt',issuing_organization='$io',issue_date='$id_date',country_of_origin='$co',status='$st',description='$desc' WHERE id=$did");
    $success = 'Document modifié.';
}

$search = $conn->real_escape_string($_GET['search'] ?? '');
$where = $search ? "WHERE document_number LIKE '%$search%' OR holder_name LIKE '%$search%' OR document_type LIKE '%$search%'" : '';
$docs = $conn->query("SELECT * FROM documents $where ORDER BY created_at ASC");
$lang = getLang(); $theme = getTheme();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents Officiels - Admin IRS</title>
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
                <div><h1 class="page-title">Documents Officiels</h1><p class="page-subtitle">Registre sécurisé IRS</p></div>
            </div>
            <button class="btn btn-sm" style="background:#ffa500;color:#0d1117;border-radius:8px;font-weight:600;" data-bs-toggle="modal" data-bs-target="#addDocModal">
                <i class="bi bi-plus-circle me-1"></i>Ajouter un document
            </button>
        </div>

        <?php if ($success): ?><div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

        <div class="irs-card mb-3">
            <div class="irs-card-body py-2">
                <form method="GET" class="d-flex gap-2">
                    <div class="search-box flex-grow-1" style="max-width:400px;">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" placeholder="Rechercher par numéro, titulaire, type..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-sm" style="background:var(--irs-blue);color:white;border-radius:8px;">Rechercher</button>
                    <?php if ($search): ?><a href="/admin/documents.php" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">Réinitialiser</a><?php endif; ?>
                </form>
            </div>
        </div>

        <div class="irs-card">
            <div style="overflow-x:auto;">
                <?php if ($docs && $docs->num_rows > 0): ?>
                <table class="irs-table w-100">
                    <thead><tr><th>#</th><th>Numéro</th><th>Titulaire</th><th>Type</th><th>Organisme</th><th>Pays</th><th>Date émission</th><th>Statut</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php $n=1; while ($d = $docs->fetch_assoc()): ?>
                        <tr>
                            <td><?= $n++ ?></td>
                            <td><code style="font-size:0.82rem;background:var(--irs-gray);padding:2px 6px;border-radius:4px;"><?= htmlspecialchars($d['document_number']) ?></code></td>
                            <td><?= htmlspecialchars($d['holder_name']) ?></td>
                            <td><?= htmlspecialchars($d['document_type']) ?></td>
                            <td><?= htmlspecialchars($d['issuing_organization']) ?></td>
                            <td><?= htmlspecialchars($d['country_of_origin']) ?></td>
                            <td><?= formatDate($d['issue_date']) ?></td>
                            <td><?php echo $d['status']==='verified' ? '<span class="badge bg-success"><i class="bi bi-patch-check-fill me-1"></i>Vérifié</span>' : '<span class="badge bg-danger"><i class="bi bi-x-circle-fill me-1"></i>Rejeté</span>'; ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-primary" style="border-radius:6px;" data-bs-toggle="modal" data-bs-target="#editDoc<?= $d['id'] ?>" title="Modifier"><i class="bi bi-pencil"></i></button>
                                    <?php if ($d['file_path']): ?><a href="/<?= htmlspecialchars($d['file_path']) ?>" class="btn btn-sm btn-outline-success" style="border-radius:6px;" target="_blank" title="Télécharger"><i class="bi bi-download"></i></a><?php endif; ?>
                                    <a href="/admin/documents.php?action=delete&id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-danger" style="border-radius:6px;" title="Supprimer" onclick="return confirm('Supprimer ce document ?')"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editDoc<?= $d['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header"><h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier le document</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="doc_id" value="<?= $d['id'] ?>">
                                            <div class="row g-3">
                                                <div class="col-md-6"><label class="form-label">Titulaire *</label><input type="text" name="holder_name" class="form-control" value="<?= htmlspecialchars($d['holder_name']) ?>" required></div>
                                                <div class="col-md-6"><label class="form-label">Type *</label><select name="document_type" class="form-select" required><?php foreach($doc_types as $dt): ?><option value="<?=$dt?>" <?=$d['document_type']===$dt?'selected':''?>><?=$dt?></option><?php endforeach; ?></select></div>
                                                <div class="col-md-6"><label class="form-label">Organisme *</label><input type="text" name="issuing_organization" class="form-control" value="<?= htmlspecialchars($d['issuing_organization']) ?>" required></div>
                                                <div class="col-md-6"><label class="form-label">Date émission</label><input type="date" name="issue_date" class="form-control" value="<?= $d['issue_date'] ?>"></div>
                                                <div class="col-md-6"><label class="form-label">Pays</label><select name="country_of_origin" class="form-select"><?php foreach($countries as $c): ?><option value="<?=$c?>" <?=$d['country_of_origin']===$c?'selected':''?>><?=$c?></option><?php endforeach; ?></select></div>
                                                <div class="col-md-6"><label class="form-label">Statut</label><select name="status" class="form-select"><option value="verified" <?=$d['status']==='verified'?'selected':''?>>Vérifié</option><option value="rejected" <?=$d['status']==='rejected'?'selected':''?>>Rejeté</option></select></div>
                                                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($d['description']) ?></textarea></div>
                                            </div>
                                        </div>
                                        <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button><button type="submit" name="edit_document" class="btn btn-primary btn-sm">Sauvegarder</button></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state"><i class="bi bi-file-earmark-x"></i><p>Aucun document dans le registre.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Document Modal -->
<div class="modal fade" id="addDocModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Ajouter au registre officiel</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Numéro unique *</label><input type="text" name="document_number" class="form-control" required placeholder="ex: IRS-2024-001234"></div>
                        <div class="col-md-6"><label class="form-label">Titulaire *</label><input type="text" name="holder_name" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Type *</label><select name="document_type" class="form-select" required><option value="">-- Sélectionner --</option><?php foreach($doc_types as $dt): ?><option value="<?=$dt?>"><?=$dt?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label class="form-label">Organisme émetteur *</label><input type="text" name="issuing_organization" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Date d'émission</label><input type="date" name="issue_date" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Pays d'origine</label><select name="country_of_origin" class="form-select"><option value="">-- Sélectionner --</option><?php foreach($countries as $c): ?><option value="<?=$c?>"><?=$c?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label class="form-label">Statut</label><select name="status" class="form-select"><option value="verified">Vérifié</option><option value="rejected">Rejeté</option></select></div>
                        <div class="col-md-6"><label class="form-label">Fichier (PDF/Image)</label><input type="file" name="doc_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button><button type="submit" name="add_document" class="btn btn-sm" style="background:#ffa500;color:#0d1117;font-weight:600;">Ajouter au registre</button></div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
</body></html>
