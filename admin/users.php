<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$success = ''; $error = '';

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $uid = (int)$_GET['id'];
    $action = $_GET['action'];
    if ($action === 'suspend') {
        $conn->query("UPDATE users SET status='suspended' WHERE id=$uid");
        $success = 'Utilisateur suspendu.';
        logActivity($conn, 'admin_suspend_user', "Suspension utilisateur ID: $uid", null, $_SESSION['admin_id']);
    } elseif ($action === 'activate') {
        $conn->query("UPDATE users SET status='active' WHERE id=$uid");
        $success = 'Utilisateur activé.';
        logActivity($conn, 'admin_activate_user', "Activation utilisateur ID: $uid", null, $_SESSION['admin_id']);
    } elseif ($action === 'delete') {
        $conn->query("DELETE FROM users WHERE id=$uid");
        $success = 'Utilisateur supprimé.';
        logActivity($conn, 'admin_delete_user', "Suppression utilisateur ID: $uid", null, $_SESSION['admin_id']);
    }
}

// Add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $fn = $conn->real_escape_string($_POST['first_name'] ?? '');
    $ln = $conn->real_escape_string($_POST['last_name'] ?? '');
    $em = $conn->real_escape_string($_POST['email'] ?? '');
    $ph = $conn->real_escape_string($_POST['phone'] ?? '');
    $co = $conn->real_escape_string($_POST['country'] ?? '');
    $pw = $conn->real_escape_string($_POST['password'] ?? '');
    if ($fn && $ln && $em && $pw) {
        $check = $conn->query("SELECT id FROM users WHERE email='$em'");
        if ($check && $check->num_rows > 0) {
            $error = 'Cet email est déjà utilisé.';
        } else {
            $conn->query("INSERT INTO users (first_name,last_name,email,phone,country,password) VALUES ('$fn','$ln','$em','$ph','$co','$pw')");
            $success = 'Utilisateur créé avec succès.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    }
}

// Edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $uid = (int)$_POST['user_id'];
    $fn = $conn->real_escape_string($_POST['first_name'] ?? '');
    $ln = $conn->real_escape_string($_POST['last_name'] ?? '');
    $ph = $conn->real_escape_string($_POST['phone'] ?? '');
    $co = $conn->real_escape_string($_POST['country'] ?? '');
    $st = $conn->real_escape_string($_POST['status'] ?? 'active');
    $conn->query("UPDATE users SET first_name='$fn',last_name='$ln',phone='$ph',country='$co',status='$st' WHERE id=$uid");
    if (!empty($_POST['new_password'])) {
        $np = $conn->real_escape_string($_POST['new_password']);
        $conn->query("UPDATE users SET password='$np' WHERE id=$uid");
    }
    $success = 'Utilisateur modifié.';
}

