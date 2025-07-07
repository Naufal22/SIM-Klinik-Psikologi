<?php
session_start();
require_once 'functions.php';
require_once '../../config/database.php';
require_once '../../auth/auth.php';

requireRole(ROLE_PSIKOLOG);
checkSessionTimeout();

$title = "Konsultasi - Klinik";
$activePage = 'konsultasi';

require '../../includes/header.php';
?>
<!-- DataTables CSS -->
<link rel="stylesheet" href="<?= $main_url ?>_dist/assets/extensions/simple-datatables/style.css">
<link rel="stylesheet" href="<?= $main_url ?>_dist/assets/compiled/css/table-datatable.css">

<?php
require '../../includes/sidebar.php';
require '../../includes/navbar.php';
?>

<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Konsultasi</h3>
                    <p class="text-subtitle text-muted">Kelola catatan konsultasi pasien dengan psikolog</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Konsultasi</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="filter-container d-flex gap-3 align-items-end">
                            <div class="form-group mb-0">
                                <label for="filterStatus" class="form-label">Status</label>
                                <select id="filterStatus" class="form-select form-select-sm" style="min-width: 150px;">
                                    <option value="">Semua Status</option>
                                    <option value="Terjadwal">Terjadwal</option>
                                    <option value="Check-in">Check-in</option>
                                    <option value="Dalam Konsultasi">Dalam Konsultasi</option>
                                    <option value="Selesai">Selesai</option>
                                    <option value="Dibatalkan">Dibatalkan</option>
                                    <option value="Tidak Hadir">Tidak Hadir</option>
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <label for="filterTanggal" class="form-label">Tanggal</label>
                                <input type="date" id="filterTanggal" class="form-control form-control-sm" style="min-width: 150px;">
                            </div>
                            <div class="form-group mb-0">
                                <label for="filterLayanan" class="form-label">Layanan</label>
                                <select id="filterLayanan" class="form-select form-select-sm" style="min-width: 150px;">
                                    <option value="">Semua Layanan</option>
                                    <?php
                                    $query = "SELECT id, nama_layanan FROM jenis_layanan WHERE status = 'Aktif' ORDER BY nama_layanan";
                                    $result = mysqli_query($conn, $query);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<option value='{$row['nama_layanan']}'>{$row['nama_layanan']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['success'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['error'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table" id="tabelKonsultasi">
                            <thead>
                                <tr>
                                    <th>Kode Janji</th>
                                    <th>Tanggal</th>
                                    <th>Jam</th>
                                    <th>Pasien</th>
                                    <th>Layanan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Get psikolog_id from users table based on logged in user
                                $user_id = $_SESSION['user_id'];
                                $get_psikolog_query = "SELECT reference_id FROM users WHERE id = ? AND role = 'psikolog'";
                                $stmt = mysqli_prepare($conn, $get_psikolog_query);
                                mysqli_stmt_bind_param($stmt, "i", $user_id);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);
                                $user_data = mysqli_fetch_assoc($result);
                                $psikolog_id = $user_data['reference_id'];

                                $query = "SELECT 
                                            jt.id,
                                            jt.kode_janji,
                                            jt.tanggal,
                                            jt.jam_mulai,
                                            jt.status,
                                            p.id as pasien_id,
                                            p.nama_lengkap as nama_pasien,
                                            jl.nama_layanan,
                                            ck.id as catatan_id,
                                            CASE 
                                                WHEN DATE(jt.tanggal) = CURDATE() THEN 1
                                                WHEN DATE(jt.tanggal) > CURDATE() THEN 2
                                                ELSE 3
                                            END as date_priority,
                                            CASE 
                                                WHEN jt.status = 'Dalam_Konsultasi' THEN 1
                                                WHEN jt.status = 'Check-in' THEN 2
                                                WHEN jt.status = 'Terjadwal' THEN 3
                                                WHEN jt.status = 'Selesai' THEN 4
                                                ELSE 5
                                            END as status_priority
                                        FROM janji_temu jt
                                        JOIN pasien p ON jt.pasien_id = p.id
                                        JOIN jenis_layanan jl ON jt.layanan_id = jl.id
                                        LEFT JOIN catatan_konsultasi ck ON jt.id = ck.janji_temu_id
                                        WHERE jt.psikolog_id = ?
                                        ORDER BY 
                                            date_priority ASC,
                                            jt.tanggal ASC,
                                            jt.jam_mulai ASC,
                                            status_priority ASC";
                                
                                $stmt = mysqli_prepare($conn, $query);
                                mysqli_stmt_bind_param($stmt, "i", $psikolog_id);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);
                                
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $statusClass = '';
                                    $statusBadge = '';
                                    switch ($row['status']) {
                                        case 'Dalam_Konsultasi':
                                            $statusClass = 'bg-primary';
                                            $statusBadge = '<i class="bi bi-person-video3"></i> ';
                                            break;
                                        case 'Check-in':
                                            $statusClass = 'bg-info';
                                            $statusBadge = '<i class="bi bi-person-check"></i> ';
                                            break;
                                        case 'Terjadwal':
                                            $statusClass = 'bg-warning';
                                            $statusBadge = '<i class="bi bi-calendar-check"></i> ';
                                            break;
                                        case 'Selesai':
                                            $statusClass = 'bg-success';
                                            $statusBadge = '<i class="bi bi-check-circle"></i> ';
                                            break;
                                        case 'Dibatalkan':
                                            $statusClass = 'bg-danger';
                                            $statusBadge = '<i class="bi bi-x-circle"></i> ';
                                            break;
                                        case 'Tidak_Hadir':
                                            $statusClass = 'bg-secondary';
                                            $statusBadge = '<i class="bi bi-person-x"></i> ';
                                            break;
                                    }

                                    $isToday = (date('Y-m-d', strtotime($row['tanggal'])) == date('Y-m-d'));
                                    $rowClass = $isToday ? 'highlight-today' : '';
                                    ?>
                                    <tr class="<?= $rowClass ?>">
                                        <td><?= $row['kode_janji'] ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                        <td><?= date('H:i', strtotime($row['jam_mulai'])) ?></td>
                                        <td><?= $row['nama_pasien'] ?></td>
                                        <td><?= $row['nama_layanan'] ?></td>
                                        <td>
                                            <span class="badge <?= $statusClass ?>">
                                                <?= $statusBadge . str_replace('_', ' ', $row['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="buttons">
                                                <a href="view.php?id=<?= $row['id'] ?>" 
                                                   class="btn btn-sm btn-info" 
                                                   title="Lihat Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                
                                                <?php if ($row['status'] === 'Dalam_Konsultasi' && !$row['catatan_id']): ?>
                                                <a href="add.php?id=<?= $row['id'] ?>" 
                                                   class="btn btn-sm btn-primary"
                                                   title="Tambah Catatan">
                                                    <i class="bi bi-plus"></i>
                                                </a>
                                                <?php endif; ?>

                                                <?php if ($row['catatan_id']): ?>
                                                <a href="edit.php?id=<?= $row['catatan_id'] ?>" 
                                                   class="btn btn-sm btn-warning"
                                                   title="Edit Catatan">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php endif; ?>

                                                <a href="riwayat.php?pasien_id=<?= $row['pasien_id'] ?>" 
                                                   class="btn btn-sm btn-secondary" 
                                                   title="Riwayat Konsultasi">
                                                    <i class="bi bi-clock-history"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>

<!-- DataTables Scripts -->
<script src="<?= $main_url ?>/_dist/assets/extensions/simple-datatables/umd/simple-datatables.js"></script>
<script src="<?= $main_url ?>/_dist/assets/static/js/pages/simple-datatables.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let dataTable = new simpleDatatables.DataTable("#tabelKonsultasi", {
        searchable: false,
        perPageSelect: false,
        labels: {
            placeholder: "Cari...",
            noRows: "Tidak ada data untuk ditampilkan",
            info: "Menampilkan {start} sampai {end} dari {rows} data",
        }
    });

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const [day, month, year] = dateStr.split('/');
        return `${year}-${month}-${day}`;
    }

    function applyFilters() {
        const status = document.getElementById('filterStatus').value;
        const tanggal = document.getElementById('filterTanggal').value;
        const layanan = document.getElementById('filterLayanan').value.toLowerCase();

        const rows = Array.from(document.querySelectorAll('#tabelKonsultasi tbody tr'));

        rows.forEach(row => {
            const statusCell = row.cells[5].textContent.trim().toLowerCase();
            const tanggalCell = formatDate(row.cells[1].textContent.split(' ')[0].trim());
            const layananCell = row.cells[4].textContent.toLowerCase();

            const matchStatus = !status || statusCell.includes(status.toLowerCase());
            const matchTanggal = !tanggal || tanggalCell === tanggal;
            const matchLayanan = !layanan || layananCell.includes(layanan);

            if (matchStatus && matchTanggal && matchLayanan) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    document.getElementById('filterStatus').addEventListener('change', applyFilters);
    document.getElementById('filterTanggal').addEventListener('change', applyFilters);
    document.getElementById('filterLayanan').addEventListener('change', applyFilters);
});
</script>

<style>
.highlight-today {
    background: linear-gradient(to right, #eef2ff 0%, #f5f7ff 100%) !important;
    border-left: 4px solid #4f46e5;
    font-weight: 500;
}

.highlight-today:hover {
    background: linear-gradient(to right, #e0e7ff 0%, #eef2ff 100%) !important;
}

.highlight-today td {
    color: #4338ca !important;
}

.badge i {
    margin-right: 3px;
}

.buttons {
    white-space: nowrap;
}

.filter-container {
    flex-wrap: wrap;
}

.filter-container .form-group {
    margin-right: 1rem;
}

.form-label {
    margin-bottom: 0.3rem;
    font-size: 0.875rem;
    color: #475569;
}
</style>