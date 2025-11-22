<?php
require_once "Controller/LoginController.php";

class LoginUI {
    private $loginController;

    public function __construct() {
        $this->loginController = new LoginController();
    }

    public function dangNhap() {
        if(isset($_POST['tai_khoan'])){
            $tai_khoan = $_POST['tai_khoan'];
            $mat_khau = $_POST['mat_khau'];
            $ten_quan = $_POST['ten_quan'];        
        }
    
        $tai_khoan = 'admin';
        $mat_khau = '123456';
        $ten_quan = 'Highland'
        $message = $this->loginController->check($tai_khoan, $mat_khau, $ten_quan);
    }
}
?>