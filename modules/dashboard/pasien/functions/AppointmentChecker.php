<?php
class AppointmentChecker {
    private $conn;
    private $userId;

    public function __construct($conn, $userId) {
        $this->conn = $conn;
        $this->userId = $userId;
    }

    public function hasActiveAppointment() {
        $query = "SELECT jt.* FROM janji_temu jt
                 JOIN pasien p ON jt.pasien_id = p.id
                 JOIN users u ON p.id = u.reference_id
                 WHERE u.id = ? AND jt.status IN ('Terjadwal', 'Check-in')
                 AND jt.tanggal >= CURDATE()
                 ORDER BY jt.tanggal, jt.jam_mulai
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }

    public function getNextAppointment() {
        $query = "SELECT jt.*, ps.nama as nama_psikolog, jl.nama_layanan
                 FROM janji_temu jt
                 JOIN psikolog ps ON jt.psikolog_id = ps.id
                 JOIN jenis_layanan jl ON jt.layanan_id = jl.id
                 JOIN pasien p ON jt.pasien_id = p.id
                 JOIN users u ON p.id = u.reference_id
                 WHERE u.id = ? AND jt.status IN ('Terjadwal', 'Check-in')
                 AND jt.tanggal >= CURDATE()
                 ORDER BY jt.tanggal, jt.jam_mulai
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>