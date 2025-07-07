<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
// require_once '../../auth/functions.php';


requireRole(ROLE_ADMIN);
checkSessionTimeout();

$title = "Detail Layanan - Klinik";
$activePage = 'layanan';

// Get layanan data
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$query = "SELECT * FROM jenis_layanan WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$layanan = $result->fetch_assoc();

if (!$layanan) {
    header('Location: index.php');
    exit;
}

require '../../includes/header.php';
require '../../includes/sidebar.php';
require '../../includes/navbar.php';
?>

<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Detail Layanan</h3>
                    <p class="text-subtitle text-muted">Informasi detail layanan</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Layanan</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Detail</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Informasi Layanan</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Nama Layanan</h6>
                                <p class="font-bold"><?= htmlspecialchars($layanan['nama_layanan']) ?></p>
                            </div>

                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Durasi</h6>
                                <p class="font-bold"><?= $layanan['durasi_menit'] ?> Menit</p>
                            </div>

                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Tarif</h6>
                                <p class="font-bold">Rp <?= number_format($layanan['tarif'], 0, ',', '.') ?></p>
                            </div>

                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Status</h6>
                                <span class="badge <?= $layanan['status'] === 'Aktif' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $layanan['status'] ?>
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Deskripsi</h6>
                                <p><?= nl2br(htmlspecialchars($layanan['deskripsi'] ?: '-')) ?></p>
                            </div>

                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Tanggal Dibuat</h6>
                                <p><?= date('d/m/Y H:i', strtotime($layanan['created_at'])) ?></p>
                            </div>

                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Terakhir Diupdate</h6>
                                <p><?= date('d/m/Y H:i', strtotime($layanan['updated_at'])) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Statistik Penggunaan -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h4>Statistik Penggunaan</h4>
                            <?php
                            // Get usage statistics from janji_temu table
                            $stats_query = "SELECT COUNT(*) as total_konsultasi,
                                          COUNT(DISTINCT pasien_id) as total_pasien
                                          FROM janji_temu 
                                          WHERE layanan_id = ? 
                                          AND status NOT IN ('Dibatalkan', 'Tidak Hadir')";
                            $stats_stmt = $conn->prepare($stats_query);
                            $stats_stmt->bind_param('i', $id);
                            $stats_stmt->execute();
                            $stats_result = $stats_stmt->get_result();
                            $stats = $stats_result->fetch_assoc();
                            ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>Total Konsultasi</h6>
                                            <h3><?= number_format($stats['total_konsultasi']) ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>Total Pasien</h6>
                                            <h3><?= number_format($stats['total_pasien']) ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <a href="index.php" class="btn btn-secondary me-1 mb-1">Kembali</a>
                            <a href="edit.php?id=<?= $layanan['id'] ?>" class="btn btn-primary me-1 mb-1">Edit</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>

</body>
</html>