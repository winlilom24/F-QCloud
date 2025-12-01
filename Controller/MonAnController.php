<?php
// controllers/MonAnController.php
require_once __DIR__ . '/../Models/MonAn.php';

class MonAnController {
    private $monAnModel;

    public function __construct() {
        $this->monAnModel = new MonAn();
    }

    public function getDanhSach() {
        return $this->monAnModel->getAll();
    }

    public function getDanhSachPaginated($page = 1) {
        require_once __DIR__ . '/../Utils/Pagination.php';

        $totalItems = $this->monAnModel->countAll();
        $pagination = new Pagination($totalItems, 5, $page);

        $dishes = $this->monAnModel->getAllPaginated(
            $pagination->getOffset(),
            $pagination->getLimit()
        );

        return [
            'data' => $dishes,
            'pagination' => $pagination
        ];
    }

    public function getDanhSachNhom() {
        return $this->monAnModel->getCategories();
    }

    public function getById($id) {
        return $this->monAnModel->getById($id);
    }

    public function add() {
        $ten_mon = trim($_POST['ten_mon'] ?? '');
        $gia_tien = (int)($_POST['gia_tien'] ?? 0);
        $mo_ta = trim($_POST['mo_ta'] ?? '');
        $trang_thai = $_POST['trang_thai'] ?? 'Còn';
        $id_nhom = !empty($_POST['id_nhom']) ? (int)$_POST['id_nhom'] : null;

        return $this->monAnModel->create($ten_mon, $gia_tien, $mo_ta, $trang_thai, $id_nhom);
    }

    public function update() {
        $id_mon = (int)($_POST['id_mon'] ?? 0);
        $ten_mon = trim($_POST['ten_mon'] ?? '');
        $gia_tien = (int)($_POST['gia_tien'] ?? 0);
        $mo_ta = trim($_POST['mo_ta'] ?? '');
        $trang_thai = $_POST['trang_thai'] ?? 'Còn';
        $id_nhom = !empty($_POST['id_nhom']) ? (int)$_POST['id_nhom'] : null;

        return $this->monAnModel->update($id_mon, $ten_mon, $gia_tien, $mo_ta, $trang_thai, $id_nhom);
    }

    public function delete($id) {
        return $this->monAnModel->delete($id);
    }
}
?>