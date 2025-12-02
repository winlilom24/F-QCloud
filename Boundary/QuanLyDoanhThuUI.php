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

        $donHangDaThanhToan = array_filter($donHangList, function($dh) {
            return isset($dh['trang_thai_hd']) && $dh['trang_thai_hd'] === 'Đã thanh toán';
        });
        $donHangDangSuDung = array_filter($donHangList, function($dh) {
            return !isset($dh['trang_thai_hd']) || $dh['trang_thai_hd'] !== 'Đã thanh toán';
        });

        $hoaDonChuaCoDoanhThu = $this->controller->getHoaDonChuaCoDoanhThu();
        ?>
        <div class="revenue-panel">

            <!-- Thông báo hóa đơn chưa có doanh thu -->
            <?php if (!empty($hoaDonChuaCoDoanhThu)): ?>
            <div class="alert alert-warning" style="background:#fef3c7;border:1px solid #f59e0b;border-radius:12px;padding:16px 20px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <i class="fa-solid fa-exclamation-triangle" style="color:#f59e0b;font-size:20px;"></i>
                    <div>
                        <strong style="color:#92400e;font-size:15px;">Có <?php echo count($hoaDonChuaCoDoanhThu); ?> hóa đơn đã thanh toán chưa có doanh thu</strong>
                        <p style="color:#78350f;font-size:13px;margin:4px 0 0 0;">Nhấn nút bên dưới để tự động tạo doanh thu từ các hóa đơn này</p>
                    </div>
                </div>
                <button class="btn btn-auto-create" onclick="taoDoanhThuTuTatCaHoaDon()" style="background:#f59e0b;color:white;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;white-space:nowrap;">
                    Tạo doanh thu tự động
                </button>
            </div>
            <?php endif; ?>

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
                        <span class="subtitle">Doanh thu được tạo tự động từ hóa đơn thanh toán</span>
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($doanhThuList)): ?>
                                <tr>
                                    <td colspan="5" class="empty-state">
                                        <i class="fa-solid fa-chart-pie"></i>
                                        <p>Chưa có dữ liệu doanh thu</p>
                                        <small>Doanh thu sẽ tự động xuất hiện khi có hóa đơn thanh toán</small>
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
                                                <?php echo htmlspecialchars(mb_strimwidth($dt['ghi_chu'], 0, 40, '...')); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="no-note">Không có ghi chú</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab content: Đơn hàng (giữ nguyên như cũ) -->
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
                                                Đã thanh toán
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge status-using">
                                                Đang sử dụng
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MODAL CHI TIẾT THỐNG KÊ (giữ nguyên) -->
            <div id="statsDetailModal" class="modal">
                <!-- Nội dung modal thống kê giữ nguyên như file gốc của bạn -->
            </div>

            <div id="printArea" style="display:none;"></div>
        </div>
        <?php
    }

    public function getHoaDonChuaCoDoanhThu() {
         return $this->controller->getHoaDonChuaCoDoanhThu(); }
    public function getDoanhThuTheoFilter($filterType, $date, $month, $year) { 
        return $this->controller->getDoanhThuTheoFilter($filterType, $date, $month, $year); }
    public function getDonHangDaThanhToan() { 
        return $this->controller->getDonHangDaThanhToan(); }
    public function getDonHangDangSuDung() { 
        return $this->controller->getDonHangDangSuDung(); }
    public function taoDoanhThuTuHoaDon($id_hoa_don) { 
        return $this->controller->taoDoanhThuTuHoaDon($id_hoa_don); }
    public function taoDoanhThuTuTatCaHoaDon() { 
        return $this->controller->taoDoanhThuTuTatCaHoaDon(); }
}
?>