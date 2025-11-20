<?php
// ui/MonAnUI.php
require_once "Controller/MonAnController.php";

class MonAnUI {
    private $controller;

    public function __construct() {
        $this->controller = new MonAnController();
    }

    public function hienThiDanhSach() {
        $monan = $this->controller->getDanhSach();

        if (empty($monan)) {
            echo '<div class="text-center py-5"><h4>Chưa có món ăn nào</h4></div>';
            return;
        }

        echo '<div class="row g-4">';
        foreach ($monan as $m) {
            $gia = number_format($m['gia_tien'], 0, ',', '.') . '₫';
            $hinh = !empty($m['hinh_anh']) ? 'images/monan/' . $m['hinh_anh'] : 'images/monan/default.jpg';
            $trangthai = $m['trang_thai'] === 'Còn' 
                ? '<span class="badge bg-success">Còn món</span>' 
                : '<span class="badge bg-secondary">Hết món</span>';


            echo '
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-primary">' . htmlspecialchars($m['ten_mon']) . '</h5>
                        <p class="card-text text-danger fw-bold fs-4">' 
                            . number_format($m['gia_tien'], 0, ',', '.') . '₫</p>
                        <p class="card-text text-muted small">' . nl2br(htmlspecialchars($m['mo_ta'] ?? 'Không có mô tả')) . '</p>
                        <div class="mt-auto">
                            <span class="badge ' . ($m['trang_thai'] === 'Còn' ? 'bg-success' : 'bg-secondary') . '">
                                ' . $m['trang_thai'] . '
                            </span>
                            <div class="mt-2">
                                <a href="?action=edit&id=' . $m['id_mon'] . '" class="btn btn-warning btn-sm">Sửa</a>
                                <a href="?action=delete&id=' . $m['id_mon'] . '" 
                                   onclick="return confirm(\'Xóa món này?\')" 
                                    class="btn btn-danger btn-sm">Xóa</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
        }
        echo '</div>';
    }

    // Form thêm món
    public function addForm() {
        echo '<div class="card"><div class="card-header bg-success text-white"><h4>Thêm món mới</h4></div>
              <div class="card-body">
              <form method="post">
                  <input type="hidden" name="action" value="add">
                  <div class="mb-3"><label>Tên món</label><input name="ten_mon" class="form-control" required></div>
                  <div class="mb-3"><label>Giá tiền</label><input type="number" name="gia_tien" class="form-control" min="1000" required></div>
                  <div class="mb-3"><label>Mô tả</label><textarea name="mo_ta" class="form-control" rows="3"></textarea></div>
                  <button type="submit" class="btn btn-success">Thêm món</button>
                  <a href="monan.php" class="btn btn-secondary">Hủy</a>
              </form></div></div>';
    }

    // Form sửa món
    public function editForm($id) {
        $mon = $this->controller->getById($id);
        if (!$mon) { echo "Không tìm thấy món!"; return; }

        echo '<div class="card"><div class="card-header bg-warning"><h4>Sửa món ăn</h4></div>
              <div class="card-body">
              <form method="post">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="id_mon" value="' . $mon['id_mon'] . '">
                  <div class="mb-3"><label>Tên món</label><input name="ten_mon" class="form-control" value="' . htmlspecialchars($mon['ten_mon']) . '" required></div>
                  <div class="mb-3"><label>Giá tiền</label><input type="number" name="gia_tien" class="form-control" value="' . $mon['gia_tien'] . '" required></div>
                  <div class="mb-3"><label>Mô tả</label><textarea name="mo_ta" class="form-control">' . htmlspecialchars($mon['mo_ta'] ?? '') . '</textarea></div>
                  <button type="submit" class="btn btn-success">Cập nhật</button>
                  <a href="monan.php" class="btn btn-secondary">Hủy</a>
              </form></div></div>';
    }
}
?>