<?php
// ui/OrderUI.php
require_once "Controller/OrderController.php";

class OrderUI {
    private $orderController;
    private $banController;

    public function __construct() {
        $this->orderController = new OrderController();
        $this->banController = new BanController();
    }

    public function hienThiChiTiet() {
        $id_order = '1';
        $data = $this->orderController->getChiTiet($id_order);

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


        // Bước 2: Form chọn món
    public function orderForm($id_ban) {
        $monan = $this->banController->getMonAn();
        $id_nhan_vien = $_SESSION['user_id'] ?? 2; // nhân viên đang đăng nhập

        echo '<div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Đặt món - Bàn ' . $id_ban . '</h4>
                </div>
                <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="confirm">
                    <input type="hidden" name="id_ban" value="' . $id_ban . '">
                    <input type="hidden" name="id_nhan_vien" value="' . $id_nhan_vien . '">

                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Món ăn</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                            </tr>
                        </thead>
                        <tbody>';
        foreach ($monan as $m) {
            $gia = number_format($m['gia_tien'], 0, ',', '.') . '₫';
            echo '<tr>
                    <td><strong>' . htmlspecialchars($m['ten_mon']) . '</strong></td>
                    <td>' . $gia . '</td>
                    <td>
                        <input type="number" name="mon[' . $m['id_mon'] . ']" 
                               min="0" value="0" class="form-control" style="width: 100px;">
                    </td>
                  </tr>';
        }
        echo '      </tbody>
                    </table>
                    <div class="text-end">
                        <a href="order.php" class="btn btn-secondary">Hủy</a>
                        <button type="submit" class="btn btn-success btn-lg">Xác nhận đặt món</button>
                    </div>
                </form>
                </div>
              </div>';
    }

}
?>