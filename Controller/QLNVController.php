<?php
// controllers/QLNVController.php
require_once __DIR__ . '/../Models/User.php';

class QLNVController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function getNhanVienCuaQuanLy($id_quan_ly) {
        return $this->userModel->getNhanVienByQuanLy($id_quan_ly);
    }

    public function themNhanVien($data) {
        // Validate dữ liệu đầu vào
        require_once __DIR__ . '/../Utils/Validator.php';
        $rs1 = Validator::validateSoDienThoai($data['sdt']);
    
        if (!$rs1['success']) {
            // Trả về lỗi validation
            return ['success' => false, 'message' => $rs1['message']];;
        }

        $rs2 = Validator::validateTen($data['ten']);
    
        if (!$rs2['success']) {
            // Trả về lỗi validation
            return ['success' => false, 'message' => $rs2['message']];;
        }

        return $this->userModel->createNhanVien(
            $data['id_quan_ly'],
            $data['ten'],
            $data['sdt'],
            $data['email'],
            $data['tai_khoan'],
            $data['mat_khau']
        );
    }

    public function suaNhanVien($user_id, $ten, $sdt, $email) {
        return $this->userModel->update($user_id, $ten, $sdt, $email);
    }

    public function xoaNhanVien($user_id) {
        return $this->userModel->delete($user_id);
    }

    public function getById($user_id) {
        return $this->userModel->getUserById($user_id);
    }
}
?>