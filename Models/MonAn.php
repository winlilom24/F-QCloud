<?php
// models/MonAn.php
require_once "Repository/Database.php";

class MonAn {
    private $conn;

    private $id_mon;
    private $ten_mon;
    private $gia_tien;
    private $mo_ta;
    private $trang_thai;
    private $id_nhom;

    public function __construct() {
        $this->conn = Database::connect();
    }

    // Lấy tất cả món ăn
    public function getAll() {
        $query = "SELECT id_mon, ten_mon, gia_tien, mo_ta, trang_thai, id_nhom 
                  FROM monan 
                  ORDER BY id_mon";
        $result = $this->conn->query($query);

        $monan = [];
        while ($row = $result->fetch_assoc()) {
            $monan[] = $row;
        }
        return $monan;
    }
}
?>