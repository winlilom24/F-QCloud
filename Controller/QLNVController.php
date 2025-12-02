<?php
// controllers/QLNVController.php
require_once __DIR__ . '/../Models/User.php';

class QLNVController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function getUserInfo($user_id) {
        return $this->userModel->getUserById($user_id);
    }

    public function getNhanVienCuaQuanLy($id_quan_ly) {
        return $this->userModel->getNhanVienByQuanLy($id_quan_ly);
    }

    public function getNhanVienCuaQuanLyPaginated($id_quan_ly, $page = 1) {
        require_once __DIR__ . '/../Utils/Pagination.php';

        $totalItems = $this->userModel->countNhanVienByQuanLy($id_quan_ly);
        $pagination = new Pagination($totalItems, 5, $page);

        $employees = $this->userModel->getNhanVienByQuanLyPaginated(
            $id_quan_ly,
            $pagination->getOffset(),
            $pagination->getLimit()
        );

        return [
            'data' => $employees,
            'pagination' => $pagination
        ];
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

        // Validate email nếu có nhập
        if (!empty($data['email'])) {
            $rs3 = Validator::validateEmail($data['email']);

            if (!$rs3['success']) {
                return ['success' => false, 'message' => $rs3['message']];
            }
        }

        // Sử dụng tên quán từ form data (đã được tự động điền từ quản lý)
        $ten_quan = $data['ten_quan'] ?? null;

        return $this->userModel->createNhanVien(
            $data['id_quan_ly'],
            $data['ten'],
            $data['sdt'],
            $data['email'],
            $data['tai_khoan'],
            $data['mat_khau'],
            $ten_quan
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