<?php
require_once "Models/Ban.php";

class BanController {
    private $banModel;

    public function __construct() {
        $this->banModel = new Ban();
    }

    public function getTable() {
        return $this->banModel->getAll();
    }

    public function getBanById($id) {
        return $this->banModel->getBan($id);
    }

    public function addTable() {
        $suc_chua = $_POST['suc_chua'] ?? 0;
        return $this->banModel->create($suc_chua);
    }

    public function editTable() {
        $id_ban = $_POST['id_ban'] ?? 0;
        $suc_chua = $_POST['suc_chua'] ?? 0;
        return $this->banModel->edit($id_ban, $suc_chua);
    }

    public function delete($id_ban) {
        return $this->banModel->delete($id_ban);
    }
}