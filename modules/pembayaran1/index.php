<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
require_once 'functions.php';

requireRole(ROLE_ADMIN);
checkSessionTimeout();

$title = "Pembayaran - Klinik";
$activePage = 'pembayaran';

require '../../includes/header.php';
require '../../includes/sidebar.php';
require '../../includes/navbar.php';

// Query untuk mengambil data pembayaran
$query = "SELECT 
            p.*,
            jt.kode_janji,
            ps.nama_lengkap as nama_pasien,
            jl.nama_layanan,
            jt.tanggal as appointment_date,
            jt.jam_mulai
        FROM pembayaran p
        JOIN janji_temu jt ON p.janji_temu_id = jt.id
        JOIN pasien ps ON jt.pasien_id = ps.id
        JOIN jenis_layanan jl ON jt.layanan_id = jl.id
        ORDER BY p.created_at DESC";

$result = mysqli_query($conn, $query);
?>

<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Pembayaran</h3>
                    <p class="text-subtitle text-muted">Kelola pembayaran konsultasi</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Pembayaran</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Daftar Pembayaran</h5>
                        <div>
                            <a href="report.php" class="btn btn-success me-2">
                                <i class="bi bi-file-earmark-text"></i> Laporan Pembayaran
                            </a>
                            <a href="create.php" class="btn btn-primary">
                                <i class="bi bi-plus"></i> Tambah Pembayaran
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="tabelPembayaran">
                            <thead>
                                <tr>
                                    <th>No. Invoice</th>
                                    <th>Tanggal</th>
                                    <th>Pasien</th>
                                    <th>Layanan</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $row['nomor_invoice'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_pembayaran'])) ?></td>
                                    <td><?= htmlspecialchars($row['nama_pasien']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_layanan']) ?></td>
                                    <td><?= formatCurrency($row['jumlah']) ?></td>
                                    <td><?= getPaymentStatus($row['status']) ?></td>
                                    <td>
                                        <div class="buttons">
                                            <a href="view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info" title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($row['status'] == 'Pending'): ?>
                                            <a href="payment.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success" title="Proses Pembayaran">
                                                <i class="bi bi-credit-card"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#tabelPembayaran').DataTable({
        "order": [[1, "desc"]],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        }
    });
});
</script>

</body>
</html>