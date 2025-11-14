<?php
// controllers/MonAnController.php
require_once "Models/MonAn.php";

class MonAnController {
    private $monAnModel;

    public function __construct() {
        $this->monAnModel = new MonAn();
    }

    public function getDanhSach() {
        return $this->monAnModel->getAll();
    }
}
?>