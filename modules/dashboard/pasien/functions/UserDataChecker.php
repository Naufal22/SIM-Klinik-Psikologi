<?php
class UserDataChecker {
    private $conn;
    private $userId;

    public function __construct($conn, $userId) {
        $this->conn = $conn;
        $this->userId = $userId;
    }

    public function isDataComplete() {
        $query = "SELECT p.* FROM pasien p 
                 JOIN users u ON p.id = u.reference_id 
                 WHERE u.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        return !empty($data['nama_lengkap']) && 
               !empty($data['tanggal_lahir']) && 
               !empty($data['alamat']) && 
               !empty($data['no_telepon']);
    }
}
?>