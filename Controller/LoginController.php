<?php
// Controller/LoginController.php
require_once "Models/TaiKhoan.php";

class LoginController {
    private $model;

    public function __construct() {
        $this->model = new TaiKhoan();
    }

    
    public function check($tai_khoan, $mat_khau) {        
        $$result = $this->model->dangNhap($tai_khoan, $mat_khau);
        if ($$result['message'] === 'success') {
            if($_SESSION['role'] == 'Quản lý'){

            } else {

            }
        } else {
            return $$result;
            //quay lại trang đăng nhập với get = $$result
        }
    }
}
?>