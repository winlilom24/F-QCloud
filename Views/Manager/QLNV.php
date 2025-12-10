<?php
// Views/QLNV.php
session_start();
require_once __DIR__ . '/../../Boundary/QuanLyUI.php';

$_SESSION['user_id'] = $_SESSION['user_id'] ?? 1;
$ui = new QuanLyUI();

// Lấy thông tin quản lý hiện tại
$quanLyInfo = $ui->getQuanLyInfo();

// XỬ LÝ AJAX (Thêm & Sửa & ResetPass)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    ob_clean();
    header('Content-Type: application/json');

    if ($_POST['action'] === 'add') {
        $result = $ui->xuLyThemNhanVien();
        echo json_encode($result);
    } elseif ($_POST['action'] === 'update') {
        $ok = $ui->xuLySuaNhanVien();
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Cập nhật thành công!' : 'Cập nhật thất bại!']);
    } elseif ($_POST['action'] === 'change_password') {
        $result = $ui->xuLyDoiMatKhau();
        echo json_encode($result);
    }
    exit;
}

// XỬ LÝ AJAX PHÂN TRANG
if (isset($_GET['ajax']) && $_GET['ajax'] === 'pagination') {
    ob_clean();
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $id_quan_ly = $_SESSION['user_id'] ?? 1;
    $result = $ui->getNhanVienCuaQuanLyPaginated($id_quan_ly, $page);
    $nhanVien = $result['data'];
    $pagination = $result['pagination'];

    // Trả về HTML cho table body và pagination
    ob_start();
    if (empty($nhanVien)): ?>
        <tr>
            <td colspan="6" class="empty-state">
                <i class="fa-solid fa-users-slash"></i>
                <p>Chưa có nhân viên nào</p>
                <small>Nhấn "Thêm nhân viên" để bắt đầu</small>
            </td>
        </tr>
    <?php else: foreach ($nhanVien as $nv):
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
    <?php endforeach; endif;

    $tableBody = ob_get_clean();

    ob_start();
    if ($pagination->getTotalPages() > 1): ?>
        <div class="pagination-wrapper">
            <?= $pagination->render('javascript:loadPage(') ?>
        </div>
    <?php endif;
    $paginationHtml = ob_get_clean();

    echo json_encode([
        'tableBody' => $tableBody,
        'pagination' => $paginationHtml,
        'currentPage' => $pagination->getCurrentPage(),
        'totalPages' => $pagination->getTotalPages()
    ]);
    exit;
}

