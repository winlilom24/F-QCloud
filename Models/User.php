<?php
// models/User.php
require_once "../Repository/Database.php";

class User {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    // Lấy danh sách nhân viên của quản lý
    public function getNhanVienByQuanLy($id_quan_ly) {
        $id_quan_ly = (int)$id_quan_ly;
        // $query = "SELECT user_id, ten, sdt, email, role 
        //           FROM user 
        //           WHERE id_quan_ly = ? AND role = 'Nhân viên'";

        $query = "SELECT tk.user_id, tk.mat_khau, u.ten, u.role, u.ten_quan, u.sdt, u.email
                  FROM taikhoan tk
                  JOIN user u ON tk.user_id = u.user_id
                  WHERE id_quan_ly = ? AND role = 'Nhân viên'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_quan_ly);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    // Tìm user theo ID
    public function findById($user_id) {
        $user_id = (int)$user_id;
        $query = "SELECT * FROM user WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Tạo nhân viên + tài khoản đăng nhập
    public function create($ten, $sdt, $email, $tai_khoan, $mat_khau, $id_quan_ly) {
        $this->conn->begin_transaction();
        try {
            // 1. Thêm vào bảng user
            $query1 = "INSERT INTO user (ten, sdt, email, role, id_quan_ly) 
                       VALUES (?, ?, ?, 'Nhân viên', ?)";
            $stmt1 = $this->conn->prepare($query1);
            $stmt1->bind_param("sssi", $ten, $sdt, $email, $id_quan_ly);
            $stmt1->execute();
            $user_id = $this->conn->insert_id;

            // 2. Tạo tài khoản đăng nhập
            $hash = password_hash($mat_khau, PASSWORD_DEFAULT);
            $query2 = "INSERT INTO taikhoan (user_id, tai_khoan, mat_khau) VALUES (?, ?, ?)";
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bind_param("iss", $user_id, $tai_khoan, $hash);
            $stmt2->execute();

            $this->conn->commit();
            return ['success' => true, 'message' => 'Thêm nhân viên thành công!', 'user_id' => $user_id];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Lỗi: Tài khoản đã tồn tại hoặc dữ liệu không hợp lệ!'];
        }
    }

    // Cập nhật thông tin nhân viên
    public function update($user_id, $ten, $sdt, $email) {
        $user_id = (int)$user_id;
        $query = "UPDATE user SET ten = ?, sdt = ?, email = ? WHERE user_id = ? AND role = 'Nhân viên'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssi", $ten, $sdt, $email, $user_id);
        return $stmt->execute() && $stmt->affected_rows > 0;
    }

    // Xóa nhân viên + tài khoản
    public function delete($user_id) {
        $user_id = (int)$user_id;
        $this->conn->begin_transaction();
        try {
            // Xóa tài khoản trước (do FK)
            $q1 = "DELETE FROM taikhoan WHERE user_id = ?";
            $s1 = $this->conn->prepare($q1);
            $s1->bind_param("i", $user_id);
            $s1->execute();

            // Xóa user
            $q2 = "DELETE FROM user WHERE user_id = ? AND role = 'Nhân viên'";
            $s2 = $this->conn->prepare($q2);
            $s2->bind_param("i", $user_id);
            $s2->execute();

            $this->conn->commit();
            return $s2->affected_rows > 0;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
}
?>