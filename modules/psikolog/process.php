<?php
session_start();
require_once '../../config/database.php';
require_once 'functions.php';

header('Content-Type: application/json');

if (!isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Action tidak ditemukan']);
    exit;
}

$action = $_POST['action'];

try {
    $conn->begin_transaction();

    switch ($action) {
        case 'add':
            error_log('REQUEST: ' . print_r($_REQUEST, true));
            error_log('FILES: ' . print_r($_FILES, true));
            // Validasi input
            $required = ['nama', 'no_izin_praktik', 'email', 'no_telepon', 'spesialisasi', 'alamat'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field $field harus diisi");
                }
            }

            // Validasi user_id dari session
            if (!isset($_SESSION['new_psikolog_user_id'])) {
                throw new Exception("Data user tidak ditemukan");
            }

            $user_id = $_SESSION['new_psikolog_user_id'];

            // Validasi nomor izin praktik unik
            $check = $conn->prepare("SELECT id FROM psikolog WHERE no_izin_praktik = ?");
            $check->bind_param("s", $_POST['no_izin_praktik']);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                // Hapus user jika validasi gagal
                $delete_user = $conn->prepare("DELETE FROM users WHERE id = ?");
                $delete_user->bind_param("i", $user_id);
                $delete_user->execute();
                
                unset($_SESSION['new_psikolog_user_id']);
                throw new Exception("Nomor izin praktik sudah terdaftar");
            }

            // Upload foto
            $foto = 'default.png'; // Default foto
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                error_log('File uploaded successfully');
                $uploadDir = "../../uploads/psikolog/";
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
            
                $fileExtension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $allowedTypes = ['jpg', 'jpeg', 'png'];
                
                if (!in_array($fileExtension, $allowedTypes)) {
                    throw new Exception("Tipe file tidak diizinkan. Hanya JPG, JPEG, dan PNG yang diperbolehkan.");
                }
            
                if ($_FILES['foto']['size'] > 2000000) {
                    throw new Exception("Ukuran file terlalu besar. Maksimal 2MB.");
                }
                // Ambil username dari email (bagian sebelum @)
                $username = explode('@', $_POST['email'])[0];
                // Bersihkan username dari karakter yang tidak diinginkan
                $username = preg_replace('/[^a-zA-Z0-9]/', '', $username);
                // Tambahkan timestamp untuk menghindari konflik nama
                $foto = $username . '_' . time() . '.' . $fileExtension;
                
                $targetPath = $uploadDir . $foto;
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $targetPath)) {
                    error_log('Upload Error: ' . print_r($_FILES['foto']['error'], true));
                    throw new Exception("Gagal mengupload foto");
                }
            } else {
                error_log('No file or upload error: ' . (isset($_FILES['foto']) ? $_FILES['foto']['error'] : 'No file'));
                $foto = 'default.png';
            }
            // Insert data psikolog
            $stmt = $conn->prepare("INSERT INTO psikolog (nama, no_izin_praktik, email, no_telepon, spesialisasi, alamat, foto, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Aktif')");
            $stmt->bind_param("sssssss", 
                $_POST['nama'],
                $_POST['no_izin_praktik'],
                $_POST['email'],
                $_POST['no_telepon'],
                $_POST['spesialisasi'],
                $_POST['alamat'],
                $foto
            );

            if (!$stmt->execute()) {
                // Hapus foto yang diupload jika insert gagal
                if ($foto !== 'default.png' && file_exists($uploadDir . $foto)) {
                    unlink($uploadDir . $foto);
                }
                throw new Exception("Gagal menyimpan data psikolog: " . $stmt->error);
            }

            $psikolog_id = $stmt->insert_id;

            // Update reference_id di tabel users
            $stmt = $conn->prepare("UPDATE users SET reference_id = ? WHERE id = ?");
            $stmt->bind_param("ii", $psikolog_id, $user_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Gagal mengupdate reference_id user");
            }

            // Log aktivitas
            $stmt = $conn->prepare("INSERT INTO activity_log (user_id, aktivitas, ip_address, user_agent) VALUES (?, ?, ?, ?)");
            $aktivitas = "Menambahkan data psikolog: " . $_POST['nama'];
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $stmt->bind_param("isss", $user_id, $aktivitas, $ip_address, $user_agent);
            $stmt->execute();

            // Hapus session new_psikolog_user_id
            unset($_SESSION['new_psikolog_user_id']);

            $conn->commit();

            // Tentukan URL redirect berdasarkan role
            $redirect_url = isset($_GET['return']) && $_GET['return'] === 'master'
                ? $main_url . 'modules/master-data/staff.php'
                : $main_url . 'modules/psikolog/index.php';

            echo json_encode([
                'status' => 'success',
                'message' => 'Data psikolog berhasil disimpan',
                'redirect' => $redirect_url
            ]);
            break;

        case 'edit':
            error_log('REQUEST EDIT: ' . print_r($_REQUEST, true));
            error_log('FILES EDIT: ' . print_r($_FILES, true));
            if (empty($_POST['id'])) {
                throw new Exception("ID psikolog tidak valid");
            }

            $required = ['nama', 'no_izin_praktik', 'email', 'no_telepon', 'spesialisasi', 'alamat', 'status'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field $field harus diisi");
                }
            }

            // Validasi nomor izin praktik unik kecuali untuk psikolog yang sedang diedit
            $check = $conn->prepare("SELECT id FROM psikolog WHERE no_izin_praktik = ? AND id != ?");
            $check->bind_param("si", $_POST['no_izin_praktik'], $_POST['id']);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                throw new Exception("Nomor izin praktik sudah digunakan");
            }

            // Handle file upload if new file is uploaded
            $foto = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                $foto = handleFileUpload($_FILES['foto'], $_POST['foto_lama']);
            }

            // Update data
            $query = "UPDATE psikolog SET 
                     nama = ?, 
                     no_izin_praktik = ?, 
                     email = ?, 
                     no_telepon = ?, 
                     spesialisasi = ?, 
                     alamat = ?, 
                     status = ?";
            
            $params = [
                $_POST['nama'],
                $_POST['no_izin_praktik'],
                $_POST['email'],
                $_POST['no_telepon'],
                $_POST['spesialisasi'],
                $_POST['alamat'],
                $_POST['status']
            ];
            $types = 'sssssss';

            if ($foto) {
                $query .= ", foto = ?";
                $params[] = $foto;
                $types .= 's';
            }
        
            $query .= " WHERE id = ?";
            $params[] = $_POST['id'];
            $types .= 'i';
        
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                throw new Exception("Gagal mengupdate data psikolog: " . $stmt->error);
            }
            // Log aktivitas
            logActivity($_SESSION['user_id'], "Mengupdate data psikolog: " . $_POST['nama']);

            $conn->commit();
            echo json_encode([
                'status' => 'success',
                'message' => 'Data psikolog berhasil diperbarui',
                'redirect' => 'index.php'
            ]);
            break;

            case 'delete':
                if (empty($_POST['id'])) {
                    throw new Exception("ID psikolog tidak valid");
                }
            
                // Check if psychologist has appointments
                $check = $conn->prepare("SELECT COUNT(*) as total FROM janji_temu WHERE psikolog_id = ?");
                $check->bind_param("i", $_POST['id']);
                $check->execute();
                $result = $check->get_result();
                $row = $result->fetch_assoc();
            
                if ($row['total'] > 0) {
                    throw new Exception("Tidak dapat menghapus psikolog karena masih memiliki riwayat konsultasi");
                }
            
                // Get user_id and photo filename before deleting
                $stmt = $conn->prepare("SELECT u.id as user_id, p.foto FROM psikolog p LEFT JOIN users u ON u.reference_id = p.id WHERE p.id = ?");
                $stmt->bind_param("i", $_POST['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_assoc();
            
                if ($data['user_id']) {
                    // Delete activity logs first
                    $stmt = $conn->prepare("DELETE FROM activity_log WHERE user_id = ?");
                    $stmt->bind_param("i", $data['user_id']);
                    $stmt->execute();
            
                    // Then delete user
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->bind_param("i", $data['user_id']);
                    $stmt->execute();
                }
            
                // Delete schedules
                $stmt = $conn->prepare("DELETE FROM jadwal_psikolog WHERE psikolog_id = ?");
                $stmt->bind_param("i", $_POST['id']);
                $stmt->execute();
            
                // Delete psychologist
                $stmt = $conn->prepare("DELETE FROM psikolog WHERE id = ?");
                $stmt->bind_param("i", $_POST['id']);
                $stmt->execute();
            
                // Delete photo file
                if ($data['foto'] && $data['foto'] != 'default.png') {
                    $fotoPath = "../../uploads/psikolog/" . $data['foto'];
                    if (file_exists($fotoPath)) {
                        unlink($fotoPath);
                    }
                }
            
                $conn->commit();
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Data psikolog berhasil dihapus'
                ]);
                break;

        case 'edit_jadwal':
            if (empty($_POST['jadwal_id'])) {
                throw new Exception("ID jadwal tidak valid");
            }

            $required = ['psikolog_id', 'hari', 'jam_mulai', 'jam_selesai'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field $field harus diisi");
                }
            }

            // Validate time format and logic
            if (!validateJadwalTime($_POST['jam_mulai'], $_POST['jam_selesai'])) {
                throw new Exception("Format waktu tidak valid atau jam selesai harus lebih besar dari jam mulai");
            }

            // Check for schedule conflicts
            if (checkJadwalConflict(
                $_POST['psikolog_id'],
                $_POST['hari'],
                $_POST['jam_mulai'],
                $_POST['jam_selesai'],
                $_POST['jadwal_id']
            )) {
                throw new Exception("Jadwal bertabrakan dengan jadwal yang sudah ada");
            }

            $stmt = $conn->prepare("UPDATE jadwal_psikolog SET psikolog_id = ?, hari = ?, jam_mulai = ?, jam_selesai = ? WHERE id = ?");
            $stmt->bind_param("isssi", 
                $_POST['psikolog_id'],
                $_POST['hari'],
                $_POST['jam_mulai'],
                $_POST['jam_selesai'],
                $_POST['jadwal_id']
            );

            if (!$stmt->execute()) {
                throw new Exception("Gagal mengupdate jadwal: " . $stmt->error);
            }

            // Log aktivitas
            logActivity($_SESSION['user_id'], "Mengupdate jadwal psikolog ID: " . $_POST['jadwal_id']);

            $conn->commit();
            echo json_encode([
                'status' => 'success',
                'message' => 'Jadwal berhasil diupdate'
            ]);
            break;
        
        // Tambahkan case ini di dalam switch statement
        case 'add_jadwal':
            // Validasi input
            $required = ['psikolog_id', 'hari', 'jam_mulai', 'jam_selesai'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field $field harus diisi");
                }
            }

            // Validasi format waktu
            if (!validateJadwalTime($_POST['jam_mulai'], $_POST['jam_selesai'])) {
                throw new Exception("Format waktu tidak valid atau jam selesai harus lebih besar dari jam mulai");
            }

            // Cek konflik jadwal
            if (checkJadwalConflict(
                $_POST['psikolog_id'],
                $_POST['hari'],
                $_POST['jam_mulai'],
                $_POST['jam_selesai']
            )) {
                throw new Exception("Jadwal bertabrakan dengan jadwal yang sudah ada");
            }

            // Insert jadwal baru
            $stmt = $conn->prepare("INSERT INTO jadwal_psikolog (psikolog_id, hari, jam_mulai, jam_selesai, status) VALUES (?, ?, ?, ?, 'Aktif')");
            $stmt->bind_param("isss", 
                $_POST['psikolog_id'],
                $_POST['hari'],
                $_POST['jam_mulai'],
                $_POST['jam_selesai']
            );

            if (!$stmt->execute()) {
                throw new Exception("Gagal menyimpan jadwal: " . $stmt->error);
            }

            // Log aktivitas
            logActivity($_SESSION['user_id'], "Menambahkan jadwal baru untuk psikolog ID: " . $_POST['psikolog_id']);

            $conn->commit();
            echo json_encode([
                'status' => 'success',
                'message' => 'Jadwal berhasil ditambahkan'
            ]);
            break;

        case 'delete_jadwal':
            if (empty($_POST['jadwal_id'])) {
                throw new Exception("ID jadwal tidak valid");
            }

            // Check if there are appointments for this schedule
            $check = $conn->prepare("SELECT COUNT(*) as total FROM janji_temu WHERE jadwal_id = ?");
            $check->bind_param("i", $_POST['jadwal_id']);
            $check->execute();
            $result = $check->get_result();
            $row = $result->fetch_assoc();

            if ($row['total'] > 0) {
                throw new Exception("Tidak dapat menghapus jadwal karena sudah ada janji temu yang terkait");
            }

            $stmt = $conn->prepare("DELETE FROM jadwal_psikolog WHERE id = ?");
            $stmt->bind_param("i", $_POST['jadwal_id']);

            if (!$stmt->execute()) {
                throw new Exception("Gagal menghapus jadwal: " . $stmt->error);
            }

            // Log aktivitas
            logActivity($_SESSION['user_id'], "Menghapus jadwal psikolog ID: " . $_POST['jadwal_id']);

            $conn->commit();
            echo json_encode([
                'status' => 'success',
                'message' => 'Jadwal berhasil dihapus'
            ]);
            break;
            default:
            throw new Exception("Action tidak valid");


    }
} catch (Exception $e) {
    $conn->rollback();
    
    // Jika masih dalam proses pembuatan psikolog baru dan terjadi error
    if (isset($_SESSION['new_psikolog_user_id'])) {
        // Hapus user yang sudah dibuat
        $delete_user = $conn->prepare("DELETE FROM users WHERE id = ?");
        $delete_user->bind_param("i", $_SESSION['new_psikolog_user_id']);
        $delete_user->execute();
        
        unset($_SESSION['new_psikolog_user_id']);
    }
    
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage()
    ]);
}


