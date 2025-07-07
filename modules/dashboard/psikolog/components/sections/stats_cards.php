<?php
function renderStatsCards($activePatients, $todayCount, $pendingNotes, $followUpsCount) {
?>
    <div class="col-12">
        <div class="row">
            <!-- Total Pasien Aktif -->
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card stats-card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                <div class="stats-icon purple mb-2">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Pasien Aktif</h6>
                                <h6 class="font-extrabold mb-0"><?= $activePatients ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Janji Temu Hari Ini -->
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card stats-card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                <div class="stats-icon blue mb-2">
                                    <i class="bi bi-calendar-check-fill"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Janji Hari Ini</h6>
                                <h6 class="font-extrabold mb-0"><?= $todayCount ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catatan Belum Selesai -->
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card stats-card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                <div class="stats-icon green mb-2">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Catatan Pending</h6>
                                <h6 class="font-extrabold mb-0"><?= $pendingNotes ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Follow-up Pasien -->
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card stats-card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                <div class="stats-icon red mb-2">
                                    <i class="bi bi-bell-fill"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Follow-up</h6>
                                <h6 class="font-extrabold mb-0"><?= $followUpsCount ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>