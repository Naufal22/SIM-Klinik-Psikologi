<?php
require_once '../../config/database.php';

$title = "Tambah Akun - Master Data";
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
                    <h3>Tambah Akun</h3>
                    <p class="text-subtitle text-muted">Tambah akun pengguna baru</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Master Data</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Tambah Akun</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Form Tambah Akun</h4>
                </div>
                <div class="card-body">
                    <form action="process.php" method="POST" class="form">
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
                                    <select class="form-select" id="role" name="role" required onchange="toggleReferenceSelect()">
                                        <option value="">Pilih Role</option>
                                        <option value="admin">Admin</option>
                                        <option value="psikolog">Psikolog</option>
                                        <option value="pasien">Pasien</option>
                                        <option value="staf">Staf</option>
                                    </select>
                                </div>

                                <div class="form-group" id="reference_container" style="display: none;">
                                    <label for="reference_id">Pilih Referensi</label>
                                    <select class="form-select" id="reference_id" name="reference_id">
                                        <option value="">Pilih Referensi</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="Aktif">Aktif</option>
                                        <option value="Tidak Aktif">Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary me-1 mb-1" name="action" value="add">Submit</button>
                            <button type="reset" class="btn btn-light-secondary me-1 mb-1">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>

<script>
function toggleReferenceSelect() {
    const role = document.getElementById('role').value;
    const referenceContainer = document.getElementById('reference_container');
    const referenceSelect = document.getElementById('reference_id');

    if (role === 'psikolog' || role === 'pasien') {
        referenceContainer.style.display = 'block';
        fetchReferences(role);
    } else {
        referenceContainer.style.display = 'none';
        referenceSelect.innerHTML = '<option value="">Pilih Referensi</option>';
    }
}

function fetchReferences(role) {
    const referenceSelect = document.getElementById('reference_id');
    
    fetch(`get_references.php?role=${role}`)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">Pilih Referensi</option>';
            data.forEach(item => {
                options += `<option value="${item.id}">${item.nama}</option>`;
            });
            referenceSelect.innerHTML = options;
        })
        .catch(error => console.error('Error:', error));
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (password !== confirmPassword) {
            e.preventDefault();
            Swal.fire({
                title: 'Error!',
                text: 'Password dan konfirmasi password tidak cocok!',
                icon: 'error'
            });
        }
    });
});
</script>
</body>
</html>