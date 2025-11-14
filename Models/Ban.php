<?php
require "Repository/Database.php";
class Ban{
    private $conn;

    private $id_ban;
    private $suc_chua;
    private $trang_thai;

    public function __construct(){
        $this->conn = Database::connect();
    }

    public function getAll(){
        $query = "SELECT id_ban, suc_chua, trang_thai FROM ban ORDER BY id_ban";
        $result = $this->conn->query($query);

        $bans = [];
        while ($row = $result->fetch_assoc()) {
            $bans[] = $row;
        }
        return $bans;
    }

}