<?php
// ui/MonAnUI.php
require_once __DIR__ . '/../Controller/MonAnController.php';

class MonAnUI {
    private $controller;

    public function __construct() {
        $this->controller = new MonAnController();
    }

    public function hienThiDanhSach() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $result = $this->controller->getDanhSachPaginated($page);
        $monan = $result['data'];
        $pagination = $result['pagination'];
        ?>
        <div class="employee-panel dish-panel">
            <div class="employee-panel__head">
                <div>
                    <p class="eyebrow">Quản lý món ăn</p>
                    <h2>Danh sách món ăn</h2>
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
            <?php if ($pagination->getTotalPages() > 1): ?>
            <div class="pagination-wrapper">
                <?= $pagination->render('?page=') ?>
            </div>
            <?php endif; ?>
        </div>

        <style>
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            padding: 20px 0;
        }

        .pagination {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .pagination-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            color: #495057;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            min-width: 40px;
            justify-content: center;
        }

        .pagination-btn:hover {
            background: #e9ecef;
            border-color: #adb5bd;
            color: #212529;
        }

        .pagination-btn.pagination-current {
            background: #28a745;
            border-color: #28a745;
            color: white;
            font-weight: 600;
        }

        .pagination-btn.pagination-prev,
        .pagination-btn.pagination-next {
            padding: 8px 16px;
        }

        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .pagination-dots {
            color: #6c757d;
            font-weight: 500;
            padding: 8px 4px;
        }
        </style>
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

    public function themMon(){
        $danhSachMonAn = $this->controller->getDanhSach();
        $html = '';
        $stt = 1;
        if (empty($danhSachMonAn)) {
            $html .= '<tr><td colspan="3" style="text-align: center; padding: 20px;">Không có món ăn nào trong danh sách.</td></tr>';
            return $html;
        }

        foreach ($danhSachMonAn as $mon) {
            $idMon = $mon['id_mon'] ?? 0;
            $tenMon = htmlspecialchars($mon['ten_mon'] ?? '');
            
            $html .= '<tr class="mon-item" data-id="' . $idMon . '" data-gia="' . ($mon['gia_ban'] ?? 0) . '">';
            $html .= '<td>' . $stt++ . '</td>';
            $html .= '<td>' . $tenMon . '</td>';
            $html .= '<td>';
            $html .= '<input type="number" class="input-so-luong" min="0" value="0" data-id="' . $idMon . '">';
            $html .= '</td>';
            $html .= '</tr>';
        }

        return $html;
    }
    
}
?>