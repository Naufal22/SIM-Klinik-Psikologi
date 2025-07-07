<?php
require_once '../../config/database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Get the action from POST or GET
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Handle different actions
switch ($action) {
    case 'create':
        handleCreate();
        break;
    case 'update':
        handleUpdate();
        break;
    case 'delete':
        handleDelete();
        break;
    default:
        sendResponse('error', 'Invalid action specified');
}

function handleCreate() {
    global $conn;
    
    try {
        // Validate required fields
        $nama_layanan = validateInput($_POST['nama_layanan'] ?? '', 'Nama layanan');
        $durasi_menit = validateDurasi($_POST['durasi_menit'] ?? 0);
        $tarif = validateTarif($_POST['tarif'] ?? 0);
        $status = validateStatus($_POST['status'] ?? 'Aktif');
        $deskripsi = $_POST['deskripsi'] ?? '';

        // Prepare and execute query
        $query = "INSERT INTO jenis_layanan (nama_layanan, deskripsi, durasi_menit, tarif, status) 
                 VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ssiis', $nama_layanan, $deskripsi, $durasi_menit, $tarif, $status);
        
        if ($stmt->execute()) {
            sendResponse('success', 'Layanan berhasil ditambahkan');
        } else {
            throw new Exception('Gagal menambahkan layanan');
        }
    } catch (Exception $e) {
        sendResponse('error', $e->getMessage());
    }
}

function handleUpdate() {
    global $conn;
    
    try {
        // Validate required fields
        $id = validateId($_POST['id'] ?? 0);
        $nama_layanan = validateInput($_POST['nama_layanan'] ?? '', 'Nama layanan');
        $durasi_menit = validateDurasi($_POST['durasi_menit'] ?? 0);
        $tarif = validateTarif($_POST['tarif'] ?? 0);
        $status = validateStatus($_POST['status'] ?? 'Aktif');
        $deskripsi = $_POST['deskripsi'] ?? '';

        // Prepare and execute query
        $query = "UPDATE jenis_layanan 
                 SET nama_layanan = ?, 
                     deskripsi = ?, 
                     durasi_menit = ?, 
                     tarif = ?, 
                     status = ? 
                 WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ssiisi', $nama_layanan, $deskripsi, $durasi_menit, $tarif, $status, $id);
        
        if ($stmt->execute()) {
            sendResponse('success', 'Layanan berhasil diperbarui');
        } else {
            throw new Exception('Gagal memperbarui layanan');
        }
    } catch (Exception $e) {
        sendResponse('error', $e->getMessage());
    }
}

function handleDelete() {
    global $conn;
    
    try {
        $id = validateId($_POST['id'] ?? 0);

        // Check if layanan is being used in janji_temu
        $check_query = "SELECT COUNT(*) as count FROM janji_temu WHERE layanan_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param('i', $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            throw new Exception('Layanan tidak dapat dihapus karena sedang digunakan dalam janji temu');
        }

        // Perform hard delete
        $query = "DELETE FROM jenis_layanan WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            sendResponse('success', 'Layanan berhasil dihapus');
        } else {
            throw new Exception('Gagal menghapus layanan');
        }
    } catch (Exception $e) {
        sendResponse('error', $e->getMessage());
    }
}

// Validation functions
function validateInput($value, $field) {
    $value = trim($value);
    if (empty($value)) {
        throw new Exception("$field tidak boleh kosong");
    }
    return $value;
}

function validateId($id) {
    $id = (int)$id;
    if ($id <= 0) {
        throw new Exception('ID tidak valid');
    }
    return $id;
}

function validateDurasi($durasi) {
    $durasi = (int)$durasi;
    if ($durasi < 15) {
        throw new Exception('Durasi minimal 15 menit');
    }
    return $durasi;
}

function validateTarif($tarif) {
    // Remove any formatting (dots, commas)
    $tarif = str_replace(['.', ','], '', $tarif);
    $tarif = (float)$tarif;
    if ($tarif <= 0) {
        throw new Exception('Tarif harus lebih dari 0');
    }
    return $tarif;
}

function validateStatus($status) {
    if (!in_array($status, ['Aktif', 'Tidak Aktif'])) {
        throw new Exception('Status tidak valid');
    }
    return $status;
}

function sendResponse($status, $message, $data = null) {
    $response = [
        'status' => $status,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}