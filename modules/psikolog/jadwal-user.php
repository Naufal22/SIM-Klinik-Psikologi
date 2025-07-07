<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';

requireRole(ROLE_PASIEN);
checkSessionTimeout();

$title = "Jadwal Psikolog - Klinik";
$activePage = 'jadwal-psikolog';

require '../../includes/header-user.php';
require '../../includes/navbar-user.php';
?>

<div class="content-wrapper container">
    <div class="page-heading">
        <h3>Jadwal Konsultasi Psikolog</h3>
        <p class="text-subtitle text-muted">Pilih psikolog untuk membuat janji temu</p>
    </div>

    <div class="page-content">
        <section class="row">
            <?php
            $query = "SELECT DISTINCT p.*, 
                        GROUP_CONCAT(
                            CONCAT(j.hari, '|', 
                            TIME_FORMAT(j.jam_mulai, '%H:%i'), '|',
                            TIME_FORMAT(j.jam_selesai, '%H:%i'))
                            ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')
                            SEPARATOR ';') as jadwal_praktek
                    FROM psikolog p
                    JOIN jadwal_psikolog j ON p.id = j.psikolog_id
                    WHERE p.status = 'Aktif' AND j.status = 'Aktif'
                    GROUP BY p.id
                    ORDER BY p.nama";
            
            $result = $conn->query($query);
            
            if ($result->num_rows === 0): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-4">
                            <div class="empty-state" data-height="400">
                                <img src="<?= $main_url ?>assets/images/empty.svg" alt="empty" class="mb-3" style="max-width: 200px;">
                                <h2 class="mt-0">Belum Ada Jadwal Tersedia</h2>
                                <p class="lead">
                                    Mohon maaf, saat ini belum ada jadwal psikolog yang tersedia.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: 
                while ($psikolog = $result->fetch_assoc()): 
                    // Process schedule data
                    $jadwal_array = explode(';', $psikolog['jadwal_praktek']);
                    $formatted_jadwal = [];
                    foreach ($jadwal_array as $jadwal) {
                        list($hari, $mulai, $selesai) = explode('|', $jadwal);
                        $formatted_jadwal[] = [
                            'hari' => $hari,
                            'waktu' => "$mulai - $selesai"
                        ];
                    }
                ?>
                <div class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-content">
                            <img src="<?= $main_url ?>uploads/psikolog/<?= $psikolog['foto'] ?>" 
                                 class="card-img-top img-fluid" 
                                 alt="<?= htmlspecialchars($psikolog['nama']) ?>"
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($psikolog['nama']) ?></h5>
                                <p class="card-text mb-3">
                                    <i class="bi bi-award"></i> <?= htmlspecialchars($psikolog['spesialisasi']) ?>
                                </p>
                                <div class="jadwal-section mb-4">
                                    <h6 class="mb-3">
                                        <i class="bi bi-calendar3"></i> Jadwal Praktik
                                    </h6>
                                    <div class="schedule-list">
                                        <table class="table table-sm">
                                            <tbody>
                                                <?php foreach ($formatted_jadwal as $jadwal): ?>
                                                <tr>
                                                    <td width="40%" class="ps-0 border-0">
                                                        <span class="fw-medium"><?= $jadwal['hari'] ?></span>
                                                    </td>
                                                    <td class="border-0">
                                                        <span class="text-primary"><?= $jadwal['waktu'] ?></span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <a href="../janji-temu/add-user.php?psikolog_id=<?= $psikolog['id'] ?>" 
                                       class="btn btn-primary">
                                        <i class="bi bi-calendar2-plus"></i> Buat Janji Temu
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile;
            endif; 
            ?>
        </section>
    </div>
</div>

<style>
.image-container {
    position: relative;
    width: 100%;
    padding-top: 100%; /* Aspect ratio 1:1 (berbentuk kotak) */
    background-color: #f8f9fa;
}

.image-container img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover; /* Kembali menggunakan cover untuk tampilan yang lebih rapi */
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
    overflow: hidden; /* Menambahkan overflow hidden */
}

/* Style yang lain tetap sama */
.schedule-list {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 12px;
}
.schedule-list .table {
    margin-bottom: 0;
}
.schedule-list .table td {
    padding: 4px 8px;
    line-height: 1.4;
}
.card-title {
    color: #2D3748;
    font-size: 1.25rem;
}
.jadwal-section h6 {
    color: #4A5568;
    font-size: 1rem;
}
.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-5px);
}
</style>

<?php require '../../includes/footer-user.php'; ?>