$search = $conn->real_escape_string($_GET['search'] ?? '');
$where = $search ? "WHERE first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%'" : '';
$users = $conn->query("SELECT * FROM users $where ORDER BY created_at DESC");
$countries = getCountriesList();
$lang = getLang(); $theme = getTheme();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs - Admin IRS</title>
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
                <div><h1 class="page-title">Gestion Utilisateurs</h1><p class="page-subtitle">Gérez tous les comptes utilisateurs</p></div>
            </div>
            <button class="btn btn-sm" style="background:#ffa500;color:#0d1117;border-radius:8px;font-weight:600;" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-person-plus me-1"></i>Ajouter utilisateur
            </button>
        </div>

        <?php if ($success): ?><div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

        <!-- SEARCH -->
        <div class="irs-card mb-3">
            <div class="irs-card-body py-2">
                <form method="GET" class="d-flex gap-2">
                    <div class="search-box flex-grow-1" style="max-width:400px;">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" placeholder="Rechercher par nom, prénom, email..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-sm" style="background:var(--irs-blue);color:white;border-radius:8px;">Rechercher</button>
                    <?php if ($search): ?><a href="/admin/users.php" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">Réinitialiser</a><?php endif; ?>
                </form>
            </div>
        </div>

        <div class="irs-card">
            <div style="overflow-x:auto;">
                <?php if ($users && $users->num_rows > 0): ?>
                <table class="irs-table w-100">
                    <thead>
                        <tr><th>#</th><th>Nom complet</th><th>Email</th><th>Téléphone</th><th>Pays</th><th>Inscrit le</th><th>Statut</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php $n=1; while ($u = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= $n++ ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:34px;height:34px;background:rgba(46,134,222,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.8rem;color:var(--irs-blue);flex-shrink:0;">
                                        <?= strtoupper(substr($u['first_name'],0,1).substr($u['last_name'],0,1)) ?>
                                    </div>
                                    <span><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['phone'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($u['country'] ?: '-') ?></td>
                            <td style="white-space:nowrap;"><?= formatDate($u['created_at']) ?></td>
                            <td>
                                <?php if ($u['status'] === 'active'): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Actif</span>
                                <?php elseif ($u['status'] === 'suspended'): ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-pause-circle me-1"></i>Suspendu</span>
                                <?php else: ?>
                                <span class="badge bg-secondary"><?= $u['status'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <button class="btn btn-sm btn-outline-primary" style="border-radius:6px;" data-bs-toggle="modal" data-bs-target="#editUser<?= $u['id'] ?>" title="Modifier"><i class="bi bi-pencil"></i></button>
                                    <?php if ($u['status'] === 'active'): ?>
                                    <a href="/admin/users.php?action=suspend&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-warning" style="border-radius:6px;" title="Suspendre" onclick="return confirm('Suspendre cet utilisateur ?')"><i class="bi bi-pause-circle"></i></a>
                                    <?php else: ?>
                                    <a href="/admin/users.php?action=activate&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-success" style="border-radius:6px;" title="Activer" onclick="return confirm('Activer cet utilisateur ?')"><i class="bi bi-play-circle"></i></a>
                                    <?php endif; ?>
                                    <a href="/admin/users.php?action=delete&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" style="border-radius:6px;" title="Supprimer" onclick="return confirm('Supprimer définitivement cet utilisateur ?')"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editUser<?= $u['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header"><h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier l'utilisateur</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <div class="row g-3">
                                                <div class="col-6"><label class="form-label">Prénom</label><input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($u['first_name']) ?>" required></div>
                                                <div class="col-6"><label class="form-label">Nom</label><input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($u['last_name']) ?>" required></div>
                                                <div class="col-6"><label class="form-label">Téléphone</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($u['phone']) ?>"></div>
                                                <div class="col-6"><label class="form-label">Pays</label><input type="text" name="country" class="form-control" value="<?= htmlspecialchars($u['country']) ?>"></div>
                                                <div class="col-6"><label class="form-label">Statut</label><select name="status" class="form-select"><option value="active" <?= $u['status']==='active'?'selected':'' ?>>Actif</option><option value="suspended" <?= $u['status']==='suspended'?'selected':'' ?>>Suspendu</option></select></div>
                                                <div class="col-6"><label class="form-label">Nouveau mot de passe</label><input type="password" name="new_password" class="form-control" placeholder="Laisser vide = inchangé"></div>
                                            </div>
                                        </div>
                                        <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button><button type="submit" name="edit_user" class="btn btn-primary btn-sm">Sauvegarder</button></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state"><i class="bi bi-people"></i><p>Aucun utilisateur trouvé.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Ajouter un utilisateur</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label">Prénom *</label><input type="text" name="first_name" class="form-control" required></div>
                        <div class="col-6"><label class="form-label">Nom *</label><input type="text" name="last_name" class="form-control" required></div>
                        <div class="col-12"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
                        <div class="col-6"><label class="form-label">Téléphone</label><input type="text" name="phone" class="form-control"></div>
                        <div class="col-6"><label class="form-label">Pays</label><input type="text" name="country" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Mot de passe *</label><input type="password" name="password" class="form-control" required></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button><button type="submit" name="add_user" class="btn btn-sm" style="background:#ffa500;color:#0d1117;font-weight:600;">Créer l'utilisateur</button></div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
</body></html>
