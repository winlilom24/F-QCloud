<?php
require_once __DIR__ . '/../Repository/Database.php';

class Ban {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    public function getByFloor($id_tang) {
    $id_tang = (int)$id_tang;
    $query = "SELECT id_ban, suc_chua, trang_thai 
              FROM ban 
              WHERE id_tang = ?
              ORDER BY id_ban";

    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $id_tang);
    $stmt->execute();
    $result = $stmt->get_result();

    $bans = [];
    while ($row = $result->fetch_assoc()) {
        $bans[] = $row;
    }
    return $bans;
}

    public function getAll() {
        $query = "SELECT id_ban, suc_chua, trang_thai FROM ban ORDER BY id_ban DESC";
        $result = $this->conn->query($query);
        $bans = [];
        while ($row = $result->fetch_assoc()) {
            $bans[] = $row;
        }
        return $bans;
    }

    public function getAllPaginated($offset = 0, $limit = 5) {
        $query = "SELECT id_ban, suc_chua, trang_thai FROM ban ORDER BY id_ban DESC LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $bans = [];
        while ($row = $result->fetch_assoc()) {
            $bans[] = $row;
        }
        return $bans;
    }

    public function countAll() {
        $query = "SELECT COUNT(*) as total FROM ban";
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }

    // SỬA: Thiếu prepare + bind + return
    public function getBan($id) {
        $id = (int)$id;
        $query = "SELECT id_ban, suc_chua, trang_thai FROM ban WHERE id_ban = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc(); // trả về 1 bàn hoặc null
    }

    public function create($suc_chua, $trang_thai = 'Trống') {
        $suc_chua = (int)$suc_chua;
        $trang_thai = in_array($trang_thai, ['Trống', 'Đang phục vụ'], true) ? $trang_thai : 'Trống';

        if ($suc_chua < 1) {
            return ['success' => false, 'message' => 'Sức chứa không hợp lệ!'];
        }

        $query = "INSERT INTO ban (suc_chua, trang_thai) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("is", $suc_chua, $trang_thai);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Thêm bàn thành công!'];
        } else {
            return ['success' => false, 'message' => 'Thêm bàn thất bại!'];
        }
    }

    public function edit($id_ban, $suc_chua, $trang_thai = 'Trống') {
        $id_ban = (int)$id_ban;
        $suc_chua = (int)$suc_chua;
        $trang_thai = in_array($trang_thai, ['Trống', 'Đang phục vụ'], true) ? $trang_thai : 'Trống';

        if ($id_ban <= 0 || $suc_chua < 1) {
            return ['success' => false, 'message' => 'Dữ liệu không hợp lệ!'];
        }

        $query = "UPDATE ban SET suc_chua = ?, trang_thai = ? WHERE id_ban = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isi", $suc_chua, $trang_thai, $id_ban);

        if ($stmt->execute()) {
            return $stmt->affected_rows > 0
                ? ['success' => true, 'message' => 'Cập nhật thành công!']
                : ['success' => false, 'message' => 'Không có thay đổi!'];
        }
        return ['success' => false, 'message' => 'Cập nhật thất bại!'];
    }

    public function delete($id_ban) {
        $id_ban = (int)$id_ban;
        $query = "DELETE FROM ban WHERE id_ban = ? AND trang_thai = 'Trống'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_ban);

        if ($stmt->execute()) {
            return $stmt->affected_rows > 0
                ? ['success' => true, 'message' => 'Xóa bàn thành công!']
                : ['success' => false, 'message' => 'Không thể xóa (bàn đang có khách)!'];
        }
        return ['success' => false, 'message' => 'Lỗi xóa bàn!'];
    }
}