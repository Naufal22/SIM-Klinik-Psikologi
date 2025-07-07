<?php
function getTotalCounts($conn) {
    $totalPasien = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pasien WHERE status = 'Aktif'"))['total'];
    $totalPsikolog = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM psikolog WHERE status = 'Aktif'"))['total'];
    $totalLayanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM jenis_layanan WHERE status = 'Aktif'"))['total'];
    
    return [
        'pasien' => $totalPasien,
        'psikolog' => $totalPsikolog,
        'layanan' => $totalLayanan
    ];
}

function getAppointmentStats($conn) {
    return mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT 
            COUNT(*) as total_appointments,
            SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'Dibatalkan' THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN status = 'Tidak Hadir' THEN 1 ELSE 0 END) as no_show
        FROM janji_temu 
        WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    "));
}

function getWeeklyStats($conn) {
    return mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT 
            COUNT(DISTINCT CASE WHEN j.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN p.id END) as new_patients,
            COUNT(CASE WHEN j.status = 'Selesai' AND j.tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as completed_sessions,
            COUNT(CASE WHEN j.status = 'Dibatalkan' AND j.tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as cancelled_sessions
        FROM janji_temu j
        LEFT JOIN pasien p ON j.pasien_id = p.id
    "));
}