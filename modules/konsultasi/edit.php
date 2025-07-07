<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
require_once 'functions.php';

requireRole(ROLE_PSIKOLOG);
checkSessionTimeout();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$catatan_id = $_GET['id'];

// Fetch consultation details
$query = "SELECT 
            ck.*,
            jt.kode_janji,
            jt.tanggal,
            jt.jam_mulai,
            jt.status,
            p.nama_lengkap as nama_pasien,
            psi.nama as nama_psikolog,
            jl.nama_layanan,
            ak.keluhan_utama,
            ak.durasi_keluhan,
            ak.harapan_konsultasi
        FROM catatan_konsultasi ck
        JOIN janji_temu jt ON ck.janji_temu_id = jt.id
        JOIN pasien p ON jt.pasien_id = p.id
        JOIN psikolog psi ON jt.psikolog_id = psi.id
        JOIN jenis_layanan jl ON jt.layanan_id = jl.id
        LEFT JOIN alasan_kunjungan ak ON jt.id = ak.janji_temu_id
        WHERE ck.id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $catatan_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$consultation = mysqli_fetch_assoc($result);

if (!$consultation) {
    header('Location: index.php');
    exit();
}

$title = "Edit Catatan Konsultasi - Klinik";
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
                    <h3>Edit Catatan Konsultasi</h3>
                    <p class="text-subtitle text-muted">Edit catatan konsultasi pasien</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Konsultasi</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Catatan</li>
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
                            <h4>Detail Konsultasi</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="30%">Kode Janji</td>
                                            <td>: <?= $consultation['kode_janji'] ?></td>
                                        </tr>
                                        <tr>
                                            <td>Tanggal & Jam</td>
                                            <td>: <?= date('d/m/Y', strtotime($consultation['tanggal'])) ?> 
                                               <?= date('H:i', strtotime($consultation['jam_mulai'])) ?></td>
                                        </tr>
                                        <tr>
                                            <td>Status</td>
                                            <td>: <span class="badge bg-<?= getStatusColor($consultation['status']) ?>">
                                                <?= $consultation['status'] ?></span></td>
                                        </tr>
                                        <tr>
                                            <td>Psikolog</td>
                                            <td>: <?= $consultation['nama_psikolog'] ?></td>
                                        </tr>
                                        <tr>
                                            <td>Pasien</td>
                                            <td>: <?= $consultation['nama_pasien'] ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Alasan Kunjungan:</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="30%">Keluhan Utama</td>
                                            <td>: <?= $consultation['keluhan_utama'] ?? '-' ?></td>
                                        </tr>
                                        <tr>
                                            <td>Durasi Keluhan</td>
                                            <td>: <?= $consultation['durasi_keluhan'] ?? '-' ?></td>
                                        </tr>
                                        <tr>
                                            <td>Harapan</td>
                                            <td>: <?= $consultation['harapan_konsultasi'] ?? '-' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4>Edit Catatan</h4>
                        </div>
                        <div class="card-body">
                            <form action="process.php" method="POST" id="formEditKonsultasi">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="catatan_id" value="<?= $catatan_id ?>">
                                <input type="hidden" name="janji_temu_id" value="<?= $consultation['janji_temu_id'] ?>">

                                <div class="form-group mb-3">
                                    <label for="diagnosa" class="form-label">Diagnosa</label>
                                    <textarea class="form-control" id="diagnosa" name="diagnosa" rows="3" required><?= htmlspecialchars($consultation['diagnosa']) ?></textarea>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="rekomendasi" class="form-label">Rekomendasi</label>
                                    <textarea class="form-control" id="rekomendasi" name="rekomendasi" rows="3" required><?= htmlspecialchars($consultation['rekomendasi']) ?></textarea>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="catatan_privat" class="form-label">Catatan Privat</label>
                                    <textarea class="form-control" id="catatan_privat" name="catatan_privat" rows="3"><?= htmlspecialchars($consultation['catatan_privat']) ?></textarea>
                                    <small class="text-muted">Catatan ini hanya dapat dilihat oleh psikolog</small>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="view.php?id=<?= $consultation['janji_temu_id'] ?>" class="btn btn-secondary">Batal</a>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#formEditKonsultasi').on('submit', function(e) {
        e.preventDefault();
        
        if (confirm('Apakah Anda yakin ingin menyimpan perubahan catatan konsultasi ini?')) {
            this.submit();
        }
    });
});
</script>

</body>
</html>