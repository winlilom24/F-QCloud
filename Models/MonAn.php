<?php
// models/MonAn.php
require_once __DIR__ . '/../Repository/Database.php';

class MonAn {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    // Lấy tất cả món ăn
    public function getAll() {
        $query = "SELECT m.id_mon, m.ten_mon, m.gia_tien, m.mo_ta, m.trang_thai, m.id_nhom, n.ten_nhom
                  FROM monan m
                  LEFT JOIN nhommonan n ON m.id_nhom = n.id_nhom
                  ORDER BY m.ten_mon";
        $result = $this->conn->query($query);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    // Lấy 1 món theo ID
    public function getById($id) {
        $id = (int)$id;
        $query = "SELECT id_mon, ten_mon, gia_tien, mo_ta, trang_thai, id_nhom 
                  FROM monan WHERE id_mon = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Thêm món mới
    public function create($ten_mon, $gia_tien, $mo_ta = '', $trang_thai = 'Còn món', $id_nhom = null) {
        $query = "INSERT INTO monan (ten_mon, gia_tien, mo_ta, trang_thai, id_nhom) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sissi", $ten_mon, $gia_tien, $mo_ta, $trang_thai, $id_nhom);

        return $stmt->execute()
            ? ['success' => true, 'message' => 'Thêm món ăn thành công!']
            : ['success' => false, 'message' => 'Thêm món thất bại!'];
    }

    // Sửa món
    public function update($id_mon, $ten_mon, $gia_tien, $mo_ta, $trang_thai, $id_nhom) {
        $id_mon = (int)$id_mon;
        $query = "UPDATE monan 
                  SET ten_mon = ?, gia_tien = ?, mo_ta = ?, trang_thai = ?, id_nhom = ? 
                  WHERE id_mon = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sissii", $ten_mon, $gia_tien, $mo_ta, $trang_thai, $id_nhom, $id_mon);

        return $stmt->execute()
            ? ['success' => true, 'message' => 'Cập nhật món thành công!']
            : ['success' => false, 'message' => 'Cập nhật thất bại!'];
    }

    // Xóa món
    public function delete($id_mon) {
        $id_mon = (int)$id_mon;
        $query = "DELETE FROM monan WHERE id_mon = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_mon);

        return $stmt->execute() && $stmt->affected_rows > 0
            ? ['success' => true, 'message' => 'Xóa món thành công!']
            : ['success' => false, 'message' => 'Không thể xóa món này!'];
    }

    public function getCategories() {
        $query = "SELECT id_nhom, ten_nhom FROM nhommonan ORDER BY ten_nhom";
        $result = $this->conn->query($query);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
}
?>