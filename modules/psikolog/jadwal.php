<?php
session_start();
require_once '../../config/database.php';
// require_once '../../auth/functions.php';
// require_once '../../auth/session.php';
require_once '../../auth/auth.php';
require_once 'functions.php';


requireRole([ROLE_ADMIN, ROLE_PSIKOLOG]);
checkSessionTimeout();

// fungsi untuk cek role
// function isAdmin() {
//     return isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ADMIN;
// }


$title = "Jadwal Psikolog - Klinik";
$activePage = 'jadwal_psikolog';

require '../../includes/header.php';
?>

<link rel="stylesheet" href="<?= $main_url ?>_dist/assets/extensions/simple-datatables/style.css">
<link rel="stylesheet" href="<?= $main_url ?>_dist/assets/compiled/css/table-datatable.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="<?= $main_url ?>_dist/assets/extensions/flatpickr/flatpickr.min.css">
<style>

    .jadwal-saya {
        background-color: #e3f2fd !important;
    }
    .jadwal-saya td {
        font-weight: 500;
    }
</style>

<?php
require '../../includes/sidebar.php';
require '../../includes/navbar.php';
?>

<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Jadwal Psikolog</h3>
                    <p class="text-subtitle text-muted">Kelola jadwal praktik psikolog</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Psikolog</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Jadwal</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Jadwal Praktik Psikolog</h5>
                    <?php if (isAdmin()): ?>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalJadwal">
                            <i class="bi bi-plus"></i> Tambah Jadwal
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php
                // Dapatkan ID psikolog dari user yang login
                $logged_in_psikolog_id = null;
                if (isPsikolog()) {
                    $stmt = $conn->prepare("SELECT reference_id FROM users WHERE id = ?");
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        $logged_in_psikolog_id = $row['reference_id'];
                    }
                }
                ?>

                <div class="table-responsive">
                    <table class="table table-striped" id="tableJadwal">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Psikolog</th>
                                <th>Hari</th>
                                <th>Jam Praktik</th>
                                <th>Status</th>
                                <?php if (isAdmin()): ?>
                                    <th>Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT j.*, p.nama as nama_psikolog 
                                     FROM jadwal_psikolog j 
                                     JOIN psikolog p ON j.psikolog_id = p.id 
                                     ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), 
                                     j.jam_mulai";
                            $result = query($query);
                            $no = 1;
                            while ($row = $result->fetch_assoc()):
                                // Tentukan apakah ini jadwal psikolog yang sedang login
                                $is_my_schedule = ($logged_in_psikolog_id && $logged_in_psikolog_id == $row['psikolog_id']);
                            ?>
                            <tr class="<?= $is_my_schedule ? 'jadwal-saya' : '' ?>">
                                <td><?= $no++ ?></td>
                                <td>
                                    <?= htmlspecialchars($row['nama_psikolog']) ?>
                                    <?php if ($is_my_schedule): ?>
                                        <span class="badge bg-primary">Jadwal Saya</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $row['hari'] ?></td>
                                <td><?= date('H:i', strtotime($row['jam_mulai'])) ?> - <?= date('H:i', strtotime($row['jam_selesai'])) ?></td>
                                <td>
                                    <span class="badge <?= $row['status'] == 'Aktif' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <?php if (isAdmin()): ?>
                                <td>
                                    <div class="buttons">
                                        <button type="button" class="btn icon btn-warning btn-sm me-1" 
                                                onclick="editJadwal(<?= $row['id'] ?>)" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <?php if($row['status'] == 'Aktif'): ?>
                                            <button type="button" class="btn icon btn-danger btn-sm" 
                                                    onclick="toggleStatus(<?= $row['id'] ?>, 'nonaktif')" title="Non-aktifkan">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn icon btn-success btn-sm" 
                                                    onclick="toggleStatus(<?= $row['id'] ?>, 'aktif')" title="Aktifkan">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>


