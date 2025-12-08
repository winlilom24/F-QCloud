<?php
// controllers/OrderController.php
require_once __DIR__ . "/../Models/Order.php";
require_once __DIR__ . "/../Models/Ban.php";

class OrderController {
    private $orderModel;

    public function __construct() {
        $this->orderModel = new Order();
        $this->banModel = new Ban();
    }

    public function getChiTiet($id_order) {
        $orderInfo = $this->orderModel->getOrderInfo($id_order);
        if (!$orderInfo) return null;

        $orderDetails = $this->orderModel->getOrderDetails($id_order);

        return [
            'order' => $orderInfo,
            'chitiet' => $orderDetails['chitiet'],
            'tong_tien' => $orderDetails['tong_tien']
        ];
    }
    
    // id_order`, `id_ban`, `id_nhan_vien`, `thoi_gian`, `trang_thai, ban, suc_chua, ten_nhan_vien
    public function getOrderInfo($id_order) {
        return $this->orderModel->getOrderInfo($id_order);
    }

    // chitiet[id_order, ten_mon, so_luong, thanh_tien, gia], tong_tien
    public function getOrderDetails($id_order) {
        return $this->orderModel->getOrderDetails($id_order);
    }

    public function getOrderByBan($id_ban) {
        return $this->orderModel->getOrderByBan($id_ban);
    }

    public function taoOrder($id_ban, $id_nhan_vien) {
        // Bàn "Mang về" (id_ban = 0) luôn có thể tạo order
        if ($id_ban > 0 && !$this->banModel->isBanTrong($id_ban)) {
            return ['success' => false, 'message' => 'Bàn đang có khách hoặc không tồn tại!'];
        }
        return $this->orderModel->taoOrder($id_ban, $id_nhan_vien);
    }

    public function themMon($id_order, $id_mon, $so_luong) {
        return $this->orderModel->themMonVaoOrder($id_order, $id_mon, $so_luong);
    }

    public function capNhatOrder($id_order, $mon) {
        error_log("OrderController::capNhatOrder called with id_order=$id_order, mon=" . json_encode($mon));
        $result = $this->orderModel->capNhatOrder($id_order, $mon);
        error_log("OrderController::capNhatOrder result: " . json_encode($result));
        return $result;
    }

    public function capNhatSoLuongMon($id_order, $id_mon, $so_luong) {
        return $this->orderModel->capNhatSoLuongMon($id_order, $id_mon, $so_luong);
    }

    public function xoaOrder($id_order) {
        return $this->orderModel->xoaOrder($id_order);
    }   
}
?>