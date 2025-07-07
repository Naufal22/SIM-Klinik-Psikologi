<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
require_once 'functions.php';

requireRole(ROLE_ADMIN);
checkSessionTimeout();

$id = $_GET['id'] ?? 0;

// Get payment details
$query = "SELECT 
            p.*,
            jt.kode_janji,
            ps.nama_lengkap as nama_pasien,
            psi.nama as nama_psikolog,
            jl.nama_layanan,
            jl.tarif
        FROM pembayaran p
        JOIN janji_temu jt ON p.janji_temu_id = jt.id
        JOIN pasien ps ON jt.pasien_id = ps.id
        JOIN psikolog psi ON jt.psikolog_id = psi.id
        JOIN jenis_layanan jl ON jt.layanan_id = jl.id
        WHERE p.id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$payment = mysqli_fetch_assoc($result);

if (!$payment) {
    $_SESSION['error'] = "Pembayaran tidak ditemukan.";
    header('Location: index.php');
    exit;
}

$title = "Detail Pembayaran - Klinik";
$activePage = 'pembayaran';

require '../../includes/header.php';
require '../../includes/sidebar.php';
require '../../includes/navbar.php';
?>

<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Detail Pembayaran</h3>
                    <p class="text-subtitle text-muted">Informasi detail pembayaran konsultasi</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Pembayaran</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Detail</li>
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
                            <h4 class="card-title">Informasi Pembayaran</h4>
                            <?php if ($payment['status'] == 'Pending'): ?>
                            <div class="card-header-action">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                                    <i class="bi bi-check-circle"></i> Update Status
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table">
                                        <tr>
                                            <th width="200">No. Invoice</th>
                                            <td><?= $payment['nomor_invoice'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal</th>
                                            <td><?= date('d/m/Y H:i', strtotime($payment['tanggal_pembayaran'])) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td><?= getPaymentStatus($payment['status']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Metode Pembayaran</th>
                                            <td><?= $payment['metode_pembayaran'] ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table">
                                        <tr>
                                            <th width="200">No. Janji Temu</th>
                                            <td><?= $payment['kode_janji'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>Pasien</th>
                                            <td><?= $payment['nama_pasien'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>Psikolog</th>
                                            <td><?= $payment['nama_psikolog'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>Layanan</th>
                                            <td><?= $payment['nama_layanan'] ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title">Rincian Pembayaran</h4>
                                        </div>
                                        <div class="card-body">
                                            <table class="table">
                                                <tr>
                                                    <th>Biaya Layanan</th>
                                                    <td class="text-end"><?= formatCurrency($payment['jumlah']) ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Total Pembayaran</th>
                                                    <td class="text-end"><strong><?= formatCurrency($payment['jumlah']) ?></strong></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <?php if ($payment['bukti_pembayaran']): ?>
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title">Bukti Pembayaran</h4>
                                        </div>
                                        <div class="card-body">
                                            <img src="../../uploads/bukti_pembayaran/<?= $payment['bukti_pembayaran'] ?>" 
                                                 alt="Bukti Pembayaran" 
                                                 class="img-fluid">
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($payment['catatan']): ?>
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title">Catatan</h4>
                                        </div>
                                        <div class="card-body">
                                            <?= nl2br(htmlspecialchars($payment['catatan'])) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Modal Update Status -->
<?php if ($payment['status'] == 'Pending'): ?>
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="process.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $payment['id'] ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="updateStatusModalLabel">Update Status Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Lunas">Lunas</option>
                            <option value="Dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require '../../includes/footer.php'; ?>

</body>
</html>
