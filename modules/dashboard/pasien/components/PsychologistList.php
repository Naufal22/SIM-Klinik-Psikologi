<?php
function renderPsychologistList($psychologists) {
    ?>
    <div class="row mb-5">
        <div class="col-12">
            <h3 class="section-title mb-4">Jadwal Psikolog</h3>
        </div>
        <?php foreach ($psychologists as $psikolog): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <div class="card-body p-4 text-center">
                    <div class="avatar avatar-xl mb-3">
                        <img src="<?= $GLOBALS['main_url'] ?>uploads/psikolog/<?= htmlspecialchars($psikolog['foto'] ?: 'default.jpg') ?>" 
                             alt="<?= htmlspecialchars($psikolog['nama']) ?>" 
                             class="doctor-img">
                    </div>
                    <h4 class="fw-bold mb-2"><?= htmlspecialchars($psikolog['nama']) ?></h4>
                    <p class="text-secondary mb-0">
                        Tersedia pada <?= htmlspecialchars($psikolog['hari_praktek']) ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
}
?>