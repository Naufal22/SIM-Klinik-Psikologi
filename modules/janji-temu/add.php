<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';

requireRole(ROLE_ADMIN);
checkSessionTimeout();

$title = "Tambah Janji Temu - Klinik";
$activePage = 'janji_temu';

try {
    // Get all active patients
    $pasienResult = query("
        SELECT id, nomor_rekam_medis, nama_lengkap 
        FROM pasien 
        WHERE status = 'Aktif' 
        ORDER BY nama_lengkap
    ");

    // Get all active psychologists
    $psikologResult = query("
        SELECT id, nama 
        FROM psikolog 
        WHERE status = 'Aktif' 
        ORDER BY nama
    ");

    // Get all active services
    $layananResult = query("
        SELECT id, nama_layanan, durasi_menit 
        FROM jenis_layanan 
        WHERE status = 'Aktif' 
        ORDER BY nama_layanan
    ");

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
                    <h3>Tambah Janji Temu</h3>
                    <p class="text-subtitle text-muted">Buat janji temu baru</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Janji Temu</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Tambah</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Form Janji Temu</h4>
                </div>
                <div class="card-body">
                    <form id="appointmentForm" method="POST" action="process.php">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pasien_id">Pasien</label>
                                    <select class="form-select" id="pasien_id" name="pasien_id" required>
                                        <option value="">Pilih Pasien</option>
                                        <?php while ($pasien = $pasienResult->fetch_assoc()): ?>
                                            <option value="<?= $pasien['id'] ?>">
                                                <?= htmlspecialchars($pasien['nomor_rekam_medis'] . ' - ' . $pasien['nama_lengkap']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="psikolog_id">Psikolog</label>
                                    <select class="form-select" id="psikolog_id" name="psikolog_id" required>
                                        <option value="">Pilih Psikolog</option>
                                        <?php while ($psikolog = $psikologResult->fetch_assoc()): ?>
                                            <option value="<?= $psikolog['id'] ?>">
                                                <?= htmlspecialchars($psikolog['nama']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="layanan_id">Layanan</label>
                                    <select class="form-select" id="layanan_id" name="layanan_id" required>
                                        <option value="">Pilih Layanan</option>
                                        <?php while ($layanan = $layananResult->fetch_assoc()): ?>
                                            <option value="<?= $layanan['id'] ?>" 
                                                    data-durasi="<?= $layanan['durasi_menit'] ?>">
                                                <?= htmlspecialchars($layanan['nama_layanan']) ?> 
                                                (<?= $layanan['durasi_menit'] ?> menit)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tanggal">Tanggal</label>
                                    <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="jam_mulai">Jam Mulai</label>
                                    <select class="form-select" id="jam_mulai" name="jam_mulai" required disabled>
                                        <option value="">Pilih tanggal & layanan dahulu</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="jam_selesai">Jam Selesai</label>
                                    <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Alasan Kunjungan Fields -->
                        <div class="form-group">
                            <label for="keluhan_utama">Keluhan Utama</label>
                            <textarea class="form-control" id="keluhan_utama" name="keluhan_utama" rows="3" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="durasi_keluhan">Durasi Keluhan</label>
                            <input type="text" class="form-control" id="durasi_keluhan" name="durasi_keluhan" 
                                   placeholder="Contoh: 2 minggu, 1 bulan" required>
                        </div>

                        <div class="form-group">
                            <label for="riwayat_pengobatan">Riwayat Pengobatan</label>
                            <textarea class="form-control" id="riwayat_pengobatan" name="riwayat_pengobatan" rows="2"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="harapan_konsultasi">Harapan dari Konsultasi</label>
                            <textarea class="form-control" id="harapan_konsultasi" name="harapan_konsultasi" rows="2" required></textarea>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="index.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('appointmentForm');
    const tanggalInput = document.getElementById('tanggal');
    const jamMulaiSelect = document.getElementById('jam_mulai');
    const jamSelesaiInput = document.getElementById('jam_selesai');
    const psikologSelect = document.getElementById('psikolog_id');
    const layananSelect = document.getElementById('layanan_id');

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    tanggalInput.min = today;

    // Function to update available slots
    async function updateAvailableSlots() {
        const tanggal = tanggalInput.value;
        const psikologId = psikologSelect.value;
        const layananId = layananSelect.value;
        
        if (!tanggal || !psikologId || !layananId) {
            jamMulaiSelect.disabled = true;
            jamMulaiSelect.innerHTML = '<option value="">Pilih tanggal & layanan dahulu</option>';
            return;
        }

        const durasi = layananSelect.options[layananSelect.selectedIndex].dataset.durasi;

        try {
            const response = await fetch('get_available_slots.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `psikolog_id=${psikologId}&tanggal=${tanggal}&durasi=${durasi}`
            });

            const slots = await response.json();
            
            jamMulaiSelect.innerHTML = '<option value="">Pilih waktu</option>';
            jamMulaiSelect.disabled = false;

            if (slots.length === 0) {
                jamMulaiSelect.innerHTML += '<option value="" disabled>Tidak ada slot tersedia</option>';
            } else {
                slots.forEach(slot => {
                    jamMulaiSelect.innerHTML += `<option value="${slot.start}" data-end="${slot.end}">
                        ${slot.start} - ${slot.end}
                    </option>`;
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'Gagal mengambil jadwal tersedia', 'error');
        }
    }

    // Update jam selesai when jam mulai is selected
    jamMulaiSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.dataset.end) {
            jamSelesaiInput.value = selectedOption.dataset.end;
        }
    });

    // Event listeners for form inputs
    tanggalInput.addEventListener('change', updateAvailableSlots);
    psikologSelect.addEventListener('change', updateAvailableSlots);
    layananSelect.addEventListener('change', updateAvailableSlots);

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validasi pasien dipilih
        const pasienId = document.getElementById('pasien_id').value;
        if (!pasienId) {
            Swal.fire('Error', 'Silakan pilih pasien', 'error');
            return;
        }
        
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
                window.location.href = 'index.php';
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        }
    });
});
</script>