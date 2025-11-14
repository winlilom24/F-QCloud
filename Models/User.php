<?php
// models/User.php
require_once "Repository/Database.php";

class User {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    // Lấy nhân viên theo id_quan_ly
    public function getByQuanLy($id_quan_ly) {        
        $id = (int)$id_quan_ly;
        $query = "SELECT user_id, ten, ten_quan, role, sdt, email, id_quan_ly 
                  FROM user 
                  WHERE id_quan_ly = ? AND role = 'Nhân viên'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;

    }

    // // Lấy quản lý (không có id_quan_ly)
    // private function fetchAll($result) {
    //     $data = [];
    //     while ($row = $result->fetch_assoc()) {
    //         $data[] = $row;
    //     }
    //     return $data;
    // }
}
?>