<!-- Modal Tambah/Edit Jadwal -->
<div class="modal fade" id="modalJadwal" tabindex="-1" aria-labelledby="modalJadwalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalJadwalLabel">Tambah Jadwal Praktik</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formJadwal" action="process.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_jadwal">
                    <input type="hidden" name="jadwal_id" id="jadwal_id">

                    <div class="mb-3">
                        <label for="psikolog_id" class="form-label">Psikolog</label>
                        <select class="form-select" id="psikolog_id" name="psikolog_id" required>
                            <option value="">Pilih Psikolog</option>
                            <?php
                            $psikolog = getPsikologAktif();
                            foreach ($psikolog as $p):
                            ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="hari" class="form-label">Hari</label>
                        <select class="form-select" id="hari" name="hari" required>
                            <option value="">Pilih Hari</option>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                            <option value="Minggu">Minggu</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jam_mulai" class="form-label">Jam Mulai</label>
                                <input type="text" class="form-control flatpickr-time" id="jam_mulai" 
                                       name="jam_mulai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jam_selesai" class="form-label">Jam Selesai</label>
                                <input type="text" class="form-control flatpickr-time" id="jam_selesai" 
                                       name="jam_selesai" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>

<!-- DataTables -->
<script src="<?= $main_url ?>_dist/assets/extensions/simple-datatables/umd/simple-datatables.js"></script>
<script src="<?= $main_url ?>_dist/assets/static/js/pages/simple-datatables.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Flatpickr -->
<script src="<?= $main_url ?>_dist/assets/extensions/flatpickr/flatpickr.min.js"></script>

<!-- Custom JS -->
<script src="jadwal_handler.js"></script>


<script>
// document.addEventListener('DOMContentLoaded', function() {
//     // Initialize DataTable
//     new simpleDatatables.DataTable('#tableJadwal');

//     // Initialize Flatpickr for time inputs
//     flatpickr(".flatpickr-time", {
//         enableTime: true,
//         noCalendar: true,
//         dateFormat: "H:i",
//         time_24hr: true,
//         minuteIncrement: 30
//     });

//     // Reset form when modal is closed
//     document.getElementById('modalJadwal').addEventListener('hidden.bs.modal', function () {
//         document.getElementById('formJadwal').reset();
//         document.getElementById('jadwal_id').value = '';
//         document.getElementById('modalJadwalLabel').textContent = 'Tambah Jadwal Praktik';
//         document.querySelector('input[name="action"]').value = 'add_jadwal';
//     });
// });

// <?php if (isAdmin()): ?>
//     function editJadwal(id) {
//         fetch(`get_jadwal.php?id=${id}`)
//         .then(response => response.json())
//         .then(data => {
//             document.getElementById('jadwal_id').value = data.id;
//             document.getElementById('psikolog_id').value = data.psikolog_id;
//             document.getElementById('hari').value = data.hari;
//             document.getElementById('jam_mulai').value = data.jam_mulai;
//             document.getElementById('jam_selesai').value = data.jam_selesai;
            
//             document.getElementById('modalJadwalLabel').textContent = 'Edit Jadwal Praktik';
//             document.querySelector('input[name="action"]').value = 'edit_jadwal';
            
//             new bootstrap.Modal(document.getElementById('modalJadwal')).show();
//         })
//         .catch(error => {
//             Swal.fire('Error!', 'Gagal mengambil data jadwal', 'error');
//         });
//     }
// <?php endif; ?>

// function toggleStatus(id, status) {
//     const title = status === 'aktif' ? 'Aktifkan Jadwal' : 'Non-aktifkan Jadwal';
//     const text = status === 'aktif' ? 
//         'Apakah Anda yakin ingin mengaktifkan jadwal ini?' : 
//         'Apakah Anda yakin ingin menonaktifkan jadwal ini?';
    
//     Swal.fire({
//         title: title,
//         text: text,
//         icon: 'warning',
//         showCancelButton: true,
//         confirmButtonColor: status === 'aktif' ? '#28a745' : '#d33',
//         cancelButtonColor: '#3085d6',
//         confirmButtonText: status === 'aktif' ? 'Ya, Aktifkan!' : 'Ya, Non-aktifkan!',
//         cancelButtonText: 'Batal'
//     }).then((result) => {
//         if (result.isConfirmed) {
//             fetch('process.php', {
//                 method: 'POST',
//                 headers: {
//                     'Content-Type': 'application/x-www-form-urlencoded',
//                     'X-Requested-With': 'XMLHttpRequest'
//                 },
//                 body: `action=toggle_jadwal&id=${id}&status=${status}`
//             })
//             .then(response => response.json())
//             .then(data => {
//                 if (data.status === 'success') {
//                     Swal.fire('Berhasil!', data.message, 'success')
//                         .then(() => window.location.reload());
//                 } else {
//                     throw new Error(data.message);
//                 }
//             })
//             .catch(error => {
//                 Swal.fire('Error!', error.message, 'error');
//             });
//         }
//     });
// }
</script>