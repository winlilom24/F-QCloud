<?php
// controllers/QLNController.php
require_once "Models/User.php";

class QLNVController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    //lay (ten,sdt,email)
    public function getNhanVienCuaQuanLy($id_quan_ly) {
        return $this->userModel->getByQuanLy($id_quan_ly);
    }
}
?>