<?php
require_once __DIR__ . '/../Models/Ban.php';

class BanController {
    private $banModel;

    public function __construct() {
        $this->banModel = new Ban();
    }

    public function getTable() {
        return $this->banModel->getAll();
    }

    public function getTablePaginated($page = 1) {
        require_once __DIR__ . '/../Utils/Pagination.php';

        $totalItems = $this->banModel->countAll();
        $pagination = new Pagination($totalItems, 5, $page);

        $tables = $this->banModel->getAllPaginated(
            $pagination->getOffset(),
            $pagination->getLimit()
        );

        return [
            'data' => $tables,
            'pagination' => $pagination
        ];
    }

    public function getBanById($id) {
        return $this->banModel->getBan($id);
    }

    // Thêm bàn
    public function addTable() {
        $suc_chua = $_POST['suc_chua'] ?? 0;
        $trang_thai = $_POST['trang_thai'] ?? 'Trống';
        return $this->banModel->create($suc_chua, $trang_thai);
    }

    // Cập nhật bàn
    public function editTable() {
        $id_ban = $_POST['id_ban'] ?? 0;
        $suc_chua = $_POST['suc_chua'] ?? 0;
        $trang_thai = $_POST['trang_thai'] ?? 'Trống';
        return $this->banModel->edit($id_ban, $suc_chua, $trang_thai);
    }

    // Xóa bàn
    public function delete($id_ban) {
        return $this->banModel->delete($id_ban);
    }
}
