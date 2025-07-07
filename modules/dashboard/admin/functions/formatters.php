<?php
function getStatusColor($status) {
    $colors = [
        'Terjadwal' => 'primary',
        'Check-in' => 'info',
        'Dalam_Konsultasi' => 'warning',
        'Selesai' => 'success',
        'Dibatalkan' => 'danger',
        'Tidak Hadir' => 'secondary'
    ];
    return $colors[$status] ?? 'light';
}

function getStatusBadge($status) {
    $badges = [
        'Konsultasi' => 'warning',
        'Tersedia' => 'success',
        'Selesai' => 'secondary'
    ];
    return $badges[$status] ?? 'primary';
}

function getProgressColor($percentage) {
    if ($percentage >= 75) return '#435ebe';
    if ($percentage >= 50) return '#3fd19e';
    if ($percentage >= 25) return '#ffc107';
    return '#ff7976';
}

function getTimeAgo($timestamp) {
    $difference = time() - $timestamp;
    
    if ($difference < 60) return "Baru saja";
    if ($difference < 3600) return floor($difference / 60) . " menit yang lalu";
    if ($difference < 86400) return floor($difference / 3600) . " jam yang lalu";
    return floor($difference / 86400) . " hari yang lalu";
}

function getActivityTitle($status) {
    $titles = [
        'Selesai' => 'Konsultasi Selesai',
        'Dalam_Konsultasi' => 'Konsultasi Dimulai',
        'Check-in' => 'Pasien Check-in',
        'Dibatalkan' => 'Janji Temu Dibatalkan'
    ];
    return $titles[$status] ?? 'Janji Temu Baru';
}