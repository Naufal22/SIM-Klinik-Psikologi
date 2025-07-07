<?php
session_start();

require_once 'functions.php';

require_once '../../config/database.php';
require_once '../../auth/auth.php';
require_once '../../auth/session_check.php';
checkSession();
// require_once '../../auth/functions.php';


requireRole([ROLE_ADMIN, ROLE_PASIEN]);
checkSessionTimeout();


$title = "Tambah Pasien Baru";
$activePage = 'pasien';

if ($_SESSION['role'] == ROLE_PASIEN) {
    $title = "Data Diri";
    $activePage = 'data-diri';
    require '../../includes/header-user.php';
    require '../../includes/navbar-user.php';
} else {
    $title = "Tambah Pasien Baru";
    $activePage = 'pasien';
    require '../../includes/header.php';
    require '../../includes/sidebar.php';
    require '../../includes/navbar.php';
}

?>

<?php if ($_SESSION['role'] == ROLE_PASIEN) {
    ?>
    <div class="content-wrapper container">
    <?php
} else {
    ?>
    <div id="main-content">
    <?php
} ?>
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                <?php if ($_SESSION['role'] == ROLE_PASIEN) { ?>
                    <h3>Data Diri</h3>
                    <p class="text-subtitle text-muted">Lengkapi data diri anda</p>
                    <?php
                } else {
                    ?>
                    <h3>Tambah Pasien Baru</h3>
                    <p class="text-subtitle text-muted">Form input data pasien baru</p>
                    <?php
                } ?>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                        <?php if ($_SESSION['role'] == ROLE_PASIEN) { ?>
                            <li class="breadcrumb-item"><a href="<?= $main_url ?>/modules/dashboard/pasien/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Data Diri</li>
                        <?php
                        } else {
                        ?>
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Pasien</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Tambah</li>
                        <?php
                        } ?>

                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Data Pasien</h4>
                </div>
                <div class="card-body">
                    <form id="formTambahPasien" class="form">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_lengkap">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                                </div>

                                <div class="form-group">
                                    <label for="tanggal_lahir">Tanggal Lahir</label>
                                    <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                                </div>

                                <div class="form-group">
                                    <label for="jenis_kelamin">Jenis Kelamin</label>
                                    <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="alamat">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_telepon">No. Telepon</label>
                                    <input type="tel" class="form-control" id="no_telepon" name="no_telepon" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                    <small class="text-muted">Opsional</small>
                                </div>

                                <div class="form-group">
                                    <label>Kontak Darurat</label>
                                    <input type="text" class="form-control mb-2" name="kontak_darurat_nama" placeholder="Nama Kontak Darurat" required>
                                    <input type="tel" class="form-control" name="kontak_darurat_telepon" placeholder="No. Telepon Darurat" required>
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

    <?php if ($_SESSION['role'] == ROLE_PASIEN) {
    require '../../includes/footer-user.php';
} else {
    require '../../includes/footer.php';
} ?>
</div>

<!-- Core Scripts -->


<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formTambahPasien');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
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

        // Create FormData object
        const formData = new FormData(form);
        
        // Convert FormData to URL-encoded string
        const data = new URLSearchParams(formData);

        // Send POST request
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
                        // Cek apakah ada URL redirect khusus
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.href = 'index.php';
                        }
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

    // Optional: Add client-side validation for phone numbers
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            // Remove non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
});
</script>

</body>
</html>