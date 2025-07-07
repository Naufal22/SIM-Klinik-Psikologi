<?php
function renderProgressSection($consultationHistory = []) {
    ?>
    <div class="row align-items-center">
        <div class="col-lg-5 mb-4 mb-lg-0">
            <div class="card">
                <div class="card-body p-4">
                    <img src="https://images.unsplash.com/photo-1499209974431-9dddcece7f88?w=800" 
                         class="img-fluid rounded-3" alt="Progress">
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body p-4">
                    <h1 class="display-4 fw-bold mb-4">Perjalananmu Sudah Jauh!</h1>
                    <p class="fs-5 text-secondary mb-4">
                        Ingat, setiap langkah kecil adalah kemajuan. Terus maju dan jangan menyerah pada dirimu sendiri.
                    </p>
                    
                    <?php if (!empty($consultationHistory)): ?>
                    <div class="consultation-history mb-4">
                        <h5 class="mb-3">Riwayat Konsultasi Terakhir:</h5>
                        <ul class="list-group">
                            <?php foreach ($consultationHistory as $consultation): ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($consultation['nama_psikolog']) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('d F Y', strtotime($consultation['tanggal'])) ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">
                                        <?= htmlspecialchars($consultation['nama_layanan']) ?>
                                    </span>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <!-- <div class="input-group">
                        <input type="text" class="form-control fs-5" placeholder="Tinggalkan komentar">
                        <button class="btn btn-primary fw-semibold px-4" type="button">Kirim</button>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>