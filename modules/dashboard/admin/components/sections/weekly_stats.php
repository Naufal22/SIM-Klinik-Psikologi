<?php
// Weekly Statistics Section
?>
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Statistik Mingguan</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="weekly-stats d-flex align-items-center">
                    <div class="weekly-stats-icon bg-primary">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div>
                        <span class="text-muted d-block">Pasien Baru</span>
                        <span class="fs-5 fw-bold"><?= $weeklyStats['new_patients'] ?> Pasien</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="weekly-stats d-flex align-items-center">
                    <div class="weekly-stats-icon bg-success">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div>
                        <span class="text-muted d-block">Sesi Selesai</span>
                        <span class="fs-5 fw-bold"><?= $weeklyStats['completed_sessions'] ?> Sesi</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="weekly-stats d-flex align-items-center">
                    <div class="weekly-stats-icon bg-warning">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <span class="text-muted d-block">Rata-rata Durasi</span>
                        <span class="fs-5 fw-bold">45 Menit</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="weekly-stats d-flex align-items-center">
                    <div class="weekly-stats-icon bg-danger">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div>
                        <span class="text-muted d-block">Pembatalan</span>
                        <span class="fs-5 fw-bold"><?= $weeklyStats['cancelled_sessions'] ?> Sesi</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>