function handleFileUpload($file, $oldFile = null) {
    // Validasi apakah ada file yang diupload
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $oldFile; // Kembalikan nama file lama jika tidak ada upload baru
    }

    $targetDir = "../../uploads/psikolog/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Validasi direktori
    if (!is_writable($targetDir)) {
        throw new Exception("Folder upload tidak dapat ditulis");
    }

    // Validasi file
    if ($file["error"] !== UPLOAD_ERR_OK) {
        throw new Exception("Error saat upload file: " . $file["error"]);
    }

    // Validasi ukuran (max 2MB)
    if ($file["size"] > 2000000) {
        throw new Exception("Ukuran file terlalu besar (maksimal 2MB)");
    }

    // Validasi tipe file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file["tmp_name"]);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception("Tipe file tidak diizinkan (hanya JPG, JPEG, PNG)");
    }

    // Generate nama file baru
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $newFileName = uniqid('psikolog_') . '.' . $imageFileType;
    $targetFile = $targetDir . $newFileName;

    // Upload file
    if (!move_uploaded_file($file["tmp_name"], $targetFile)) {
        throw new Exception("Gagal mengupload file");
    }

    // Hapus file lama jika ada
    if ($oldFile && $oldFile !== 'default.png' && file_exists($targetDir . $oldFile)) {
        unlink($targetDir . $oldFile);
    }

    return $newFileName;
}



function logActivity($user_id, $activity) {
    global $conn;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO activity_log (user_id, aktivitas, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $activity, $ip_address, $user_agent);
    $stmt->execute();
}

$conn->close();
?>