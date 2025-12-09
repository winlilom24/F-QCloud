<?php 
session_start(); 
require_once __DIR__ . '/../../Models/User.php';
require_once __DIR__ . '/../../Boundary/BanUI.php'; 
require_once __DIR__ . '/../../Boundary/MonAnUI.php'; 

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) { 
    header('Location: ../Login/Form.php'); 
    exit; 
}

require_once __DIR__ . '/../../Controller/HeThongController.php';
$controller = new HeThongController();

// Xử lý logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $controller->dangXuat($_SESSION['user_id'] ?? 0);
    $controller->loadPage();
    exit;
}

// // Load page mặc định
// $controller->loadPage();

$userModel = new User();
$userRecord = $userModel->getUserById($_SESSION['user_id']);

// Dữ liệu người dùng
$storeName = $userRecord['ten_quan'] ?? ($_SESSION['ten_quan'] ?? 'F-QCloud');
$userName  = $userRecord['ten']      ?? ($_SESSION['ten']      ?? 'Người dùng');
$userRole  = $userRecord['role']     ?? ($_SESSION['role']     ?? 'Quản lý');

$banUI = new BanUI();
$thongKeBan = $banUI->layThongKeBan();
$danhSachBanHtml = $banUI->hienThiDanhSachBanGrid();


$monAnUI = new MonAnUI();
$danhSachMon = $monAnUI->themMon();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>F-QCloud - Hệ thống quản lý nhà hàng</title>

    <!-- CSS -->
    <link rel=  "stylesheet" href="../../Public/css/Page.css?v=<?= time() ?>">
</head>

<body class="theme-blue">
<div id="app">

    <!-- ================== HEADER ================== -->
    <header class="top-bar">
        <div class="brand">
            <div class="logo">☁️</div>
            <h1>F-QCloud</h1>
        </div>

        <div class="header-controls">
            <div class="user-info-card">
                <div class="user-info">
                    <div class="store-name"><?= htmlspecialchars($storeName) ?></div>
                    <div class="user-details">
                        <span class="user-name"><?= htmlspecialchars($userName) ?></span>
                        <span class="user-separator">•</span>
                        <span class="user-role"><?= htmlspecialchars($userRole) ?></span>
                    </div>
                </div>
            </div>
            <button id="bellBtn" class="icon-btn">🔔</button>
            <button id="hamburgerMenu" class="hamburger-menu">☰</button>

            <div id="dropdownMenu" class="dropdown-menu">
    <div class="dropdown-content">
        
        <?php
        if ($userRole == "Quản lý"){
            echo '
            <a href="../Manager/QLNV.php" class="dropdown-item">
                <svg width="16" height="16" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="8" cy="4" r="3"></circle>
                <path d="M2 14c0-3 3-5 6-5s6 2 6 5"></path>
                </svg>
                <span>Quản lý</span>
            </a>
            ';
        }
        ?>

        <div class="dropdown-divider"></div>

        <a href="#" class="dropdown-item">
            <svg width="16" height="16" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="3" width="12" height="9" rx="2"></rect>
                <path d="M2 7h12"></path>
            </svg>
            <span>Báo cáo cuối ngày</span>
        </a>

        <a href="#" class="dropdown-item">
            <svg width="16" height="16" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="2" width="10" height="14" rx="2"></rect>
                <path d="M3 6h10"></path>
            </svg>
            <span>Phiếu bàn giao ca</span>
        </a>

        <a href="#" class="dropdown-item">
            <svg width="16" height="16" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 3H5a2 2 0 0 0-2 2v9h11V5a2 2 0 0 0-2-2z"></path>
                <path d="M9 9H7"></path>
            </svg>
            <span>Lập phiếu thu</span>
        </a>

        <div class="dropdown-divider"></div>

        <a href="#" class="dropdown-item">
            <svg width="16" height="16" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="5" r="2"></circle>
                <path d="M3 21v-6a4 4 0 0 1 4-4h4"></path>
            </svg>
            <span>Cài đặt chung</span>
        </a>

        <div class="dropdown-divider"></div>

        <a href="#" class="dropdown-item">
            <svg width="16" height="16" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h12v12H4z"></path>
                <path d="M4 9h12"></path>
            </svg>
            <span>Món có sẵn trong đơn</span>
        </a>

        <a href="#" class="dropdown-item">
            <svg width="16" height="16" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 4h12"></path>
                <path d="M6 9h12"></path>
                <path d="M6 14h12"></path>
            </svg>
            <span>Phím tắt</span>
        </a>

        <div class="dropdown-divider"></div>

        <a href="#" class="dropdown-item">
            <svg width="16" height="16" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 2l6 6-6 6"></path>
            </svg>
            <span>Đóng ca làm việc</span>
        </a>

        <a href="?action=logout" class="dropdown-item" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất không?');">
            <svg width="16" height="16" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <path d="M16 17l5-5-5-5"></path>
                <path d="M21 12H9"></path>
            </svg>
            <span>Đăng xuất</span>
        </a>

    </div>
</div>
        </div>
    </header>

    <!-- ================== MAIN ================== -->
    <main class="workspace">
        <div class="workspace-shell">

            <!-- TOP TAB BAR -->
            <div class="workspace-bar">

                <div class="workspace-tabs">
                    <button class="workspace-tab active" id="tab-ban">
                        <span class="tab-icon">🪑</span> <span>Phòng bàn</span>
                    </button>

                    <button class="workspace-tab" id="tab-menu">
                        <span class="tab-icon">🍽</span> <span>Thực đơn</span>
                    </button>
                </div>

                <div class="workspace-search">
                    <span class="search-icon">🔍</span>
                    <input type="text" placeholder="Tìm món (F3)" id="tableSearch">
                    <button id="searchBtn" class="search-clear">✕</button>
                </div>

                <div class="workspace-actions">
                    <button id="viewToggle" class="action-pill">▦</button>
                    <button class="action-pill">⚡</button>
                </div>

            </div>

            <!-- ================== BODY ================== -->
            <div class="workspace-body">

                <!-- LEFT PANEL -->
                <section class="board-panel">

                    <div class="panel-card">


