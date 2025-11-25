<?php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/TaiKhoan.php';

class RegisterController {
    public $error = '';
    public $success = '';

    private $userModel;
    private $tkModel;

    public function __construct() {
        $this->userModel = new User();
        $this->tkModel = new TaiKhoan();
    }

    public function register($post) {
        $ten = trim($post['ten']);
        $ten_quan = trim($post['ten_quan']);
        $sdt = trim($post['sdt']);
        $email = trim($post['email']);
        $tai_khoan = trim($post['tai_khoan']);
        $mat_khau = trim($post['mat_khau']);

        // Kiểm tra tài khoản tồn tại
        if ($this->tkModel->checkExists($tai_khoan)) {
            $this->error = "❌ Tài khoản đã tồn tại!";
            return;
        }

        // Kiểm tra thông tin hợp lệ cơ bản
        if (empty($ten) || empty($ten_quan) || empty($sdt) || empty($email) || empty($tai_khoan) || empty($mat_khau)) {
            $this->error = "❌ Vui lòng điền đầy đủ thông tin!";
            return;
        }

        // Tạo user + tài khoản chủ quán (role = Quản lý)
        $result = $this->userModel->createAccount($ten, $ten_quan, $sdt, $email, $tai_khoan, $mat_khau);

        if ($result['success']) {
            $this->success = "🎉 Đăng ký thành công! <a href='../Login/Form.php'>Đăng nhập</a>";
        } else {
            $this->error = "❌ " . $result['message'];
        }
    }
}
?>
