<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
// require_once '../../auth/functions.php';


requireRole(ROLE_ADMIN);
checkSessionTimeout();

$title = "Edit Janji Temu - Klinik";
$activePage = 'janji_temu';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

try {
    // Get appointment details with alasan_kunjungan
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
        WHERE jt.id = ? AND jt.status = 'Terjadwal'
    ", [$id]);

    $appointment = $result->fetch_assoc();

    if (!$appointment) {
        header('Location: index.php');
        exit;
    }

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
                    <h3>Edit Janji Temu</h3>
                    <p class="text-subtitle text-muted">Update informasi janji temu</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Janji Temu</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Form Edit Janji Temu</h4>
                </div>
                <div class="card-body">
                    <form id="editForm" method="POST" action="process.php">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nomor Rekam Medis</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($appointment['nomor_rekam_medis']) ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Pasien</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($appointment['pasien_nama']) ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="psikolog_id">Psikolog</label>
                                    <select class="form-select" id="psikolog_id" name="psikolog_id" required>
                                        <?php while ($psikolog = $psikologResult->fetch_assoc()): ?>
                                            <option value="<?= $psikolog['id'] ?>" <?= $psikolog['id'] == $appointment['psikolog_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($psikolog['nama']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="layanan_id">Layanan</label>
                                    <select class="form-select" id="layanan_id" name="layanan_id" required>
                                        <?php while ($layanan = $layananResult->fetch_assoc()): ?>
                                            <option value="<?= $layanan['id'] ?>" 
                                                    data-durasi="<?= $layanan['durasi_menit'] ?>"
                                                    <?= $layanan['id'] == $appointment['layanan_id'] ? 'selected' : '' ?>>
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
                                    <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                           value="<?= $appointment['tanggal'] ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="jam_mulai">Jam Mulai</label>
                                    <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" 
                                           value="<?= $appointment['jam_mulai'] ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="jam_selesai">Jam Selesai</label>
                                    <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" 
                                           value="<?= $appointment['jam_selesai'] ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Alasan Kunjungan Fields -->
                        <div class="form-group">
                            <label for="keluhan_utama">Keluhan Utama</label>
                            <textarea class="form-control" id="keluhan_utama" name="keluhan_utama" rows="3" required><?= htmlspecialchars($appointment['keluhan_utama']) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="durasi_keluhan">Durasi Keluhan</label>
                            <input type="text" class="form-control" id="durasi_keluhan" name="durasi_keluhan" 
                                   value="<?= htmlspecialchars($appointment['durasi_keluhan']) ?>"
                                   placeholder="Contoh: 2 minggu, 1 bulan">
                        </div>

                        <div class="form-group">
                            <label for="riwayat_pengobatan">Riwayat Pengobatan</label>
                            <textarea class="form-control" id="riwayat_pengobatan" name="riwayat_pengobatan" rows="2"><?= htmlspecialchars($appointment['riwayat_pengobatan']) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="harapan_konsultasi">Harapan dari Konsultasi</label>
                            <textarea class="form-control" id="harapan_konsultasi" name="harapan_konsultasi" rows="2"><?= htmlspecialchars($appointment['harapan_konsultasi']) ?></textarea>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="view.php?id=<?= $id ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>

<!-- Tambahkan SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const jamMulaiInput = document.getElementById('jam_mulai');
    const jamSelesaiInput = document.getElementById('jam_selesai');
    const layananSelect = document.getElementById('layanan_id');
    
    function updateJamSelesai() {
        const jamMulai = jamMulaiInput.value;
        if (!jamMulai) return;
        
        const durasi = parseInt(layananSelect.options[layananSelect.selectedIndex].dataset.durasi);
        const [hours, minutes] = jamMulai.split(':');
        const startDate = new Date(2000, 0, 1, hours, minutes);
        const endDate = new Date(startDate.getTime() + durasi * 60000);
        
        const endHours = endDate.getHours().toString().padStart(2, '0');
        const endMinutes = endDate.getMinutes().toString().padStart(2, '0');
        jamSelesaiInput.value = `${endHours}:${endMinutes}`;
    }
    
    jamMulaiInput.addEventListener('change', updateJamSelesai);
    layananSelect.addEventListener('change', updateJamSelesai);
    
    // Form submission handling
    const form = document.getElementById('editForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Basic validation
        const tanggal = document.getElementById('tanggal').value;
        const jamMulai = document.getElementById('jam_mulai').value;
        const keluhan = document.getElementById('keluhan_utama').value.trim();
        
        if (!tanggal || !jamMulai || !keluhan) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'Mohon lengkapi semua field yang diperlukan',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // Check if date is in the past
        const selectedDate = new Date(tanggal);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'Tidak dapat memilih tanggal yang sudah lewat',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // Show loading state
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
        
        // Submit form using fetch
        fetch('process.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed && data.redirect) {
                        window.location.href = data.redirect;
                    }
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
                text: 'Terjadi kesalahan saat memproses permintaan',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    });
});
</script>