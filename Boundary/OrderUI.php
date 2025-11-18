<?php
// ui/OrderUI.php
require_once "Controller/OrderController.php";

class OrderUI {
    private $controller;

    public function __construct() {
        $this->controller = new OrderController();
    }

    public function hienThiChiTiet() {
        $id_order = '1';
        $data = $this->controller->getChiTiet($id_order);

        if (!$data) {
            echo '<div>Không tìm thấy đơn hàng!</div>';
            return;
        }

        $o = $data['order'];
        $ct = $data['chitiet'];
        $tong = number_format($data['tong_tien'], 0, ',', '.') . '₫';

        echo '
        <div>
            <div>
                <div">
                    <h4>
                        <i></i> 
                        ĐƠN HÀNG #<strong>' . $o['id_order'] . '</strong>
                    </h4>
                </div>
                <div>
                    <div>
                        <div>
                            <p><strong>Bàn:</strong> Bàn ' . $o['id_ban'] . ' (' . $o['suc_chua'] . ' chỗ)</p>
                            <p><strong>Nhân viên:</strong> ' . htmlspecialchars($o['ten_nhanvien']) . '</p>
                        </div>
                        <div>
                            <p><strong>Thời gian:</strong> ' . date('d/m/Y H:i', strtotime($o['thoi_gian'])) . '</p>
                            <p><strong>Trạng thái:</strong>
                                <span class="badge ' . ($o['trang_thai'] === 'Đang xử lý' ? 'bg-warning' : 'bg-success') . '">
                                    ' . $o['trang_thai'] . '
                                </span>
                            </p>
                        </div>
                    </div>

                    <h5>Chi tiết món ăn</h5>
                    <div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Món</th>
                                    <th class="text-center">SL</th>
                                    <th class="text-end">Đơn giá</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>';

        foreach ($ct as $item) {
            $don_gia = number_format($item['gia'], 0, ',', '.') . '₫';
            $thanh_tien = number_format($item['thanh_tien'], 0, ',', '.') . '₫';
            echo '
                            <tr>
                                <td><strong>' . htmlspecialchars($item['ten_mon']) . '</strong></td>
                                <td class="text-center">' . $item['so_luong'] . '</td>
                                <td class="text-end">' . $don_gia . '</td>
                                <td class="text-end">' . $thanh_tien . '</td>
                            </tr>';
        }

        echo '
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3">TỔNG CỘNG:</th>
                                    <th>' . $tong . '</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>';
    }
}
?>