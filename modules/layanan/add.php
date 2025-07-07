<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
// require_once '../../auth/functions.php';


requireRole(ROLE_ADMIN);
checkSessionTimeout();

$title = "Tambah Layanan - Klinik";
$activePage = 'layanan';

require '../../includes/header.php';
require '../../includes/sidebar.php';
require '../../includes/navbar.php';
?>

<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Tambah Layanan</h3>
                    <p class="text-subtitle text-muted">Tambah data layanan baru</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Layanan</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Tambah</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Form Tambah Layanan</h4>
                </div>
                <div class="card-body">
                    <form id="formTambahLayanan" class="form">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_layanan">Nama Layanan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama_layanan" name="nama_layanan" required maxlength="100">
                                </div>

                                <div class="form-group">
                                    <label for="durasi_menit">Durasi (Menit) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="durasi_menit" name="durasi_menit" required min="15">
                                </div>

                                <div class="form-group">
                                    <label for="tarif">Tarif (Rp) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="tarif" name="tarif" required>
                                </div>

                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="Aktif">Aktif</option>
                                        <option value="Tidak Aktif">Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="deskripsi">Deskripsi</label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary me-1 mb-1">Simpan</button>
                                <a href="index.php" class="btn btn-light-secondary me-1 mb-1">Batal</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <?php include_once '../../includes/footer.php'; ?>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- jQuery Mask Plugin -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize currency mask
    $('#tarif').mask('000.000.000', {reverse: true});

    const form = document.getElementById('formTambahLayanan');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Menyimpan Data',
            text: 'Mohon tunggu...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Get tarif value and remove dots
        let tarifValue = $('#tarif').val().replace(/\./g, '');
        
        const formData = new FormData(form);
        formData.set('tarif', tarifValue);
        const data = new URLSearchParams(formData);

        fetch('process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: data
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message,
                    icon: 'success',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'index.php';
                    }
                });
            } else {
                throw new Error(data.message || 'Terjadi kesalahan saat menyimpan data');
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error!',
                text: error.message || 'Terjadi kesalahan saat menyimpan data',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    });
});
</script>

</body>
</html>