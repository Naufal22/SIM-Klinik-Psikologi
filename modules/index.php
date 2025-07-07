<?php
$title = "Dashboard - Klinik Psikologi";
$activePage = 'dashboard';

require '../includes/header.php';
?>

<!-- Custom CSS -->
<style>
.stats-card {
    transition: transform 0.3s ease;
}
.stats-card:hover {
    transform: translateY(-5px);
}
.stats-icon {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
.stats-icon i {
    color: #fff;
    font-size: 1.8rem;
}
.stats-icon.purple {
    background: linear-gradient(45deg, #9694ff, #6e6cff);
}
.stats-icon.blue {
    background: linear-gradient(45deg, #57caeb, #3ac2e8);
}
.stats-icon.green {
    background: linear-gradient(45deg, #5ddab4, #3fd19e);
}
.stats-icon.red {
    background: linear-gradient(45deg, #ff7976, #ff5956);
}
.appointment-card {
    border-left: 4px solid transparent;
}
.appointment-card.waiting {
    border-left-color: #ffc107;
}
.appointment-card.completed {
    border-left-color: #198754;
}
.appointment-card.cancelled {
    border-left-color: #dc3545;
}
.psychologist-card {
    transition: all 0.3s ease;
}
.psychologist-card:hover {
    background-color: #f8f9fa;
}
.status-badge {
    padding: 0.5em 1em;
    border-radius: 50px;
}
</style>

<?php
require '../includes/sidebar.php';
require '../includes/navbar.php';
?>

<div class="page-heading">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Dashboard Klinik Psikologi</h3>
            <p class="text-subtitle text-muted">Selamat datang kembali, Admin! Berikut ringkasan aktivitas klinik hari ini.</p>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="row">
        <!-- Statistik Cards -->
        <div class="col-12">
            <div class="row">
                <!-- Total Pasien -->
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card stats-card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                    <div class="stats-icon purple mb-2">
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Total Pasien Aktif</h6>
                                    <h6 class="font-extrabold mb-0">1,485</h6>
                                    <small class="text-success">↑ 12% bulan ini</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Janji Temu -->
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card stats-card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                    <div class="stats-icon blue mb-2">
                                        <i class="bi bi-calendar-check-fill"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Janji Temu Hari Ini</h6>
                                    <h6 class="font-extrabold mb-0">42</h6>
                                    <small class="text-primary">8 dalam antrian</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pendapatan -->
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card stats-card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                    <div class="stats-icon green mb-2">
                                        <i class="bi bi-currency-dollar"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Pendapatan Bulan Ini</h6>
                                    <h6 class="font-extrabold mb-0">Rp 245,8 Jt</h6>
                                    <small class="text-success">↑ 8.3% dari bulan lalu</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rating -->
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card stats-card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                    <div class="stats-icon red mb-2">
                                        <i class="bi bi-star-fill"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Rating Kepuasan</h6>
                                    <h6 class="font-extrabold mb-0">4.8/5.0</h6>
                                    <small class="text-success">↑ 0.2 poin</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-12 col-lg-8">
            <!-- Grafik Kunjungan -->
            <div class="card">
                <div class="card-header">
                    <h4>Tren Kunjungan Pasien</h4>
                    <div class="card-header-action">
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                7 Hari Terakhir
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item active" href="#">7 Hari Terakhir</a></li>
                                <li><a class="dropdown-item" href="#">30 Hari Terakhir</a></li>
                                <li><a class="dropdown-item" href="#">3 Bulan Terakhir</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="chart-kunjungan"></div>
                </div>
            </div>

            <!-- Janji Temu Hari Ini -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Janji Temu Hari Ini</h4>
                    <a href="#" class="btn btn-primary btn-sm">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <!-- Appointment Cards -->
                    <div class="appointment-card waiting card mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Budi Santoso</h6>
                                    <small class="text-muted">Konsultasi Umum • Dr. Sarah</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-warning status-badge">09:00 - Menunggu</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="appointment-card completed card mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Ani Wijaya</h6>
                                    <small class="text-muted">Terapi Kognitif • Dr. James</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success status-badge">10:30 - Selesai</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="appointment-card cancelled card mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Dewi Putri</h6>
                                    <small class="text-muted">Konseling Keluarga • Dr. Maria</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger status-badge">11:00 - Dibatalkan</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-12 col-lg-4">
            <!-- Psikolog Aktif -->
            <div class="card">
                <div class="card-header">
                    <h4>Psikolog Aktif Hari Ini</h4>
                </div>
                <div class="card-body">
                    <div class="psychologist-card d-flex align-items-center p-3 rounded mb-3">
                        <div class="avatar avatar-lg me-3">
                            <img src="../assets/images/faces/1.jpg" alt="Avatar">
                            <span class="avatar-status bg-success"></span>
                        </div>
                        <div>
                            <h6 class="mb-1">Dr. Sarah Johnson</h6>
                            <small class="text-muted d-block">Psikolog Klinis</small>
                            <small class="text-primary">8 pasien hari ini</small>
                        </div>
                    </div>

                    <div class="psychologist-card d-flex align-items-center p-3 rounded mb-3">
                        <div class="avatar avatar-lg me-3">
                            <img src="../assets/images/faces/2.jpg" alt="Avatar">
                            <span class="avatar-status bg-success"></span>
                        </div>
                        <div>
                            <h6 class="mb-1">Dr. James Wilson</h6>
                            <small class="text-muted d-block">Psikolog Anak</small>
                            <small class="text-primary">5 pasien hari ini</small>
                        </div>
                    </div>

                    <div class="psychologist-card d-flex align-items-center p-3 rounded">
                        <div class="avatar avatar-lg me-3">
                            <img src="../assets/images/faces/3.jpg" alt="Avatar">
                            <span class="avatar-status bg-success"></span>
                        </div>
                        <div>
                            <h6 class="mb-1">Dr. Maria Garcia</h6>
                            <small class="text-muted d-block">Psikolog Keluarga</small>
                            <small class="text-primary">6 pasien hari ini</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik Layanan -->
            <div class="card">
                <div class="card-header">
                    <h4>Statistik Layanan</h4>
                </div>
                <div class="card-body">
                    <div id="chart-layanan"></div>
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Konsultasi Umum</span>
                            <span class="fw-bold">45%</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Terapi Kognitif</span>
                            <span class="fw-bold">30%</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Konseling Keluarga</span>
                            <span class="fw-bold">25%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require '../includes/footer.php'; ?>

<!-- Apexcharts -->
<script src="<?= $main_url ?>/_dist/assets/extensions/apexcharts/apexcharts.min.js"></script>

<script>
// Grafik Tren Kunjungan
var optionsKunjungan = {
    chart: {
        type: 'area',
        height: 350,
        toolbar: {
            show: false
        },
        animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 800
        }
    },
    series: [{
        name: 'Kunjungan',
        data: [35, 41, 36, 26, 45, 48, 52]
    }],
    xaxis: {
        categories: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']
    },
    colors: ['#57caeb'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.7,
            opacityTo: 0.9,
            stops: [0, 90, 100]
        }
    },
    stroke: {
        curve: 'smooth',
        width: 3
    },
    markers: {
        size: 4,
        colors: ['#fff'],
        strokeColors: '#57caeb',
        strokeWidth: 2
    },
    tooltip: {
        theme: 'light',
        y: {
            formatter: function(value) {
                return value + ' Pasien';
            }
        }
    }
};

var chartKunjungan = new ApexCharts(
    document.querySelector("#chart-kunjungan"),
    optionsKunjungan
);
chartKunjungan.render();

// Grafik Statistik Layanan
var optionsLayanan = {
    chart: {
        type: 'donut',
        height: 300
    },
    series: [45, 30, 25],
    labels: ['Konsultasi Umum', 'Terapi Kognitif', 'Konseling Keluarga'],
    colors: ['#57caeb', '#5ddab4', '#ff7976'],
    legend: {
        show: false
    },
    plotOptions: {
        pie: {
            donut: {
                size: '75%',
                labels: {
                    show: true,
                    name: {
                        show: true,
                        fontSize: '22px',
                        fontFamily: 'Helvetica, Arial, sans-serif',
                        fontWeight: 600,
                        offsetY: -10
                    },
                    value: {
                        show: true,
                        fontSize: '16px',
                        fontFamily: 'Helvetica, Arial, sans-serif',
                        fontWeight: 400,
                        offsetY: 16
                    },
                    total: {
                        show: true,
                        showAlways: false,
                        label: 'Total',
                        fontSize: '22px',
                        fontWeight: 600,
                        formatter: function (w) {
                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0) + '%';
                        }
                    }
                }
            }
        }
    }
};

var chartLayanan = new ApexCharts(
    document.querySelector("#chart-layanan"),
    optionsLayanan
);
chartLayanan.render();
</script>

</body>
</html>