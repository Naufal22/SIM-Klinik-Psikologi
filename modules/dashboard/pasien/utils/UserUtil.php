
<?php
class UserUtil {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function getUserDisplayName($userId) {
        // Cek data user
        $query = "SELECT u.username, u.reference_id, p.nama_lengkap 
                 FROM users u 
                 LEFT JOIN pasien p ON u.reference_id = p.id 
                 WHERE u.id = ?";
                 
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        
        // Jika ada nama lengkap di tabel pasien, gunakan itu
        if ($userData['nama_lengkap']) {
            return $userData['nama_lengkap'];
        }
        
        // Jika tidak ada, gunakan username
        return $userData['username'];
    }
}
