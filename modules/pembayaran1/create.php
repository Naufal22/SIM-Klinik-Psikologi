<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
require_once 'functions.php';

requireRole(ROLE_ADMIN);
checkSessionTimeout();

$title = "Tambah Pembayaran - Klinik";
$activePage = 'pembayaran';

require '../../includes/header.php';
require '../../includes/sidebar.php';
require '../../includes/navbar.php';

// Get unpaid appointments
$query = "SELECT 
            jt.id,
            jt.kode_janji,
            ps.nama_lengkap as nama_pasien,
            psi.nama as nama_psikolog,
            jl.nama_layanan,
            jl.tarif,
            jt.tanggal,
            jt.jam_mulai
        FROM janji_temu jt
        JOIN pasien ps ON jt.pasien_id = ps.id
        JOIN psikolog psi ON jt.psikolog_id = psi.id
        JOIN jenis_layanan jl ON jt.layanan_id = jl.id
        LEFT JOIN pembayaran p ON jt.id = p.janji_temu_id
        WHERE p.id IS NULL AND jt.status = 'Selesai'
        ORDER BY jt.tanggal DESC, jt.jam_mulai DESC";

$result = mysqli_query($conn, $query);
?>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Tambah Pembayaran</h3>
                    <p class="text-subtitle text-muted">Buat pembayaran baru untuk konsultasi</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Pembayaran</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Tambah</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Form Pembayaran</h4>
                </div>
                <div class="card-body">
                    <form action="process.php" method="POST" enctype="multipart/form-data" id="payment-form">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="janji_temu_id">Pilih Janji Temu</label>
                                    <select class="form-select" id="janji_temu_id" name="janji_temu_id" required>
                                        <option value="">Pilih Janji Temu</option>
                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                            <option value="<?= $row['id'] ?>" 
                                                    data-tarif="<?= $row['tarif'] ?>"
                                                    data-pasien="<?= htmlspecialchars($row['nama_pasien']) ?>"
                                                    data-layanan="<?= htmlspecialchars($row['nama_layanan']) ?>">
                                                <?= $row['kode_janji'] ?> - <?= htmlspecialchars($row['nama_pasien']) ?> 
                                                (<?= date('d/m/Y', strtotime($row['tanggal'])) ?> <?= date('H:i', strtotime($row['jam_mulai'])) ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="form-group mt-3">
                                    <label>Detail Layanan</label>
                                    <div id="detail_layanan" class="alert alert-light">
                                        Silahkan pilih janji temu terlebih dahulu
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="jumlah">Jumlah Pembayaran</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" id="jumlah" name="jumlah" required readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="metode_pembayaran">Metode Pembayaran</label>
                                    <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" required>
                                        <option value="">Pilih Metode</option>
                                        <option value="Tunai">Tunai</option>
                                        <option value="Transfer">Transfer Bank</option>
                                        <option value="Kartu Kredit">QRIS</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="status">Status Pembayaran</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="Lunas">Lunas</option>
                                        <option value="Pending">Pending (Khusus Transfer)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bukti_pembayaran">Bukti Pembayaran</label>
                                    <input type="file" class="form-control" id="bukti_pembayaran" name="bukti_pembayaran" accept="image/jpeg,image/png,application/pdf">
                                    <div class="bukti-pembayaran-info text-muted mt-2">
                                        <small>Format: JPG, PNG, PDF. Maks: 2MB</small>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="catatan">Catatan</label>
                                    <textarea class="form-control" id="catatan" name="catatan" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary me-1 mb-1">Simpan</button>
                            <a href="index.php" class="btn btn-light-secondary me-1 mb-1">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Payment form script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fungsi untuk memformat angka ke format rupiah
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR'
        }).format(angka);
    }

    // Handler untuk perubahan pada select janji temu
    document.getElementById('janji_temu_id').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var tarif = selectedOption.getAttribute('data-tarif');
        var pasien = selectedOption.getAttribute('data-pasien');
        var layanan = selectedOption.getAttribute('data-layanan');
        
        // Set jumlah pembayaran
        if (tarif) {
            document.getElementById('jumlah').value = tarif;
            
            // Update detail layanan
            var detailHtml = `
                <strong>Pasien:</strong> ${pasien}<br>
                <strong>Layanan:</strong> ${layanan}<br>
                <strong>Tarif:</strong> ${formatRupiah(tarif)}
            `;
            var detailElement = document.getElementById('detail_layanan');
            detailElement.innerHTML = detailHtml;
            detailElement.classList.remove('alert-light');
            detailElement.classList.add('alert-info');
        } else {
            document.getElementById('jumlah').value = '';
            var detailElement = document.getElementById('detail_layanan');
            detailElement.innerHTML = 'Silahkan pilih janji temu terlebih dahulu';
            detailElement.classList.remove('alert-info');
            detailElement.classList.add('alert-light');
        }
    });

    // Handler untuk metode pembayaran
    document.getElementById('metode_pembayaran').addEventListener('change', function() {
        var metode = this.value;
        var buktiPembayaran = document.getElementById('bukti_pembayaran');
        var status = document.getElementById('status');
        var buktiInfo = document.querySelector('.bukti-pembayaran-info');
        
        if(metode === 'Transfer') {
            buktiPembayaran.required = true;
            status.value = 'Pending';
            buktiInfo.style.display = 'block';
        } else {
            buktiPembayaran.required = false;
            status.value = 'Lunas';
            buktiInfo.style.display = 'none';
        }
    });

    // Handler untuk status pembayaran
    document.getElementById('status').addEventListener('change', function() {
        var status = this.value;
        var metode = document.getElementById('metode_pembayaran').value;
        var buktiPembayaran = document.getElementById('bukti_pembayaran');
        var buktiInfo = document.querySelector('.bukti-pembayaran-info');
        
        if(status === 'Lunas' && metode === 'Transfer') {
            buktiPembayaran.required = true;
            buktiInfo.style.display = 'block';
        } else if (status === 'Pending') {
            buktiPembayaran.required = false;
            buktiInfo.style.display = 'none';
        }
    });

    // Form submission handler with SweetAlert2
    document.getElementById('payment-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin menyimpan pembayaran ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    // Show SweetAlert2 for success/error messages
    <?php if (isset($_SESSION['success'])): ?>
        Swal.fire({
            title: 'Berhasil!',
            text: '<?= $_SESSION['success'] ?>',
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        Swal.fire({
            title: 'Error!',
            text: '<?= $_SESSION['error'] ?>',
            icon: 'error'
        });
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
});
</script>

</body>
</html>