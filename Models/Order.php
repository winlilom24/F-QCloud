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
}
?>