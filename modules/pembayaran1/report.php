<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
require_once 'functions.php';

requireRole(ROLE_ADMIN);
checkSessionTimeout();

$title = "Laporan Pembayaran - Klinik";
$activePage = 'pembayaran';

require '../../includes/header.php';
require '../../includes/sidebar.php';
require '../../includes/navbar.php';

// Default to current month
$startDate = date('Y-m-01');
$endDate = date('Y-m-t');

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $startDate = $_GET['start_date'];
    $endDate = $_GET['end_date'];
}

$result = getPaymentReport($startDate, $endDate);
?>

<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Laporan Pembayaran</h3>
                    <p class="text-subtitle text-muted">Laporan pembayaran konsultasi</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Pembayaran</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Laporan</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Filter Laporan</h4>
                </div>
                <div class="card-body">
                    <form method="GET" class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tanggal Mulai</label>
                                <input type="date" class="form-control" name="start_date" value="<?= $startDate ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tanggal Selesai</label>
                                <input type="date" class="form-control" name="end_date" value="<?= $endDate ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block">Tampilkan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Ringkasan Pembayaran</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Metode Pembayaran</th>
                                    <th class="text-center">Jumlah Transaksi</th>
                                    <th class="text-end">Total Pembayaran</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalTransaksi = 0;
                                $totalPembayaran = 0;
                                
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $totalTransaksi += $row['jumlah_transaksi'];
                                    $totalPembayaran += $row['total_pembayaran'];
                                    ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                        <td><?= $row['metode_pembayaran'] ?></td>
                                        <td class="text-center"><?= $row['jumlah_transaksi'] ?></td>
                                        <td class="text-end"><?= formatCurrency($row['total_pembayaran']) ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2">Total</th>
                                    <th class="text-center"><?= $totalTransaksi ?></th>
                                    <th class="text-end"><?= formatCurrency($totalPembayaran) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>

</body>
</html>
