<?php
// Recent Activities Section
?>
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Aktivitas Terkini</h4>
    </div>
    <div class="card-body">
        <?php while ($activity = mysqli_fetch_assoc($recentActivities)):
            $timeAgo = getTimeAgo(strtotime($activity['updated_at']));
        ?>
            <div class="activity-item new">
                <h6 class="mb-1"><?= getActivityTitle($activity['status']) ?></h6>
                <p class="mb-0">
                    <?= $activity['nama_psikolog'] ?> dengan <?= $activity['nama_pasien'] ?>
                </p>
                <small class="text-muted"><?= $timeAgo ?></small>
            </div>
        <?php endwhile; ?>
    </div>
</div>