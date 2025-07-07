<?php
// Today's Schedule Section
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title">Jadwal Konsultasi Hari Ini</h4>
        <span class="badge bg-primary"><?= mysqli_num_rows($todayAppointments) ?> Sesi</span>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($todayAppointments) > 0): ?>
            <?php while ($appointment = mysqli_fetch_assoc($todayAppointments)): ?>
                <div class="schedule-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">
                                <?= date('H:i', strtotime($appointment['jam_mulai'])) ?> -
                                <?= $appointment['nama_layanan'] ?>
                            </h6>
                            <p class="mb-0 text-muted">
                                <?= $appointment['nama_psikolog'] ?> dengan <?= $appointment['nama_pasien'] ?>
                            </p>
                        </div>
                        <span class="badge bg-<?= getStatusColor($appointment['status']) ?>">
                            <?= $appointment['status'] ?>
                        </span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-3">
                <p class="text-muted mb-0">Tidak ada jadwal konsultasi hari ini</p>
            </div>
        <?php endif; ?>
    </div>
</div>