<?php
// ui/UserUI.php
require_once "Controller/QLNVController.php";

class QuanLyUI {
    private $controller;

    public function __construct() {
        $this->controller = new QLNVController();
    }

    public function hienThiDanhSachNhanVien() {
        $id = '1'; 
 
        $nhanvien = $this->controller->getNhanVienCuaQuanLy($id);

        if (empty($nhanvien)) {
            echo '<p class="text-muted fst-italic">Chưa có nhân viên trực thuộc.</p>';
        } else {
            echo '<div>';
            foreach ($nhanvien as $nv) {
                $ten_nv = htmlspecialchars($nv['ten']);
                echo '
                <div>
                    <div>
                        <div>
                            <strong>' . $ten_nv . '</strong><br>
                            <small class="text-muted">
                                <i class="fas fa-phone"></i> ' . $nv['sdt'] . ' | 
                                <i class="fas fa-envelope"></i> ' . $nv['email'] . '
                            </small>
                        </div>
                    </div>
                </div>';
            }
            echo '</div>';
        }
    }
}
?>