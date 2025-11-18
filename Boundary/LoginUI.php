<?php
require_once "Controller/LoginController.php";

class LoginUI {
    private $loginController;

    public function __construct() {
        $this->loginController = new LoginController();
    }

    public function hienForm(){
        echo '';
    }
    public function dangNhap() {
        $tai_khoan = 'admin';
        $mat_khau = '123456';

        // SỬA: Dùng $this->loginController
        if ($this->loginController->check($tai_khoan, $mat_khau)) {
            echo 'Đăng nhập thành công!';
        } else {
            echo 'Tài khoản hoặc mật khẩu không đúng!';
        }
    }
}
?>