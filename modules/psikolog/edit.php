<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
require_once 'functions.php';


requireRole(ROLE_ADMIN);
checkSessionTimeout();

// if (!isset($_GET['id']) || empty($_GET['id'])) {
//     header('Location: index.php');
//     exit;
// }

$id = $_GET['id'];
$query = "SELECT * FROM psikolog WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$psikolog = $result->fetch_assoc();
$title = "Edit Psikolog - Klinik";
$activePage = 'psikolog';

require '../../includes/header.php';
?>

<link rel="stylesheet" href="<?= $main_url ?>_dist/assets/extensions/filepond/filepond.css">
<link rel="stylesheet" href="<?= $main_url ?>_dist/assets/extensions/filepond-plugin-image-preview/filepond-plugin-image-preview.css">
<link rel="stylesheet" href="<?= $main_url ?>_dist/assets/extensions/toastify-js/src/toastify.css">

<?php
require '../../includes/sidebar.php';
require '../../includes/navbar.php';
?>

<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Edit Psikolog</h3>
                    <p class="text-subtitle text-muted">Edit data psikolog</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Psikolog</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Form Edit Psikolog</h4>
                </div>
                <div class="card-body">
                <form id="formPsikolog" action="process.php" method="POST" enctype="multipart/form-data" class="form form-horizontal" onsubmit="console.log('Form submitted')">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?= $psikolog['id'] ?>">
                        
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="foto">Foto Profil</label>
                                </div>
                                <div class="col-md-8 form-group">
                                    <input type="file" name="foto" id="foto" 
                                           accept="image/*">
                                    <input type="hidden" name="foto_lama" value="<?= $psikolog['foto'] ?>">
                                    <small class="text-muted">Upload foto profil psikolog (Max 2MB)</small>
                                    <?php if($psikolog['foto']): ?>
                                        <div class="mt-2">
                                            <img src="<?= $main_url ?>uploads/psikolog/<?= $psikolog['foto'] ?>" 
                                                 alt="Foto <?= htmlspecialchars($psikolog['nama']) ?>"
                                                 class="rounded" width="150">
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-4">
                                    <label for="no_izin_praktik">Nomor Izin Praktik</label>
                                </div>
                                <div class="col-md-8 form-group">
                                    <input type="text" id="no_izin_praktik" 
                                           class="form-control" name="no_izin_praktik"
                                           value="<?= htmlspecialchars($psikolog['no_izin_praktik']) ?>"
                                           placeholder="Masukkan nomor izin praktik" required>
                                </div>

                                <div class="col-md-4">
                                    <label for="nama">Nama Lengkap</label>
                                </div>
                                <div class="col-md-8 form-group">
                                    <input type="text" id="nama" 
                                           class="form-control" name="nama"
                                           value="<?= htmlspecialchars($psikolog['nama']) ?>"
                                           placeholder="Masukkan nama lengkap" required>
                                </div>

                                <div class="col-md-4">
                                    <label for="email">Email</label>
                                </div>
                                <div class="col-md-8 form-group">
                                    <input type="email" id="email" 
                                           class="form-control" name="email"
                                           value="<?= htmlspecialchars($psikolog['email']) ?>"
                                           placeholder="Masukkan alamat email" required>
                                </div>

                                <div class="col-md-4">
                                    <label for="no_telepon">Nomor Telepon</label>
                                </div>
                                <div class="col-md-8 form-group">
                                    <input type="tel" id="no_telepon" 
                                           class="form-control" name="no_telepon"
                                           value="<?= htmlspecialchars($psikolog['no_telepon']) ?>"
                                           placeholder="Masukkan nomor telepon" required>
                                </div>

                                <div class="col-md-4">
                                    <label for="spesialisasi">Spesialisasi</label>
                                </div>
                                <div class="col-md-8 form-group">
                                    <input type="text" id="spesialisasi" 
                                           class="form-control" name="spesialisasi"
                                           value="<?= htmlspecialchars($psikolog['spesialisasi']) ?>"
                                           placeholder="Masukkan spesialisasi" required>
                                </div>

                                <div class="col-md-4">
                                    <label for="alamat">Alamat</label>
                                </div>
                                <div class="col-md-8 form-group">
                                    <textarea id="alamat" class="form-control" 
                                              name="alamat" rows="3"
                                              placeholder="Masukkan alamat lengkap" required><?= htmlspecialchars($psikolog['alamat']) ?></textarea>
                                </div>

                                <div class="col-md-4">
                                    <label for="status">Status</label>
                                </div>
                                <div class="col-md-8 form-group">
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="Aktif" <?= $psikolog['status'] == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                                        <option value="Cuti" <?= $psikolog['status'] == 'Cuti' ? 'selected' : '' ?>>Cuti</option>
                                        <option value="Tidak Aktif" <?= $psikolog['status'] == 'Tidak Aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                                    </select>
                                </div>

                                <div class="col-sm-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary me-1 mb-1">
                                        <i class="bi bi-save"></i> Simpan Perubahan
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
<script src="<?= $main_url ?>_dist/assets/static/js/pages/filepond.js"></script>

<!-- Form Validation -->
<script src="<?= $main_url ?>_dist/assets/extensions/jquery/jquery.min.js"></script>
<script src="<?= $main_url ?>_dist/assets/extensions/parsleyjs/parsley.min.js"></script>
<script src="<?= $main_url ?>_dist/assets/static/js/pages/parsley.js"></script>

// Hapus script yang duplikat dan gunakan satu event listener saja
<script>
// Initialize FilePond
FilePond.registerPlugin(
    FilePondPluginImagePreview,
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize
);

// Konfigurasi FilePond yang benar
const pond = FilePond.create(document.querySelector('input[type="file"]'), {
    allowImagePreview: true,
    allowFileTypeValidation: true,
    acceptedFileTypes: ['image/*'],
    allowFileSizeValidation: true,
    maxFileSize: '2MB',
    labelIdle: 'Drag & Drop foto atau <span class="filepond--label-action">Browse</span>',
    labelFileTypeNotAllowed: 'File harus berupa gambar',
    labelMaxFileSize: 'File terlalu besar, maksimal 2MB',
    required: false,
    // Nonaktifkan server endpoints
    server: null
});

// Satu event listener untuk form submit
document.getElementById('formPsikolog').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'edit');
    
    // Tambahkan file dari FilePond jika ada
    const pondFiles = pond.getFiles();
    if (pondFiles.length > 0 && pondFiles[0].file) {
        formData.set('foto', pondFiles[0].file);
    }
    
    try {
        const response = await fetch('process.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            Swal.fire({
                title: 'Berhasil!',
                text: data.message,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = data.redirect;
            });
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        Swal.fire({
            title: 'Error!',
            text: error.message || 'Terjadi kesalahan saat memproses data',
            icon: 'error'
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