// XỬ LÝ XÓA (dùng AJAX trong JS)
if (isset($_GET['delete'])) {
    $ok = $ui->xuLyXoaNhanVien((int)$_GET['delete']);
    echo "<script>
        alert('" . ($ok ? 'Xóa thành công!' : 'Xóa thất bại!') . "');
        window.location='QLNV.php';
    </script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách nhân viên - FQCloud</title>
    <link rel="stylesheet" href="../../Public/css/QLNV.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .modal.active { display: flex; }
        .modal-content {
            background: white; border-radius: 16px; width: 90%; max-width: 520px; box-shadow: 0 15px 40px rgba(0,0,0,0.25);
            animation: modalShow 0.3s ease-out; max-height: 90vh; display: flex; flex-direction: column;
        }
        @keyframes modalShow { from { transform: scale(0.85); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal-header {
            padding: 20px 25px; background: #007bff; color: white; border-radius: 16px 16px 0 0;
            display: flex; justify-content: space-between; align-items: center; font-size: 1.4rem;
        }
        .modal-body { padding: 30px; overflow-y: auto; flex: 1; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-group input, .form-group select {
            width: 100%; padding: 12px 14px; border: 1px solid #ddd; border-radius: 10px; font-size: 15px;
            transition: border 0.3s;
        }
        .form-group input:focus { border-color: #007bff; outline: none; }
        .btn { padding: 12px 28px; border: none; border-radius: 10px; cursor: pointer; font-size: 16px; font-weight: 600; }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; margin-left: 12px; }
        .close { font-size: 32px; cursor: pointer; opacity: 0.8; }
        .close:hover { opacity: 1; }

        /* Styles cho modal ResetPass */
        .password-modal .modal-content { max-width: 480px; }
        .password-input-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-input-container input {
            padding-right: 50px;
        }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 16px;
            padding: 5px;
            transition: color 0.3s;
        }
        .password-toggle:hover { color: #007bff; }
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
            display: none;
        }
        .password-strength.weak { color: #dc3545; }
        .password-strength.medium { color: #ffc107; }
        .password-strength.strong { color: #28a745; }
        .password-requirements {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            font-size: 13px;
        }
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }
        .password-requirements li {
            margin-bottom: 3px;
            color: #666;
        }
        .password-requirements li.valid { color: #28a745; }
    </style>
</head>
<body>
<div class="wrapper">
    <aside class="sidebar">
        <div class="logo"><i class="fa-solid fa-cloud"></i> FQCloud</div>
        <ul class="menu">
            <li class="active"><a href="QLNV.php"><i class="fa-solid fa-users"></i> Quản lý nhân viên</a></li>
            <li><a href="QLBan.php"><i class="fa-solid fa-table"></i> Quản lý bàn</a></li>
            <li><a href="QLMonAn.php"><i class="fa-solid fa-bowl-food"></i> Quản lý món ăn</a></li>
            <li><a href="PhieuBanGiao.php"><i class="fa-solid fa-handshake"></i> Phiếu bàn giao ca</a></li>
            <li><a href="BaoCaoCuoiNgay.php"><i class="fa-solid fa-file-lines"></i> Báo cáo cuối ngày</a></li>
            <li><a href="QLDoanhThu.php"><i class="fa-solid fa-chart-line"></i> Doanh thu</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="page-header">
            <div class="page-header__info">
                <p class="eyebrow">Nhân sự F-QCloud</p>
                <h1>Danh sách nhân viên</h1>
                <span>Tối ưu hóa quy trình quản lý nhân viên</span>
            </div>
            <div class="page-header__actions">
                <button class="icon-button" aria-label="Thông báo">
                    <i class="fa-regular fa-bell"></i>
                </button>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fa-solid fa-user-plus"></i> Thêm nhân viên
                </button>
                <div class="user-profile" onclick="toggleUserMenu(event)">
                    <div class="user-avatar-circle">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($quanLyInfo['ten'] ?? 'Admin') ?></div>
                        <div class="user-role">Quản lý</div>
                    </div>
                    <i class="fa-solid fa-caret-down arrow"></i>

                    <div class="user-menu" id="userMenu">
                        <a href="../../Views/Home/Page.php" class="employee-item">
                            <i class="fa-solid fa-users"></i> Nhân viên
                        </a>
                        <a href="javascript:void(0)" onclick="openChangePasswordModal()">
                            <i class="fa-solid fa-key"></i> ResetPass
                        </a>
                        <a href="logout.php" class="logout-item">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Đăng xuất
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div id="danhSachNhanVien">
            <?php $ui->hienThiDanhSach(); ?>
        </div>
    </main>
</div>

<!-- MODAL THÊM & SỬA NHÂN VIÊN -->
<div id="employeeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Thêm nhân viên mới</h3>
            <span class="close" onclick="closeModal()">×</span>
        </div>
        <div class="modal-body">
            <form id="employeeForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="user_id" id="userId">

                <div class="form-group">
                    <label>Tên đăng nhập *</label>
                    <input type="text" name="tai_khoan" id="tai_khoan" required>
                </div>
                <div class="form-group" id="passwordGroup">
                    <label>Mật khẩu *</label>
                    <input type="password" name="mat_khau" id="mat_khau" minlength="6" required>
                </div>
                <div class="form-group" id="passwordConfirmGroup">
                    <label>Nhập lại mật khẩu *</label>
                    <input type="password" name="mat_khau_confirm" id="mat_khau_confirm" minlength="6" required>
                </div>
                <div class="form-group">
                    <label>Tên quán *</label>
                    <input type="text" name="ten_quan" id="ten_quan" value="<?= htmlspecialchars($quanLyInfo['ten_quan'] ?? '') ?>" required readonly>
                    <small style="color: #6c757d; font-size: 12px;">Tự động xác nhận theo tài khoản quản lý</small>
                </div>
                <div class="form-group">
                    <label>Họ và tên *</label>
                    <input type="text" name="ten" id="ten" required>
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="sdt" id="sdt">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="email">
                </div>

                <div style="text-align: center; margin-top: 25px;">
                    <button type="submit" class="btn btn-primary">Lưu lại</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL ĐỔI MẬT KHẨU -->
<div id="passwordModal" class="modal password-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa-solid fa-key"></i> ResetPass</h3>
            <span class="close" onclick="closePasswordModal()">×</span>
        </div>
        <div class="modal-body">
            <form id="passwordForm">
                <input type="hidden" name="action" value="change_password">

                <div class="form-group">
                    <label>Mật khẩu hiện tại *</label>
                    <div class="password-input-container">
                        <input type="password" name="old_password" id="old_password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('old_password')">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Mật khẩu mới *</label>
                    <div class="password-input-container">
                        <input type="password" name="new_password" id="new_password" minlength="6" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div id="passwordStrength" class="password-strength"></div>
                </div>

                <div class="form-group">
                    <label>Nhập lại mật khẩu mới *</label>
                    <div class="password-input-container">
                        <input type="password" name="confirm_password" id="confirm_password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="password-requirements">
                    <strong>Yêu cầu mật khẩu:</strong>
                    <ul>
                        <li id="req-length">Ít nhất 6 ký tự</li>
                        <li id="req-match">Mật khẩu xác nhận phải khớp</li>
                        <li id="req-different">Khác với mật khẩu hiện tại</li>
                    </ul>
                </div>

                <div style="text-align: center; margin-top: 25px;">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fa-solid fa-save"></i> Cập nhật mật khẩu
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closePasswordModal()">
                        <i class="fa-solid fa-times"></i> Hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../Public/js/QLNV.js"></script>
</body>
</html>