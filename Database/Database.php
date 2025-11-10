<?php
class Database {
    private $conn;
    private $config;

    // Nhận đường dẫn file config khi khởi tạo
    public function __construct($configPath) {
        $this->config = include($configPath);
    }

    // Kết nối tới CSDL
    public function connect() {
        $this->conn = null;
        try {
            // Tạo kết nối 
            $this->conn = new mysqli(
                $this->config['host'],
                $this->config['username'],
                $this->config['password'],
                $this->config['db_name']
            );

            // Kiểm tra lỗi kết nối
            if ($this->conn->connect_error) {
                throw new Exception("Lỗi kết nối: " . $this->conn->connect_error);
            }
        } catch (Exception $e) {
            echo "❌ " . $e->getMessage();
        }

        return $this->conn;
    }

    // Đóng kết nối
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
