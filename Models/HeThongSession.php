<?php
// models/HeThongSession.php
require_once "Repository/Database.php";

class HeThongSession {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    /**
     * LƯU SESSION KHI ĐĂNG NHẬP
     * @param int $user_id
     * @return int session_id (ID vừa tạo)
     */

    public function luuSession($user_id) {
        $user_id = (int)$user_id;

        $query = "INSERT INTO hethongsession (user_id, login_time) 
                  VALUES (?, NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        return $this->conn->insert_id; // Trả về ID session
    }

    /**
     * CẬP NHẬT LOGOUT
     * @param int $user_id
     * @return bool
     */
    public function destroySession($user_id) {
        $user_id = (int)$user_id;

        $query = "UPDATE hethongsession 
                  SET logout_time = NOW() 
                  WHERE user_id = ? AND logout_time IS NULL 
                  ORDER BY login_time DESC 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // 2. XÓA TOÀN BỘ $_SESSION
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Xóa từng key
        unset(
            $_SESSION['user_id'],
            $_SESSION['ten'],
            $_SESSION['role'],
            $_SESSION['ten_quan'],
            $_SESSION['session_id']
        );

        return true;
    }
}
?>