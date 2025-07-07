<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';


requireRole(ROLE_PSIKOLOG);
checkSessionTimeout();


if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$janji_id = $_GET['id'];

$query = "SELECT 
            jt.*,
            p.nama_lengkap as nama_pasien,
            psi.nama as nama_psikolog,
            jl.nama_layanan,
            ak.keluhan_utama,
            ak.durasi_keluhan,
            ak.harapan_konsultasi,
            ck.id as catatan_id,
            ck.diagnosa,
            ck.rekomendasi,
            ck.catatan_privat,
            ck.created_at as waktu_catatan
        FROM janji_temu jt
        JOIN pasien p ON jt.pasien_id = p.id
        JOIN psikolog psi ON jt.psikolog_id = psi.id
        JOIN jenis_layanan jl ON jt.layanan_id = jl.id
        LEFT JOIN alasan_kunjungan ak ON jt.id = ak.janji_temu_id
        LEFT JOIN catatan_konsultasi ck ON jt.id = ck.janji_temu_id
        WHERE jt.id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $janji_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$consultation = mysqli_fetch_assoc($result);

if (!$consultation) {
    header('Location: index.php');
    exit();
}

$title = "Detail Konsultasi - Klinik";
$activePage = 'konsultasi';

require '../../includes/header.php';
require '../../includes/sidebar.php';
require '../../includes/navbar.php';
?>

<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Detail Konsultasi</h3>
                    <p class="text-subtitle text-muted">Detail konsultasi pasien</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Konsultasi</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Detail</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-12">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['success'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <!-- Combined Appointment Information and Visit Reason -->
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="card-title">Informasi Konsultasi</h4>
                                <span class="badge bg-<?= getStatusColor($consultation['status']) ?> fs-6">
                                    <?= str_replace('_', ' ', $consultation['status']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-6 border-end">
                                    <h6 class="text-muted mb-3">Informasi Dasar</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td style="width: 35%"><strong>Kode Janji</strong></td>
                                            <td>: <?= $consultation['kode_janji'] ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal & Jam</strong></td>
                                            <td>: <?= date('d/m/Y', strtotime($consultation['tanggal'])) ?> 
                                               <?= date('H:i', strtotime($consultation['jam_mulai'])) ?> WIB</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Layanan</strong></td>
                                            <td>: <?= $consultation['nama_layanan'] ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Psikolog</strong></td>
                                            <td>: <?= $consultation['nama_psikolog'] ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Pasien</strong></td>
                                            <td>: <?= $consultation['nama_pasien'] ?></td>
                                        </tr>
                                    </table>
                                </div>

                                <!-- Visit Reason -->
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Alasan Kunjungan</h6>
                                    <div class="mb-3">
                                        <label class="form-label text-primary"><strong>Keluhan Utama</strong></label>
                                        <p class="border-start border-primary ps-2 mb-0">
                                            <?= $consultation['keluhan_utama'] ?? '-' ?>
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-info"><strong>Durasi Keluhan</strong></label>
                                        <p class="border-start border-info ps-2 mb-0">
                                            <?= $consultation['durasi_keluhan'] ?? '-' ?>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="form-label text-success"><strong>Harapan Konsultasi</strong></label>
                                        <p class="border-start border-success ps-2 mb-0">
                                            <?= $consultation['harapan_konsultasi'] ?? '-' ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Consultation Notes -->
                    <?php if ($consultation['catatan_id']): ?>
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="card-title">Catatan Konsultasi</h4>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    <?= date('d/m/Y H:i', strtotime($consultation['waktu_catatan'])) ?> WIB
                                </small>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-clipboard2-pulse fs-5 me-2"></i>
                                            <h6 class="mb-0">Diagnosa</h6>
                                        </div>
                                        <p class="border-start border-4 border-primary ps-3 mb-0">
                                            <?= nl2br($consultation['diagnosa']) ?>
                                        </p>
                                    </div>

                                    <div class="mb-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-journal-medical fs-5 me-2"></i>
                                            <h6 class="mb-0">Rekomendasi</h6>
                                        </div>
                                        <p class="border-start border-4 border-success ps-3 mb-0">
                                            <?= nl2br($consultation['rekomendasi']) ?>
                                        </p>
                                    </div>

                                    <?php if ($consultation['catatan_privat']): ?>
                                    <div class="mb-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-shield-lock fs-5 me-2"></i>
                                            <h6 class="mb-0">Catatan Privat</h6>
                                        </div>
                                        <div class="alert alert-light-warning mb-0">
                                            <p class="mb-2"><?= nl2br($consultation['catatan_privat']) ?></p>
                                            <small class="text-muted">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Catatan ini hanya dapat dilihat oleh psikolog
                                            </small>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between mb-4">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>
                            Kembali
                        </a>
                        <?php if ($consultation['status'] === 'Dalam_Konsultasi' && !$consultation['catatan_id']): ?>
                        <a href="add.php?id=<?= $janji_id ?>" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i>
                            Tambah Catatan
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php 
function getStatusColor($status) {
    switch ($status) {
        case 'Terjadwal':
            return 'primary';
        case 'Check-in':
            return 'info';
        case 'Dalam_Konsultasi':
            return 'warning';
        case 'Selesai':
            return 'success';
        case 'Dibatalkan':
            return 'danger';
        case 'Tidak Hadir':
            return 'secondary';
        default:
            return 'secondary';
    }
}

require '../../includes/footer.php'; 
?>

</body>
</html>