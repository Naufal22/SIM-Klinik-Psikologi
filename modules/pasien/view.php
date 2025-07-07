<?php
session_start();

require_once 'functions.php';

require_once '../../config/database.php';
require_once '../../auth/auth.php';


requireRole([ROLE_ADMIN, ROLE_PSIKOLOG]);
checkSessionTimeout();



// if (!isset($_GET['id'])) {
//     header('Location: index.php');
//     exit();
// }

$id = $_GET['id'];
$pasien = getPasienById($id);

// if (!$pasien) {
//     header('Location: index.php');
//     exit();
// }



$title = "Detail Pasien - Klinik";
$activePage = 'pasien';

require '../../includes/header.php';
require '../../includes/sidebar.php';
require '../../includes/navbar.php';
?>

<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Detail Pasien</h3>
                    <p class="text-subtitle text-muted">Informasi lengkap data pasien</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Pasien</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Detail</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Data Pasien</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row mb-4">
                                <label class="col-form-label col-12 col-md-4">No. Rekam Medis</label>
                                <div class="col-12 col-md-8">
                                    <p class="form-control-static"><?= htmlspecialchars($pasien['nomor_rekam_medis']) ?></p>
                                </div>
                            </div>
                            
                            <div class="form-group row mb-4">
                                <label class="col-form-label col-12 col-md-4">Nama Lengkap</label>
                                <div class="col-12 col-md-8">
                                    <p class="form-control-static"><?= htmlspecialchars($pasien['nama_lengkap']) ?></p>
                                </div>
                            </div>
                            
                            <div class="form-group row mb-4">
                                <label class="col-form-label col-12 col-md-4">Tanggal Lahir</label>
                                <div class="col-12 col-md-8">
                                    <p class="form-control-static"><?= date('d/m/Y', strtotime($pasien['tanggal_lahir'])) ?></p>
                                </div>
                            </div>
                            
                            <div class="form-group row mb-4">
                                <label class="col-form-label col-12 col-md-4">Jenis Kelamin</label>
                                <div class="col-12 col-md-8">
                                    <p class="form-control-static"><?= htmlspecialchars($pasien['jenis_kelamin']) ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group row mb-4">
                                <label class="col-form-label col-12 col-md-4">Alamat</label>
                                <div class="col-12 col-md-8">
                                    <p class="form-control-static"><?= htmlspecialchars($pasien['alamat']) ?></p>
                                </div>
                            </div>
                            
                            <div class="form-group row mb-4">
                                <label class="col-form-label col-12 col-md-4">No. Telepon</label>
                                <div class="col-12 col-md-8">
                                    <p class="form-control-static"><?= htmlspecialchars($pasien['no_telepon']) ?></p>
                                </div>
                            </div>
                            
                            <div class="form-group row mb-4">
                                <label class="col-form-label col-12 col-md-4">Email</label>
                                <div class="col-12 col-md-8">
                                    <p class="form-control-static"><?= htmlspecialchars($pasien['email'] ?: '-') ?></p>
                                </div>
                            </div>
                            
                            <div class="form-group row mb-4">
                                <label class="col-form-label col-12 col-md-4">Kontak Darurat</label>
                                <div class="col-12 col-md-8">
                                    <p class="form-control-static">
                                        <?= htmlspecialchars($pasien['kontak_darurat_nama']) ?><br>
                                        <?= htmlspecialchars($pasien['kontak_darurat_telepon']) ?>
                                    </p>
                                </div>
                            </div>

                            <div class="form-group row mb-4">
                                <label class="col-form-label col-12 col-md-4">Status</label>
                                <div class="col-12 col-md-8">
                                    <p class="form-control-static">
                                        <span class="badge bg-<?= $pasien['status'] == 'Aktif' ? 'success' : 'danger' ?>">
                                            <?= htmlspecialchars($pasien['status']) ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                        <?php if (isAdmin()): ?>
                                            <a href="edit.php?id=<?= $pasien['id'] ?>" class="btn btn-warning me-1">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <button type="button" class="btn btn-danger me-1" onclick="confirmDelete(<?= $pasien['id'] ?>, '<?= htmlspecialchars($pasien['nama_lengkap']) ?>')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                        <?php endif; ?>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>


<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
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
            Swal.fire({
                title: 'Menghapus Data',
                text: 'Mohon tunggu...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

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
                        icon: 'success',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'index.php';
                        }
                    });
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan saat menghapus data');
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Terjadi kesalahan saat menghapus data',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }
    });
}
</script>

</body>
</html>