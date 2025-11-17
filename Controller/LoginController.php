<?php
// Controller/LoginController.php
require_once "Models/TaiKhoan.php";

class LoginController {
    private $model;

    public function __construct() {
        $this->model = new TaiKhoan();
    }

    // SỬA: Dùng $this->model + truyền tham số
    public function check($tai_khoan, $mat_khau) {        
        return $this->model->dangNhap($tai_khoan, $mat_khau);
    }
}
?>