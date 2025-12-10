<?php
// Views/QLDoanhThu.php
session_start();
require_once __DIR__ . '/../../Boundary/QuanLyDoanhThuUI.php';

$_SESSION['user_id'] = $_SESSION['user_id'] ?? 1;

// Lấy thông tin quản lý hiện tại
require_once __DIR__ . '/../../Controller/QLNVController.php';
$quanLyController = new QLNVController();
$quanLyInfo = $quanLyController->getUserInfo($_SESSION['user_id']);

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
    } elseif ($_POST['action'] === 'change_password') {
        // Sử dụng QLNVController để ResetPass
        $quanLyController = new QLNVController();
        $result = $quanLyController->doiMatKhau($_SESSION['user_id'] ?? 1, $_POST['old_password'] ?? '', $_POST['new_password'] ?? '');
        echo json_encode($result);
    } elseif ($_POST['action'] === 'tao_doanh_thu_tu_hoa_don') {
        $id_hoa_don = isset($_POST['id_hoa_don']) ? (int)$_POST['id_hoa_don'] : 0;
        if ($id_hoa_don > 0) {
            $result = $ui->taoDoanhThuTuHoaDon($id_hoa_don);
        } else {
            $result = $ui->taoDoanhThuTuTatCaHoaDon();
        }
        echo json_encode($result);
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

// XỬ LÝ API LẤY THỐNG KÊ THEO NGÀY
if (isset($_GET['api']) && $_GET['api'] === 'getRevenueStatsForDate') {
    ob_clean();
    header('Content-Type: application/json');

    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

    try {
        $stats = $ui->controller->getRevenueStatsForDate($date);
        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
    exit;
}

// XỬ LÝ API LẤY CHI TIẾT DOANH THU
if (isset($_GET['api']) && $_GET['api'] === 'getRevenueDetail') {
    ob_clean();
    header('Content-Type: application/json');

    $id_doanh_thu = isset($_GET['id_doanh_thu']) ? (int)$_GET['id_doanh_thu'] : null;
    $id_hoa_don = isset($_GET['id_hoa_don']) ? (int)$_GET['id_hoa_don'] : null;

    if (!$id_doanh_thu || !$id_hoa_don) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin ID doanh thu hoặc hóa đơn']);
        exit;
    }

    try {
        // Lấy thông tin doanh thu
        $doanhThu = $ui->controller->getDoanhThuById($id_doanh_thu);
        if (!$doanhThu) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy doanh thu']);
            exit;
        }

        // Lấy chi tiết đơn hàng từ hóa đơn
        $chiTietDonHang = $ui->controller->getChiTietDonHangByHoaDon($id_hoa_don);

        echo json_encode([
            'success' => true,
            'doanhThu' => $doanhThu,
            'chiTietDonHang' => $chiTietDonHang
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý doanh thu - FQCloud</title>
    <link rel="stylesheet" href="../../Public/css/QLDoanhThu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* OVERRIDE CSS để đảm bảo stat-card nhỏ gọn */
        .stats-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) !important;
            gap: 12px !important;
            margin-bottom: 20px !important;
        }

        .stat-card {
            background: white !important;
            border-radius: 10px !important;
            padding: 12px !important;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06) !important;
            border: 1px solid rgba(15, 23, 42, 0.06) !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            transition: all 0.3s ease !important;
            position: relative !important;
            overflow: hidden !important;
            cursor: pointer !important;
        }

        .stat-card .stat-icon {
            width: 36px !important;
            height: 36px !important;
            border-radius: 6px !important;
            background: linear-gradient(135deg, #1f6fff, #3b82f6) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: white !important;
            font-size: 14px !important;
            flex-shrink: 0 !important;
        }

        .stat-card .stat-content {
            flex: 1 !important;
        }

        .stat-card .stat-value {
            font-size: 16px !important;
            font-weight: 600 !important;
            color: #1f2a37 !important;
            margin-bottom: 2px !important;
            line-height: 1.2 !important;
            letter-spacing: -0.2px !important;
            text-align: left !important;
        }

        .stat-card .stat-label {
            font-size: 10px !important;
            color: #64748b !important;
            font-weight: 400 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.1px !important;
        }

        /* Responsive override */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)) !important;
                gap: 8px !important;
            }

            .stat-card {
                padding: 8px !important;
            }

            .stat-card .stat-icon {
                width: 28px !important;
                height: 28px !important;
                font-size: 12px !important;
            }

            .stat-card .stat-value {
                font-size: 14px !important;
            }

            .stat-card .stat-label {
                font-size: 9px !important;
            }
        }

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
            <li><a href="QLNV.php"><i class="fa-solid fa-users"></i> Quản lý nhân viên</a></li>
            <li><a href="QLBan.php"><i class="fa-solid fa-table"></i> Quản lý bàn</a></li>
            <li><a href="QLMonAn.php"><i class="fa-solid fa-bowl-food"></i> Quản lý món ăn</a></li>
            <li><a href="PhieuBanGiao.php"><i class="fa-solid fa-handshake"></i> Phiếu bàn giao ca</a></li>
            <li><a href="BaoCaoCuoiNgay.php"><i class="fa-solid fa-file-lines"></i> Báo cáo cuối ngày</a></li>
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
                <button class="btn btn-print-page" onclick="printRevenueReport()" title="In báo cáo doanh thu">
                    <i class="fa-solid fa-print"></i> In báo cáo
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

