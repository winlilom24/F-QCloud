<?php
require_once "Controller/BanController.php";

class BanUI {
    private $banController;

    // SỬA: Gán vào $this->banController
    public function __construct() {
        $this->banController = new BanController();
    }

    // SỬA: Bỏ static
    public function showTable() {
        $bans = $this->banController->getTable(); // Bây giờ $this->banController tồn tại!

        if (empty($bans)) {
            echo '<p class="text-center text-muted">Không có bàn nào!</p>';
            return;
        }

        echo '<div class="row g-3">';
        foreach ($bans as $ban) {
            $id = $ban['id_ban'];
            $sucChua = $ban['suc_chua'];
            $trangThai = $ban['trang_thai'];
            $badgeClass = $trangThai === 'Trống' ? 'bg-success' : 'bg-danger';

            echo '
            <div>
                <div>
                    <h5>Bàn ' . $id . '</h5>
                    <p><strong>' . $sucChua . ' chỗ</strong></p>
                    <span class="badge ' . $badgeClass . '">' . $trangThai . '</span>
                </div>
            </div>';
        }
        echo '</div>';
    }
}
?>