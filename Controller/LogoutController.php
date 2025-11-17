<?php
// controllers/LogoutController.php
require_once "Models/HeThongSession.php"; // Dùng để destroySession

class LogoutController {
    private $sessionModel;

    public function __construct() {
        $this->sessionModel = new HeThongSession();
    }

    /**
     * ĐĂNG XUẤT: XÓA SESSION + CẬP NHẬT CSDL
     * @param int $user_id
     * @return bool true nếu thành công, false nếu lỗi
     */
    public function dangXuat($user_id) {
        $user_id = (int)$user_id;

        if ($user_id <= 0) {
            return false;
        }

        // 1. CẬP NHẬT logout_time TRONG CSDL
        $result = $this->sessionModel->destroySession($user_id);

        // 2. XÓA HOÀN TOÀN $_SESSION (PHP)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_unset();     // Xóa tất cả biến
        session_destroy();   // Hủy session

        return $result; // true hoặc false
    }
}
?>