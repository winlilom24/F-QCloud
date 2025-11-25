<?php
// controllers/HoaDonController.php
require_once "Models/HoaDon.php";
require_once "Controller/OrderController.php";

class HoaDonController {
    private $model;
    private $orderController;

    public function __construct() {
        $this->model = new HoaDon();
        $this->orderController = new OrderController();
    }

    public function taoHoaDon($id_order, $ghi_chu = null) {
        $id_order = (int)$id_order;

        // 1. LẤY DỮ LIỆU TỪ ORDER (TỔNG TIỀN + CHI TIẾT)
        $orderData = $this->orderController->getChiTiet($id_order);

        if (!$orderData) {
            return false;
        }

        $tong_tien = $orderData['tong_tien'];
        $chi_tiet = $orderData['chitiet']; // Dùng để hiển thị

        // 2. GỌI MODEL CHỈ ĐỂ LƯU
        $result = $this->model->create($id_order, $tong_tien, $ghi_chu);

        return $result;
    }
}
?>