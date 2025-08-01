<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
require_once '../../auth/session_check.php';
checkSession();

requireRole([ROLE_ADMIN, ROLE_PSIKOLOG]);
checkSessionTimeout();

$title = "Pasien - Klinik";
$activePage = 'pasien';

require '../../includes/header.php';
?>
<link rel="stylesheet" href="<?= $main_url ?>_dist/assets/extensions/simple-datatables/style.css">
<link rel="stylesheet" href="<?= $main_url ?>_dist/assets/compiled/css/table-datatable.css">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<?php
require '../../includes/sidebar.php';
require '../../includes/navbar.php';
?>

<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Daftar Pasien</h3>
                    <p class="text-subtitle text-muted">Kelola data pasien klinik</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Pasien</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Data Pasien</h5>
                        <?php if (isAdmin()): ?>
                        <a href="add.php" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Tambah Pasien
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    // Get the current user's ID and role
                    $current_user_id = $_SESSION['user_id'];
                    $current_role = $_SESSION['role'];

                    // Modify the query based on role
                    if ($current_role === ROLE_PSIKOLOG) {
                        // For psychologists, only show their associated patients through janji_temu
                        $query = "SELECT DISTINCT p.* 
                                FROM pasien p 
                                INNER JOIN janji_temu j ON p.id = j.pasien_id 
                                WHERE j.psikolog_id = (
                                    SELECT reference_id 
                                    FROM users 
                                    WHERE id = ? AND role = 'psikolog'
                                )
                                ORDER BY p.created_at DESC";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $current_user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                    } else {
                        // For admin, show all patients
                        $query = "SELECT * FROM pasien ORDER BY created_at DESC";
                        $result = $conn->query($query);
                    }
                    ?>
                    <table class="table table-striped" id="table-pasien">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No. RM</th>
                                <th>Nama Lengkap</th>
                                <th>Tanggal Lahir</th>
                                <th>No. Telepon</th>
                                <th>Alamat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($row = $result->fetch_assoc()):
                            ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['nomor_rekam_medis']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_lahir'])) ?></td>
                                    <td><?= htmlspecialchars($row['no_telepon']) ?></td>
                                    <td><?= htmlspecialchars($row['alamat']) ?></td>
                                    <td>
                                        <div class="buttons">
                                            <a href="view.php?id=<?= $row['id'] ?>" class="btn icon btn-primary me-2">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                            <?php if (isAdmin()): ?>
                                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn icon btn-warning me-2">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button type="button" class="btn icon btn-danger" onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama_lengkap']) ?>')">
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

<!-- DataTables Scripts -->
<script src="<?= $main_url ?>/_dist/assets/extensions/simple-datatables/umd/simple-datatables.js"></script>
<script src="<?= $main_url ?>/_dist/assets/static/js/pages/simple-datatables.js"></script>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    let tablePasien = document.querySelector('#table-pasien');
    if (tablePasien) {
        new simpleDatatables.DataTable(tablePasien);
    }
});

<?php if (isAdmin()): ?>
function confirmDelete(id, nama) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus data pasien "${nama}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=delete&id=${id}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
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
                    throw new Error(data.message || 'Terjadi kesalahan saat menghapus data');
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Terjadi kesalahan saat menghapus data',
                    icon: 'error'
                });
            });
        }
    });
}
<?php endif; ?>
</script>

</body>
</html>
z