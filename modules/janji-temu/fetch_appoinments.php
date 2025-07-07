<?php
require_once '../../config/database.php';

// Get JSON data from POST request
$data = json_decode(file_get_contents('php://input'), true);

// Build query with filters
$query = "SELECT 
    jt.id,
    jt.kode_janji,
    jt.tanggal,
    jt.jam_mulai,
    jt.jam_selesai,
    p.nama_lengkap as nama_pasien,
    ps.nama as nama_psikolog,
    jl.nama_layanan,
    jt.status
FROM janji_temu jt
JOIN pasien p ON jt.pasien_id = p.id
JOIN psikolog ps ON jt.psikolog_id = ps.id
JOIN jenis_layanan jl ON jt.layanan_id = jl.id
WHERE 1=1";

$params = [];
$types = "";

if (!empty($data['psikolog_id'])) {
    $query .= " AND jt.psikolog_id = ?";
    $params[] = $data['psikolog_id'];
    $types .= "i";
}

if (!empty($data['status'])) {
    $query .= " AND jt.status = ?";
    $params[] = $data['status'];
    $types .= "s";
}

if (!empty($data['layanan_id'])) {
    $query .= " AND jt.layanan_id = ?";
    $params[] = $data['layanan_id'];
    $types .= "i";
}

if (!empty($data['tanggal'])) {
    $query .= " AND DATE(jt.tanggal) = ?";
    $params[] = $data['tanggal'];
    $types .= "s";
}

$query .= " ORDER BY jt.tanggal DESC, jt.jam_mulai DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = [
        'id' => $row['id'],
        'kode_janji' => htmlspecialchars($row['kode_janji']),
        'tanggal' => $row['tanggal'],
        'jam_mulai' => $row['jam_mulai'],
        'jam_selesai' => $row['jam_selesai'],
        'nama_pasien' => htmlspecialchars($row['nama_pasien']),
        'nama_psikolog' => htmlspecialchars($row['nama_psikolog']),
        'nama_layanan' => htmlspecialchars($row['nama_layanan']),
        'status' => $row['status']
    ];
}

header('Content-Type: application/json');
echo json_encode($appointments);