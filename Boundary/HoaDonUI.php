<?php
require_once __DIR__ . "/../Controller/HoaDonController.php";
require_once __DIR__ . "/../Controller/OrderController.php";

class HoaDonUI {
    private $controller;
    private $orderController;

    public function __construct() {
        $this->controller = new HoaDonController();
        $this->orderController = new OrderController();
    }

    // Tạo hóa đơn (thanh toán)
    public function taoHoaDon($id_order, $ghi_chu = null) {
        return $this->controller->taoHoaDon($id_order, $ghi_chu);
    }

    // Lấy chi tiết hóa đơn để in
    public function getChiTietHoaDon($id_order) {
        $orderData = $this->orderController->getChiTiet($id_order);
        
        if (!$orderData) {
            return null;
        }

        // Lấy thông tin hóa đơn từ database
        require_once __DIR__ . '/../Models/HoaDon.php';
        require_once __DIR__ . '/../Repository/Database.php';
        
        // Tìm hóa đơn theo id_order
        $conn = Database::connect();
        $query = "SELECT hd.* FROM hoadon hd WHERE hd.id_order = ? ORDER BY hd.thoi_gian DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_order);
        $stmt->execute();
        $result = $stmt->get_result();
        $hoaDon = $result->fetch_assoc();

        return [
            'hoadon' => $hoaDon,
            'order' => $orderData['order'],
            'chitiet' => $orderData['chitiet'],
            'tong_tien' => $orderData['tong_tien']
        ];
    }

    // In hóa đơn (trả về HTML)
    public function inHoaDon($id_order) {
        $data = $this->getChiTietHoaDon($id_order);
        
        if (!$data) {
            return '<div>Không tìm thấy hóa đơn!</div>';
        }

        $hd = $data['hoadon'];
        $o = $data['order'];
        $ct = $data['chitiet'];
        $tong = number_format($data['tong_tien'], 0, ',', '.') . '₫';
        $thoi_gian = $hd ? date('d/m/Y H:i', strtotime($hd['thoi_gian'])) : date('d/m/Y H:i');

        $html = '<div class="invoice-print" id="invoice-content">';
        $html .= '<div class="invoice-header">';
        $html .= '<h2>HÓA ĐƠN THANH TOÁN</h2>';
        $html .= '<p>Mã hóa đơn: <strong>#' . ($hd ? $hd['id_hoa_don'] : 'N/A') . '</strong></p>';
        $html .= '<p>Thời gian: ' . $thoi_gian . '</p>';
        $html .= '</div>';

        $html .= '<div class="invoice-info">';
        $html .= '<p><strong>Bàn:</strong> ' . ($o['id_ban'] == 0 ? 'Mang về' : 'Bàn ' . $o['id_ban']) . '</p>';
        $html .= '<p><strong>Nhân viên:</strong> ' . htmlspecialchars($o['ten_nhanvien']) . '</p>';
        $html .= '</div>';

        $html .= '<table class="invoice-table">';
        $html .= '<thead><tr><th>Món</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>';
        $html .= '<tbody>';

        foreach ($ct as $item) {
            $don_gia = number_format($item['gia'], 0, ',', '.') . '₫';
            $thanh_tien = number_format($item['thanh_tien'], 0, ',', '.') . '₫';
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($item['ten_mon']) . '</td>';
            $html .= '<td class="text-center">' . $item['so_luong'] . '</td>';
            $html .= '<td class="text-end">' . $don_gia . '</td>';
            $html .= '<td class="text-end">' . $thanh_tien . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '<tfoot><tr><th colspan="3">TỔNG CỘNG:</th><th>' . $tong . '</th></tr></tfoot>';
        $html .= '</table>';

        $html .= '<div class="invoice-footer">';
        $html .= '<p>Cảm ơn quý khách!</p>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
?>