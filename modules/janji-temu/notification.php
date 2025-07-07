<?php
require_once '../../config/database.php';

class NotificationSystem {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function createReminder($appointmentId, $type = 'Email') {
        try {
            $sql = "INSERT INTO reminder_janji_temu (
                janji_temu_id, 
                tipe_reminder, 
                status, 
                waktu_kirim,
                created_at
            ) VALUES (
                ?, 
                ?, 
                'Pending',
                DATE_ADD(NOW(), INTERVAL 1 DAY),
                NOW()
            )";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('is', $appointmentId, $type);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error creating reminder: " . $e->getMessage());
            return false;
        }
    }
    
    public function processPendingReminders() {
        try {
            $sql = "
                SELECT 
                    r.*,
                    jt.kode_janji,
                    jt.tanggal,
                    jt.jam_mulai,
                    p.nama_lengkap,
                    p.email,
                    p.no_telepon,
                    ps.nama as psikolog_nama
                FROM reminder_janji_temu r
                JOIN janji_temu jt ON r.janji_temu_id = jt.id
                JOIN pasien p ON jt.pasien_id = p.id
                JOIN psikolog ps ON jt.psikolog_id = ps.id
                WHERE r.status = 'Pending'
                AND jt.status = 'Terjadwal'
                LIMIT 50
            ";
            
            $result = $this->conn->query($sql);
            
            while ($reminder = $result->fetch_assoc()) {
                $success = $this->sendNotification($reminder);
                $newStatus = $success ? 'Terkirim' : 'Gagal';
                
                $updateSql = "UPDATE reminder_janji_temu 
                             SET status = ?, 
                                 waktu_terkirim = NOW() 
                             WHERE id = ?";
                $stmt = $this->conn->prepare($updateSql);
                $stmt->bind_param('si', $newStatus, $reminder['id']);
                $stmt->execute();
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("Error processing reminders: " . $e->getMessage());
            return false;
        }
    }
    
    public function sendStatusUpdateNotification($appointmentId, $newStatus) {
        try {
            $sql = "
                SELECT 
                    jt.*,
                    p.nama_lengkap,
                    p.email,
                    ps.nama as psikolog_nama
                FROM janji_temu jt
                JOIN pasien p ON jt.pasien_id = p.id
                JOIN psikolog ps ON jt.psikolog_id = ps.id
                WHERE jt.id = ?
            ";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('i', $appointmentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $appointment = $result->fetch_assoc();
            
            if (!$appointment) {
                return false;
            }
            
            return $this->sendNotification([
                'email' => $appointment['email'],
                'nama_lengkap' => $appointment['nama_lengkap'],
                'kode_janji' => $appointment['kode_janji'],
                'status' => $newStatus
            ]);
            
        } catch (\Exception $e) {
            error_log("Error sending status update: " . $e->getMessage());
            return false;
        }
    }
    
    private function sendNotification($data) {
        // Simulasi pengiriman notifikasi
        return true;
    }
}

// Initialize notification system
$notificationSystem = new NotificationSystem($conn);

// Process reminders if called via CLI
if (php_sapi_name() === 'cli') {
    $notificationSystem->processPendingReminders();
}
?>