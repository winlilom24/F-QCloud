<?php
require_once __DIR__ . '/../Controller/BanController.php';

class BanUI {
    private $banController;

    public function __construct() {
        $this->banController = new BanController();
    }

    public function hienThiDanhSach() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $result = $this->banController->getTablePaginated($page);
        $bans = $result['data'];
        $pagination = $result['pagination'];
        ?>
        <div class="employee-panel table-panel">
            <div class="employee-panel__head">
                <div>
                    <p class="eyebrow">Quản lý bàn ăn</p>
                    <h2>Danh sách bàn</h2>
                    <span class="subtitle">Theo dõi sức chứa và trạng thái phục vụ</span>
                </div>
            </div>
            <div class="table-wrapper">
                <table class="employee-table dish-table">
                    <thead>
                    <tr>
                        <th>Bàn</th>
                        <th>Sức chứa</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($bans)): ?>
                        <tr>
                            <td colspan="4" class="empty-state">
                                <i class="fa-solid fa-table"></i>
                                <p>Chưa có bàn nào</p>
                                <small>Nhấn "Thêm bàn mới" để bắt đầu quản lý</small>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bans as $ban): ?>
                            <?php
                            $status = $ban['trang_thai'] ?? 'Trống';
                            $isFree = $status === 'Trống';
                            $statusClass = $isFree ? 'role-badge dish-available' : 'role-badge dish-soldout';
                            ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="avatar"><?= (int)$ban['id_ban'] ?></div>
                                        <div class="user-text">
                                            <strong>Bàn #<?= (int)$ban['id_ban'] ?></strong>
                                            <span>ID: <?= (int)$ban['id_ban'] ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="price-tag"><?= (int)$ban['suc_chua'] ?> chỗ</span></td>
                                <td><span class="<?= $statusClass ?>"><?= htmlspecialchars($status) ?></span></td>
                                <td>
                                    <a class="btn-action edit" href="javascript:void(0)"
                                       onclick="openTableEditModal(
                                           <?= (int)$ban['id_ban'] ?>,
                                           <?= (int)$ban['suc_chua'] ?>,
                                           '<?= htmlspecialchars($status, ENT_QUOTES) ?>'
                                       )" title="Sửa bàn">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a class="btn-action delete" href="javascript:void(0)"
                                       onclick="confirmTableDelete(<?= (int)$ban['id_ban'] ?>)"
                                       title="Xóa bàn">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
            background: #1f6fff;
            border-color: #1f6fff;
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

    public function showTable() {
        $bans = $this->banController->getTable();

        if (empty($bans)) {
            echo '<p class="text-muted text-center">Chưa có bàn nào được tạo.</p>';
            return;
        }

        echo '<table class="table table-bordered">
                <thead class="table-primary"><tr>
                    <th>Bàn</th><th>Sức chứa</th><th>Trạng thái</th><th>Hành động</th>
                </tr></thead><tbody>';

        foreach ($bans as $ban) {
            $status = $ban['trang_thai'] === 'Trống' 
                ? '<span class="badge bg-success">Trống</span>' 
                : '<span class="badge bg-danger">Đang phục vụ</span>';

            echo '<tr>
                    <td><strong>Bàn ' . $ban['id_ban'] . '</strong></td>
                    <td>' . $ban['suc_chua'] . ' chỗ</td>
                    <td>' . $status . '</td>
                    <td>
                        <a href="?action=edit&id=' . $ban['id_ban'] . '" class="btn btn-sm btn-warning">Sửa</a>
                        <a href="?action=delete&id=' . $ban['id_ban'] . '" 
                           onclick="return confirm(\'Xóa bàn này?\')" 
                           class="btn btn-sm btn-danger">Xóa</a>
                    </td>
                  </tr>';
        }
        echo '</tbody></table>';
    }

    public function showAddForm() {
        echo '<form method="post">
                <div class="mb-3">
                    <label>Sức chứa</label>
                    <input type="number" name="suc_chua" class="form-control" min="1" required value="4">
                </div>
                <button type="submit" name="action" value="add" class="btn btn-primary">Thêm bàn</button>
              </form>';
    }

    public function showEditForm() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo '<p class="text-danger">Không có ID bàn!</p>'; 
            return;
        }

        $ban = $this->banController->getBanById($id);
        if (!$ban) {
            echo '<p class="text-danger">Bàn không tồn tại!</p>'; 
            return;
        }

        echo '<form method="post">
                <input type="hidden" name="id_ban" value="' . $ban['id_ban'] . '">
                <div class="mb-3">
                    <label>Bàn ' . $ban['id_ban'] . ' - Sức chứa hiện tại: ' . $ban['suc_chua'] . '</label>
                    <input type="number" name="suc_chua" class="form-control" min="1" required value="' . $ban['suc_chua'] . '">
                </div>
                <button type="submit" name="action" value="update" class="btn btn-success">Cập nhật</button>
              </form>';
    }

    public function addTable(){
        return $this->banController->addTable();
    }

    public function editTable(){
        return $this->banController->editTable();
    }

    public function delete($id){
        return $this->banController->delete($id);
    }

    public function hienThiDanhSachBanGrid() {
    $bans = $this->banController->getTable();
    
    if (empty($bans)) {
        return '<div class="table-grid-empty">Chưa có bàn nào được tạo.</div>';
    }

    $html = '';
    $currentRow = 0;
    $maxColumns = 10; // Số bàn tối đa trên một hàng

    foreach ($bans as $ban) {
        $trangThai = $ban['trang_thai'] ?? 'Trống';
        $isFree = $trangThai === 'Trống';
        
        // Tạo class và màu sắc cho bàn
        $banClass = $isFree ? 'ban-trong' : 'ban-dang-su-dung';
        $icon = $isFree ? '🪑' : '👥';
        
        $html .= '<div class="ban ' . $banClass . '" data-id="' . (int)$ban['id_ban'] . '">';
        $html .= '<div class="ban-so">' . (int)$ban['id_ban'] . '</div>';
        $html .= '<div class="ban-icon">' . $icon . '</div>';
        $html .= '<div class="ban-ten">Bàn ' . (int)$ban['id_ban'] . '</div>';
        $html .= '<div class="ban-trang-thai">' . htmlspecialchars($trangThai) . '</div>';
        $html .= '<div class="ban-suc-chua">' . (int)$ban['suc_chua'] . ' chỗ</div>';
        $html .= '</div>';

        $currentRow++;
        if ($currentRow % $maxColumns === 0 && $currentRow < count($bans)) {
            // Có thể thêm logic để tạo hàng mới nếu cần
        }
    }

    return $html;
}

public function layThongKeBan() {
    $bans = $this->banController->getTable();
    
    $thongKe = [
        'tong_ban' => 0,
        'ban_dang_su_dung' => 0,
        'ban_con_trong' => 0
    ];

    if (!empty($bans)) {
        $thongKe['tong_ban'] = count($bans);
        
        foreach ($bans as $ban) {
            $trangThai = $ban['trang_thai'] ?? 'Trống';
            if ($trangThai !== 'Trống') {
                $thongKe['ban_dang_su_dung']++;
            } else {
                $thongKe['ban_con_trong']++;
            }
        }
    }

    return $thongKe;
}
}