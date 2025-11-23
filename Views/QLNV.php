<?php
// Views/QLNV.php
session_start();
require_once __DIR__ . '/../Boundary/QuanLyUI.php';

$_SESSION['user_id'] = $_SESSION['user_id'] ?? 1;
$ui = new QuanLyUI();

// XỬ LÝ AJAX (Thêm & Sửa)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    ob_clean();
    header('Content-Type: application/json');

    if ($_POST['action'] === 'add') {
        $result = $ui->xuLyThemNhanVien();
        echo json_encode($result);
    } elseif ($_POST['action'] === 'update') {
        $ok = $ui->xuLySuaNhanVien();
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Cập nhật thành công!' : 'Cập nhật thất bại!']);
    }
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
    <link rel="stylesheet" href="../Public/css/QLNV.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .modal.active { display: flex; }
        .modal-content {
            background: white; border-radius: 16px; width: 90%; max-width: 520px; box-shadow: 0 15px 40px rgba(0,0,0,0.25);
            animation: modalShow 0.3s ease-out;
        }
        @keyframes modalShow { from { transform: scale(0.85); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal-header {
            padding: 20px 25px; background: #007bff; color: white; border-radius: 16px 16px 0 0;
            display: flex; justify-content: space-between; align-items: center; font-size: 1.4rem;
        }
        .modal-body { padding: 30px; }
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
    </style>
</head>
<body>
<div class="wrapper">
    <aside class="sidebar">
        <div class="logo"><i class="fa-solid fa-cloud"></i> FQCloud</div>
        <ul class="menu">
            <li class="active"><a href="QLNV.php"><i class="fa-solid fa-users"></i> Quản lý nhân viên</a></li>
            <li><a href="#"><i class="fa-solid fa-table"></i> Quản lý bàn</a></li>
            <li><a href="#"><i class="fa-solid fa-bowl-food"></i> Quản lý món ăn</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="header">
            <h2>Danh sách nhân viên</h2>
            <div class="header-right">
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fa-solid fa-user-plus"></i> Thêm nhân viên
        </button>

        <div class="user-profile" onclick="toggleUserMenu(event)">
            <div class="user-avatar-circle">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="user-info">
                <div class="user-name">Admin</div>
                <div class="user-role">Quản trị viên</div>
            </div>
            <i class="fa-solid fa-caret-down arrow"></i>

            <div class="user-menu" id="userMenu">
                <a href="javascript:void(0)" onclick="openChangePasswordModal()">
                    <i class="fa-solid fa-key"></i> Đổi mật khẩu
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
                <div class="form-group">
                    <label>Họ và tên *</label>
                    <input type="text" name="ten" id="ten" required>
                </div>
                <div class="form-group">
                    <label>Số điện thoại *</label>
                    <input type="text" name="sdt" id="sdt" required>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../Public/js/QLNV.js"></script>
</body>
</html>