<?php
// Active Psychologists Section
?>
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Psikolog Aktif Hari Ini</h4>
    </div>
    <div class="card-body">
        <?php while ($psikolog = mysqli_fetch_assoc($activePsychologists)): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <div class="avatar me-3">
                        <?php if ($psikolog['foto']): ?>
                            <img src="../../../uploads/psikolog/<?= $psikolog['foto'] ?>" alt="<?= $psikolog['nama'] ?>">
                        <?php else: ?>
                            <img src="../../../uploads/psikolog/default.png" alt="Default">
                        <?php endif; ?>
                    </div>
                    <div>
                        <h6 class="mb-0"><?= $psikolog['nama'] ?></h6>
                        <small class="text-muted"><?= $psikolog['sessions_today'] ?> sesi hari ini</small>
                    </div>
                </div>
                <span class="badge bg-<?= getStatusBadge($psikolog['current_status']) ?>">
                    <?= $psikolog['current_status'] ?>
                </span>
            </div>
        <?php endwhile; ?>
    </div>
</div>