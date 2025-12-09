<?php
// controllers/HeThongController.php
// session_start(); // bắt buộc để dùng session

require_once __DIR__ . "/../Models/HeThongSession.php";
require_once __DIR__ . "/../Models/User.php";

class HeThongController {
    private $sessionModel;
    private $userModel;

    public function __construct() {
        $this->sessionModel = new HeThongSession();
        $this->userModel = new User();
    }

    public function dangXuat($user_id) {
        $user_id = (int)$user_id;
        if ($user_id <= 0) return false;

        $result = $this->sessionModel->destroySession($user_id);

        session_unset();
        session_destroy();

        return $result;
    }

    public function loadPage() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../Login/Form.php");
            exit;
        }

        $user = $this->userModel->getUserById($_SESSION['user_id']);
        require __DIR__ . "/../Views/Home/Page.php";
    }
}
