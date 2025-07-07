<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Action tidak ditemukan']);
    exit;
}

$action = $_POST['action'];

switch ($action) {
    case 'add':
        try {
            $conn->begin_transaction();
    
            // Validasi input
            $required = ['username', 'email', 'password', 'role'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field $field harus diisi");
                }
            }
    
            // Validasi password
            if ($_POST['password'] !== $_POST['confirm_password']) {
                throw new Exception("Password dan konfirmasi password tidak cocok");
            }

            // Validasi email
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Format email tidak valid");
            }
    
            // Validasi email unik
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $_POST['email']);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                throw new Exception("Email sudah terdaftar");
            }
    
            // Validasi username unik
            $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check->bind_param("s", $_POST['username']);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                throw new Exception("Username sudah digunakan");
            }
    
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $role = $_POST['role'];
            $status = $_POST['status'] ?? 'Aktif';
    
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare statement error: " . $conn->error);
            }
            
            $stmt->bind_param("sssss", $username, $email, $password, $role, $status);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute error: " . $stmt->error);
            }
    
            $user_id = $stmt->insert_id;
            
            if ($role === 'psikolog') {
                $_SESSION['new_psikolog_user_id'] = $user_id;
            }
    
            logActivity($_SESSION['user_id'], "Membuat akun baru: $username");
            
            $conn->commit();
    
            $redirect_url = ($role === 'psikolog')
                ? $main_url . 'modules/psikolog/add.php' . (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'master' ? '?return=master' : '')
                : $main_url . 'modules/master-data/staff.php';
    
            echo json_encode([
                'status' => 'success',
                'message' => 'Akun berhasil dibuat',
                'redirect' => $redirect_url
            ]);
            
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
        break;

    case 'edit':
        try {
            $conn->begin_transaction();

            if (empty($_POST['id'])) {
                throw new Exception("ID user tidak valid");
            }

            $required = ['username', 'email', 'role', 'status'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field $field harus diisi");
                }
            }

            $id = $_POST['id'];
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $role = $_POST['role'];
            $status = $_POST['status'];

            // Validasi email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Format email tidak valid");
            }

            // Check unique email and username except current user
            $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check->bind_param("si", $email, $id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                throw new Exception("Email sudah digunakan oleh user lain");
            }

            $check = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $check->bind_param("si", $username, $id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                throw new Exception("Username sudah digunakan oleh user lain");
            }

            // Jika password diisi, update password juga
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password_hash = ?, role = ?, status = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $username, $email, $password, $role, $status, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, status = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $username, $email, $role, $status, $id);
            }

            if (!$stmt->execute()) {
                throw new Exception("Gagal mengupdate user: " . $stmt->error);
            }

            logActivity($_SESSION['user_id'], "Mengupdate data user: $username");

            $conn->commit();
            echo json_encode([
                'status' => 'success',
                'message' => 'Data user berhasil diupdate'
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
        break;

    case 'delete':
        try {
            $conn->begin_transaction();

            if (empty($_POST['id'])) {
                throw new Exception("ID user tidak valid");
            }

            $id = $_POST['id'];

            // Cek apakah user yang akan dihapus adalah user yang sedang login
            if ($id == $_SESSION['user_id']) {
                throw new Exception("Tidak dapat menghapus akun sendiri");
            }

            // Ambil informasi user sebelum dihapus untuk logging
            $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (!$user) {
                throw new Exception("User tidak ditemukan");
            }

            // Hapus activity log terlebih dahulu
            $stmt = $conn->prepare("DELETE FROM activity_log WHERE user_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            // Kemudian hapus user
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            if (!$stmt->execute()) {
                throw new Exception("Gagal menghapus user");
            }

            // Log aktivitas penghapusan
            logActivity($_SESSION['user_id'], "Menghapus user: " . $user['username']);

            $conn->commit();
            echo json_encode([
                'status' => 'success',
                'message' => 'User berhasil dihapus'
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
        break;

    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Action tidak valid'
        ]);
        break;
}

function logActivity($user_id, $activity) {
    global $conn;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO activity_log (user_id, aktivitas, ip_address, user_agent) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("isss", $user_id, $activity, $ip_address, $user_agent);
            $stmt->execute();
        }
    } catch (Exception $e) {
        error_log("Error logging activity: " . $e->getMessage());
    }
}

$conn->close();
?>