<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
require_once 'functions.php';


requireRole([ROLE_ADMIN, ROLE_PSIKOLOG]);
checkSessionTimeout();

// if (!isset($_GET['id']) || empty($_GET['id'])) {
//     header('Location: index.php');
//     exit;
// }

$id = $_GET['id'];
$query = "SELECT p.*, 
         (SELECT COUNT(*) FROM janji_temu WHERE psikolog_id = p.id) as total_konsultasi,
         (SELECT COUNT(DISTINCT pasien_id) FROM janji_temu WHERE psikolog_id = p.id) as total_pasien
         FROM psikolog p 
         WHERE p.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$psikolog = $result->fetch_assoc();
$title = "Detail Psikolog - Klinik";
$activePage = 'psikolog';

require '../../includes/header.php';
?>

<?php
require '../../includes/sidebar.php';
require '../../includes/navbar.php';
?>

<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Detail Psikolog</h3>
                    <p class="text-subtitle text-muted">Informasi lengkap psikolog</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Psikolog</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Detail</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-column align-items-center text-center">
                                <img src="<?= $main_url ?>uploads/psikolog/<?= $psikolog['foto'] ?: 'default.png' ?>" 
                                     alt="Foto <?= htmlspecialchars($psikolog['nama']) ?>"
                                     class="rounded-circle img-fluid" width="150">
                                <div class="mt-3">
                                    <h4><?= htmlspecialchars($psikolog['nama']) ?></h4>
                                    <p class="text-muted font-size-sm"><?= htmlspecialchars($psikolog['spesialisasi']) ?></p>
                                    <span class="badge <?= $psikolog['status'] == 'Aktif' ? 'bg-success' : ($psikolog['status'] == 'Cuti' ? 'bg-warning' : 'bg-danger') ?>">
                                        <?= $psikolog['status'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="d-flex flex-column">
                                <div class="text-center mb-3">
                                    <h6 class="mb-0">Total Konsultasi</h6>
                                    <div class="mt-2">
                                        <h2 class="mb-0"><?= $psikolog['total_konsultasi'] ?></h2>
                                        <span class="text-muted">Sesi</span>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <h6 class="mb-0">Total Pasien</h6>
                                    <div class="mt-2">
                                        <h2 class="mb-0"><?= $psikolog['total_pasien'] ?></h2>
                                        <span class="text-muted">Pasien</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Informasi Psikolog</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="30%">No. Izin Praktik</th>
                                        <td><?= htmlspecialchars($psikolog['no_izin_praktik']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td><?= htmlspecialchars($psikolog['email']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>No. Telepon</th>
                                        <td><?= htmlspecialchars($psikolog['no_telepon']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Alamat</th>
                                        <td><?= nl2br(htmlspecialchars($psikolog['alamat'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Terdaftar Sejak</th>
                                        <td><?= date('d F Y', strtotime($psikolog['created_at'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Terakhir Diupdate</th>
                                        <td><?= date('d F Y H:i', strtotime($psikolog['updated_at'])) ?></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class="d-flex justify-content-end mt-3">
                                <?php if (isAdmin()): ?>
                                <a href="edit.php?id=<?= $psikolog['id'] ?>" class="btn btn-warning me-2">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <?php endif; ?>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h4 class="card-title">Jadwal Praktik</h4>
                        </div>
                        <div class="card-body">
                            <?php
                            $queryJadwal = "SELECT * FROM jadwal_psikolog WHERE psikolog_id = ? ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')";
                            $stmtJadwal = $conn->prepare($queryJadwal);
                            $stmtJadwal->bind_param('i', $id);
                            $stmtJadwal->execute();
                            $resultJadwal = $stmtJadwal->get_result();
                            ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Hari</th>
                                            <th>Jam Praktik</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($jadwal = $resultJadwal->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $jadwal['hari'] ?></td>
                                            <td><?= date('H:i', strtotime($jadwal['jam_mulai'])) ?> - <?= date('H:i', strtotime($jadwal['jam_selesai'])) ?></td>
                                            <td>
                                                <span class="badge <?= $jadwal['status'] == 'Aktif' ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= $jadwal['status'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                        <?php if ($resultJadwal->num_rows === 0): ?>
                                        <tr>
                                            <td colspan="3" class="text-center">Belum ada jadwal praktik</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>