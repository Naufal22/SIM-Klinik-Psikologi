<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
// require_once '../../auth/functions.php';


requireRole([ROLE_ADMIN, ROLE_PSIKOLOG]);
checkSessionTimeout();



$title = "Psikolog - Klinik";
$activePage = 'psikolog';

require '../../includes/header.php';
?>
<link rel="stylesheet" href="<?= $main_url ?>_dist/assets/extensions/simple-datatables/style.css">
<link rel="stylesheet" href="<?= $main_url ?>_dist/assets/compiled/css/table-datatable.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<?php
require '../../includes/sidebar.php';
require '../../includes/navbar.php';
?>


<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Daftar Psikolog</h3>
                    <p class="text-subtitle text-muted">Kelola data psikolog klinik</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Psikolog</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Data Psikolog</h5>
                        <div>
                            <a href="jadwal.php" class="btn btn-success me-2">
                                <i class="bi bi-calendar"></i> Jadwal Psikolog
                            </a>
                            <?php if (isAdmin()): ?>
                                <a href="add.php" class="btn btn-primary">
                                    <i class="bi bi-plus"></i> Tambah Psikolog
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    $query = "SELECT p.*, 
                             (SELECT COUNT(*) FROM janji_temu WHERE psikolog_id = p.id) as total_konsultasi,
                             (SELECT COUNT(DISTINCT pasien_id) FROM janji_temu WHERE psikolog_id = p.id) as total_pasien
                             FROM psikolog p 
                             ORDER BY p.created_at DESC";
                    $result = query($query);
                    ?>
                    <table class="table table-striped" id="table-psikolog">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Foto</th>
                                <th>No. Izin Praktik</th>
                                <th>Nama</th>
                                <th>Spesialisasi</th>
                                <th>Total Pasien</th>
                                <th>Total Konsultasi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($row = $result->fetch_assoc()):
                                $foto = $row['foto'] ? $row['foto'] : 'default.png';
                            ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <img src="<?= $main_url ?>uploads/psikolog/<?= $foto ?>" 
                                             alt="Foto <?= htmlspecialchars($row['nama']) ?>"
                                             class="rounded-circle"
                                             width="40" height="40">
                                    </td>
                                    <td><?= htmlspecialchars($row['no_izin_praktik']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($row['nama']) ?>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($row['spesialisasi']) ?></td>
                                    <td><?= $row['total_pasien'] ?></td>
                                    <td><?= $row['total_konsultasi'] ?></td>
                                    <td>
                                        <span class="badge <?= $row['status'] == 'Aktif' ? 'bg-success' : ($row['status'] == 'Cuti' ? 'bg-warning' : 'bg-danger') ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="buttons">
                                            <a href="view.php?id=<?= $row['id'] ?>" class="btn icon btn-primary me-1" title="Lihat Detail">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                            <?php if (isAdmin()): ?>
                                            <a href="edit.php?id=<?= $row['id'] ?>" class="btn icon btn-warning me-1" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button type="button" class="btn icon btn-danger me-1" 
                                                onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama']) ?>')"
                                                title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                            <!-- <?php if (isAdmin()): ?>
                                                <?php if($row['status'] == 'Aktif'): ?>
                                                    <button type="button" class="btn icon btn-danger" 
                                                    onclick="confirmDeactivate(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama']) ?>')"
                                                    title="Non-aktifkan">
                                                    <i class="bi bi-person-x"></i>
                                                </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn icon btn-success" 
                                                    onclick="confirmActivate(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama']) ?>')"
                                                    title="Aktifkan">
                                                    <i class="bi bi-person-check"></i>
                                                </button>
                                                <?php endif; ?>
                                            <?php endif; ?> -->
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>

<!-- Core Scripts -->


<!-- DataTables Scripts -->
<script src="<?= $main_url ?>/_dist/assets/extensions/simple-datatables/umd/simple-datatables.js"></script>
<script src="<?= $main_url ?>/_dist/assets/static/js/pages/simple-datatables.js"></script>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let tablePsikolog = document.querySelector('#table-psikolog');
    if (tablePsikolog) {
        new simpleDatatables.DataTable(tablePsikolog);
    }
});

function confirmDelete(id, nama) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus psikolog "${nama}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=delete&id=${id}`
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
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan saat menghapus data');
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Terjadi kesalahan saat menghapus data',
                    icon: 'error'
                });
            });
        }
    });
}


function confirmDeactivate(id, nama) {
    Swal.fire({
        title: 'Konfirmasi Non-aktifkan',
        text: `Apakah Anda yakin ingin menon-aktifkan psikolog "${nama}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Non-aktifkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            processStatusChange(id, 'deactivate', nama);
        }
    });
}

function confirmActivate(id, nama) {
    Swal.fire({
        title: 'Konfirmasi Aktifkan',
        text: `Apakah Anda yakin ingin mengaktifkan kembali psikolog "${nama}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Aktifkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            processStatusChange(id, 'activate', nama);
        }
    });
}

function processStatusChange(id, action, nama) {
    fetch('process.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `action=${action}&id=${id}`
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
                icon: 'success'
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(data.message || 'Terjadi kesalahan saat memproses permintaan');
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error!',
            text: error.message || 'Terjadi kesalahan saat memproses permintaan',
            icon: 'error'
        });
    });
}
</script>