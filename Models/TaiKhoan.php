<?php
// models/TaiKhoan.php
require_once "Repository/Database.php";

class TaiKhoan {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

// KIỂM TRA ĐĂNG NHẬP: tai_khoan + mat_khau
    public function dangNhap($tai_khoan, $mat_khau_nhap) {
        // 1. TÌM USER THEO TÀI KHOẢN
        $query = "SELECT tk.user_id, tk.mat_khau, u.ten, u.role, u.ten_quan
                  FROM taikhoan tk
                  JOIN user u ON tk.user_id = u.user_id
                  WHERE tk.tai_khoan = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $tai_khoan);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return false; // Không tồn tại tài khoản
        }

        $row = $result->fetch_assoc();

        // 2. KIỂM TRA MẬT KHẨU (DÙNG BĂM)
        if (!password_verify($mat_khau_nhap, $row['mat_khau'])) {
            return false; // Mật khẩu sai
        }

        $user_id = $row['user_id'];

        // 3. LƯU VÀO PHP SESSION
        session_start();
        $_SESSION['user_id']     = $user_id;
        $_SESSION['ten']         = $row['ten'];
        $_SESSION['role']        = $row['role'];
        $_SESSION['ten_quan']    = $row['ten_quan'];

        // 4. LƯU VÀO BẢNG hethongsession
        $session_id = $this->sessionModel->luuSession($user_id);
        $_SESSION['session_id'] = $session_id;

        // 5. TRẢ VỀ TRUE CHO CONTROLLER
        return true;
    }
}
?>