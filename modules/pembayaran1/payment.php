<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
require_once 'functions.php';

requireRole(ROLE_ADMIN);
checkSessionTimeout();

// Get payment ID and validate
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    $_SESSION['error'] = "ID Pembayaran tidak valid.";
    header('Location: index.php');
    exit;
}

// Fetch payment data
$query = "SELECT 
            p.*,
            jt.kode_janji,
            ps.nama_lengkap as nama_pasien,
            psi.nama as nama_psikolog,
            jl.nama_layanan
        FROM pembayaran p
        JOIN janji_temu jt ON p.janji_temu_id = jt.id
        JOIN pasien ps ON jt.pasien_id = ps.id
        JOIN psikolog psi ON jt.psikolog_id = psi.id
        JOIN jenis_layanan jl ON jt.layanan_id = jl.id
        WHERE p.id = ? AND p.status = 'Pending'";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$payment = mysqli_fetch_assoc($result);

if (!$payment) {
    $_SESSION['error'] = "Pembayaran tidak ditemukan atau sudah diproses.";
    header('Location: index.php');
    exit;
}

$title = "Proses Pembayaran - Klinik";
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
                    <h3>Proses Pembayaran</h3>
                    <p class="text-subtitle text-muted">Proses pembayaran konsultasi</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Pembayaran</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Proses</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Form Pembayaran</h4>
                        </div>
                        <div class="card-body">
                            <form action="process.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $payment['id'] ?>">
                                <input type="hidden" name="action" value="update">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>No. Invoice</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($payment['nomor_invoice']) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tanggal</label>
                                            <input type="text" class="form-control" value="<?= date('d/m/Y H:i', strtotime($payment['tanggal_pembayaran'])) ?>" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Pasien</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($payment['nama_pasien']) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Layanan</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($payment['nama_layanan']) ?>" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Total Pembayaran</label>
                                            <input type="text" class="form-control" value="<?= formatCurrency($payment['jumlah']) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-m d-6">
                                        <div class="form-group">
                                            <label>Metode Pembayaran</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($payment['metode_pembayaran']) ?>" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="status">Status Pembayaran</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="">Pilih Status</option>
                                        <option value="Lunas">Lunas</option>
                                        <option value="Dibatalkan">Dibatalkan</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="bukti_pembayaran">Bukti Pembayaran Baru</label>
                                    <input type="file" class="form-control" id="bukti_pembayaran" name="bukti_pembayaran" accept="image/jpeg,image/png,application/pdf">
                                    <div class="bukti-pembayaran-info text-muted mt-2">
                                        <small>Format: JPG, PNG, PDF. Maks: 2MB</small>
                                    </div>
                                </div>


                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary me-1 mb-1">Update Status</button>
                                    <a href="view.php?id=<?= $payment['id'] ?>" class="btn btn-light-secondary me-1 mb-1">Batal</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <?php if ($payment['bukti_pembayaran']): ?>
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Bukti Pembayaran</h4>
                            </div>
                            <div class="card-body">
                                <img src="../../uploads/bukti_pembayaran/<?= htmlspecialchars($payment['bukti_pembayaran']) ?>"
                                    alt="Bukti Pembayaran"
                                    class="img-fluid">
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($payment['catatan']): ?>
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Catatan</h4>
                            </div>
                            <div class="card-body">
                                <?= nl2br(htmlspecialchars($payment['catatan'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>

</body>

</html>