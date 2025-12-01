<?php
// Boundary/QuanLyUI.php
require_once __DIR__ . '/../Controller/QLNVController.php';

class QuanLyUI {
    private $controller;

    public function __construct() {
        $this->controller = new QLNVController();
    }

    public function hienThiDanhSach() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $id_quan_ly = $_SESSION['user_id'] ?? 1;
        $result = $this->controller->getNhanVienCuaQuanLyPaginated($id_quan_ly, $page);
        $nhanVien = $result['data'];
        $pagination = $result['pagination'];
        ?>
        <div class="employee-panel">
            <div class="employee-panel__head">
                <div>
                    <p class="eyebrow">Quản lý nhân viên</p>
                    <h2>Danh sách nhân viên</h2>
                    <span class="subtitle">Quản lý thông tin nhân viên trong hệ thống</span>
                </div>
            </div>
            <div class="table-wrapper">
                <table class="employee-table">
                    <thead>
                        <tr>
                            <th>Tên đăng nhập</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Vai trò</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($nhanVien)): ?>
                            <tr>
                                <td colspan="6" class="empty-state">
                                    <i class="fa-solid fa-users-slash"></i>
                                    <p>Chưa có nhân viên nào</p>
                                    <small>Nhấn "Thêm nhân viên" để bắt đầu</small>
                                </td>
                            </tr>
                        <?php else: foreach ($nhanVien as $nv): ?>
                            <?php
                                $avatarText = strtoupper(mb_substr($nv['tai_khoan'] ?? $nv['ten'], 0, 1));
                            ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="avatar"><?= $avatarText ?></div>
                                        <div class="user-text">
                                            <strong><?= htmlspecialchars($nv['tai_khoan'] ?? 'Chưa có') ?></strong>
                                            <span><?= htmlspecialchars($nv['ten'] ?? '') ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><i class="fa-regular fa-envelope"></i> <?= htmlspecialchars($nv['email'] ?? 'Chưa có') ?></td>
                                <td><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($nv['sdt'] ?? 'Chưa có') ?></td>
                                <td><span class="role-badge"><?= htmlspecialchars($nv['role'] ?? 'Nhân viên') ?></span></td>
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
            <?php if ($pagination->getTotalPages() > 1): ?>
            <div class="pagination-wrapper">
                <?= $pagination->render('?page=') ?>
            </div>
            <?php endif; ?>
        </div>

        <style>
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            padding: 20px 0;
        }

        .pagination {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .pagination-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            color: #495057;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            min-width: 40px;
            justify-content: center;
        }

        .pagination-btn:hover {
            background: #e9ecef;
            border-color: #adb5bd;
            color: #212529;
        }

        .pagination-btn.pagination-current {
            background: #007bff;
            border-color: #007bff;
            color: white;
            font-weight: 600;
        }

        .pagination-btn.pagination-prev,
        .pagination-btn.pagination-next {
            padding: 8px 16px;
        }

        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .pagination-dots {
            color: #6c757d;
            font-weight: 500;
            padding: 8px 4px;
        }
        </style>
        <?php
    }

    public function xuLyThemNhanVien() {
        $mat_khau = $_POST['mat_khau'];
        $mat_khau_confirm = $_POST['mat_khau_confirm'] ?? '';
        if ($mat_khau !== $mat_khau_confirm) {
            return ['success' => false, 'message' => 'Mật khẩu nhập lại không khớp!'];
        }

        $data = [
            'ten'        => $_POST['ten'],
            'sdt'        => $_POST['sdt'],
            'email'      => $_POST['email'] ?? null,
            'tai_khoan'  => $_POST['tai_khoan'],
            'mat_khau'   => $mat_khau,
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