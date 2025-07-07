<?php
// Styles for Psikolog Dashboard
?>
<style>
.stats-card {
    transition: transform 0.3s ease;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.stats-icon {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stats-icon i {
    color: #fff;
    font-size: 1.8rem;
}

.stats-icon.purple { background: linear-gradient(45deg, #9694ff, #6e6cff); }
.stats-icon.blue { background: linear-gradient(45deg, #57caeb, #3ac2e8); }
.stats-icon.green { background: linear-gradient(45deg, #5ddab4, #3fd19e); }
.stats-icon.red { background: linear-gradient(45deg, #ff7976, #ff5956); }

.appointment-card {
    border-left: 4px solid transparent;
    transition: all 0.3s ease;
}

.appointment-card:hover {
    transform: translateX(5px);
    background-color: #f8f9fa;
}

.appointment-card.status-Terjadwal { border-left-color: #435ebe; }
.appointment-card.status-Check-in { border-left-color: #3ac2e8; }
.appointment-card.status-Dalam_Konsultasi { border-left-color: #ffc107; }
.appointment-card.status-Selesai { border-left-color: #198754; }
.appointment-card.status-Dibatalkan { border-left-color: #dc3545; }
.appointment-card.status-Tidak_Hadir { border-left-color: #6c757d; }

.quick-action-btn {
    transition: all 0.3s ease;
    border-radius: 10px;
    border: 1px solid #dee2e6;
}

.quick-action-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-color: #435ebe;
}

.follow-up-item {
    border-left: 3px solid #435ebe;
    transition: all 0.2s ease;
}

.follow-up-item:hover {
    background-color: #f8f9fa;
}

.recent-notes-card {
    transition: all 0.3s ease;
}

.recent-notes-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>