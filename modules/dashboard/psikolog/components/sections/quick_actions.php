<?php
function renderQuickActions() {
?>
    <div class="card mb-4">
        <div class="card-header">
            <h4>Aksi Cepat</h4>
        </div>
        <div class="card-body">
            <div class="d-grid gap-2">
                <a href="../../konsultasi/index.php" class="quick-action-btn btn btn-outline-primary p-3 text-start">
                    <i class="bi bi-journal-text me-2"></i>
                    Tulis Catatan Konsultasi
                </a>
                <a href="../../janji-temu/index.php" class="quick-action-btn btn btn-outline-primary p-3 text-start">
                    <i class="bi bi-calendar-week me-2"></i>
                    Lihat Janji Temu
                </a>
        </div>
    </div>
<?php
}