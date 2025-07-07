<?php
// Statistics Cards Section
?>
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
                            <h6 class="text-muted font-semibold">Total Pasien</h6>
                            <h6 class="font-extrabold mb-0"><?= number_format($totalCounts['pasien']) ?></h6>
                            <small class="text-success">↑ <?= $weeklyStats['new_patients'] ?> minggu ini</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Psikolog Aktif -->
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card stats-card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon blue mb-2">
                                <i class="bi bi-person-badge-fill"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Psikolog Aktif</h6>
                            <h6 class="font-extrabold mb-0"><?= number_format($totalCounts['psikolog']) ?></h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tingkat Kehadiran -->
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card stats-card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon green mb-2">
                                <i class="bi bi-calendar2-check"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Tingkat Kehadiran</h6>
                            <h6 class="font-extrabold mb-0"><?= $completionRate ?>%</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Layanan Tersedia -->
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card stats-card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon red mb-2">
                                <i class="bi bi-clipboard2-pulse"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Layanan Tersedia</h6>
                            <h6 class="font-extrabold mb-0"><?= number_format($totalCounts['layanan']) ?></h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>