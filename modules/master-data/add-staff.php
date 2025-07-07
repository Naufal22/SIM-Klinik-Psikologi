<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';

requireRole([ROLE_ADMIN, ROLE_MASTER]);
checkSessionTimeout();

$title = "Tambah Akun Staff - Master Data";
$activePage = 'master-data';

require '../../includes/header.php';
require '../../includes/sidebar.php';
require '../../includes/navbar.php';
?>

<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Tambah Akun Staff</h3>
                    <p class="text-subtitle text-muted">Tambah akun staff baru</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="staff.php">Manajemen Akun Staff</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Tambah Akun</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Form Tambah Akun Staff</h4>
                </div>
                <div class="card-body">
                    <form action="process.php" method="POST" class="form" id="staffForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>

                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password">Konfirmasi Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role">Role</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="">Pilih Role</option>
                                        <option value="admin">Admin</option>
                                        <option value="psikolog">Psikolog</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="Aktif">Aktif</option>
                                        <option value="Tidak Aktif">Tidak Aktif</option>
                                    </select>
                                </div>

                                <div id="psikologNote" class="alert alert-info mt-3" style="display: none;">
                                    <i class="bi bi-info-circle"></i> Setelah membuat akun psikolog, Anda akan diarahkan ke halaman pengisian data psikolog.
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="return_url" value="<?= $main_url ?>modules/master-data/staff.php">

                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary me-1 mb-1">Submit</button>
                            <button type="reset" class="btn btn-light-secondary me-1 mb-1">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('#staffForm');
    const roleSelect = document.getElementById('role');
    const psikologNote = document.getElementById('psikologNote');

    roleSelect.addEventListener('change', function() {
        psikologNote.style.display = this.value === 'psikolog' ? 'block' : 'none';
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const formData = new FormData(this);
            
            // Validasi password
            if (formData.get('password') !== formData.get('confirm_password')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Password dan konfirmasi password tidak cocok!'
                });
                return;
            }

            const response = await fetch('process.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            
            if (data.status === 'success') {
                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                });

                // Redirect sesuai role
                if (formData.get('role') === 'psikolog') {
                    window.location.href = data.redirect;
                } else {
                    window.location.href = '<?= $main_url ?>modules/master-data/staff.php';
                }
            } else {
                throw new Error(data.message || 'Terjadi kesalahan');
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'Terjadi kesalahan pada server'
            });
        }
    });
});
</script>

</body>
</html>
