
<?php
require_once __DIR__ . '/../utils/UserUtil.php';

class DashboardData {
    private $conn;
    private $userId;
    private $userUtil;

    public function __construct($conn, $userId) {
        $this->conn = $conn;
        $this->userId = $userId;
        $this->userUtil = new UserUtil($conn);
    }

    public function getUserData() {
        // Ambil data user termasuk username jika data pasien belum ada
        $query = "SELECT p.*, u.email, u.username 
                 FROM users u 
                 LEFT JOIN pasien p ON u.reference_id = p.id
                 WHERE u.id = ?";
                 
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        
        // Jika tidak ada nama_lengkap, gunakan username
        if (!$userData['nama_lengkap']) {
            $userData['nama_lengkap'] = $userData['username'];
        }
        
        return $userData;
    }

    // Method lain tetap sama



    public function getPsychologists() {
        $query = "SELECT p.*, 
                 GROUP_CONCAT(DISTINCT jp.hari ORDER BY FIELD(jp.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')) as hari_praktek
                 FROM psikolog p
                 LEFT JOIN jadwal_psikolog jp ON p.id = jp.psikolog_id
                 WHERE p.status = 'Aktif'
                 GROUP BY p.id
                 ORDER BY p.nama";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getConsultationHistory() {
        $query = "SELECT jt.*, ps.nama as nama_psikolog, jl.nama_layanan
                 FROM janji_temu jt
                 JOIN psikolog ps ON jt.psikolog_id = ps.id
                 JOIN jenis_layanan jl ON jt.layanan_id = jl.id
                 JOIN pasien p ON jt.pasien_id = p.id
                 JOIN users u ON p.id = u.reference_id
                 WHERE u.id = ? AND jt.status = 'Selesai'
                 ORDER BY jt.tanggal DESC, jt.jam_mulai DESC
                 LIMIT 5";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>