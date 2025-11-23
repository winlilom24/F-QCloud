<?php
// classes/Database.php
class Database {
    private static $conn = null;
    private $config;

    private function __construct() {
        $this->config = include __DIR__ . '/../Resources/config.php';
    }

    // Chỉ cho phép gọi từ lớp khác
    public static function connect() {
        if (self::$conn === null) {
            $db = new self(); // Tạo tạm để đọc config
            self::$conn = new mysqli(
                $db->config['host'],
                $db->config['username'],
                $db->config['password'],
                $db->config['db_name']
            );

            if (self::$conn->connect_error) {
                die("Lỗi kết nối CSDL: " . self::$conn->connect_error);
            }

            self::$conn->set_charset("utf8mb4");
        }
        return self::$conn;
    }

    // (Tùy chọn) Đóng kết nối khi cần
    public static function close() {
        if (self::$conn) {
            self::$conn->close();
            self::$conn = null;
        }
    }
}
?>