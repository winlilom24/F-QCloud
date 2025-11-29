<?php 
session_start(); 
require_once __DIR__ . '/../../Models/User.php'; 

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) { 
    header('Location: ../Login/Form.php'); 
    exit; 
}

$userModel = new User();
$userRecord = $userModel->getUserById($_SESSION['user_id']);

// Dữ liệu người dùng
$storeName = $userRecord['ten_quan'] ?? ($_SESSION['ten_quan'] ?? 'F-QCloud');
$userName  = $userRecord['ten']      ?? ($_SESSION['ten']      ?? 'Người dùng');
$userRole  = $userRecord['role']     ?? ($_SESSION['role']     ?? 'Quản lý');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>F-QCloud - Hệ thống quản lý nhà hàng</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../../Public/css/Page.css?v=<?= time() ?>">
</head>

<body class="theme-blue">
<div id="app">

    <!-- ================== SIDE MENU ================== -->
    <div id="sideMenu" class="side-menu">
        <h3>Chức năng</h3>
        <a href="#">Quản lý</a>
        <a href="#">Nhà bếp</a>
        <a href="#">Lễ tân</a>
        <a href="#">Màn hình phụ</a>
        <a href="#">Báo cáo cuối ngày</a>
        <a href="#">Phiếu bàn giao ca</a>
        <a href="#">Lập phiếu thu</a>
        <a href="#">Chọn hóa đơn trả hàng</a>
        <a href="#">Xem danh sách đặt bàn</a>
        <a href="#">Cài đặt chung</a>
        <a href="#">Thiết lập giá</a>
        <a href="#">Món có sẵn trong đơn</a>
        <a href="#">Phím tắt</a>
        <a href="#">Đóng ca làm việc</a>
    </div>
    <div id="menuOverlay" class="menu-overlay"></div>

    <!-- ================== HEADER ================== -->
    <header class="top-bar">
        <div class="brand">
            <img src="../../Public/images/logo.png" class="logo" alt="F-QCloud logo"/>

            <div class="brand-info" style="display: none;">
                <span class="title"><?= htmlspecialchars($storeName) ?></span>
                <span class="subtitle"><?= htmlspecialchars($userName) ?> • <?= htmlspecialchars($userRole) ?></span>
            </div>
        </div>

        <div class="header-controls">
            <button class="icon-btn">🔔</button>
            <button class="icon-btn">❔</button>
            <button id="hamburgerMenu" class="hamburger-menu">☰</button>
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

                        <!-- Bộ lọc khu vực -->
                        <div class="filter-strip">
                            <div class="floor-tabs">
                                <button class="floor-btn active" data-floor="all">Tất cả</button>
                                <button class="floor-btn" data-floor="1">Tầng 1</button>
                                <button class="floor-btn" data-floor="2">Tầng 2</button>
                            </div>

                            <label class="auto-open">
                                <input id="autoOpenMenu" type="checkbox">
                                <span>Mở thực đơn khi chọn bàn</span>
                            </label>
                        </div>

                        <!-- Bộ lọc trạng thái -->
                        <div class="status-pills">
                            <label class="status-pill">
                                <input type="radio" name="statusFilter" value="all" checked>
                                <span>Tất cả <strong id="count-all">(0)</strong></span>
                            </label>

                            <label class="status-pill">
                                <input type="radio" name="statusFilter" value="used">
                                <span>Sử dụng <strong id="count-used">(0)</strong></span>
                            </label>

                            <label class="status-pill">
                                <input type="radio" name="statusFilter" value="free">
                                <span>Còn trống <strong id="count-free">(0)</strong></span>
                            </label>
                        </div>

                        <!-- Grid bàn -->
                        <div id="table-grid" class="table-grid"></div>

                        <!-- Phân trang -->
                        <div class="table-pagination">
                            <span id="pageInfo">1/1</span>
                            <button id="prevPage" class="page-btn">←</button>
                            <button id="nextPage" class="page-btn">→</button>
                        </div>

                    </div>

                    <!-- Thống kê -->
                    <div class="metric-row">
                        <div class="metric-card">
                            <span class="metric-label">Tổng bàn</span>
                            <span class="metric-value" id="stat-all">0</span>
                        </div>

                        <div class="metric-card">
                            <span class="metric-label">Đang sử dụng</span>
                            <span class="metric-value busy" id="stat-used">0</span>
                        </div>

                        <div class="metric-card">
                            <span class="metric-label">Còn trống</span>
                            <span class="metric-value free" id="stat-free">0</span>
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
                                <span>Khách: <strong id="order-customer">-</strong></span>
                            </div>
                        </div>

                        <!-- Trạng thái rỗng -->
                        <div id="order-empty" class="order-empty">
                            <div class="order-empty-icon">🧾</div>
                            <p>Chưa có món trong đơn. Vui lòng chọn món từ menu.</p>
                        </div>

                        <!-- Chi tiết đơn -->
                        <div id="order-detail" class="order-detail hidden">
                            <div id="order-items" class="order-items"></div>
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
                            <button class="btn ghost" id="btnNotify">Thông báo (F10)</button>
                            <button class="btn outline" id="btnPrint">In tạm tính</button>
                            <button class="btn primary" id="btnPay">Thanh toán (F9)</button>
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
<script src="../../Public/js/Page.js?v=<?= time() ?>"></script>

</body>
</html>
