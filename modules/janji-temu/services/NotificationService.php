<?php
class NotificationService {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function createAppointmentNotification($userId, $appointmentId, $type = 'created') {
        $messages = [
            'created' => 'Janji temu baru telah dibuat',
            'updated' => 'Janji temu telah diperbarui',
            'cancelled' => 'Janji temu telah dibatalkan'
        ];

        $stmt = $this->conn->prepare("
            INSERT INTO notifikasi (user_id, judul, pesan, tipe)
            VALUES (?, 'Janji Temu', ?, 'appointment')
        ");

        $message = $messages[$type] ?? $messages['created'];
        $stmt->bind_param("is", $userId, $message);
        
        if (!$stmt->execute()) {
            throw new Exception('Gagal membuat notifikasi');
        }
    }

    public function sendAppointmentReminder($appointmentId, $userId, $appointmentDate) {
        // Implement reminder logic here
        // This could be email, SMS, or other notification methods
    }

    public function sendCancellationNotification($appointmentId) {
        try {
            // Get appointment details including patient and psychologist info
            $stmt = $this->conn->prepare("
                SELECT 
                    jt.id,
                    jt.pasien_id,
                    jt.psikolog_id,
                    jt.tanggal,
                    jt.jam_mulai,
                    p.nama as nama_pasien,
                    psi.nama as nama_psikolog
                FROM janji_temu jt
                JOIN pasien p ON jt.pasien_id = p.id
                JOIN psikolog psi ON jt.psikolog_id = psi.id
                WHERE jt.id = ?
            ");
            
            $stmt->bind_param("i", $appointmentId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Data janji temu tidak ditemukan");
            }
            
            $appointment = $result->fetch_assoc();
            
            // Create notification for patient
            $patientMessage = sprintf(
                "Janji temu Anda dengan %s pada tanggal %s pukul %s telah dibatalkan",
                $appointment['nama_psikolog'],
                date('d/m/Y', strtotime($appointment['tanggal'])),
                date('H:i', strtotime($appointment['jam_mulai']))
            );
            
            $this->createNotification(
                $appointment['pasien_id'],
                'Pembatalan Janji Temu',
                $patientMessage,
                'cancellation'
            );
            
            // Create notification for psychologist
            $psychologistMessage = sprintf(
                "Janji temu dengan pasien %s pada tanggal %s pukul %s telah dibatalkan",
                $appointment['nama_pasien'],
                date('d/m/Y', strtotime($appointment['tanggal'])),
                date('H:i', strtotime($appointment['jam_mulai']))
            );
            
            $this->createNotification(
                $appointment['psikolog_id'],
                'Pembatalan Janji Temu',
                $psychologistMessage,
                'cancellation'
            );
            
        } catch (Exception $e) {
            // Log error but don't throw exception to prevent disrupting the main cancellation flow
            error_log("Error sending cancellation notification: " . $e->getMessage());
        }
    }

    private function createNotification($userId, $title, $message, $type) {
        $stmt = $this->conn->prepare("
            INSERT INTO notifikasi (user_id, judul, pesan, tipe)
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->bind_param("isss", $userId, $title, $message, $type);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal membuat notifikasi");
        }
    }
}