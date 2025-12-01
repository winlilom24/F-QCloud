<?php
// Views/QLDoanhThu.php
session_start();
require_once __DIR__ . '/../Boundary/QuanLyDoanhThuUI.php';

$_SESSION['user_id'] = $_SESSION['user_id'] ?? 1;
$ui = new QuanLyDoanhThuUI();

// XỬ LÝ AJAX (Thêm & Sửa)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    ob_clean();
    header('Content-Type: application/json');

    if ($_POST['action'] === 'add') {
        $result = $ui->xuLyThemDoanhThu();
        echo json_encode($result);
    } elseif ($_POST['action'] === 'update') {
        $ok = $ui->xuLySuaDoanhThu();
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Cập nhật thành công!' : 'Cập nhật thất bại!']);
    } elseif ($_POST['action'] === 'get_unpaid_bills') {
        $unpaidBills = $ui->getHoaDonChuaCoDoanhThu();
        echo json_encode(['success' => true, 'data' => $unpaidBills]);
    } elseif ($_POST['action'] === 'get_revenue_details') {
        $filterType = $_POST['filter_type'] ?? 'all';
        $date = $_POST['date'] ?? null;
        $month = $_POST['month'] ?? null;
        $year = $_POST['year'] ?? null;
        $data = $ui->getDoanhThuTheoFilter($filterType, $date, $month, $year);
        echo json_encode(['success' => true, 'data' => $data]);
    } elseif ($_POST['action'] === 'get_paid_orders') {
        $data = $ui->getDonHangDaThanhToan();
        echo json_encode(['success' => true, 'data' => $data]);
    } elseif ($_POST['action'] === 'get_active_orders') {
        $data = $ui->getDonHangDangSuDung();
        echo json_encode(['success' => true, 'data' => $data]);
    } elseif ($_POST['action'] === 'get_invoice_details') {
        $tempController = new QLDoanhThuController();
        $data = $tempController->getAllDoanhThu();
        echo json_encode(['success' => true, 'data' => $data]);
    }
    exit;
}

// XỬ LÝ XÓA (dùng AJAX trong JS)
if (isset($_GET['delete'])) {
    $ok = $ui->xuLyXoaDoanhThu((int)$_GET['delete']);
    echo "<script>
        alert('" . ($ok ? 'Xóa thành công!' : 'Xóa thất bại!') . "');
        window.location='QLDoanhThu.php';
    </script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý doanh thu - FQCloud</title>
    <link rel="stylesheet" href="../Public/css/QLDoanhThu.css">
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
            <li><a href="QLNV.php"><i class="fa-solid fa-users"></i> Quản lý nhân viên</a></li>
            <li><a href="QLBan.php"><i class="fa-solid fa-table"></i> Quản lý bàn</a></li>
            <li><a href="QLMonAn.php"><i class="fa-solid fa-bowl-food"></i> Quản lý món ăn</a></li>
            <li class="active"><a href="QLDoanhThu.php"><i class="fa-solid fa-chart-line"></i> Doanh thu</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="page-header">
            <div class="page-header__info">
                <p class="eyebrow">Tài chính F-QCloud</p>
                <h1>Quản lý doanh thu</h1>
                <span>Tối ưu hóa quản lý doanh thu và báo cáo tài chính</span>
            </div>
            <div class="page-header__actions">
                <button class="icon-button" aria-label="Thông báo">
                    <i class="fa-regular fa-bell"></i>
                </button>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fa-solid fa-plus"></i> Thêm doanh thu
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
                        <a href="../index.php?action=logout" class="logout-item">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Đăng xuất
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div id="danhSachDoanhThu">
            <?php $ui->hienThiDanhSach(); ?>
        </div>
    </main>
</div>

<!-- MODAL THÊM & SỬA DOANH THU -->
<div id="revenueModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Thêm doanh thu mới</h3>
            <span class="close" onclick="closeModal()">×</span>
        </div>
        <div class="modal-body">
            <form id="revenueForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id_doanh_thu" id="revenueId">

                <div class="form-group">
                    <label>ID Hóa đơn *</label>
                    <input type="number" name="id_hoa_don" id="id_hoa_don" required>
                </div>
                <div class="form-group">
                    <label>Tổng tiền *</label>
                    <input type="number" name="tong_tien" id="tong_tien" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label>Ngày tính *</label>
                    <input type="date" name="ngay_tinh" id="ngay_tinh" required>
                </div>
                <div class="form-group">
                    <label>Ghi chú</label>
                    <input type="text" name="ghi_chu" id="ghi_chu" placeholder="Nhập ghi chú (không bắt buộc)">
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
<script src="../Public/js/QLDoanhThu.js"></script>
</body>
</html>
