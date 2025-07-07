<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
// require_once '../../auth/functions.php';


requireRole(ROLE_ADMIN);
checkSessionTimeout();

$title = "Layanan - Klinik";
$activePage = 'layanan';

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
                    <h3>Daftar Layanan</h3>
                    <p class="text-subtitle text-muted">Kelola data layanan klinik</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Layanan</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Data Layanan</h5>
                        <a href="add.php" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Tambah Layanan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    $query = "SELECT * FROM jenis_layanan ORDER BY created_at DESC";
                    $result = query($query);
                    ?>
                    <table class="table table-striped" id="table-layanan">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Layanan</th>
                                <th>Durasi (Menit)</th>
                                <th>Tarif</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($row = $result->fetch_assoc()):
                                $statusClass = $row['status'] === 'Aktif' ? 'bg-success' : 'bg-danger';
                            ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['nama_layanan']) ?></td>
                                    <td><?= htmlspecialchars($row['durasi_menit']) ?></td>
                                    <td>Rp <?= number_format($row['tarif'], 0, ',', '.') ?></td>
                                    <td>
                                        <span class="badge <?= $statusClass ?>"><?= $row['status'] ?></span>
                                    </td>
                                    <td>
                                        <div class="buttons">
                                            <a href="view.php?id=<?= $row['id'] ?>" class="btn icon btn-primary me-2">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                            <a href="edit.php?id=<?= $row['id'] ?>" class="btn icon btn-warning me-2">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button type="button" class="btn icon btn-danger" onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama_layanan']) ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
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
    let tableLayanan = document.querySelector('#table-layanan');
    if (tableLayanan) {
        new simpleDatatables.DataTable(tableLayanan);
    }
});

function confirmDelete(id, nama) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus layanan "${nama}"?`,
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
</script>

</body>
</html>