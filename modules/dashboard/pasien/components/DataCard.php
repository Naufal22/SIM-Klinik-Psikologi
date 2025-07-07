<?php
function renderDataCard($isComplete = false) {
    if ($isComplete) {
        ?>
        <div class="row mb-5">
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="text-secondary mb-3 text-uppercase fs-6">Fitur Tambahan</h6>
                                <h3 class="mb-3">Data kamu sudah lengkap! Yuk, eksplor fitur lain</h3>
                                <div class="d-flex gap-3">
                                    <a href="../../skrining.php" class="btn btn-primary fw-semibold">Skrining Gratis</a>
                                    <a href="../../janji-temu/index-user.php" class="btn btn-outline-primary fw-semibold">Riwayat Konsultasi</a>
                                </div>
                            </div>
                            <div class="col-md-4 d-none d-md-block">
                                <img src="https://images.unsplash.com/photo-1434494878577-86c23bcb06b9?w=600" 
                                     class="img-fluid rounded-3" alt="Features">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {
        ?>
        <div class="row mb-5">
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="text-secondary mb-3 text-uppercase fs-6">Data Diri</h6>
                                <h3 class="mb-3">Lengkapi data dirimu sekarang yuk!</h3>
                                <p class="text-secondary mb-4">Data lengkap membantu kami memberikan pelayanan terbaik untukmu</p>
                                <a href="../../pasien/edit.php" class="btn btn-primary fw-semibold">Lengkapi Data</a>
                            </div>
                            <div class="col-md-4 d-none d-md-block">
                                <img src="https://images.unsplash.com/photo-1434494878577-86c23bcb06b9?w=600" 
                                     class="img-fluid rounded-3" alt="Profile">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>