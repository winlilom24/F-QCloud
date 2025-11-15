<?php
// models/TaiKhoan.php
require_once "Repository/Database.php";

class TaiKhoan {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    // CHỈ LẤY TÀI KHOẢN + MẬT KHẨU THEO user_id
    public function getCredentials($user_id) {
        $id = (int)$user_id;

        $query = "SELECT tai_khoan, mat_khau 
                  FROM taikhoan 
                  WHERE user_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc(); // ['tai_khoan' => '...', 'mat_khau' => '...']
    }

// KIỂM TRA ĐĂNG NHẬP: tai_khoan + mat_khau
    public function dangNhap($tai_khoan, $mat_khau_nhap) {
        $query = "SELECT *
                  FROM taikhoan 
                  WHERE tai_khoan = ? and mat_khau= ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $tai_khoan, $mat_khau_nhap);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return false;
        } 
        return true;

    }
}
?>