<!-- Cập nhật số lượng trong bộ lọc trạng thái -->
<div class="status-pills">
    <label class="status-pill">
        <input type="radio" name="statusFilter" value="all" checked>
        <span>Tất cả <strong id="count-all">(<?php echo $thongKeBan['tong_ban']; ?>)</strong></span>
    </label>
    <label class="status-pill">
        <input type="radio" name="statusFilter" value="used">
        <span>Sử dụng <strong id="count-used">(<?php echo $thongKeBan['ban_dang_su_dung']; ?>)</strong></span>
    </label>
    <label class="status-pill">
        <input type="radio" name="statusFilter" value="free">
        <span>Còn trống <strong id="count-free">(<?php echo $thongKeBan['ban_con_trong']; ?>)</strong></span>
    </label>
</div>

    <!-- Grid bàn -->
    <div id="table-grid" class="table-grid">
        <?php echo $danhSachBanHtml; ?>
    </div>                    
 </div>

<!-- Cập nhật các số liệu thống kê -->
<div class="metric-row">
    <div class="metric-card">
        <span class="metric-label">Tổng bàn</span>
        <span class="metric-value" id="stat-all"><?php echo $thongKeBan['tong_ban']; ?></span>
    </div>
    <div class="metric-card">
        <span class="metric-label">Đang sử dụng</span>
        <span class="metric-value busy" id="stat-used"><?php echo $thongKeBan['ban_dang_su_dung']; ?></span>
    </div>
    <div class="metric-card">
        <span class="metric-label">Còn trống</span>
        <span class="metric-value free" id="stat-free"><?php echo $thongKeBan['ban_con_trong']; ?></span>
    </div>
</div>

                </section>

                <!-- RIGHT PANEL -->
                <section class="order-panel">

                    <div class="panel-card order-card">

                        <!-- Tiêu đề đơn -->
                        <div class="order-heading">
                            <div>
                                <div class="order-title" id="order-table-name">Chưa chọn bàn</div>
                                <div class="order-status" id="order-status">Vui lòng chọn bàn bên trái</div>
                            </div>

                            <div class="order-meta">
                                <span>Mã hóa đơn: <strong id="order-id">-</strong></span>
                            </div>
                        </div>

                        <!-- Trạng thái rỗng -->
                        <div id="order-empty" class="order-empty">
                            <div class="order-empty-icon">🧾</div>
                            <p>Chưa có món trong đơn. Vui lòng chọn món từ menu.</p>
                        </div>

                        <!-- Chi tiết đơn -->
                        <div id="order-detail" class="order-detail hidden" >
                            <div id="order-items" class="order-items">
                                <div id="formThemMon" class="form-them-mon hidden">
    <div class="form-header">
        <h3>Thêm món vào đơn</h3>
        <button class="btn-close" id="btnDongForm">×</button>
    </div>
   <form action="" method="post">
    <div class="form-body">
        <div class="table-container">
            <table class="table-mon-an">
                <thead>
                    <tr>
                        <th>Mã món</th>
                        <th>Tên món</th>
                        <th>Số lượng</th>
                    </tr>
                </thead>
                <tbody id="tbodyMonAn">
                                    <?php
                                        echo $monAnUI->themMon(); 
                                    ?>
                </tbody>
            </table>
        </div>
       
        <div class="form-actions">
            <button class="btn btn-primary" id="btnXacNhan">Xác nhận</button>
            <button class="btn btn-secondary" id="btnHuy">Hủy</button>
        </div>
    </div>
    </form>
</div>
                            </div>
                        </div>

                        <!-- Tổng tiền -->
                        <div class="bill-summary">
                            <div class="row">
                                <span>Tạm tính</span> <strong id="sum">0đ</strong>
                            </div>

                            <div class="row">
                                <span>Giảm giá</span> <strong id="discount">0đ</strong>
                            </div>

                            <div class="row total">
                                <span>Tổng cộng</span> <strong id="total">0đ</strong>
                            </div>
                        </div>

                        <!-- Nút -->
                        <div class="action-bar">
                            <button class="btn ghost" id="btnXoaOrder" style="display: none;">Xóa đơn</button>
                            <button class="btn ghost" id="btnNotify">Thông báo (F10)</button>
                            <button class="btn outline" id="btnPrint" style="display: none;">In tạm tính</button>
                            <button class="btn primary" id="btnPay" style="display: none;">Thanh toán (F9)</button>
                        </div>

                    </div>
                </section>

            </div>
        </div>
    </main>

    <!-- ================== FOOTER ================== -->
    <footer class="footer-strip">
        <div class="footer-left">
            <span>Hỗ trợ 1900 6522</span>
            <span class="footer-separator">|</span>
            <span>Chi nhánh trung tâm</span>
            <span class="footer-separator">|</span>
            <span>Phím tắt</span>
        </div>

        <div class="footer-right">
            <span>Phiên bản 25.11.33</span>
        </div>
    </footer>

</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Đường dẫn API được truyền từ PHP để đảm bảo chính xác
// Từ Views/Home/Page.php -> ../../api/order_api.php
window.API_BASE_URL = '../../api/order_api.php';
</script>
<script src="../../Public/js/Page.js?v=<?= time() ?>"></script>

</body>
</html>
