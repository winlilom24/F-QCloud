<?php
// models/Order.php
require_once "Repository/Database.php";

class Order {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    // 1. LẤY THÔNG TIN ĐƠN HÀNG (bàn, nhân viên, thời gian...)
    public function getOrderInfo($id_order) {
        $id = (int)$id_order;

        $query = "SELECT o.*, b.suc_chua, u.ten AS ten_nhanvien 
                  FROM `order` o
                  LEFT JOIN ban b ON o.id_ban = b.id_ban
                  LEFT JOIN user u ON o.id_nhan_vien = u.user_id
                  WHERE o.id_order = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc(); // Trả về 1 dòng hoặc null
    }

    // 2. LẤY CHI TIẾT MÓN ĂN + TÍNH TỔNG TIỀN
    public function getOrderDetails($id_order) {
        $id = (int)$id_order;

        $query = "SELECT ct.id_mon, ct.so_luong, m.ten_mon, m.gia_tien AS gia
                  FROM chitietorder ct
                  JOIN monan m ON ct.id_mon = m.id_mon
                  WHERE ct.id_order = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $chitiet = [];
        $tong_tien = 0;

        while ($row = $result->fetch_assoc()) {
            $thanh_tien = $row['gia'] * $row['so_luong'];
            $row['thanh_tien'] = $thanh_tien;
            $chitiet[] = $row;
            $tong_tien += $thanh_tien;
        }

        return [
            'chitiet' => $chitiet,
            'tong_tien' => $tong_tien
        ];
    }

    // Tạo order mới
    public function taoOrder($id_ban, $id_nhan_vien) {
        $this->conn->begin_transaction();
        try {
            // 1. Tạo order
            $query = "INSERT INTO `order` (id_ban, id_nhan_vien, trang_thai) VALUES (?, ?, 'Đang xử lý')";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $id_ban, $id_nhan_vien);
            $stmt->execute();
            $id_order = $this->conn->insert_id;

            // 2. Cập nhật bàn thành Đang phục vụ
            $query2 = "UPDATE ban SET trang_thai = 'Đang phục vụ' WHERE id_ban = ?";
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bind_param("i", $id_ban);
            $stmt2->execute();

            $this->conn->commit();
            return ['success' => true, 'id_order' => $id_order];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Lỗi tạo order!'];
        }
    }

    // Thêm món vào order
    public function themMonVaoOrder($id_order, $id_mon, $so_luong = 1) {
        $query = "INSERT INTO chitietorder (id_order, id_mon, so_luong) VALUES (?, ?, ?)
                  ON DUPLICATE KEY UPDATE so_luong = so_luong + VALUES(so_luong)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $id_order, $id_mon, $so_luong);
        return $stmt->execute();
    }

    public function capNhatOrder($id_order, $danh_sach_mon) {
    $this->conn->begin_transaction();
    try {
        // Xóa hết chi tiết cũ
        $query_del = "DELETE FROM chitietorder WHERE id_order = ?";
        $stmt_del = $this->conn->prepare($query_del);
        $stmt_del->bind_param("i", $id_order);
        $stmt_del->execute();

        // Thêm lại các món mới (chỉ những món có số lượng > 0)
        $query_add = "INSERT INTO chitietorder (id_order, id_mon, so_luong) VALUES (?, ?, ?)";
        $stmt_add = $this->conn->prepare($query_add);

        foreach ($danh_sach_mon as $id_mon => $so_luong) {
            $so_luong = (int)$so_luong;
            if ($so_luong > 0) {
                $stmt_add->bind_param("iii", $id_order, $id_mon, $so_luong);
                $stmt_add->execute();
            }
        }

        $this->conn->commit();
        return ['success' => true, 'message' => 'Cập nhật order thành công!'];
    } catch (Exception $e) {
        $this->conn->rollback();
        return ['success' => false, 'message' => 'Lỗi cập nhật order!'];
    }
}

// === XÓA ORDER HOÀN TOÀN ===
public function xoaOrder($id_order) {
    $this->conn->begin_transaction();
    try {
        // 1. Lấy id_ban để cập nhật lại trạng thái bàn
        $query_ban = "SELECT id_ban FROM `order` WHERE id_order = ?";
        $stmt_ban = $this->conn->prepare($query_ban);
        $stmt_ban->bind_param("i", $id_order);
        $stmt_ban->execute();
        $result = $stmt_ban->get_result();
        $order = $result->fetch_assoc();

        if (!$order) {
            return ['success' => false, 'message' => 'Order không tồn tại!'];
        }

        // 2. Xóa chi tiết order
        $query1 = "DELETE FROM chitietorder WHERE id_order = ?";
        $stmt1 = $this->conn->prepare($query1);
        $stmt1->bind_param("i", $id_order);
        $stmt1->execute();

        // 3. Xóa order
        $query2 = "DELETE FROM `order` WHERE id_order = ?";
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->bind_param("i", $id_order);
        $stmt2->execute();

        // 4. Cập nhật bàn về Trống
        $query3 = "UPDATE ban SET trang_thai = 'Trống' WHERE id_ban = ?";
        $stmt3 = $this->conn->prepare($query3);
        $stmt3->bind_param("i", $order['id_ban']);
        $stmt3->execute();

        $this->conn->commit();
        return ['success' => true, 'message' => 'Xóa order và giải phóng bàn thành công!'];
    } catch (Exception $e) {
        $this->conn->rollback();
        return ['success' => false, 'message' => 'Lỗi xóa order!'];
    }
}
}
?>