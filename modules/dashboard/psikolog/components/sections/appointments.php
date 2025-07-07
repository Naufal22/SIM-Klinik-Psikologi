<?php
function renderTodayAppointments($appointments) {
?>
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4>Janji Temu Hari Ini</h4>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($appointments) > 0): ?>
                    <?php while ($appointment = mysqli_fetch_assoc($appointments)): ?>
                        <div class="appointment-card card mb-3 status-<?= $appointment['status'] ?>">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($appointment['nama_pasien']) ?></h6>
                                        <p class="text-muted mb-0">
                                            <?= formatPsikologTime($appointment['jam_mulai']) ?> - 
                                            <?= formatPsikologTime($appointment['jam_selesai']) ?> 
                                            (<?= $appointment['durasi_menit'] ?> menit)
                                        </p>
                                        <small class="text-muted"><?= htmlspecialchars($appointment['nama_layanan']) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?= getPsikologStatusColor($appointment['status']) ?>">
                                            <?= formatPsikologStatus($appointment['status']) ?>
                                        </span>
                                        <div class="mt-2">
                                            <a href="detail_janji.php?id=<?= $appointment['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">Tidak ada janji temu hari ini</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php
}