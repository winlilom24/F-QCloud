<?php
require_once __DIR__ . '/../Models/TaiKhoan.php';

class LoginController {
    private $model;
    public $error = '';

    public function __construct() {
        $this->model = new TaiKhoan();
    }

    public function login($post) {
        $tai_khoan = $post['tai_khoan'];
        $mat_khau  = $post['mat_khau'];
        $ten_quan  = $post['ten_quan'];

        // Gọi đúng số tham số với hàm dangNhap()
        $result = $this->model->dangNhap($tai_khoan, $mat_khau, $ten_quan);

        if (!$result['success']) {
            $this->error = "❌ " . $result['message'];
            return;
        }

        // Nếu thành công, redirect sang TrangChuUI.php
        header("Location: ../../Boundary/TrangChuUI.php");
        exit;
    }
}
?>
