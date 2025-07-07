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

// Fetch appointment details
$query = "SELECT 
            jt.*,
            p.nama_lengkap as nama_pasien,
            psi.nama as nama_psikolog,
            jl.nama_layanan,
            ak.keluhan_utama,
            ak.durasi_keluhan,
            ak.harapan_konsultasi
        FROM janji_temu jt
        JOIN pasien p ON jt.pasien_id = p.id
        JOIN psikolog psi ON jt.psikolog_id = psi.id
        JOIN jenis_layanan jl ON jt.layanan_id = jl.id
        LEFT JOIN alasan_kunjungan ak ON jt.id = ak.janji_temu_id
        WHERE jt.id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $janji_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$appointment = mysqli_fetch_assoc($result);

if (!$appointment) {
    header('Location: index.php');
    exit();
}

$title = "Tambah Catatan Konsultasi - Klinik";
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
                    <h3>Tambah Catatan Konsultasi</h3>
                    <p class="text-subtitle text-muted">Catat hasil konsultasi pasien</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Konsultasi</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Tambah Catatan</li>
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
                            <h4>Detail Janji Temu</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="30%">Kode Janji</td>
                                            <td>: <?= $appointment['kode_janji'] ?></td>
                                        </tr>
                                        <tr>
                                            <td>Tanggal & Jam</td>
                                            <td>: <?= date('d/m/Y', strtotime($appointment['tanggal'])) ?> 
                                               <?= date('H:i', strtotime($appointment['jam_mulai'])) ?></td>
                                        </tr>
                                        <tr>
                                            <td>Psikolog</td>
                                            <td>: <?= $appointment['nama_psikolog'] ?></td>
                                        </tr>
                                        <tr>
                                            <td>Pasien</td>
                                            <td>: <?= $appointment['nama_pasien'] ?></td>
                                        </tr>
                                        <tr>
                                            <td>Layanan</td>
                                            <td>: <?= $appointment['nama_layanan'] ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Alasan Kunjungan:</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="30%">Keluhan Utama</td>
                                            <td>: <?= $appointment['keluhan_utama'] ?? '-' ?></td>
                                        </tr>
                                        <tr>
                                            <td>Durasi Keluhan</td>
                                            <td>: <?= $appointment['durasi_keluhan'] ?? '-' ?></td>
                                        </tr>
                                        <tr>
                                            <td>Harapan</td>
                                            <td>: <?= $appointment['harapan_konsultasi'] ?? '-' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4>Catatan Konsultasi</h4>
                        </div>
                        <div class="card-body">
                            <form action="process.php" method="POST" id="formKonsultasi">
                                <input type="hidden" name="janji_temu_id" value="<?= $janji_id ?>">
                                <input type="hidden" name="action" value="add">

                                <div class="form-group mb-3">
                                    <label for="diagnosa" class="form-label">Diagnosa</label>
                                    <textarea class="form-control" id="diagnosa" name="diagnosa" rows="3" required></textarea>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="rekomendasi" class="form-label">Rekomendasi</label>
                                    <textarea class="form-control" id="rekomendasi" name="rekomendasi" rows="3" required></textarea>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="catatan_privat" class="form-label">Catatan Privat</label>
                                    <textarea class="form-control" id="catatan_privat" name="catatan_privat" rows="3"></textarea>
                                    <small class="text-muted">Catatan ini hanya dapat dilihat oleh psikolog</small>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="index.php" class="btn btn-secondary">Batal</a>
                                    <button type="submit" class="btn btn-primary">Simpan Catatan</button>
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
    $('#formKonsultasi').on('submit', function(e) {
        e.preventDefault();
        
        if (confirm('Apakah Anda yakin ingin menyimpan catatan konsultasi ini?')) {
            this.submit();
        }
    });
});
</script>

</body>
</html>