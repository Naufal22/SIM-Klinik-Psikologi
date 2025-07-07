<?php
// Service Statistics Section
?>
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Statistik Layanan</h4>
    </div>
    <div class="card-body">
        <?php while ($service = mysqli_fetch_assoc($serviceStats)):
            $percentage = round($service['percentage'], 1);
        ?>
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-1">
                    <span><?= $service['nama_layanan'] ?></span>
                    <span class="fw-bold"><?= $percentage ?>%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar" role="progressbar"
                        style="width: <?= $percentage ?>%; background-color: <?= getProgressColor($percentage) ?>"
                        aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>