<!-- MODAL XEM CHI TIẾT DOANH THU -->
<div id="revenueDetailModal" class="modal revenue-detail-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Chi tiết doanh thu</h3>
            <span class="close" onclick="closeRevenueDetailModal()">×</span>
        </div>
        <div class="modal-body" id="revenueDetailContent">
            <div class="loading">Đang tải...</div>
        </div>
    </div>
</div>

<!-- MODAL CHI TIẾT THỐNG KÊ -->
<div id="statsDetailModal" class="modal large-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="statsModalTitle">Chi tiết thống kê</h3>
            <span class="close" onclick="closeStatsModal()">×</span>
        </div>
        <div class="modal-body">
            <!-- Thông tin ngày đang xem -->
            <div id="currentDateInfo" class="date-info-banner" style="display: none; margin-bottom: 20px; padding: 12px 16px; background: #e3f2fd; border-radius: 8px; border-left: 4px solid #1f6fff;">
                <i class="fa-solid fa-calendar-day" style="color: #1f6fff; margin-right: 8px;"></i>
                <span id="currentDateText" style="font-weight: 500; color: #1565c0;"></span>
            </div>

            <div id="filterSection" style="display: none; margin-bottom: 20px;">
                <div class="filter-controls">
                    <label for="dateFrom">Từ ngày:</label>
                    <input type="date" id="dateFrom" onchange="filterRevenueByDate()">

                    <label for="dateTo">Đến ngày:</label>
                    <input type="date" id="dateTo" onchange="filterRevenueByDate()">

                    <button class="btn btn-secondary" onclick="resetDateFilter()">Đặt lại</button>
                </div>
            </div>

            <div id="statsDetailContent">
                <div class="loading" style="text-align: center; padding: 40px;">
                    <i class="fa-solid fa-spinner fa-spin" style="font-size: 24px; color: #007bff;"></i>
                    <p style="margin-top: 12px; color: #666;">Đang tải dữ liệu...</p>
                </div>
            </div>

            <!-- Stats Summary -->
            <div id="statsSummary" style="display: none; margin-bottom: 24px;">
                <!-- Summary sẽ được render bởi JavaScript -->
            </div>

            <!-- Detail Table -->
            <div id="detailTableContainer" style="display: none;">
                <div class="table-wrapper">
                    <table class="revenue-table">
                        <thead id="detailTableHead">
                            <!-- Table headers sẽ được render bởi JavaScript -->
                        </thead>
                        <tbody id="detailTableBody">
                            <!-- Table body sẽ được render bởi JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button id="printBtn" class="btn btn-primary" onclick="printRevenueDetails()" style="display: none;">
                <i class="fa-solid fa-print"></i> In chi tiết
            </button>
            <button class="btn btn-secondary" onclick="closeStatsModal()">Đóng</button>
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
<script src="../../Public/js/QLDoanhThu.js"></script>
</body>
</html>
