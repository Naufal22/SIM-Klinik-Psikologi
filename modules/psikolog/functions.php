<?php
require_once '../../config/database.php';

/**
 * Get all active psychologists
 * 
 * @return array Array of active psychologists
 */
function getPsikologAktif() {
    global $conn;
    $query = "SELECT id, nama FROM psikolog WHERE status = 'Aktif' ORDER BY nama";
    $result = $conn->query($query);
    
    $psikolog = [];
    while ($row = $result->fetch_assoc()) {
        $psikolog[] = $row;
    }
    
    return $psikolog;
}

/**
 * Check if schedule conflicts with existing schedules
 * 
 * @param int $psikologId Psychologist ID
 * @param string $hari Day of the week
 * @param string $jamMulai Start time
 * @param string $jamSelesai End time
 * @param int|null $excludeId Schedule ID to exclude (for edit)
 * @return bool True if conflict exists, false otherwise
 */
function checkJadwalConflict($psikologId, $hari, $jamMulai, $jamSelesai, $excludeId = null) {
    global $conn;
    
    $query = "SELECT COUNT(*) as conflict FROM jadwal_psikolog 
              WHERE psikolog_id = ? 
              AND hari = ?
              AND status = 'Aktif'
              AND (
                  (jam_mulai <= ? AND jam_selesai > ?) OR
                  (jam_mulai < ? AND jam_selesai >= ?) OR
                  (jam_mulai >= ? AND jam_selesai <= ?)
              )";
    
    $params = [
        $psikologId,
        $hari,
        $jamMulai,
        $jamMulai,
        $jamSelesai,
        $jamSelesai,
        $jamMulai,
        $jamSelesai
    ];
    $types = 'ssssssss';
    
    if ($excludeId) {
        $query .= " AND id != ?";
        $params[] = $excludeId;
        $types .= 'i';
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['conflict'] > 0;
}

/**
 * Get schedule by ID
 * 
 * @param int $id Schedule ID
 * @return array|null Schedule data or null if not found
 */
function getJadwalById($id) {
    global $conn;
    
    $query = "SELECT * FROM jadwal_psikolog WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    $jadwal = $result->fetch_assoc();
    // Format times for form
    $jadwal['jam_mulai'] = date('H:i', strtotime($jadwal['jam_mulai']));
    $jadwal['jam_selesai'] = date('H:i', strtotime($jadwal['jam_selesai']));
    
    return $jadwal;
}

/**
 * Validate schedule time
 * 
 * @param string $jamMulai Start time
 * @param string $jamSelesai End time
 * @return string|null Error message if invalid, null if valid
 */
function validateJadwalTime($jamMulai, $jamSelesai) {
    // Konversi string waktu ke timestamp untuk perbandingan
    $start = strtotime($jamMulai);
    $end = strtotime($jamSelesai);
    
    // Validasi format waktu
    if ($start === false || $end === false) {
        return false;
    }
    
    // Pastikan jam selesai lebih besar dari jam mulai
    if ($end <= $start) {
        return false;
    }
    
    // Pastikan durasi minimal 30 menit
    if (($end - $start) < 1800) { // 1800 detik = 30 menit
        return false;
    }
    
    return true;
}

?>