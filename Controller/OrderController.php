<?php
// controllers/OrderController.php
require_once "models/Order.php";

class OrderController {
    private $orderModel;

    public function __construct() {
        $this->orderModel = new Order();
    }

    public function getChiTiet($id_order) {
        return $this->orderModel->getById($id_order);
    }
}
?>