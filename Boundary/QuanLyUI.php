<?php
// Boundary/QuanLyUI.php
require_once __DIR__ . '/../Controller/QLNVController.php';

class QuanLyUI {
    private $controller;

    public function __construct() {
        $this->controller = new QLNVController();
    }

    public function hienThiDanhSach() {
        $id_quan_ly = $_SESSION['user_id'] ?? 1;
        $nhanVien = $this->controller->getNhanVienCuaQuanLy($id_quan_ly);
        ?>
        <div class="table-wrapper">
            <table class="employee-table">
                <thead>
                    <tr>
                        <th>Tên nhân viên</th><th>Email</th><th>Số điện thoại</th><th>Tên quán</th><th>Vai trò</th><th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($nhanVien)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:70px; color:#999;">
                                <i class="fa-solid fa-users-slash" style="font-size:60px; opacity:0.4;"></i><br><br>
                                <strong>Chưa có nhân viên nào</strong><br>
                                <small>Nhấn nút "Thêm nhân viên" để bắt đầu</small>
                            </td>
                        </tr>
                    <?php else: foreach ($nhanVien as $nv): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="avatar"><?= strtoupper(mb_substr($nv['ten'], 0, 2)) ?></div>
                                    <div class="user-text">
                                        <strong><?= htmlspecialchars($nv['ten']) ?></strong>
                                        <span>ID: <?= $nv['user_id'] ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><i class="fa-regular fa-envelope"></i> <?= htmlspecialchars($nv['email'] ?? 'Chưa có') ?></td>
                            <td><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($nv['sdt'] ?? 'Chưa có') ?></td>
                            <td>Quán chính</td>
                            <td><span class="role-badge">Nhân viên</span></td>
                            <td>
                                <a class="btn-action edit" href="javascript:void(0)"
                                   onclick="openEditModal(
                                       <?= $nv['user_id'] ?>,
                                       '<?= htmlspecialchars(addslashes($nv['ten']), ENT_QUOTES) ?>',
                                       '<?= htmlspecialchars(addslashes($nv['sdt'] ?? ''), ENT_QUOTES) ?>',
                                       '<?= htmlspecialchars(addslashes($nv['email'] ?? ''), ENT_QUOTES) ?>'
                                   )" title="Sửa">
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                <a class="btn-action delete" href="javascript:void(0)"
                                   onclick="confirmDelete(<?= $nv['user_id'] ?>, '<?= htmlspecialchars(addslashes($nv['ten']), ENT_QUOTES) ?>')"
                                   title="Xóa">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function xuLyThemNhanVien() {
        $data = [
            'ten'        => $_POST['ten'],
            'sdt'        => $_POST['sdt'],
            'email'      => $_POST['email'] ?? null,
            'tai_khoan'  => $_POST['tai_khoan'],
            'mat_khau'   => $_POST['mat_khau'],
            'id_quan_ly' => $_SESSION['user_id'] ?? 1
        ];
        return $this->controller->themNhanVien($data);
    }

    public function xuLySuaNhanVien() {
        $user_id = (int)$_POST['user_id'];
        $ten     = $_POST['ten'];
        $sdt     = $_POST['sdt'];
        $email   = $_POST['email'] ?? null;
        return $this->controller->suaNhanVien($user_id, $ten, $sdt, $email);
    }

    public function xuLyXoaNhanVien($user_id) {
        return $this->controller->xoaNhanVien($user_id);
    }
}