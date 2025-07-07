<?php
session_start();

require_once 'functions.php';
require_once '../../config/database.php';
require_once '../../auth/auth.php';

// Izinkan akses untuk admin dan pasien
requireRole([ROLE_ADMIN, ROLE_PASIEN]);
checkSessionTimeout();

// Untuk pasien, ambil ID dari reference_id user
if ($_SESSION['role'] == ROLE_PASIEN) {
    $query = "SELECT reference_id FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $id = $user['reference_id'];
    
    // Jika belum ada reference_id, redirect ke add.php
    if (!$id) {
        header('Location: add.php');
        exit();
    }
} else {
    // Untuk admin, ambil ID dari parameter URL
    if (!isset($_GET['id'])) {
        header('Location: index.php');
        exit();
    }
    $id = $_GET['id'];
}

$pasien = getPasienById($id);
if (!$pasien) {
    header('Location: index.php');
    exit();
}

// Cek apakah pasien memiliki janji temu aktif
$query = "SELECT COUNT(*) as count FROM janji_temu 
          WHERE pasien_id = ? AND status IN ('Terjadwal', 'Check-in', 'Dalam_Konsultasi')";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$hasActiveAppointment = $row['count'] > 0;

$title = "Data Diri";
$activePage = $_SESSION['role'] == ROLE_PASIEN ? 'data-diri' : 'pasien';

if ($_SESSION['role'] == ROLE_PASIEN) {
    require '../../includes/header-user.php';
    require '../../includes/navbar-user.php';
} else {
    require '../../includes/header.php';
    require '../../includes/sidebar.php';
    require '../../includes/navbar.php';
}
?>

<?php if ($_SESSION['role'] == ROLE_PASIEN) { ?>
    <div class="content-wrapper container">
<?php } else { ?>
    <div id="main-content">
<?php } ?>
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><?= $_SESSION['role'] == ROLE_PASIEN ? 'Data Diri' : 'Edit Data Pasien' ?></h3>
                    <p class="text-subtitle text-muted">
                        <?= $_SESSION['role'] == ROLE_PASIEN ? 'Informasi data diri Anda' : 'Form edit data pasien' ?>
                    </p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <?php if ($_SESSION['role'] == ROLE_PASIEN) { ?>
                                <li class="breadcrumb-item"><a href="<?= $main_url ?>modules/dashboard/pasien/index.php">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Data Diri</li>
                            <?php } else { ?>
                                <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="index.php">Pasien</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Edit</li>
                            <?php } ?>
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
                    <form id="formEditPasien" class="form">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?= $pasien['id'] ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nomor_rekam_medis">Nomor Rekam Medis</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($pasien['nomor_rekam_medis']) ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="nama_lengkap">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                           value="<?= htmlspecialchars($pasien['nama_lengkap']) ?>" 
                                           <?= $hasActiveAppointment ? 'readonly' : 'required' ?>>
                                </div>

                                <div class="form-group">
                                    <label for="tanggal_lahir">Tanggal Lahir</label>
                                    <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" 
                                           value="<?= $pasien['tanggal_lahir'] ?>" 
                                           <?= $hasActiveAppointment ? 'readonly' : 'required' ?>>
                                </div>

                                <div class="form-group">
                                    <label for="jenis_kelamin">Jenis Kelamin</label>
                                    <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" 
                                            <?= $hasActiveAppointment ? 'disabled' : 'required' ?>>
                                        <option value="Laki-laki" <?= $pasien['jenis_kelamin'] == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                                        <option value="Perempuan" <?= $pasien['jenis_kelamin'] == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="alamat">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?= htmlspecialchars($pasien['alamat']) ?></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_telepon">No. Telepon</label>
                                    <input type="tel" class="form-control" id="no_telepon" name="no_telepon" 
                                           value="<?= htmlspecialchars($pasien['no_telepon']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($pasien['email']) ?>">
                                    <small class="text-muted">Opsional</small>
                                </div>

                                <div class="form-group">
                                    <label>Kontak Darurat</label>
                                    <input type="text" class="form-control mb-2" name="kontak_darurat_nama" 
                                           placeholder="Nama Kontak Darurat" 
                                           value="<?= htmlspecialchars($pasien['kontak_darurat_nama']) ?>" required>
                                    <input type="tel" class="form-control" name="kontak_darurat_telepon" 
                                           placeholder="No. Telepon Darurat" 
                                           value="<?= htmlspecialchars($pasien['kontak_darurat_telepon']) ?>" required>
                                </div>

                                <?php if ($_SESSION['role'] == ROLE_ADMIN) { ?>
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="Aktif" <?= $pasien['status'] == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                                            <option value="Tidak Aktif" <?= $pasien['status'] == 'Tidak Aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                                        </select>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <?php if ($hasActiveAppointment) { ?>
                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle"></i>
                                Beberapa data tidak dapat diubah karena memiliki janji temu yang aktif.
                            </div>
                        <?php } ?>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary me-1 mb-1">Update</button>
                                <?php if ($_SESSION['role'] == ROLE_PASIEN) { ?>
                                    <a href="<?= $main_url ?>modules/dashboard/pasien/index.php" class="btn btn-light-secondary me-1 mb-1">Kembali</a>
                                <?php } else { ?>
                                    <a href="index.php" class="btn btn-light-secondary me-1 mb-1">Batal</a>
                                <?php } ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <?php 
    if ($_SESSION['role'] == ROLE_PASIEN) {
        require '../../includes/footer-user.php';
    } else {
        require '../../includes/footer.php';
    }
    ?>
</div>

<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formEditPasien');
    const hasActiveAppointment = <?= json_encode($hasActiveAppointment) ?>;
    const userRole = <?= json_encode($_SESSION['role']) ?>;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Mengupdate Data',
            text: 'Mohon tunggu...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData(form);
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
                        if (userRole === 'pasien') {
                            window.location.href = '<?= $main_url ?>modules/dashboard/pasien/index.php';
                        } else {
                            window.location.href = 'index.php';
                        }
                    }
                });
            } else {
                throw new Error(data.message || 'Terjadi kesalahan saat mengupdate data');
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error!',
                text: error.message || 'Terjadi kesalahan saat mengupdate data',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    });

    // Phone number validation
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
});
</script>

</body>
</html>