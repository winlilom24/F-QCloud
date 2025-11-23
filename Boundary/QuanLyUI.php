<?php
// ui/QuanLyUI.php
require_once "Controller/QLNVController.php";

class QuanLyUI {
    private $controller;

    public function __construct() {
        $this->controller = new QLNVController();
    }

    public function hienThiDanhSachNhanVien() {
        $id_quan_ly = $_SESSION['user_id'] ?? 1;
        $nhanvien = $this->controller->getNhanVienCuaQuanLy($id_quan_ly);

        if (empty($nhanvien)) {
            echo '<div class="alert alert-info">Chưa có nhân viên nào trực thuộc.</div>';
            return;
        }

        echo '<table class="table table-bordered table-hover">
                <thead class="table-primary text-center">
                    <tr>
                        <th>ID</th><th>Họ tên</th><th>SĐT</th><th>Email</th><th>Hành động</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($nhanvien as $nv) {
            echo '<tr>
                    <td class="text-center">' . $nv['user_id'] . '</td>
                    <td>' . htmlspecialchars($nv['ten']) . '</td>
                    <td>' . htmlspecialchars($nv['sdt'] ?? '-') . '</td>
                    <td>' . htmlspecialchars($nv['email'] ?? '-') . '</td>
                    <td class="text-center">
                        <a href="?action=edit&id=' . $nv['user_id'] . '" class="btn btn-warning btn-sm">Sửa</a>
                        <a href="?action=delete&id=' . $nv['user_id'] . '" 
                           onclick="return confirm(\'Xóa nhân viên này?\')" 
                           class="btn btn-danger btn-sm">Xóa</a>
                    </td>
                  </tr>';
        }
        echo '</tbody></table>';
    }

    public function showAddForm() {
        $id_quan_ly = $_SESSION['user_id'] ?? 1;
        echo '<div class="card">
                <div class="card-header bg-success text-white"><h4>Thêm nhân viên mới</h4></div>
                <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id_quan_ly" value="' . $id_quan_ly . '">
                    <div class="mb-3"><label>Họ tên</label><input name="ten" class="form-control" required></div>
                    <div class="mb-3"><label>SĐT</label><input name="sdt" class="form-control" required></div>
                    <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control"></div>
                    <div class="mb-3"><label>Tài khoản đăng nhập</label><input name="tai_khoan" class="form-control" required></div>
                    <div class="mb-3"><label>Mật khẩu</label><input type="password" name="mat_khau" class="form-control" required minlength="6"></div>
                    <button type="submit" class="btn btn-success">Thêm nhân viên</button>
                    <a href="quanly-nhanvien.php" class="btn btn-secondary">Hủy</a>
                </form>
                </div>
              </div>';
    }

    public function showEditForm($user_id) {
        $nv = $this->controller->getById($user_id);
        if (!$nv || $nv['role'] !== 'Nhân viên') {
            echo '<div class="alert alert-danger">Không tìm thấy nhân viên!</div>';
            return;
        }

        echo '<div class="card">
                <div class="card-header bg-warning"><h4>Sửa nhân viên</h4></div>
                <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" value="' . $nv['user_id'] . '">
                    <div class="mb-3"><label>Họ tên</label><input name="ten" class="form-control" value="' . htmlspecialchars($nv['ten']) . '" required></div>
                    <div class="mb-3"><label>SĐT</label><input name="sdt" class="form-control" value="' . htmlspecialchars($nv['sdt'] ?? '') . '" required></div>
                    <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="' . htmlspecialchars($nv['email'] ?? '') . '"></div>
                    <button type="submit" class="btn btn-success">Cập nhật</button>
                    <a href="quanly-nhanvien.php" class="btn btn-secondary">Hủy</a>
                </form>
                </div>
              </div>';
    }
}
?>