<?php
/**
 * Get appropriate color class for status badge
 * 
 * @param string $status Status of the appointment
 * @return string Bootstrap color class
 */
function getStatusColor($status) {
    switch ($status) {
        case 'Terjadwal':
            return 'primary';
        case 'Check-in':
            return 'info';
        case 'Dalam_Konsultasi':
            return 'warning';
        case 'Selesai':
            return 'success';
        case 'Dibatalkan':
            return 'danger';
        case 'Tidak Hadir':
            return 'secondary';
        default:
            return 'secondary';
    }
}

/**
 * Format date to Indonesian format
 * 
 * @param string $date Date in Y-m-d format
 * @return string Formatted date
 */
function formatTanggal($date) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );

    $split = explode('-', $date);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

/**
 * Get consultation history for a patient
 * 
 * @param mysqli $conn Database connection
 * @param int $pasien_id Patient ID
 * @return array Array of consultation records
 */
function getRiwayatKonsultasi($conn, $pasien_id) {
    $query = "SELECT 
                jt.id as janji_id,
                jt.kode_janji,
                jt.tanggal,
                jt.jam_mulai,
                jt.status,
                psi.nama as nama_psikolog,
                jl.nama_layanan,
                ck.diagnosa,
                ck.rekomendasi,
                ak.keluhan_utama
            FROM janji_temu jt
            JOIN psikolog psi ON jt.psikolog_id = psi.id
            JOIN jenis_layanan jl ON jt.layanan_id = jl.id
            LEFT JOIN catatan_konsultasi ck ON jt.id = ck.janji_temu_id
            LEFT JOIN alasan_kunjungan ak ON jt.id = ak.janji_temu_id
            WHERE jt.pasien_id = ?
            ORDER BY jt.tanggal DESC, jt.jam_mulai DESC";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $pasien_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $riwayat = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $riwayat[] = $row;
    }
    
    return $riwayat;
}

/**
 * Get patient details
 * 
 * @param mysqli $conn Database connection
 * @param int $pasien_id Patient ID
 * @return array|null Patient details or null if not found
 */
function getDataPasien($conn, $pasien_id) {
    $query = "SELECT * FROM pasien WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $pasien_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

/**
 * Get consultation details
 * 
 * @param mysqli $conn Database connection
 * @param int $janji_id Appointment ID
 * @return array|null Consultation details or null if not found
 */
function getDetailKonsultasi($conn, $janji_id) {
    $query = "SELECT 
                jt.*,
                p.nama_lengkap as nama_pasien,
                psi.nama as nama_psikolog,
                jl.nama_layanan,
                ak.keluhan_utama,
                ak.durasi_keluhan,
                ak.harapan_konsultasi,
                ck.diagnosa,
                ck.rekomendasi,
                ck.catatan_privat,
                ck.created_at as waktu_catatan
            FROM janji_temu jt
            JOIN pasien p ON jt.pasien_id = p.id
            JOIN psikolog psi ON jt.psikolog_id = psi.id
            JOIN jenis_layanan jl ON jt.layanan_id = jl.id
            LEFT JOIN alasan_kunjungan ak ON jt.id = ak.janji_temu_id
            LEFT JOIN catatan_konsultasi ck ON jt.id = ck.janji_temu_id
            WHERE jt.id = ?";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $janji_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

/**
 * Validate consultation note data
 * 
 * @param array $data Data to validate
 * @return array Array of errors (empty if validation passes)
 */
function validateCatatanKonsultasi($data) {
    $errors = array();
    
    if (empty($data['diagnosa'])) {
        $errors[] = "Diagnosa harus diisi";
    }
    
    if (empty($data['rekomendasi'])) {
        $errors[] = "Rekomendasi harus diisi";
    }
    
    return $errors;
}
?>