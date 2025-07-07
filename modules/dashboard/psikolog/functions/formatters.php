<?php
function formatPsikologStatus($status) {
    $statusMap = [
        'Terjadwal' => 'Terjadwal',
        'Check-in' => 'Check-in',
        'Dalam_Konsultasi' => 'Dalam Konsultasi',
        'Selesai' => 'Selesai',
        'Dibatalkan' => 'Dibatalkan',
        'Tidak_Hadir' => 'Tidak Hadir'
    ];
    return $statusMap[$status] ?? $status;
}

function getPsikologStatusColor($status) {
    $colorMap = [
        'Terjadwal' => 'primary',
        'Check-in' => 'info',
        'Dalam_Konsultasi' => 'warning',
        'Selesai' => 'success',
        'Dibatalkan' => 'danger',
        'Tidak_Hadir' => 'secondary'
    ];
    return $colorMap[$status] ?? 'light';
}

function formatPsikologTime($time) {
    return date('H:i', strtotime($time));
}

function formatPsikologDate($date) {
    return date('d M Y', strtotime($date));
}

function formatDuration($minutes) {
    if ($minutes < 60) {
        return "$minutes menit";
    }
    $hours = floor($minutes / 60);
    $remainingMinutes = $minutes % 60;
    return $remainingMinutes > 0 ? "$hours jam $remainingMinutes menit" : "$hours jam";
}