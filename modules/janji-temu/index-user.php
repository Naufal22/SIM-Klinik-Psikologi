<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';

requireRole(ROLE_PASIEN);
checkSessionTimeout();

$title = "Janji Temu Saya - Klinik";
$activePage = 'janji-temu';


require '../../includes/header-user.php';
require '../../includes/navbar-user.php';
?>

<div id="main-content">
    <div class="container py-3">
        <div class="row">
            <!-- Janji Temu Section -->
            <div class="col-lg-7 mb-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calendar-check"></i> Janji Temu Saya
                        </h5>
                        <a href="../psikolog/jadwal-user.php" class="btn btn-sm btn-light">
                            <i class="bi bi-plus-circle"></i> Buat Janji
                        </a>
                    </div>
                    <div class="card-body">
                        <?php
                        $user_id = $_SESSION['user_id'];
                        $query = "SELECT jt.*, p.nama as nama_psikolog, jl.nama_layanan, jl.durasi_menit
                                FROM janji_temu jt
                                JOIN psikolog p ON jt.psikolog_id = p.id
                                JOIN jenis_layanan jl ON jt.layanan_id = jl.id
                                WHERE jt.pasien_id = (SELECT reference_id FROM users WHERE id = ?)
                                AND jt.status != 'Dibatalkan'
                                ORDER BY jt.tanggal DESC, jt.jam_mulai DESC
                                LIMIT 5";

                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows === 0) {
                            echo '<div class="text-center py-3">
                                    <img src="' . $main_url . '_dist/assets/images/no-data.svg" alt="No appointments" style="width: 150px;">
                                    <p class="text-muted mt-2">Belum ada janji temu yang terjadwal</p>
                                  </div>';
                        } else {
                            while ($row = $result->fetch_assoc()) {
                                $statusClass = match ($row['status']) {
                                    'Terjadwal' => 'bg-primary',
                                    'Selesai' => 'bg-success',
                                    'Dalam_Konsultasi' => 'bg-info',
                                    'Check-in' => 'bg-warning',
                                    default => 'bg-secondary'
                                };
                        ?>
                                <div class="card mb-2 border">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($row['nama_psikolog']) ?></h6>
                                                <small class="text-muted d-block">
                                                    <i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($row['tanggal'])) ?>
                                                    <i class="bi bi-clock ms-2"></i> <?= date('H:i', strtotime($row['jam_mulai'])) ?>
                                                </small>
                                                <small class="text-muted">
                                                    <i class="bi bi-tag"></i> <?= htmlspecialchars($row['nama_layanan']) ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge <?= $statusClass ?> mb-2"><?= $row['status'] ?></span>
                                                <?php if ($row['status'] === 'Terjadwal'): ?>
                                                    <br>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="cancelAppointment(<?= $row['id'] ?>)">
                                                        <i class="bi bi-x-circle"></i> Batalkan
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Riwayat Konsultasi Section -->
            <div class="col-lg-5 mb-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-journal-text"></i> Riwayat Konsultasi
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $query = "SELECT ck.*, jt.tanggal, p.nama as nama_psikolog, jl.nama_layanan
                                FROM catatan_konsultasi ck
                                JOIN janji_temu jt ON ck.janji_temu_id = jt.id
                                JOIN psikolog p ON ck.psikolog_id = p.id
                                JOIN jenis_layanan jl ON jt.layanan_id = jl.id
                                WHERE jt.pasien_id = (SELECT reference_id FROM users WHERE id = ?)
                                ORDER BY jt.tanggal DESC";

                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows === 0) {
                            echo '<div class="text-center py-3">
                                    <p class="text-muted mt-2">Belum ada riwayat konsultasi</p>
                                  </div>';
                        } else {
                            while ($row = $result->fetch_assoc()) {
                        ?>
                                <div class="card mb-2 border">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($row['nama_psikolog']) ?></h6>
                                                <small class="text-muted d-block mb-2">
                                                    <i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($row['tanggal'])) ?>
                                                    <i class="bi bi-tag ms-2"></i> <?= htmlspecialchars($row['nama_layanan']) ?>
                                                </small>
                                            </div>
                                            <button class="btn btn-sm btn-outline-secondary" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#detail-<?= $row['id'] ?>">
                                                <i class="bi bi-chevron-down"></i>
                                            </button>
                                        </div>
                                        <div class="collapse mt-2" id="detail-<?= $row['id'] ?>">
                                            <div class="border-top pt-2">
                                                <h6 class="text-muted mb-1">Diagnosa:</h6>
                                                <p class="small mb-2"><?= nl2br(htmlspecialchars($row['diagnosa'])) ?></p>
                                                <h6 class="text-muted mb-1">Rekomendasi:</h6>
                                                <p class="small mb-0"><?= nl2br(htmlspecialchars($row['rekomendasi'])) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require '../../includes/footer-user.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterStatus = document.getElementById('filterStatus');
        if (filterStatus) {
            const appointmentCards = document.querySelectorAll('.appointment-card');

            filterStatus.addEventListener('change', function() {
                const selectedStatus = this.value;

                appointmentCards.forEach(card => {
                    if (!selectedStatus || card.dataset.status === selectedStatus) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
    });


    function cancelAppointment(id) {
        Swal.fire({
            title: 'Batalkan Janji Temu?',
            text: 'Apakah Anda yakin ingin membatalkan janji temu ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('process.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `action=cancel&id=${id}`
                    })
                    .then(response => {
                        console.log(response); // Log respons untuk debugging
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire('Berhasil', 'Janji temu berhasil dibatalkan', 'success');
                            location.reload(); // Menyegarkan halaman setelah pembatalan
                        } else {
                            Swal.fire('Gagal', 'Terjadi kesalahan saat membatalkan janji temu', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('There was a problem with the fetch operation:', error);
                        Swal.fire('Error', error.message, 'error');
                    });
            }
        });
    }
</script>