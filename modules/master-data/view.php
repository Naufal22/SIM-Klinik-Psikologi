<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';

requireRole([ROLE_ADMIN, ROLE_MASTER]);
checkSessionTimeout();

$title = "Detail Staff - Master Data";
$activePage = 'master-data-staff';

if (!isset($_GET['id'])) {
    header('Location: staff.php');
    exit;
}

$id = $_GET['id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header('Location: staff.php');
    exit;
}

require '../../includes/header.php';
require '../../includes/sidebar.php';
require '../../includes/navbar.php';
?>

<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Detail Staff</h3>
                    <p class="text-subtitle text-muted">Informasi detail akun staff</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="staff.php">Master Data</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Detail Staff</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Informasi Staff</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6>Username</h6>
                                <p><?= htmlspecialchars($user['username']) ?></p>
                            </div>
                            <div class="mb-3">
                                <h6>Email</h6>
                                <p><?= htmlspecialchars($user['email']) ?></p>
                            </div>
                            <div class="mb-3">
                                <h6>Role</h6>
                                <p><span class="badge bg-<?= getRoleBadgeClass($user['role']) ?>"><?= ucfirst($user['role']) ?></span></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6>Status</h6>
                                <p><span class="badge bg-<?= $user['status'] == 'Aktif' ? 'success' : 'danger' ?>"><?= $user['status'] ?></span></p>
                            </div>
                            <div class="mb-3">
                                <h6>Tanggal Dibuat</h6>
                                <p><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></p>
                            </div>
                            <div class="mb-3">
                                <h6>Login Terakhir</h6>
                                <p><?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : '-' ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="staff.php" class="btn btn-secondary">Kembali</a>
                        <a href="edit.php?id=<?= $user['id'] ?>" class="btn btn-primary">Edit</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>