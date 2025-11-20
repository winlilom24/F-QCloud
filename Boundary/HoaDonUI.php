<?php
require_once "controllers/HoaDonController.php";

class HoaDonUI {
    private $controller;

    public function __construct() {
        $this->controller = new HoaDonController();
    }

}
?>