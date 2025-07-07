<?php
session_start();
require_once '../../config/database.php';

$title = "Master Data - Manajemen Akun";
$activePage = 'master-data';

require '../../includes/header.php';
?>
<link rel="stylesheet" href="<?= $main_url ?>_dist/assets/extensions/simple-datatables/style.css">
<link rel="stylesheet" href="<?= $main_url ?>_dist/assets/compiled/css/table-datatable.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<?php
require '../../includes/sidebar.php';
require '../../includes/navbar.php';

function getRoleBadgeClass($role) {
    switch($role) {
        case 'admin': return 'primary';
        case 'psikolog': return 'success';
        case 'pasien': return 'info';
        case 'staf': return 'warning';
        default: return 'secondary';
    }
}
?>

<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Manajemen Akun</h3>
                    <p class="text-subtitle text-muted">Kelola data akun pengguna sistem</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Master Data</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Data Akun</h5>
                        <a href="add.php" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Tambah Akun
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="table-accounts">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Nama Lengkap</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT 
                                        u.*,
                                        CASE 
                                            WHEN u.role = 'psikolog' AND u.reference_id IS NOT NULL THEN (SELECT nama FROM psikolog WHERE id = u.reference_id)
                                            WHEN u.role = 'pasien' AND u.reference_id IS NOT NULL THEN (SELECT nama_lengkap FROM pasien WHERE id = u.reference_id)
                                            ELSE u.username
                                        END as nama_lengkap
                                    FROM users u
                                    ORDER BY u.created_at DESC";
                            
                            $result = mysqli_query($conn, $query);
                            $no = 1;
                            
                            while ($row = mysqli_fetch_assoc($result)):
                                $nama_lengkap = $row['nama_lengkap'];
                            ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= getRoleBadgeClass($row['role']) ?>">
                                            <?= ucfirst($row['role']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($nama_lengkap) ?></td>
                                    <td>
                                        <span class="badge <?= $row['status'] == 'Aktif' ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= $row['last_login'] ? date('d/m/Y H:i', strtotime($row['last_login'])) : '-' ?></td>
                                    <td>
                                        <div class="buttons">
                                            <a href="view.php?id=<?= $row['id'] ?>" class="btn icon btn-primary btn-sm me-1">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                            <a href="edit.php?id=<?= $row['id'] ?>" class="btn icon btn-warning btn-sm me-1">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                            <button type="button" class="btn icon btn-danger btn-sm" 
                                                    onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars($row['username']) ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>

<script src="<?= $main_url ?>/_dist/assets/extensions/simple-datatables/umd/simple-datatables.js"></script>
<script src="<?= $main_url ?>/_dist/assets/static/js/pages/simple-datatables.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let tableAccounts = document.querySelector('#table-accounts');
    if (tableAccounts) {
        new simpleDatatables.DataTable(tableAccounts);
    }
});

function confirmDelete(id, username) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menonaktifkan akun "${username}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Nonaktifkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=deactivate&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: data.message,
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message,
                    icon: 'error'
                });
            });
        }
    });
}
</script>
</body>
</html>