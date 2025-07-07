<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
require_once 'functions.php';

requireRole(ROLE_PASIEN);
checkSessionTimeout();

$title = "Buat Janji Temu - Klinik";

// Get selected psychologist
$psikolog_id = $_GET['psikolog_id'] ?? null;
if (!$psikolog_id) {
    header('Location: ../psikolog/jadwal-user.php');
    exit;
}

$psikolog = query("SELECT * FROM psikolog WHERE id = ?", [$psikolog_id])->fetch_assoc();
if (!$psikolog) {
    header('Location: ../psikologi/jadwal-user.php');
    exit;
}

// Get active services
$layananResult = query("
    SELECT id, nama_layanan, durasi_menit, tarif 
    FROM jenis_layanan 
    WHERE status = 'Aktif' 
    ORDER BY nama_layanan
");

require '../../includes/header-user.php';
require '../../includes/navbar-user.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Buat Janji Temu</h3>
                    
                    <!-- Selected Psychologist Info -->
                    <div class="selected-psikolog mb-4">
                        <div class="d-flex align-items-center">
                            <img src="<?= $main_url ?>uploads/psikolog/<?= $psikolog['foto'] ?>" 
                                 alt="<?= htmlspecialchars($psikolog['nama']) ?>"
                                 class="rounded-circle me-3"
                                 style="width: 60px; height: 60px; object-fit: cover;">
                            <div>
                                <h5 class="mb-1"><?= htmlspecialchars($psikolog['nama']) ?></h5>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-award"></i> <?= htmlspecialchars($psikolog['spesialisasi']) ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <form id="appointmentForm" method="POST" action="process.php">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="psikolog_id" value="<?= $psikolog_id ?>">
                        
                        <!-- Service Selection -->
                        <div class="mb-4">
                            <label class="form-label">Pilih Layanan</label>
                            <div class="row">
                                <?php while ($layanan = $layananResult->fetch_assoc()): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card service-card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" 
                                                       name="layanan_id" 
                                                       value="<?= $layanan['id'] ?>"
                                                       data-durasi="<?= $layanan['durasi_menit'] ?>"
                                                       id="layanan_<?= $layanan['id'] ?>" required>
                                                <label class="form-check-label" for="layanan_<?= $layanan['id'] ?>">
                                                    <strong><?= htmlspecialchars($layanan['nama_layanan']) ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="bi bi-clock"></i> <?= $layanan['durasi_menit'] ?> menit
                                                        <br>
                                                        <i class="bi bi-tag"></i> Rp <?= number_format($layanan['tarif'], 0, ',', '.') ?>
                                                    </small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <!-- Date and Time Selection -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal" class="form-label">Pilih Tanggal</label>
                                    <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="jam_mulai" class="form-label">Pilih Jam</label>
                                    <select class="form-select" id="jam_mulai" name="jam_mulai" required disabled>
                                        <option value="">Pilih tanggal terlebih dahulu</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Consultation Details -->
                        <div class="mb-4">
                            <label for="keluhan_utama" class="form-label">Keluhan Utama</label>
                            <textarea class="form-control" id="keluhan_utama" name="keluhan_utama" 
                                    rows="3" required></textarea>
                            <div class="form-text">Ceritakan keluhan atau masalah yang ingin Anda konsultasikan</div>
                        </div>

                        <div class="mb-4">
                            <label for="durasi_keluhan" class="form-label">Sudah Berapa Lama?</label>
                            <input type="text" class="form-control" id="durasi_keluhan" name="durasi_keluhan" 
                                   placeholder="Contoh: 2 minggu, 1 bulan" required>
                        </div>

                        <div class="mb-4">
                            <label for="harapan_konsultasi" class="form-label">Apa yang Anda Harapkan dari Konsultasi Ini?</label>
                            <textarea class="form-control" id="harapan_konsultasi" name="harapan_konsultasi" 
                                    rows="2" required></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-calendar2-check"></i> Buat Janji Temu
                            </button>
                            <a href="../psikologi/jadwal-user.php" class="btn btn-light">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require '../../includes/footer-user.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('appointmentForm');
    const tanggalInput = document.getElementById('tanggal');
    const jamSelect = document.getElementById('jam_mulai');
    const layananInputs = document.querySelectorAll('input[name="layanan_id"]');

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    tanggalInput.min = today;

    // Handle date and service selection
    async function updateAvailableSlots() {
        const tanggal = tanggalInput.value;
        const selectedLayanan = document.querySelector('input[name="layanan_id"]:checked');
        
        if (!tanggal || !selectedLayanan) return;

        const durasi = selectedLayanan.dataset.durasi;
        const psikologId = <?= $psikolog_id ?>;

        try {
            const response = await fetch('get_available_slots.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `psikolog_id=${psikologId}&tanggal=${tanggal}&durasi=${durasi}`
            });

            const slots = await response.json();
            
            jamSelect.innerHTML = '<option value="">Pilih waktu</option>';
            jamSelect.disabled = false;

            if (slots.length === 0) {
                jamSelect.innerHTML += '<option value="" disabled>Tidak ada slot tersedia</option>';
            } else {
                slots.forEach(slot => {
                    jamSelect.innerHTML += `<option value="${slot.start}">${slot.start} - ${slot.end}</option>`;
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'Gagal mengambil jadwal tersedia', 'error');
        }
    }

    tanggalInput.addEventListener('change', updateAvailableSlots);
    layananInputs.forEach(input => {
        input.addEventListener('change', updateAvailableSlots);
    });

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        try {
            const formData = new FormData(this);
            const response = await fetch('process.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.status === 'success') {
                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: result.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                window.location.href = 'index-user.php';
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        }
    });
});
</script>