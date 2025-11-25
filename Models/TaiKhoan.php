<?php
require_once __DIR__ . '/../Repository/Database.php';
require_once __DIR__ . '/../models/HeThongSession.php'; 

class TaiKhoan {
    private $conn;
    private $sessionModel;

    public function __construct() {
        $this->conn = Database::connect();
        $this->sessionModel = new HeThongSession(); // dùng để lưu session vào DB
    }

    // Kiểm tra tài khoản đã tồn tại
    public function checkExists($tai_khoan) {
        $sql = "SELECT id_tai_khoan FROM taikhoan WHERE tai_khoan = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $tai_khoan);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // Tạo tài khoản mới
    public function create($tai_khoan, $mat_khau_hash, $user_id) {
        $sql = "INSERT INTO taikhoan (tai_khoan, mat_khau, user_id) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $tai_khoan, $mat_khau_hash, $user_id);
        return $stmt->execute();
    }

    // ===============================================
    // ĐĂNG NHẬP HOÀN CHỈNH (GHÉP TỪ CODE TRANG 1)
    // ===============================================
    public function dangNhap($tai_khoan, $mat_khau_nhap, $ten_quan) {
        // 1. Lấy user theo tài khoản
        $query = "SELECT tk.user_id, tk.mat_khau, u.ten, u.role, u.ten_quan
                  FROM taikhoan tk
                  JOIN user u ON tk.user_id = u.user_id
                  WHERE tk.tai_khoan = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $tai_khoan);
        $stmt->execute();
        $result = $stmt->get_result();

        // Tài khoản không tồn tại
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Tài khoản không tồn tại!'];
        }

        $row = $result->fetch_assoc();

        // 2. Kiểm tra mật khẩu (password_verify)
        if (!password_verify($mat_khau_nhap, $row['mat_khau'])) {
            return ['success' => false, 'message' => 'Sai mật khẩu!'];
        }

        // 3. Kiểm tra tên quán
        if ($ten_quan !== $row['ten_quan']) {
            return ['success' => false, 'message' => 'Sai tên quán!'];
        }

        $user_id = $row['user_id'];

        // 4. Lưu PHP SESSION
        session_start();
        $_SESSION['user_id']     = $user_id;
        $_SESSION['ten']         = $row['ten'];
        $_SESSION['role']        = $row['role'];
        $_SESSION['ten_quan']    = $row['ten_quan'];

        // 5. Lưu vào bảng hethongsession
        $session_id = $this->sessionModel->luuSession($user_id);
        $_SESSION['session_id'] = $session_id;

        return ['success' => true, 'message' => 'Đăng nhập thành công'];
    }
}
?>
