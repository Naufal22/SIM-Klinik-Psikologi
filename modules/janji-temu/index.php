<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';
require_once '../../auth/session_check.php';
checkSession();

requireRole([ROLE_ADMIN, ROLE_PSIKOLOG]);
checkSessionTimeout();

// Dapatkan psikolog_id jika user adalah psikolog
$psikolog_id = null;
if ($_SESSION['role'] === ROLE_PSIKOLOG) {
    $userQuery = "SELECT reference_id FROM users WHERE id = ? AND role = 'psikolog'";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $psikolog_id = $row['reference_id'];
    }
}

$title = "Janji Temu - Klinik";
$activePage = 'janji-temu';

require '../../includes/header.php';
?>
<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css' rel='stylesheet' />

<?php
require '../../includes/sidebar.php';
require '../../includes/navbar.php';
?>

<div id="main-content">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Janji Temu</h3>
                    <p class="text-subtitle text-muted">Kelola janji temu pasien dengan psikolog</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Janji Temu</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Kalendar Janji Temu</h5>
                                <?php if ($_SESSION['role'] === ROLE_ADMIN): ?>
                                <div>
                                    <a href="add.php" class="btn btn-primary">
                                        <i class="bi bi-plus"></i> Buat Janji Temu
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <?php if ($_SESSION['role'] === ROLE_ADMIN): ?>
                                <div class="col-md-3">
                                    <select id="filterPsikolog" class="form-select">
                                        <option value="">Semua Psikolog</option>
                                        <?php
                                        $query = "SELECT id, nama FROM psikolog WHERE status = 'Aktif' ORDER BY nama";
                                        $result = mysqli_query($conn, $query);
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<option value='{$row['id']}'>{$row['nama']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                <div class="col-md-3">
                                    <select id="filterStatus" class="form-select">
                                        <option value="">Semua Status</option>
                                        <option value="Terjadwal">Terjadwal</option>
                                        <option value="Check-in">Check-in</option>
                                        <option value="Dalam_Konsultasi">Dalam Konsultasi</option>
                                        <option value="Selesai">Selesai</option>
                                        <option value="Dibatalkan">Dibatalkan</option>
                                        <option value="Tidak Hadir">Tidak Hadir</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="filterLayanan" class="form-select">
                                        <option value="">Semua Layanan</option>
                                        <?php
                                        $query = "SELECT id, nama_layanan FROM jenis_layanan WHERE status = 'Aktif' ORDER BY nama_layanan";
                                        $result = mysqli_query($conn, $query);
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<option value='{$row['id']}'>{$row['nama_layanan']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>

<!-- FullCalendar Bundle -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    // Set default date to current date
    var today = new Date();
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        initialDate: today,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        slotMinTime: '08:00:00',
        slotMaxTime: '18:00:00',
        allDaySlot: false,
        height: 'auto',
        locale: 'id',
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            week: 'Minggu',
            day: 'Hari',
            list: 'Daftar'
        },
        nowIndicator: true,
        businessHours: {
            daysOfWeek: [1, 2, 3, 4, 5, 6],
            startTime: '08:00',
            endTime: '18:00',
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        events: function(info, successCallback, failureCallback) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'calendar.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    console.log('Response:', xhr.responseText); // Debug
                    var events = JSON.parse(xhr.responseText);
                    successCallback(events);
                } else {
                    failureCallback(new Error('Failed to fetch events'));
                }
            };
            
            xhr.onerror = function() {
                failureCallback(new Error('Network error'));
            };
            
            var params = new URLSearchParams({
                start: info.startStr,
                end: info.endStr,
                status: document.getElementById('filterStatus').value,
                layanan_id: document.getElementById('filterLayanan').value
            });

            <?php if ($_SESSION['role'] === ROLE_PSIKOLOG && isset($psikolog_id)): ?>
            params.append('psikolog_id', '<?php echo $psikolog_id; ?>');
            <?php elseif ($_SESSION['role'] === ROLE_ADMIN): ?>
            var filterPsikolog = document.getElementById('filterPsikolog');
            if (filterPsikolog) {
                params.append('psikolog_id', filterPsikolog.value);
            }
            <?php endif; ?>
            
            xhr.send(params.toString());
        },
        eventClick: function(info) {
            window.location.href = 'view.php?id=' + info.event.id;
        },
        dateClick: function(info) {
            <?php if ($_SESSION['role'] === ROLE_ADMIN): ?>
            // Only allow booking on business hours
            var clickedDate = new Date(info.dateStr);
            var day = clickedDate.getDay();
            var hour = clickedDate.getHours();
            
            if (day >= 1 && day <= 6 && hour >= 8 && hour < 18) {
                window.location.href = 'add.php?date=' + info.dateStr;
            }
            <?php endif; ?>
        },
        eventDidMount: function(info) {
            // Add tooltips
            info.el.title = info.event.title + '\nStatus: ' + info.event.extendedProps.status;
        }
    });
    
    calendar.render();

    // Event listeners for filters
    var filterIds = <?php echo $_SESSION['role'] === ROLE_ADMIN ? 
        "['filterPsikolog', 'filterStatus', 'filterLayanan']" : 
        "['filterStatus', 'filterLayanan']"; ?>;

    filterIds.forEach(function(filterId) {
        var element = document.getElementById(filterId);
        if (element) {
            element.addEventListener('change', function() {
                calendar.refetchEvents();
            });
        }
    });
});
</script>
</body>
</html>
