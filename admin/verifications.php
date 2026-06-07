<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vid = (int)($_POST['submission_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $reason = $conn->real_escape_string($_POST['reason'] ?? '');
    $admin_id = (int)$_SESSION['admin_id'];

    if ($vid > 0) {
        $sub = $conn->query("SELECT * FROM submitted_documents WHERE id=$vid")->fetch_assoc();
        if ($sub) {
            if ($action === 'validate') {
                $conn->query("UPDATE submitted_documents SET status='validated',reviewed_by=$admin_id,reviewed_at=NOW(),rejection_reason=NULL WHERE id=$vid");
                createNotification($conn, $sub['user_id'], 'Document authentique !', "Votre document \"{$sub['document_name']}\" est un document authentique.", 'success');
                logActivity($conn, 'admin_validate_doc', "Validation soumission ID: $vid", null, $admin_id);
                $success = 'Document validé.';
            } elseif ($action === 'reject') {
                if (empty($reason)) {
                    $error = 'Veuillez préciser le motif du rejet.';
                } else {
                    $conn->query("UPDATE submitted_documents SET status='rejected',reviewed_by=$admin_id,reviewed_at=NOW(),rejection_reason='$reason' WHERE id=$vid");
                    createNotification($conn, $sub['user_id'], 'Document rejeté', "Votre document \"{$sub['document_name']}\" a été rejeté. Motif: $reason", 'danger');
                    logActivity($conn, 'admin_reject_doc', "Rejet soumission ID: $vid", null, $admin_id);
                    $success = 'Document rejeté.';
                }
            } elseif ($action === 'info_requested') {
                $conn->query("UPDATE submitted_documents SET status='info_requested',admin_notes='$reason' WHERE id=$vid");
                createNotification($conn, $sub['user_id'], 'Informations requises', "Des informations complémentaires sont requises pour votre document \"{$sub['document_name']}\": $reason", 'warning');
                $success = 'Demande d\'information envoyée.';
            }
        }
    }
}

$search = $conn->real_escape_string($_GET['search'] ?? '');
$status_filter = $conn->real_escape_string($_GET['status'] ?? '');
$where = "1=1";
if ($search) $where .= " AND (sd.document_name LIKE '%$search%' OR sd.document_number LIKE '%$search%' OR u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%')";
if ($status_filter) $where .= " AND sd.status='$status_filter'";

$subs = $conn->query("SELECT sd.*, u.first_name, u.last_name, u.email FROM submitted_documents sd LEFT JOIN users u ON sd.user_id=u.id WHERE $where ORDER BY sd.submitted_at DESC");

$reject_reasons = ['Informations incohérentes', 'Document falsifié', 'Document incomplet', 'Qualité insuffisante', 'Numéro invalide', 'Autre'];
$lang = getLang(); $theme = getTheme();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérifications Soumises - Admin IRS</title>
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
                <div><h1 class="page-title">Vérifications Soumises</h1><p class="page-subtitle">Gérez les demandes de vérification</p></div>
            </div>
        </div>

        <?php if ($success): ?><div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

        <div class="irs-card mb-3">
            <div class="irs-card-body py-2">
                <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
                    <div class="search-box flex-grow-1" style="max-width:350px;">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                    <select name="status" class="form-select form-select-sm" style="max-width:160px;border-radius:8px;">
                        <option value="">Tous les statuts</option>
                        <option value="pending" <?= $status_filter==='pending'?'selected':'' ?>>En attente</option>
                        <option value="validated" <?= $status_filter==='validated'?'selected':'' ?>>Validés</option>
                        <option value="rejected" <?= $status_filter==='rejected'?'selected':'' ?>>Rejetés</option>
                        <option value="info_requested" <?= $status_filter==='info_requested'?'selected':'' ?>>Info requise</option>
                    </select>
                    <button type="submit" class="btn btn-sm" style="background:var(--irs-blue);color:white;border-radius:8px;">Filtrer</button>
                    <a href="/admin/verifications.php" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">Réinit.</a>
                </form>
            </div>
        </div>

        <div class="irs-card">
            <div class="table-wrap">
                <?php if ($subs && $subs->num_rows > 0): ?>
                <table class="irs-table w-100">
                    <thead><tr>
                        <th>#</th>
                        <th>Document</th>
                        <th>Utilisateur</th>
                        <th>Type</th>
                        <th>Soumis le</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr></thead>
                    <tbody>
                        <?php $n=1; while ($s = $subs->fetch_assoc()): ?>
                        <tr>
                            <td><?= $n++ ?></td>
                            <td>
                                <div style="font-weight:600;font-size:0.9rem;"><?= htmlspecialchars($s['document_name']) ?></div>
                                <code style="font-size:0.78rem;color:var(--irs-text-muted);"><?= htmlspecialchars($s['document_number']) ?></code>
                            </td>
                            <td>
                                <div style="font-size:0.85rem;"><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></div>
                                <div style="font-size:0.78rem;color:var(--irs-text-muted);"><?= htmlspecialchars($s['email']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($s['document_type']) ?></td>
                            <td style="white-space:nowrap;"><?= formatDateTime($s['submitted_at']) ?></td>
                            <td><?= getStatusBadge($s['status']) ?></td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <button class="btn btn-sm btn-outline-secondary" style="border-radius:6px;" data-bs-toggle="modal" data-bs-target="#detailModal<?= $s['id'] ?>" title="Détails"><i class="bi bi-eye"></i></button>
                                    <?php if ($s['status'] === 'pending' || $s['status'] === 'info_requested'): ?>
                                    <button class="btn btn-sm btn-outline-success" style="border-radius:6px;" data-bs-toggle="modal" data-bs-target="#actionModal<?= $s['id'] ?>validate" title="Valider"><i class="bi bi-check-lg"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" style="border-radius:6px;" data-bs-toggle="modal" data-bs-target="#actionModal<?= $s['id'] ?>reject" title="Rejeter"><i class="bi bi-x-lg"></i></button>
                                    <button class="btn btn-sm btn-outline-warning" style="border-radius:6px;" data-bs-toggle="modal" data-bs-target="#actionModal<?= $s['id'] ?>info" title="Infos complémentaires"><i class="bi bi-info-circle"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>

                        <!-- DETAIL MODAL -->
                        <div class="modal fade" id="detailModal<?= $s['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header"><h5 class="modal-title"><i class="bi bi-file-text me-2"></i><?= htmlspecialchars($s['document_name']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="info-row"><span class="info-label"><i class="bi bi-hash"></i>Numéro</span><span class="info-value"><?= htmlspecialchars($s['document_number']) ?></span></div>
                                                <div class="info-row"><span class="info-label"><i class="bi bi-tag"></i>Type</span><span class="info-value"><?= htmlspecialchars($s['document_type']) ?></span></div>
                                                <div class="info-row"><span class="info-label"><i class="bi bi-building"></i>Organisme</span><span class="info-value"><?= htmlspecialchars($s['issuing_organization']) ?></span></div>
                                                <div class="info-row"><span class="info-label"><i class="bi bi-geo-alt"></i>Pays</span><span class="info-value"><?= htmlspecialchars($s['country_of_origin']) ?></span></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-row"><span class="info-label"><i class="bi bi-person"></i>Utilisateur</span><span class="info-value"><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></span></div>
                                                <div class="info-row"><span class="info-label"><i class="bi bi-envelope"></i>Email</span><span class="info-value"><?= htmlspecialchars($s['email']) ?></span></div>
                                                <div class="info-row"><span class="info-label"><i class="bi bi-calendar"></i>Soumis le</span><span class="info-value"><?= formatDateTime($s['submitted_at']) ?></span></div>
                                                <div class="info-row"><span class="info-label"><i class="bi bi-check-circle"></i>Statut</span><span class="info-value"><?= getStatusBadge($s['status']) ?></span></div>
                                            </div>
                                            <?php if ($s['description']): ?><div class="col-12"><label class="form-label">Description</label><p style="background:var(--irs-gray);padding:0.75rem;border-radius:8px;font-size:0.9rem;"><?= nl2br(htmlspecialchars($s['description'])) ?></p></div><?php endif; ?>
                                            <?php if ($s['rejection_reason']): ?><div class="col-12"><div class="alert alert-danger"><i class="bi bi-x-circle-fill me-2"></i><strong>Motif de rejet:</strong> <?= htmlspecialchars($s['rejection_reason']) ?></div></div><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <?php if ($s['file_path']): ?><a href="/<?= htmlspecialchars($s['file_path']) ?>" class="btn btn-success btn-sm" target="_blank"><i class="bi bi-download me-1"></i>PDF</a><?php endif; ?>
                                        <?php if ($s['image_path']): ?><a href="/<?= htmlspecialchars($s['image_path']) ?>" class="btn btn-info btn-sm text-white" target="_blank"><i class="bi bi-image me-1"></i>Image</a><?php endif; ?>
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- VALIDATE MODAL -->
                        <div class="modal fade" id="actionModal<?= $s['id'] ?>validate" tabindex="-1">
                            <div class="modal-dialog"><div class="modal-content">
                                <div class="modal-header" style="background:#198754;border-radius:12px 12px 0 0;"><h5 class="modal-title text-white"><i class="bi bi-check-circle-fill me-2"></i>Valider le document</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                                <form method="POST"><div class="modal-body">
                                    <input type="hidden" name="submission_id" value="<?= $s['id'] ?>">
                                    <input type="hidden" name="action" value="validate">
                                    <p style="color:var(--irs-text);">Confirmer la validation de <strong>"<?= htmlspecialchars($s['document_name']) ?>"</strong> ?</p>
                                    <p style="color:var(--irs-text-muted);font-size:0.88rem;"><i class="bi bi-info-circle me-1"></i>L'utilisateur recevra une notification indiquant que son document est authentique.</p>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-lg me-1"></i>Valider</button></div></form>
                            </div></div>
                        </div>

                        <!-- REJECT MODAL -->
                        <div class="modal fade" id="actionModal<?= $s['id'] ?>reject" tabindex="-1">
                            <div class="modal-dialog"><div class="modal-content">
                                <div class="modal-header" style="background:#dc3545;border-radius:12px 12px 0 0;"><h5 class="modal-title text-white"><i class="bi bi-x-circle-fill me-2"></i>Rejeter le document</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                                <form method="POST" onsubmit="return validateRejectForm(this, <?= $s['id'] ?>)">
                                    <div class="modal-body">
                                        <input type="hidden" name="submission_id" value="<?= $s['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="reason" id="hiddenReason<?= $s['id'] ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Motif du rejet *</label>
                                            <select class="form-select" id="reasonSelect<?= $s['id'] ?>" onchange="onReasonChange(this, <?= $s['id'] ?>)">
                                                <option value="">-- Sélectionner --</option>
                                                <?php foreach($reject_reasons as $r): ?>
                                                <option value="<?= htmlspecialchars($r) ?>"><?= htmlspecialchars($r) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div id="customReasonDiv<?= $s['id'] ?>" style="display:none;">
                                            <label class="form-label">Précisez le motif *</label>
                                            <textarea class="form-control" id="customReason<?= $s['id'] ?>" rows="3" placeholder="Décrivez le motif du rejet..." oninput="document.getElementById('hiddenReason<?= $s['id'] ?>').value=this.value"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-x-lg me-1"></i>Rejeter</button></div>
                                </form>
                            </div></div>
                        </div>

                        <!-- INFO MODAL -->
                        <div class="modal fade" id="actionModal<?= $s['id'] ?>info" tabindex="-1">
                            <div class="modal-dialog"><div class="modal-content">
                                <div class="modal-header" style="background:#ffc107;border-radius:12px 12px 0 0;"><h5 class="modal-title text-dark"><i class="bi bi-info-circle-fill me-2"></i>Demande d'informations</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <form method="POST"><div class="modal-body"><input type="hidden" name="submission_id" value="<?= $s['id'] ?>"><input type="hidden" name="action" value="info_requested">
                                    <div class="mb-3"><label class="form-label">Informations requises *</label><textarea name="reason" class="form-control" rows="3" required placeholder="Décrivez les informations manquantes..."></textarea></div>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-warning btn-sm text-dark"><i class="bi bi-send me-1"></i>Envoyer</button></div></form>
                            </div></div>
                        </div>

                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state"><i class="bi bi-inbox"></i><p>Aucune vérification soumise.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
<script>
function onReasonChange(sel, id) {
    const customDiv = document.getElementById('customReasonDiv' + id);
    const hidden = document.getElementById('hiddenReason' + id);
    if (sel.value === 'Autre') {
        customDiv.style.display = 'block';
        hidden.value = '';
        document.getElementById('customReason' + id).value = '';
        document.getElementById('customReason' + id).focus();
    } else {
        customDiv.style.display = 'none';
        hidden.value = sel.value;
    }
}

function validateRejectForm(form, id) {
    const sel = document.getElementById('reasonSelect' + id);
    const hidden = document.getElementById('hiddenReason' + id);
    if (!sel.value) {
        alert('Veuillez sélectionner un motif de rejet.');
        return false;
    }
    if (sel.value === 'Autre') {
        const custom = document.getElementById('customReason' + id).value.trim();
        if (!custom) {
            alert('Veuillez préciser le motif du rejet.');
            document.getElementById('customReason' + id).focus();
            return false;
        }
        hidden.value = custom;
    } else {
        hidden.value = sel.value;
    }
    return true;
}
</script>
</body></html>
