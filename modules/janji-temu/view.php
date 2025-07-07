<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';

// requireRole(ROLE_ADMIN);
requireRole([ROLE_ADMIN, ROLE_PSIKOLOG]);
checkSessionTimeout();

$title = "Detail Janji Temu - Klinik";
$activePage = 'janji-temu';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

try {
    $result = query("
        SELECT 
            jt.*,
            p.nama_lengkap as pasien_nama,
            p.nomor_rekam_medis,
            ps.nama as psikolog_nama,
            jl.nama_layanan,
            jl.durasi_menit,
            ak.keluhan_utama,
            ak.durasi_keluhan,
            ak.riwayat_pengobatan,
            ak.harapan_konsultasi
        FROM janji_temu jt
        JOIN pasien p ON jt.pasien_id = p.id
        JOIN psikolog ps ON jt.psikolog_id = ps.id
        JOIN jenis_layanan jl ON jt.layanan_id = jl.id
        LEFT JOIN alasan_kunjungan ak ON jt.id = ak.janji_temu_id
        WHERE jt.id = ?
    ", [$id]);

    $appointment = $result->fetch_assoc();


    if (!$appointment) {
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
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
                    <h3>Detail Janji Temu</h3>
                    <p class="text-subtitle text-muted">Informasi lengkap janji temu</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Janji Temu</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Detail</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">
                            Kode Janji: <?= htmlspecialchars($appointment['kode_janji']) ?>
                        </h5>
                        <div>
                            <?php if ($appointment['status'] === 'Terjadwal'): ?>
                            <a href="edit.php?id=<?= $id ?>" class="btn btn-warning">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                            <?php endif; ?>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Detail Informasi -->
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informasi Pasien</h6>
                            <table class="table">
                                <tr>
                                    <th width="150">Nama</th>
                                    <td><?= htmlspecialchars($appointment['pasien_nama']) ?></td>
                                </tr>
                                <tr>
                                    <th>No. Rekam Medis</th>
                                    <td><?= htmlspecialchars($appointment['nomor_rekam_medis']) ?></td>
                                </tr>
                            </table>

                            <h6 class="mt-4">Informasi Psikolog</h6>
                            <table class="table">
                                <tr>
                                    <th width="150">Nama</th>
                                    <td><?= htmlspecialchars($appointment['psikolog_nama']) ?></td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h6>Detail Janji Temu</h6>
                            <table class="table">
                                <tr>
                                    <th width="150">Layanan</th>
                                    <td><?= htmlspecialchars($appointment['nama_layanan']) ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal</th>
                                    <td><?= date('d/m/Y', strtotime($appointment['tanggal'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Waktu</th>
                                    <td>
                                        <?= date('H:i', strtotime($appointment['jam_mulai'])) ?> - 
                                        <?= date('H:i', strtotime($appointment['jam_selesai'])) ?> 
                                        (<?= $appointment['durasi_menit'] ?> menit)
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge bg-<?= getStatusColor($appointment['status']) ?>">
                                            <?= $appointment['status'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Keluhan Awal</th>
                                    <td><?= nl2br(htmlspecialchars($appointment['keluhan_utama'])) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Tombol Aksi Status -->
                    <?php if ($appointment['status'] === 'Terjadwal'): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-success me-2" onclick="updateStatus('Check-in')">
                                    <i class="bi bi-check-circle"></i> Check-in
                                </button>
                                <button type="button" class="btn btn-danger" onclick="updateStatus('Dibatalkan')">
                                    <i class="bi bi-x-circle"></i> Batalkan
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($appointment['status'] === 'Check-in'): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary" onclick="updateStatus('Dalam_Konsultasi')">
                                    <i class="bi bi-clipboard-check"></i> Mulai Konsultasi
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($appointment['status'] === 'Dalam_Konsultasi'): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-success" onclick="updateStatus('Selesai')">
                                    <i class="bi bi-check-circle"></i> Selesai
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Riwayat Status -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Riwayat Status</h6>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Status Lama</th>
                                        <th>Status Baru</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $logs = query("
                                        SELECT * FROM janji_temu_log 
                                        WHERE janji_temu_id = ? 
                                        ORDER BY created_at DESC
                                    ", [$id]);

                                    while ($log = $logs->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                                        <td>
                                            <span class="badge bg-<?= getStatusColor($log['status_lama']) ?>">
                                                <?= $log['status_lama'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= getStatusColor($log['status_baru']) ?>">
                                                <?= $log['status_baru'] ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($log['catatan']) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function updateStatus(newStatus) {
    Swal.fire({
        title: 'Konfirmasi',
        text: `Apakah Anda yakin ingin mengubah status menjadi "${newStatus}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Tidak'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=updateStatus&id=<?= $id ?>&status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal!',
                        text: data.message || 'Terjadi kesalahan',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat mengubah status',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }
    });
}

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'Terjadwal':
            return 'primary';
        case 'Check-in':
            return 'warning';
        case 'Dalam_Konsultasi':
            return 'info';
        case 'Selesai':
            return 'success';
        case 'Dibatalkan':
            return 'danger';
        case 'Tidak Hadir':
            return 'secondary';
        default:
            return 'light';
    }
}
?>
</script>

</body>
</html>
