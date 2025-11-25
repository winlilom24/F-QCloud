<?php
// models/HoaDon.php
require_once "Repository/Database.php";

class HoaDon {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    // CHỈ NHẬN TỔNG TIỀN → LƯU HÓA ĐƠN + DOANH THU
    public function create($id_order, $tong_tien, $ghi_chu = null) {
        $id_order = (int)$id_order;
        $tong_tien = (float)$tong_tien;

        $this->conn->autocommit(false);

        try {
            // 1. TẠO HÓA ĐƠN
            $query = "INSERT INTO hoadon (id_order, thoi_gian, trang_thai) 
                      VALUES (?, NOW(), 'Đã thanh toán')";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id_order);
            $stmt->execute();
            $id_hoa_don = $this->conn->insert_id;

            // 2. TẠO DOANH THU
            $query = "INSERT INTO doanhthu (id_hoa_don, tong_tien, ngay_tinh, ghi_chu) 
                      VALUES (?, ?, CURDATE(), ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ids", $id_hoa_don, $tong_tien, $ghi_chu);
            $stmt->execute();

            // 3. CẬP NHẬT TRẠNG THÁI ORDER
            $stmt = $this->conn->prepare("UPDATE `order` SET trang_thai = 'Đã thanh toán' WHERE id_order = ?");
            $stmt->bind_param("i", $id_order);
            $stmt->execute();

             // 4. GIẢI PHÓNG BÀN (nếu có)
            $stmt = $this->conn->prepare("UPDATE ban b JOIN `order` o ON b.id_ban = o.id_ban SET b.trang_thai = 'Trống' WHERE o.id_order = ?");
            $stmt->bind_param("i", $id_order);
            $stmt->execute();
            
            $this->conn->commit();

           return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
}
?>