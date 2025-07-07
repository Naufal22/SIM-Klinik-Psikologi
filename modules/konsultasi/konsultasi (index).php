<?php
require_once '../../config/database.php';
require_once 'functions.php';

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
                    <h5 class="card-title">Daftar Konsultasi</h5>
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

                    <!-- Filter Section -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filterPsikolog">Psikolog</label>
                                <select id="filterPsikolog" class="form-select">
                                    <option value="">Semua Psikolog</option>
                                    <?php
                                    $query = "SELECT id, nama FROM psikolog WHERE status = 'Aktif' ORDER BY nama";
                                    $result = mysqli_query($conn, $query);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<option value='{$row['nama']}'>{$row['nama']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filterStatus">Status</label>
                                <select id="filterStatus" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="Terjadwal">Terjadwal</option>
                                    <option value="Check-in">Check-in</option>
                                    <option value="Dalam Konsultasi">Dalam Konsultasi</option>
                                    <option value="Selesai">Selesai</option>
                                    <option value="Dibatalkan">Dibatalkan</option>
                                    <option value="Tidak Hadir">Tidak Hadir</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filterTanggal">Tanggal</label>
                                <input type="date" id="filterTanggal" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filterLayanan">Layanan</label>
                                <select id="filterLayanan" class="form-select">
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

                    <!-- Table Section -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="tabelKonsultasi">
                            <thead>
                                <tr>
                                    <th>Kode Janji</th>
                                    <th>Tanggal</th>
                                    <th>Jam</th>
                                    <th>Psikolog</th>
                                    <th>Pasien</th>
                                    <th>Layanan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT 
                                            jt.id,
                                            jt.kode_janji,
                                            jt.tanggal,
                                            jt.jam_mulai,
                                            jt.status,
                                            p.id as pasien_id,
                                            p.nama_lengkap as nama_pasien,
                                            psi.nama as nama_psikolog,
                                            jl.nama_layanan,
                                            ck.id as catatan_id
                                        FROM janji_temu jt
                                        JOIN pasien p ON jt.pasien_id = p.id
                                        JOIN psikolog psi ON jt.psikolog_id = psi.id
                                        JOIN jenis_layanan jl ON jt.layanan_id = jl.id
                                        LEFT JOIN catatan_konsultasi ck ON jt.id = ck.janji_temu_id
                                        ORDER BY jt.tanggal ASC, jt.jam_mulai ASC";
                                
                                $result = mysqli_query($conn, $query);
                                
                                while ($row = mysqli_fetch_assoc($result)):
                                    $statusClass = getStatusColor($row['status']);
                                ?>
                                    <tr>
                                        <td><?= $row['kode_janji'] ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                        <td><?= date('H:i', strtotime($row['jam_mulai'])) ?></td>
                                        <td><?= $row['nama_psikolog'] ?></td>
                                        <td><?= $row['nama_pasien'] ?></td>
                                        <td><?= $row['nama_layanan'] ?></td>
                                        <td>
                                            <span class="badge bg-<?= $statusClass ?>">
                                                <?= str_replace('_', ' ', $row['status']) ?>
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
                                <?php endwhile; ?>
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
        perPage: 10,
        labels: {
            placeholder: "Cari...",
            noRows: "Tidak ada data untuk ditampilkan",
            info: "Menampilkan {start} sampai {end} dari {rows} data",
        }
    });

    // Function to format date for comparison
    function formatDate(dateStr) {
        if (!dateStr) return '';
        const [day, month, year] = dateStr.split('/');
        return `${year}-${month}-${day}`;
    }

    // Function to apply filters
    function applyFilters() {
        const psikolog = document.getElementById('filterPsikolog').value.toLowerCase();
        const status = document.getElementById('filterStatus').value;
        const tanggal = document.getElementById('filterTanggal').value;
        const layanan = document.getElementById('filterLayanan').value.toLowerCase();

        // Get all rows
        const rows = Array.from(document.querySelectorAll('#tabelKonsultasi tbody tr'));

        rows.forEach(row => {
            const psikologCell = row.cells[3].textContent.toLowerCase();
            const statusCell = row.cells[6].textContent.trim().toLowerCase();
            const tanggalCell = formatDate(row.cells[1].textContent.trim());
            const layananCell = row.cells[5].textContent.toLowerCase();

            const matchPsikolog = !psikolog || psikologCell.includes(psikolog);
            const matchStatus = !status || statusCell === status.toLowerCase();
            const matchTanggal = !tanggal || tanggalCell === tanggal;
            const matchLayanan = !layanan || layananCell.includes(layanan);

            if (matchPsikolog && matchStatus && matchTanggal && matchLayanan) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Add event listeners to all filters
    document.getElementById('filterPsikolog').addEventListener('change', applyFilters);
    document.getElementById('filterStatus').addEventListener('change', applyFilters);
    document.getElementById('filterTanggal').addEventListener('change', applyFilters);
    document.getElementById('filterLayanan').addEventListener('change', applyFilters);
});
</script>

<style>
.dataTable-top,
.dataTable-bottom {
    display: none;
}
</style>

</body>
</html>