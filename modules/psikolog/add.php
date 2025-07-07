<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
require_once 'functions.php';


requireRole([ROLE_MASTER,ROLE_ADMIN]);
checkSessionTimeout();

$title = "Tambah Psikolog - Klinik";
$activePage = 'psikolog';

require '../../includes/header.php';
?>

<link rel="stylesheet" href="<?= $main_url ?>_dist/assets/extensions/filepond/filepond.css">
<link rel="stylesheet" href="<?= $main_url ?>_dist/assets/extensions/filepond-plugin-image-preview/filepond-plugin-image-preview.css">
<link rel="stylesheet" href="<?= $main_url ?>_dist/assets/extensions/sweetalert2/sweetalert2.min.css">

<?php
require '../../includes/sidebar.php';
require '../../includes/navbar.php';
?>

<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Tambah Psikolog</h3>
                    <p class="text-subtitle text-muted">Tambah data psikolog baru</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Psikolog</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Tambah</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Form Tambah Psikolog</h4>
                </div>
                <div class="card-body">
                    <form id="formPsikolog" action="process.php" method="POST" enctype="multipart/form-data" class="form form-horizontal">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="foto">Foto Profil</label>
                                </div>
                                <div class="col-md-8 form-group">
                                    <input type="file" name="foto" id="foto" 
                                           accept="image/*" required>
                                    <small class="text-muted">Upload foto profil psikolog (Max 2MB)</small>
                                </div>

                                <div class="col-md-4">
                                    <label for="no_izin_praktik">Nomor Izin Praktik</label>
                                </div>
                                <div class="col-md-8 form-group">
                                    <input type="text" id="no_izin_praktik" 
                                           class="form-control" name="no_izin_praktik"
                                           placeholder="Masukkan nomor izin praktik" required>
                                </div>

                                <div class="col-md-4">
                                    <label for="nama">Nama Lengkap</label>
                                </div>
                                <div class="col-md-8 form-group">
                                    <input type="text" id="nama" 
                                           class="form-control" name="nama"
                                           placeholder="Masukkan nama lengkap" required>
                                </div>

                                <div class="col-md-4">
                                    <label for="email">Email</label>
                                </div>
                                <div class="col-md-8 form-group">
                                    <input type="email" id="email" 
                                           class="form-control" name="email"
                                           placeholder="Masukkan alamat email" required>
                                </div>

                                <div class="col-md-4">
                                    <label for="no_telepon">Nomor Telepon</label>
                                </div>
                                <div class="col-md-8 form-group">
                                    <input type="tel" id="no_telepon" 
                                           class="form-control" name="no_telepon"
                                           placeholder="Masukkan nomor telepon" required>
                                </div>

                                <div class="col-md-4">
                                    <label for="spesialisasi">Spesialisasi</label>
                                </div>
                                <div class="col-md-8 form-group">
                                    <input type="text" id="spesialisasi" 
                                           class="form-control" name="spesialisasi"
                                           placeholder="Masukkan spesialisasi" required>
                                </div>

                                <div class="col-md-4">
                                    <label for="alamat">Alamat</label>
                                </div>
                                <div class="col-md-8 form-group">
                                    <textarea id="alamat" class="form-control" 
                                              name="alamat" rows="3"
                                              placeholder="Masukkan alamat lengkap" required></textarea>
                                </div>

                                <div class="col-sm-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary me-1 mb-1" id="submitBtn">
                                        <i class="bi bi-save"></i> Simpan
                                    </button>
                                    <a href="index.php" class="btn btn-light-secondary mb-1">
                                        <i class="bi bi-x"></i> Batal
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>

<!-- FilePond -->
<script src="<?= $main_url ?>_dist/assets/extensions/filepond/filepond.js"></script>
<script src="<?= $main_url ?>_dist/assets/extensions/filepond-plugin-image-preview/filepond-plugin-image-preview.js"></script>
<script src="<?= $main_url ?>_dist/assets/extensions/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.js"></script>
<script src="<?= $main_url ?>_dist/assets/extensions/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.js"></script>

<!-- Form Validation -->
<script src="<?= $main_url ?>_dist/assets/extensions/jquery/jquery.min.js"></script>
<script src="<?= $main_url ?>_dist/assets/extensions/parsleyjs/parsley.min.js"></script>
<script src="<?= $main_url ?>_dist/assets/static/js/pages/parsley.js"></script>

<!-- SweetAlert2 -->
<script src="<?= $main_url ?>_dist/assets/extensions/sweetalert2/sweetalert2.min.js"></script>

<script>
// Initialize FilePond
const pond = FilePond.create(document.querySelector('input[type="file"]'), {
    allowImagePreview: true,
    allowFileTypeValidation: true,
    acceptedFileTypes: ['image/*'],
    allowFileSizeValidation: true,
    maxFileSize: '2MB',
    labelIdle: 'Drag & Drop foto atau <span class="filepond--label-action">Browse</span>',
    labelFileTypeNotAllowed: 'File harus berupa gambar',
    labelMaxFileSize: 'File terlalu besar, maksimal 2MB',
    // Tambahkan ini untuk memastikan file terkirim dengan benar
    server: {
        process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
            const formData = new FormData();
            formData.append('foto', file, file.name);
            
            // Simpan file ke FormData untuk pengiriman nanti
            window.pondFile = formData;
            
            // Beri tahu FilePond bahwa upload berhasil
            load(file.name);
        }
    }
});

// Form Validation and Submit
$('#formPsikolog').parsley();

$('#formPsikolog').on('submit', function(e) {
    e.preventDefault();
    
    if ($(this).parsley().isValid()) {
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="bi bi-hourglass-split"></i> Menyimpan...').prop('disabled', true);
        
        const formData = new FormData(this);
        
        // Tambahkan file dari FilePond jika ada
        if (window.pondFile) {
            const pondFileData = window.pondFile.get('foto');
            formData.set('foto', pondFileData);
        }
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = 'index.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message
                    });
                    submitBtn.html(originalText).prop('disabled', false);
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat menyimpan data'
                });
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    }
});

// Phone Number Validation
document.getElementById('no_telepon').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 13) value = value.slice(0, 13);
    e.target.value = value;
});
</script>