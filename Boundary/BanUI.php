<?php
require_once "Controller/BanController.php";

class BanUI {
    private $banController;

    public function __construct() {
        $this->banController = new BanController();
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
}