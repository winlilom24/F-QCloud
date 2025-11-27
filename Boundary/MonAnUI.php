<?php
// ui/MonAnUI.php
require_once __DIR__ . '/../Controller/MonAnController.php';

class MonAnUI {
    private $controller;

    public function __construct() {
        $this->controller = new MonAnController();
    }

    public function hienThiDanhSach() {
        $monan = $this->controller->getDanhSach();
        ?>
        <div class="employee-panel dish-panel">
            <div class="employee-panel__head">
                <div>
                    <p class="eyebrow">Quản lý món ăn</p>
                    <h2>Danh sách món ăn</h2>
                    <span class="subtitle">Đồng bộ dữ liệu từ bảng `monan` và `nhommonan`</span>
                </div>
            </div>
            <div class="table-wrapper">
                <table class="employee-table dish-table">
                    <thead>
                        <tr>
                            <th>Tên món</th>
                            <th>Giá tiền</th>
                            <th>Nhóm món</th>
                            <th>Trạng thái</th>
                            <th>Mô tả</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($monan)): ?>
                            <tr>
                                <td colspan="6" class="empty-state">
                                    <i class="fa-solid fa-bowl-rice"></i>
                                    <p>Chưa có món ăn nào</p>
                                    <small>Nhấn "Thêm món ăn" để bổ sung thực đơn</small>
                                </td>
                            </tr>
                        <?php else: foreach ($monan as $mon): ?>
                            <?php
                                $price = number_format((float)$mon['gia_tien'], 0, ',', '.');
                                $statusClass = ($mon['trang_thai'] === 'Còn món') ? 'role-badge dish-available' : 'role-badge dish-soldout';
                                $statusLabel = $mon['trang_thai'] ?? 'Còn món';
                            ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="avatar"><?= strtoupper(mb_substr($mon['ten_mon'], 0, 1)) ?></div>
                                        <div class="user-text">
                                            <strong><?= htmlspecialchars($mon['ten_mon']) ?></strong>
                                            <span>ID: <?= $mon['id_mon'] ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="price-tag"><?= $price ?>₫</span></td>
                                <td><?= htmlspecialchars($mon['ten_nhom'] ?? 'Chưa phân nhóm') ?></td>
                                <td><span class="<?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span></td>
                                <td class="description-cell"><?= htmlspecialchars($mon['mo_ta'] ?? '—') ?></td>
                                <td>
                                    <a class="btn-action edit" href="javascript:void(0)"
                                       onclick="openDishEditModal(
                                           <?= (int)$mon['id_mon'] ?>,
                                           '<?= htmlspecialchars(addslashes($mon['ten_mon']), ENT_QUOTES) ?>',
                                           '<?= htmlspecialchars($mon['gia_tien']) ?>',
                                           '<?= htmlspecialchars(addslashes($mon['mo_ta'] ?? ''), ENT_QUOTES) ?>',
                                           '<?= htmlspecialchars($mon['trang_thai']) ?>',
                                           '<?= $mon['id_nhom'] !== null ? (int)$mon['id_nhom'] : '' ?>'
                                       )" title="Sửa món">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a class="btn-action delete" href="javascript:void(0)"
                                       onclick="confirmDishDelete(<?= (int)$mon['id_mon'] ?>, '<?= htmlspecialchars(addslashes($mon['ten_mon']), ENT_QUOTES) ?>')"
                                       title="Xóa món">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public function getDanhSachNhom() {
        return $this->controller->getDanhSachNhom();
    }

    public function xuLyThemMon() {
        return $this->controller->add();
    }

    public function xuLySuaMon() {
        return $this->controller->update();
    }

    public function xuLyXoaMon($id_mon) {
        return $this->controller->delete($id_mon);
    }
}
?>