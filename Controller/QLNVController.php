<?php
// controllers/QLNVController.php
require_once "../Models/User.php";

class QLNVController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function getNhanVienCuaQuanLy($id_quan_ly) {
        return $this->userModel->getNhanVienByQuanLy($id_quan_ly);
    }

    public function themNhanVien($data) {
        return $this->userModel->create(
            $data['ten'],
            $data['sdt'],
            $data['email'],
            $data['tai_khoan'],
            $data['mat_khau'],
            $data['id_quan_ly']
        );
    }

    public function suaNhanVien($user_id, $ten, $sdt, $email) {
        return $this->userModel->update($user_id, $ten, $sdt, $email);
    }

    public function xoaNhanVien($user_id) {
        return $this->userModel->delete($user_id);
    }

    public function getById($user_id) {
        return $this->userModel->findById($user_id);
    }
}
?>