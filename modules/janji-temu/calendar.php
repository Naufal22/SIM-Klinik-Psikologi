<?php
session_start();
require_once '../../config/database.php';
require_once '../../auth/auth.php';

// Pastikan user sudah login dan memiliki role yang sesuai
requireRole([ROLE_ADMIN, ROLE_PSIKOLOG]);
checkSessionTimeout();

// Pastikan ini adalah request AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die('Direct access not permitted');
}

// Ambil parameter filter
$start = $_POST['start'] ?? '';
$end = $_POST['end'] ?? '';
$status = $_POST['status'] ?? '';
$layanan_id = $_POST['layanan_id'] ?? '';

// Bangun query dasar
$query = "SELECT 
    jt.id,
    jt.kode_janji,
    p.nama_lengkap as nama_pasien,
    ps.nama as nama_psikolog,
    jl.nama_layanan,
    jt.tanggal,
    jt.jam_mulai,
    jt.jam_selesai,
    jt.status,
    jt.psikolog_id
FROM janji_temu jt
JOIN pasien p ON jt.pasien_id = p.id
JOIN psikolog ps ON jt.psikolog_id = ps.id
JOIN jenis_layanan jl ON jt.layanan_id = jl.id
WHERE jt.tanggal BETWEEN ? AND ?";

$params = [$start, $end];
$types = "ss";

// Jika user adalah psikolog, filter berdasarkan user_id
if ($_SESSION['role'] === ROLE_PSIKOLOG) {
    // Dapatkan psikolog_id dari tabel users
    $userQuery = "SELECT reference_id FROM users WHERE id = ? AND role = 'psikolog'";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param("i", $_SESSION['user_id']);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    
    if ($psikologData = $userResult->fetch_assoc()) {
        $query .= " AND jt.psikolog_id = ?";
        $params[] = $psikologData['reference_id'];
        $types .= "i";
        
        // Debug log
        error_log("Psikolog ID: " . $psikologData['reference_id']);
    }
}

// Tambahkan filter status jika ada
if ($status) {
    $query .= " AND jt.status = ?";
    $params[] = $status;
    $types .= "s";
}

// Tambahkan filter layanan jika ada
if ($layanan_id) {
    $query .= " AND jt.layanan_id = ?";
    $params[] = $layanan_id;
    $types .= "i";
}

// Debug log
error_log("Query: " . $query);
error_log("User ID: " . $_SESSION['user_id']);
error_log("Role: " . $_SESSION['role']);
error_log("Params: " . print_r($params, true));

// Siapkan dan eksekusi query
$stmt = $conn->prepare($query);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    die("Query preparation failed");
}

$stmt->bind_param($types, ...$params);
if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    die("Query execution failed");
}

$result = $stmt->get_result();

// Siapkan array events
$events = [];

// Pemetaan warna untuk status
$statusColors = [
    'Terjadwal' => '#3788d8',     // Biru
    'Check-in' => '#ffa500',      // Oranye
    'Dalam_Konsultasi' => '#00a65a', // Hijau Tua
    'Selesai' => '#28a745',       // Hijau
    'Dibatalkan' => '#dc3545',    // Merah
    'Tidak Hadir' => '#6c757d'    // Abu-abu
];

// Build events array
while ($row = $result->fetch_assoc()) {
    $start = $row['tanggal'] . 'T' . $row['jam_mulai'];
    $end = $row['tanggal'] . 'T' . $row['jam_selesai'];
    
    $events[] = [
        'id' => $row['id'],
        'title' => "[{$row['kode_janji']}] {$row['nama_pasien']}",
        'start' => $start,
        'end' => $end,
        'backgroundColor' => $statusColors[$row['status']] ?? '#3788d8',
        'borderColor' => $statusColors[$row['status']] ?? '#3788d8',
        'extendedProps' => [
            'status' => $row['status'],
            'layanan' => $row['nama_layanan'],
            'psikolog' => $row['nama_psikolog'],
            'psikolog_id' => $row['psikolog_id']
        ]
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($events);
