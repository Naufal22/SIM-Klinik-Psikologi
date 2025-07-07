<?php
function renderAppointmentCard($appointment = null) {
    if ($appointment) {
        // Card untuk janji temu yang sudah ada
        ?>
        <div class="row mb-5">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="text-white-50 mb-3 text-uppercase fs-6">Janji Temu Mendatang</h6>
                                <h3 class="text-white mb-3">
                                    Jadwal konsultasi dengan <?= htmlspecialchars($appointment['nama_psikolog']) ?>
                                </h3>
                                <p class="text-white-50 mb-4">
                                    <?= date('d F Y', strtotime($appointment['tanggal'])) ?>, 
                                    <?= date('H:i', strtotime($appointment['jam_mulai'])) ?> - 
                                    <?= date('H:i', strtotime($appointment['jam_selesai'])) ?> WIB
                                </p>
                                <!-- <button class="btn btn-light fw-semibold">Reschedule</button> -->
                            </div>
                            <div class="col-md-4 d-none d-md-block">
                                <img src="<?= $GLOBALS['main_url'] ?>/_dist/gambar/jadwal-psikolog.jpg " 
                                     class="img-fluid rounded-3" alt="Appointment" style="width: 100%; height: 250px; object-fit: cover; filter: brightness(1.2);">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {
        // Card untuk membuat janji temu baru
        ?>
        <div class="row mb-5">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="text-white-50 mb-3 text-uppercase fs-6">Buat Janji Temu</h6>
                                <h3 class="text-white mb-3">Belum ada janji temu? Yuk, pilih psikolog favoritmu</h3>
                                <p class="text-white-50 mb-4">Konsultasikan masalahmu dengan psikolog terpercaya</p>
                                <a href="../../janji-temu/add-user.php" class="btn btn-light fw-semibold">
                                    Buat Janji Sekarang
                                </a>
                            </div>
                            <div class="col-md-4 d-none d-md-block">
                                <img src="<?= $GLOBALS['main_url'] ?>/_dist/gambar/pilih-psikolog.jpg " 
                                     class="img-fluid rounded-3" alt="Appointment" style="width: 100%; height: 250px; object-fit: cover; filter: brightness(1.2);">
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