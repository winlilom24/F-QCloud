<?php
// ui/MonAnUI.php
require_once "Controller/MonAnController.php";

class MonAnUI {
    private $controller;

    public function __construct() {
        $this->controller = new MonAnController();
    }

    // Hiển thị tất cả món ăn
    public function hienThiMonAn() {
        $monan = $this->controller->getDanhSach();

        if (empty($monan)) {
            echo '<p class="text-center text-muted">Không có món ăn nào!</p>';
            return;
        }

        foreach ($monan as $m) {
            $id = $m['id_mon'];
            $ten = htmlspecialchars($m['ten_mon']);
            $gia = number_format($m['gia_tien'], 0, ',', '.') . '₫';
            
            //nay neu them truong hinh anh :v, do mình chưa có
            $hinh = $m['hinh_anh'] ?? 'default.jpg';
            $hinhPath = "images/monan/" . $hinh;

            echo '
            <div>
                <div>
                    <img src="' . $hinhPath . '" class="card-img-top" alt="' . $ten . '" style="height: 200px; object-fit: cover;">
                    <div>
                        <h5>' . $ten . '</h5>
                        <p>' . $gia . '</p>
                        <button data-id="' . $id . '">
                            Thêm vào order
                        </button>
                    </div>
                </div>
            </div>';
        }
    }
}
?>