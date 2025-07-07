<?php
function renderFollowUp($followUps) {
?>
    <div class="card">
        <div class="card-header">
            <h4>Follow-up Pasien</h4>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($followUps) > 0): ?>
                <?php while ($followUp = mysqli_fetch_assoc($followUps)): ?>
                    <div class="follow-up-item p-3 mb-3">
                        <h6 class="mb-1"><?= htmlspecialchars($followUp['nama_pasien'] ?? 'Nama Tidak Tersedia') ?></h6>
                        <p class="text-muted mb-2">
                            <small>
                                Konsultasi terakhir: <?= formatPsikologDate($followUp['tanggal']) ?>
                                <?php
                                // Hitung selisih hari
                                $lastDate = new DateTime($followUp['tanggal']);
                                $today = new DateTime();
                                $interval = $today->diff($lastDate);
                                $daysSince = $interval->days;
                                ?>
                                (<?= $daysSince ?> hari yang lalu)
                            </small>
                        </p>
                        <p class="mb-2">
                            <small class="text-muted">Rekomendasi:</small><br>
                            <?= nl2br(htmlspecialchars($followUp['rekomendasi'] ?? 'Belum ada rekomendasi')) ?>
                        </p>
                        <div class="text-end">
                            <button class="btn btn-sm btn-outline-primary">
                                Jadwalkan Follow-up
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-3">
                    <p class="text-muted mb-0">Tidak ada follow-up yang perlu dilakukan</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
}
?>