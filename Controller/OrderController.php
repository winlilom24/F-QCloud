<?php
// controllers/OrderController.php
require_once "models/Order.php";
require_once "models/Ban.php";

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

    public function taoOrder($id_ban, $id_nhan_vien) {
        if (!$this->banModel->isBanTrong($id_ban)) {
            return ['success' => false, 'message' => 'Bàn đang có khách hoặc không tồn tại!'];
        }
        return $this->model->taoOrder($id_ban, $id_nhan_vien);
    }

    public function themMon($id_order, $id_mon, $so_luong) {
        return $this->model->themMonVaoOrder($id_order, $id_mon, $so_luong);
    }

    public function capNhatOrder($id_order, $mon) {
        return $this->model->capNhatOrder($id_order, $mon);
    }

    public function xoaOrder($id_order) {
        return $this->model->xoaOrder($id_order);
    }   
}
?>