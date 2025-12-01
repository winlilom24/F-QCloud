<?php
// Boundary/QuanLyDoanhThuUI.php
require_once __DIR__ . '/../Controller/QLDoanhThuController.php';

class QuanLyDoanhThuUI {
    private $controller;

    public function __construct() {
        $this->controller = new QLDoanhThuController();
    }

    public function hienThiDanhSach() {
        $doanhThuList = $this->controller->getAllDoanhThu();
        $donHangList = $this->controller->getAllDonHang();
        $thongKe = $this->controller->getThongKeTongQuan();

        // Đếm số đơn hàng theo trạng thái
        $donHangDaThanhToan = array_filter($donHangList, function($dh) {
            return isset($dh['trang_thai_hd']) && $dh['trang_thai_hd'] === 'Đã thanh toán';
        });
        $donHangDangSuDung = array_filter($donHangList, function($dh) {
            return !isset($dh['trang_thai_hd']) || $dh['trang_thai_hd'] !== 'Đã thanh toán';
        });
        ?>
        <div class="revenue-panel">
            <!-- Thống kê tổng quan -->
            <div class="stats-grid">
                <div class="stat-card clickable" onclick="showRevenueDetails()">
                    <div class="stat-icon">
                        <i class="fa-solid fa-calculator"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($thongKe['tong_doanh_thu'] ?? 0, 0, ',', '.'); ?>₫</div>
                        <div class="stat-label">Tổng doanh thu</div>
                        <div class="stat-subtitle">Nhấn để xem chi tiết</div>
                    </div>
                </div>
                <div class="stat-card clickable" onclick="showInvoiceDetails()">
                    <div class="stat-icon">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $thongKe['tong_so_ban_ghi'] ?? 0; ?></div>
                        <div class="stat-label">Số hóa đơn</div>
                        <div class="stat-subtitle">Nhấn để xem chi tiết</div>
                    </div>
                </div>
                <div class="stat-card clickable" onclick="showPaidOrders()">
                    <div class="stat-icon">
                        <i class="fa-solid fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo count($donHangDaThanhToan); ?></div>
                        <div class="stat-label">Đã thanh toán</div>
                        <div class="stat-subtitle">Nhấn để xem chi tiết</div>
                    </div>
                </div>
                <div class="stat-card clickable" onclick="showActiveOrders()">
                    <div class="stat-icon">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo count($donHangDangSuDung); ?></div>
                        <div class="stat-label">Đang sử dụng</div>
                        <div class="stat-subtitle">Nhấn để xem chi tiết</div>
                    </div>
                </div>
            </div>

            <!-- Tab navigation -->
            <div class="tab-navigation">
                <button class="tab-btn active" onclick="showTab('doanhthu')">Doanh thu</button>
                <button class="tab-btn" onclick="showTab('donhang')">Đơn hàng</button>
            </div>

            <!-- Tab content: Doanh thu -->
            <div id="doanhthu-tab" class="tab-content active">
                <div class="revenue-panel__head">
                    <div>
                        <p class="eyebrow">Quản lý doanh thu</p>
                        <h2>Danh sách doanh thu</h2>
                        <span class="subtitle">Theo dõi và quản lý doanh thu từ các hóa đơn</span>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="revenue-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hóa đơn</th>
                                <th>Tổng tiền</th>
                                <th>Ngày tính</th>
                                <th>Ghi chú</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($doanhThuList)): ?>
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <i class="fa-solid fa-chart-pie"></i>
                                        <p>Chưa có dữ liệu doanh thu</p>
                                        <small>Nhấn "Thêm doanh thu" để bắt đầu</small>
                                    </td>
                                </tr>
                            <?php else: foreach ($doanhThuList as $dt): ?>
                                <tr>
                                    <td><strong>#<?php echo $dt['id_doanh_thu']; ?></strong></td>
                                    <td>
                                        <div class="order-info">
                                            <span class="order-id">HD<?php echo str_pad($dt['id_hoa_don'], 3, '0', STR_PAD_LEFT); ?></span>
                                            <small><?php echo date('d/m/Y H:i', strtotime($dt['thoi_gian'])); ?></small>
                                        </div>
                                    </td>
                                    <td><span class="amount"><?php echo number_format($dt['tong_tien'], 0, ',', '.'); ?>₫</span></td>
                                    <td><?php echo date('d/m/Y', strtotime($dt['ngay_tinh'])); ?></td>
                                    <td>
                                        <?php if ($dt['ghi_chu']): ?>
                                            <span class="note" title="<?php echo htmlspecialchars($dt['ghi_chu']); ?>">
                                                <?php echo htmlspecialchars(mb_strimwidth($dt['ghi_chu'], 0, 30, '...')); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="no-note">Không có ghi chú</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a class="btn-action edit" href="javascript:void(0)"
                                           onclick="openEditModal(
                                               <?php echo $dt['id_doanh_thu']; ?>,
                                               <?php echo $dt['id_hoa_don']; ?>,
                                               '<?php echo number_format($dt['tong_tien'], 2, '.', ''); ?>',
                                               '<?php echo $dt['ngay_tinh']; ?>',
                                               '<?php echo htmlspecialchars(addslashes($dt['ghi_chu'] ?? ''), ENT_QUOTES); ?>'
                                           )" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        <a class="btn-action delete" href="javascript:void(0)"
                                           onclick="confirmDelete(<?php echo $dt['id_doanh_thu']; ?>, 'HD<?php echo str_pad($dt['id_hoa_don'], 3, '0', STR_PAD_LEFT); ?>')"
                                           title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab content: Đơn hàng -->
            <div id="donhang-tab" class="tab-content">
                <div class="revenue-panel__head">
                    <div>
                        <p class="eyebrow">Quản lý đơn hàng</p>
                        <h2>Danh sách đơn hàng</h2>
                        <span class="subtitle">Theo dõi trạng thái thanh toán của các đơn hàng</span>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="revenue-table">
                        <thead>
                            <tr>
                                <th>ID Đơn</th>
                                <th>Bàn</th>
                                <th>Nhân viên</th>
                                <th>Thời gian</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($donHangList)): ?>
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <i class="fa-solid fa-shopping-cart"></i>
                                        <p>Chưa có đơn hàng nào</p>
                                        <small>Đơn hàng sẽ xuất hiện khi có khách hàng đặt món</small>
                                    </td>
                                </tr>
                            <?php else: foreach ($donHangList as $dh): ?>
                                <tr>
                                    <td><strong>#<?php echo $dh['id_order']; ?></strong></td>
                                    <td>
                                        <div class="table-info">
                                            <span class="table-id">Bàn <?php echo $dh['id_ban']; ?></span>
                                            <small><?php echo $dh['suc_chua'] ?? 0; ?> người</small>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($dh['ten_nhan_vien'] ?? 'Chưa phân công'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($dh['thoi_gian_order'])); ?></td>
                                    <td>
                                        <?php if ($dh['tong_tien']): ?>
                                            <span class="amount"><?php echo number_format($dh['tong_tien'], 0, ',', '.'); ?>₫</span>
                                        <?php else: ?>
                                            <span class="pending-amount">Chưa tính</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($dh['trang_thai_hd']) && $dh['trang_thai_hd'] === 'Đã thanh toán'): ?>
                                            <span class="status-badge status-paid">
                                                <i class="fa-solid fa-check-circle"></i> Đã thanh toán
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge status-using">
                                                <i class="fa-solid fa-clock"></i> Đang sử dụng
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MODAL CHI TIẾT THỐNG KÊ -->
            <div id="statsDetailModal" class="modal">
                <div class="modal-content large-modal">
                    <div class="modal-header">
                        <h3 id="statsModalTitle">Chi tiết thống kê</h3>
                        <span class="close" onclick="closeStatsModal()">×</span>
                    </div>
                    <div class="modal-body">
                        <!-- Filter controls -->
                        <div class="filter-section" id="filterSection" style="display: none;">
                            <div class="filter-controls">
                                <div class="filter-group">
                                    <label>Khoảng thời gian:</label>
                                    <select id="timeFilter" onchange="changeTimeFilter()">
                                        <option value="all">Tất cả</option>
                                        <option value="day">Theo ngày</option>
                                        <option value="month">Theo tháng</option>
                                        <option value="year">Theo năm</option>
                                    </select>
                                </div>
                                <div class="filter-group" id="dateFilter" style="display: none;">
                                    <label>Chọn ngày:</label>
                                    <input type="date" id="selectedDate">
                                </div>
                                <div class="filter-group" id="monthFilter" style="display: none;">
                                    <label>Chọn tháng:</label>
                                    <input type="month" id="selectedMonth">
                                </div>
                                <div class="filter-group" id="yearFilter" style="display: none;">
                                    <label>Chọn năm:</label>
                                    <input type="number" id="selectedYear" min="2020" max="2030" value="2025">
                                </div>
                                <button class="btn btn-primary" onclick="applyFilter()">Áp dụng</button>
                            </div>
                        </div>

                        <!-- Stats summary -->
                        <div class="stats-summary" id="statsSummary"></div>

                        <!-- Detail table -->
                        <div class="detail-table-container">
                            <table class="detail-table" id="detailTable">
                                <thead id="detailTableHead"></thead>
                                <tbody id="detailTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function xuLyThemDoanhThu() {
        $data = [
            'id_hoa_don' => $_POST['id_hoa_don'],
            'tong_tien'  => $_POST['tong_tien'],
            'ngay_tinh'  => $_POST['ngay_tinh'],
            'ghi_chu'    => $_POST['ghi_chu'] ?? null
        ];
        return $this->controller->themDoanhThu($data);
    }

    public function xuLySuaDoanhThu() {
        $id_doanh_thu = (int)$_POST['id_doanh_thu'];
        $data = [
            'id_hoa_don' => $_POST['id_hoa_don'],
            'tong_tien'  => $_POST['tong_tien'],
            'ngay_tinh'  => $_POST['ngay_tinh'],
            'ghi_chu'    => $_POST['ghi_chu'] ?? null
        ];
        return $this->controller->suaDoanhThu($id_doanh_thu, $data);
    }

    public function xuLyXoaDoanhThu($id_doanh_thu) {
        return $this->controller->xoaDoanhThu($id_doanh_thu);
    }

    public function getHoaDonChuaCoDoanhThu() {
        return $this->controller->getHoaDonChuaCoDoanhThu();
    }

    public function getDoanhThuTheoFilter($filterType, $date, $month, $year) {
        return $this->controller->getDoanhThuTheoFilter($filterType, $date, $month, $year);
    }

    public function getDonHangDaThanhToan() {
        return $this->controller->getDonHangDaThanhToan();
    }

    public function getDonHangDangSuDung() {
        return $this->controller->getDonHangDangSuDung();
    }
}
?>