<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';

requireRole([ROLE_ADMIN, ROLE_MASTER]);
checkSessionTimeout();

$title = "Edit Staff - Master Data";
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
                    <h3>Edit Staff</h3>
                    <p class="text-subtitle text-muted">Edit informasi akun staff</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="staff.php">Master Data</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Staff</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Form Edit Staff</h4>
                </div>
                <div class="card-body">
                    <form id="editForm">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?= htmlspecialchars($user['username']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="password">Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role">Role</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="psikolog" <?= $user['role'] == 'psikolog' ? 'selected' : '' ?>>Psikolog</option>
                                        <option value="staf" <?= $user['role'] == 'staf' ? 'selected' : '' ?>>Staff</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="Aktif" <?= $user['status'] == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                                        <option value="Tidak Aktif" <?= $user['status'] == 'Tidak Aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="staff.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = 'staff.php';
            });
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message
        });
    });
});
</script>