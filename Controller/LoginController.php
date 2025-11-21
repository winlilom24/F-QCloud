<?php
// Controller/LoginController.php
require_once "Models/TaiKhoan.php";

class LoginController {
    private $model;

    public function __construct() {
        $this->model = new TaiKhoan();
    }

    
    public function check($tai_khoan, $mat_khau) {        
        $message = $this->model->dangNhap($tai_khoan, $mat_khau);
        if ($message === 'success') {
            return "Đăng nhập thành công";             //sau này chuyển sang trang chính
        } else {
            return $message;
            //quay lại trang đăng nhập với get = $message
        }
    }
}
?>