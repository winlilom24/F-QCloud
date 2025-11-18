<?php
require_once "controllers/HoaDonController.php";

class HoaDonUI {
    private $controller;

    public function __construct() {
        $this->controller = new HoaDonController();
    }

    public function hienThiForm() {
        $id_order = '1';
        $result = $this->controller->taoHoaDon($id_order,'Test_thu_thoi');

        if($result){
            echo 'thanh Cong';
        } else {
            echo 'that bai';
        }
    }
}
?>