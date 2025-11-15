<?php
// models/Order.php
require_once "Repository/Database.php";

class Order {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    public function getById($id_order) {
        $id = (int)$id_order;

        // 1. Lấy thông tin order
        $query = "SELECT o.*, b.suc_chua, u.ten AS ten_nhanvien 
                  FROM `order` o
                  LEFT JOIN ban b ON o.id_ban = b.id_ban
                  LEFT JOIN user u ON o.id_nhan_vien = u.user_id
                  WHERE o.id_order = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();

        if (!$order) return null;

        // 2. Lấy chi tiết món
        $query = "SELECT ct.id_mon, ct.so_luong, m.ten_mon, m.gia_tien as gia
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
            'order' => $order,
            'chitiet' => $chitiet,
            'tong_tien' => $tong_tien
        ];
    }
}
?>