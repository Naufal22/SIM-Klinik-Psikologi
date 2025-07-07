<?php
function getActivePatients($psikolog_id) {
    global $conn;
    $sql = "SELECT COUNT(DISTINCT p.id) as count 
            FROM pasien p 
            INNER JOIN janji_temu j ON p.id = j.pasien_id 
            WHERE j.psikolog_id = ? 
            AND j.tanggal >= DATE_SUB(CURRENT_DATE, INTERVAL 3 MONTH)
            AND p.status = 'Aktif'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $psikolog_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'];
}

function getTodayAppointments($psikolog_id) {
    global $conn;
    $sql = "SELECT j.*, p.nama_lengkap as nama_pasien, l.nama_layanan, l.durasi_menit
            FROM janji_temu j
            INNER JOIN pasien p ON j.pasien_id = p.id
            INNER JOIN jenis_layanan l ON j.layanan_id = l.id
            WHERE j.psikolog_id = ? 
            AND DATE(j.tanggal) = CURRENT_DATE
            ORDER BY j.jam_mulai ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $psikolog_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getTodayAppointmentCount($psikolog_id) {
    global $conn;
    $sql = "SELECT COUNT(*) as count 
            FROM janji_temu 
            WHERE psikolog_id = ? 
            AND DATE(tanggal) = CURRENT_DATE";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $psikolog_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'];
}

function getPendingNotesCount($psikolog_id) {
    global $conn;
    $sql = "SELECT COUNT(*) as count 
            FROM janji_temu j
            LEFT JOIN catatan_konsultasi c ON j.id = c.janji_temu_id
            WHERE j.psikolog_id = ? 
            AND j.status = 'Selesai'
            AND c.id IS NULL";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $psikolog_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'];
}

function getFollowUpReminders($psikolog_id) {
    global $conn;
    $sql = "SELECT j.*, p.nama_lengkap as nama_pasien, c.rekomendasi
            FROM janji_temu j
            INNER JOIN pasien p ON j.pasien_id = p.id
            INNER JOIN catatan_konsultasi c ON j.id = c.janji_temu_id
            WHERE j.psikolog_id = ? 
            AND j.status = 'Selesai'
            AND DATE(j.tanggal) >= DATE_SUB(CURRENT_DATE, INTERVAL 2 WEEK)
            ORDER BY j.tanggal DESC
            LIMIT 5";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $psikolog_id);
    $stmt->execute();
    return $stmt->get_result();
}


function getRecentConsultationNotes($psikolog_id) {
    global $conn;
    $sql = "SELECT 
                c.*,
                j.id as janji_temu_id, 
                j.tanggal, 
                p.nama_lengkap as nama_pasien,
                l.nama_layanan
            FROM catatan_konsultasi c
            INNER JOIN janji_temu j ON c.janji_temu_id = j.id
            INNER JOIN pasien p ON j.pasien_id = p.id
            INNER JOIN jenis_layanan l ON j.layanan_id = l.id
            WHERE c.psikolog_id = ?
            ORDER BY c.created_at DESC
            LIMIT 5";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $psikolog_id);
    $stmt->execute();
    return $stmt->get_result();
}
