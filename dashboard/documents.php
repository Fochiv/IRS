<?php
require_once __DIR__ . '/../includes/functions.php';
requireUserLogin();
$user = getUserById($conn, $_SESSION['user_id']);

$uid = (int)$_SESSION['user_id'];
$filter = $conn->real_escape_string($_GET['status'] ?? '');
$where = "user_id = $uid";
if ($filter) $where .= " AND status = '$filter'";

$docs = $conn->query("SELECT * FROM submitted_documents WHERE $where ORDER BY submitted_at DESC");

// Pré-charger les documents officiels pour les soumissions validées
$officialDocs = [];
if ($docs && $docs->num_rows > 0) {
    $docsCopy = $conn->query("SELECT * FROM submitted_documents WHERE $where ORDER BY submitted_at DESC");
    while ($tmp = $docsCopy->fetch_assoc()) {
        if ($tmp['status'] === 'validated') {
            $dn = $conn->real_escape_string($tmp['document_number']);
            $official = $conn->query("SELECT * FROM documents WHERE document_number='$dn' LIMIT 1");
            if ($official && $official->num_rows > 0) {
                $officialDocs[$tmp['document_number']] = $official->fetch_assoc();
            }
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
    <title><?= t('my_documents') ?> - <?= t('site_name') ?></title>
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
                    <h1 class="page-title"><?= t('my_documents') ?></h1>
                    <p class="page-subtitle">Gérez tous vos documents soumis</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <?php $notif_count = getUserNotificationsCount($conn, $_SESSION['user_id']); ?>
                <a href="/dashboard/notifications.php" class="nav-bell-btn" title="Notifications">
                    <i class="bi bi-bell-fill"></i>
                    <?php if ($notif_count > 0): ?><span class="nav-bell-count"><?= $notif_count > 99 ? '99+' : $notif_count ?></span><?php endif; ?>
                </a>
                <a href="/dashboard/submit.php" class="btn btn-sm" style="background:var(--irs-blue);color:white;border-radius:8px;">
                    <i class="bi bi-upload me-1"></i>Nouveau document
                </a>
            </div>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'submitted'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>Document soumis avec succès ! Notre équipe va l'analyser.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- FILTERS -->
        <div class="irs-card">
            <div class="irs-card-header">
                <h5 class="irs-card-title"><i class="bi bi-funnel text-irs-blue"></i>Filtrer</h5>
            </div>
            <div class="irs-card-body py-2">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="/dashboard/documents.php" class="btn btn-sm <?= !$filter ? 'btn-primary' : 'btn-outline-secondary' ?>" style="border-radius:6px;">Tous</a>
                    <a href="/dashboard/documents.php?status=pending" class="btn btn-sm <?= $filter==='pending' ? 'btn-warning text-dark' : 'btn-outline-warning' ?>" style="border-radius:6px;"><i class="bi bi-hourglass-split me-1"></i>En attente</a>
                    <a href="/dashboard/documents.php?status=validated" class="btn btn-sm <?= $filter==='validated' ? 'btn-success' : 'btn-outline-success' ?>" style="border-radius:6px;"><i class="bi bi-check-circle me-1"></i>Validés</a>
                    <a href="/dashboard/documents.php?status=rejected" class="btn btn-sm <?= $filter==='rejected' ? 'btn-danger' : 'btn-outline-danger' ?>" style="border-radius:6px;"><i class="bi bi-x-circle me-1"></i>Rejetés</a>
                    <a href="/dashboard/documents.php?status=info_requested" class="btn btn-sm <?= $filter==='info_requested' ? 'btn-info text-white' : 'btn-outline-info' ?>" style="border-radius:6px;"><i class="bi bi-info-circle me-1"></i>Info requise</a>
                </div>
            </div>
        </div>

        <div class="irs-card">
            <div class="table-wrap">
                <?php if ($docs && $docs->num_rows > 0): ?>
                <table class="irs-table w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Document</th>
                            <th>Numéro</th>
                            <th>Type</th>
                            <th>Organisme</th>
                            <th>Soumis le</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $n=1; while ($doc = $docs->fetch_assoc()):
                            $official = $officialDocs[$doc['document_number']] ?? null;
                            $certUrl = '/api/download-certificate.php?doc=' . urlencode($doc['document_number']);
                        ?>
                        <tr>
                            <td><?= $n++ ?></td>
                            <td><span style="font-weight:600;"><?= htmlspecialchars($doc['document_name']) ?></span></td>
                            <td><code style="font-size:0.82rem;background:var(--irs-gray);padding:2px 6px;border-radius:4px;"><?= htmlspecialchars($doc['document_number']) ?></code></td>
                            <td><?= htmlspecialchars($doc['document_type']) ?></td>
                            <td><?= htmlspecialchars($doc['issuing_organization']) ?></td>
                            <td style="white-space:nowrap;"><?= formatDateTime($doc['submitted_at']) ?></td>
                            <td><?= getStatusBadge($doc['status']) ?></td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary" style="border-radius:6px;" data-bs-toggle="modal" data-bs-target="#docModal<?= $doc['id'] ?>" title="Détails">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <?php if ($doc['status'] === 'validated'): ?>
                                    <a href="<?= $certUrl ?>" class="btn btn-sm btn-outline-success" style="border-radius:6px;" target="_blank" title="Télécharger le certificat">
                                        <i class="bi bi-award"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL DETAILS -->
                        <div class="modal fade" id="docModal<?= $doc['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><i class="bi bi-file-text me-2"></i><?= htmlspecialchars($doc['document_name']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="info-row"><span class="info-label"><i class="bi bi-hash"></i>Numéro</span><span class="info-value"><?= htmlspecialchars($doc['document_number']) ?></span></div>
                                                <div class="info-row"><span class="info-label"><i class="bi bi-tag"></i>Type</span><span class="info-value"><?= htmlspecialchars($doc['document_type']) ?></span></div>
                                                <div class="info-row"><span class="info-label"><i class="bi bi-building"></i>Organisme</span><span class="info-value"><?= htmlspecialchars($doc['issuing_organization']) ?></span></div>
                                                <div class="info-row"><span class="info-label"><i class="bi bi-geo-alt"></i>Pays</span><span class="info-value"><?= htmlspecialchars($doc['country_of_origin']) ?></span></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-row"><span class="info-label"><i class="bi bi-calendar"></i>Soumis le</span><span class="info-value"><?= formatDateTime($doc['submitted_at']) ?></span></div>
                                                <div class="info-row"><span class="info-label"><i class="bi bi-check-circle"></i>Statut</span><span class="info-value"><?= getStatusBadge($doc['status']) ?></span></div>
                                                <?php if ($doc['rejection_reason']): ?>
                                                <div class="info-row"><span class="info-label"><i class="bi bi-x-circle text-danger"></i>Motif</span><span class="info-value text-danger"><?= htmlspecialchars($doc['rejection_reason']) ?></span></div>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($doc['description']): ?>
                                            <div class="col-12">
                                                <label class="form-label">Description</label>
                                                <p style="color:var(--irs-text-muted);font-size:0.9rem;background:var(--irs-gray);padding:0.75rem;border-radius:8px;"><?= nl2br(htmlspecialchars($doc['description'])) ?></p>
                                            </div>
                                            <?php endif; ?>

                                            <?php if ($doc['status'] === 'validated' && $official): ?>
                                            <!-- DOCUMENT OFFICIEL -->
                                            <div class="col-12">
                                                <div style="background:rgba(40,167,69,0.08);border:1px solid rgba(40,167,69,0.25);border-radius:10px;padding:1rem;">
                                                    <div style="font-weight:700;font-size:0.9rem;color:var(--irs-green);margin-bottom:0.75rem;">
                                                        <i class="bi bi-patch-check-fill me-2"></i>Document officiel IRS
                                                    </div>
                                                    <?php
                                                    $officialFile = $official['file_path'] ?? '';
                                                    $officialImg  = $official['image_path'] ?? '';
                                                    $previewFile  = $officialFile ?: $officialImg;
                                                    $ext = $previewFile ? strtolower(pathinfo($previewFile, PATHINFO_EXTENSION)) : '';
                                                    $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                                    $isPDF = ($ext === 'pdf');
                                                    ?>
                                                    <?php if ($previewFile && $isImg): ?>
                                                    <div class="doc-file-preview mb-3">
                                                        <div class="doc-preview-label"><i class="bi bi-eye me-1"></i>Aperçu du document officiel</div>
                                                        <div class="doc-preview-img-wrap">
                                                            <img src="/<?= htmlspecialchars($previewFile) ?>" alt="Document officiel" class="doc-preview-img" onclick="this.closest('.doc-file-preview').querySelector('.doc-preview-fullscreen').classList.toggle('show')">
                                                            <div class="doc-preview-fullscreen">
                                                                <button class="doc-preview-close" onclick="this.closest('.doc-preview-fullscreen').classList.remove('show')"><i class="bi bi-x-lg"></i></button>
                                                                <img src="/<?= htmlspecialchars($previewFile) ?>" alt="Document officiel">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php elseif ($previewFile && $isPDF): ?>
                                                    <div class="doc-file-preview mb-3">
                                                        <div class="doc-preview-label"><i class="bi bi-file-pdf me-1"></i>Aperçu du document officiel (PDF)</div>
                                                        <iframe src="/<?= htmlspecialchars($previewFile) ?>" class="doc-preview-pdf" title="Document officiel PDF"></iframe>
                                                    </div>
                                                    <?php else: ?>
                                                    <div style="text-align:center;padding:1rem;color:var(--irs-text-muted);font-size:0.9rem;">
                                                        <i class="bi bi-file-earmark-text" style="font-size:2.5rem;opacity:0.4;display:block;margin-bottom:0.5rem;"></i>
                                                        Document disponible en téléchargement
                                                    </div>
                                                    <?php endif; ?>

                                                    <div class="d-flex gap-2 flex-wrap mt-2">
                                                        <a href="<?= $certUrl ?>" class="btn btn-success btn-sm" target="_blank">
                                                            <i class="bi bi-file-earmark-check me-1"></i>Télécharger le certificat officiel
                                                        </a>
                                                        <?php if ($previewFile): ?>
                                                        <a href="/<?= htmlspecialchars($previewFile) ?>" class="btn btn-outline-primary btn-sm" target="_blank" download>
                                                            <i class="bi bi-download me-1"></i>Télécharger le document officiel
                                                        </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php elseif ($doc['status'] === 'validated'): ?>
                                            <div class="col-12">
                                                <div style="background:rgba(40,167,69,0.08);border:1px solid rgba(40,167,69,0.25);border-radius:10px;padding:1rem;">
                                                    <div style="font-weight:700;font-size:0.9rem;color:var(--irs-green);margin-bottom:0.75rem;">
                                                        <i class="bi bi-patch-check-fill me-2"></i>Document authentique
                                                    </div>
                                                    <a href="<?= $certUrl ?>" class="btn btn-success btn-sm" target="_blank">
                                                        <i class="bi bi-file-earmark-check me-1"></i>Télécharger le certificat officiel
                                                    </a>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-folder-x"></i>
                    <p>Aucun document trouvé.</p>
                    <a href="/dashboard/submit.php" class="btn btn-sm" style="background:var(--irs-blue);color:white;border-radius:8px;margin-top:0.75rem;">
                        <i class="bi bi-upload me-1"></i>Soumettre un document
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
</body>
</html>
