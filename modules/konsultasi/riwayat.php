<?php
session_start();
require_once 'functions.php';
require_once '../../config/database.php';
require_once '../../auth/auth.php';


requireRole(ROLE_PSIKOLOG);
checkSessionTimeout();

if (!isset($_GET['pasien_id'])) {
    header('Location: index.php');
    exit();
}

$pasien_id = $_GET['pasien_id'];

// Fetch patient details
$query = "SELECT * FROM pasien WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $pasien_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pasien = mysqli_fetch_assoc($result);

if (!$pasien) {
    header('Location: index.php');
    exit();
}

$title = "Riwayat Konsultasi Pasien - Klinik";
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
                    <h3>Riwayat Konsultasi Pasien</h3>
                    <p class="text-subtitle text-muted">Riwayat konsultasi <?= $pasien['nama_lengkap'] ?></p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Konsultasi</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Riwayat</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Data Pasien</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="30%">No. Rekam Medis</td>
                                            <td>: <?= $pasien['nomor_rekam_medis'] ?></td>
                                        </tr>
                                        <tr>
                                            <td>Nama Lengkap</td>
                                            <td>: <?= $pasien['nama_lengkap'] ?></td>
                                        </tr>
                                        <tr>
                                            <td>Tanggal Lahir</td>
                                            <td>: <?= date('d/m/Y', strtotime($pasien['tanggal_lahir'])) ?></td>
                                        </tr>
                                        <tr>
                                            <td>Jenis Kelamin</td>
                                            <td>: <?= $pasien['jenis_kelamin'] ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="30%">No. Telepon</td>
                                            <td>: <?= $pasien['no_telepon'] ?></td>
                                        </tr>
                                        <tr>
                                            <td>Email</td>
                                            <td>: <?= $pasien['email'] ?? '-' ?></td>
                                        </tr>
                                        <tr>
                                            <td>Alamat</td>
                                            <td>: <?= $pasien['alamat'] ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4>Riwayat Konsultasi</h4>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <?php
                                $query = "SELECT 
                                            jt.id as janji_id,
                                            jt.kode_janji,
                                            jt.tanggal,
                                            jt.jam_mulai,
                                            jt.status,
                                            psi.nama as nama_psikolog,
                                            jl.nama_layanan,
                                            ck.diagnosa,
                                            ck.rekomendasi,
                                            ak.keluhan_utama
                                        FROM janji_temu jt
                                        JOIN psikolog psi ON jt.psikolog_id = psi.id
                                        JOIN jenis_layanan jl ON jt.layanan_id = jl.id
                                        LEFT JOIN catatan_konsultasi ck ON jt.id = ck.janji_temu_id
                                        LEFT JOIN alasan_kunjungan ak ON jt.id = ak.janji_temu_id
                                        WHERE jt.pasien_id = ?
                                        ORDER BY jt.tanggal DESC, jt.jam_mulai DESC";
                                
                                $stmt = mysqli_prepare($conn, $query);
                                mysqli_stmt_bind_param($stmt, "i", $pasien_id);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);

                                while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">
                                                        <?= date('d/m/Y', strtotime($row['tanggal'])) ?> 
                                                        (<?= date('H:i', strtotime($row['jam_mulai'])) ?>)
                                                    </h6>
                                                    <span class="badge bg-<?= getStatusColor($row['status']) ?>">
                                                        <?= $row['status'] ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <p><strong>Psikolog:</strong> <?= $row['nama_psikolog'] ?></p>
                                                <p><strong>Layanan:</strong> <?= $row['nama_layanan'] ?></p>
                                                
                                                <?php if ($row['keluhan_utama']): ?>
                                                <p><strong>Keluhan:</strong> <?= $row['keluhan_utama'] ?></p>
                                                <?php endif; ?>

                                                <?php if ($row['diagnosa']): ?>
                                                <div class="mt-3">
                                                    <p><strong>Diagnosa:</strong><br><?= nl2br($row['diagnosa']) ?></p>
                                                    <p><strong>Rekomendasi:</strong><br><?= nl2br($row['rekomendasi']) ?></p>
                                                </div>
                                                <?php endif; ?>

                                                <div class="mt-2">
                                                    <a href="view.php?id=<?= $row['janji_id'] ?>" class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i> Detail
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-start">
                        <a href="index.php" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>

<style>
.timeline {
    position: relative;
    margin: 20px 0;
    padding: 0;
}

.timeline-item {
    position: relative;
    margin-left: 20px;
    padding-left: 20px;
    padding-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #435ebe;
    border: 2px solid #fff;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 2px;
    background: #e5e5e5;
}

.timeline-content {
    padding: 10px 0;
}
</style>

</body>
</html>