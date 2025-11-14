<?php
// models/MonAn.php
require_once "classes/Database.php";

class MonAn {
    private $conn;
    private $table = 'monan';

    public function __construct() {
        $this->conn = Database::connect();
    }

    // Lấy tất cả món ăn
    public function getAll() {
        $query = "SELECT id_mon, ten_mon, gia, hinh_anh, mo_ta, trang_thai 
                  FROM {$this->table} 
                  ORDER BY id_mon";
        $result = $this->conn->query($query);

        $monan = [];
        while ($row = $result->fetch_assoc()) {
            $monan[] = $row;
        }
        return $monan;
    }

    // Lấy món đang có sẵn
    public function getCoSan() {
        $query = "SELECT id_mon, ten_mon, gia, hinh_anh 
                  FROM {$this->table} 
                  WHERE trang_thai = 'Có sẵn'";
        $result = $this->conn->query($query);

        $monan = [];
        while ($row = $result->fetch_assoc()) {
            $monan[] = $row;
        }
        return $monan;
    }
}
?>