<?php
require_once __DIR__ . '/../Repository/Database.php';

class DoanhThu {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    public function getAll() {
        $stmt = $this->conn->prepare("
            SELECT dt.*, hd.id_order, hd.thoi_gian, hd.trang_thai as trang_thai_hd
            FROM doanhthu dt
            LEFT JOIN hoadon hd ON dt.id_hoa_don = hd.id_hoa_don
            ORDER BY dt.ngay_tinh DESC, dt.id_doanh_thu DESC
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id_doanh_thu) {
        $stmt = $this->conn->prepare("
            SELECT dt.*, hd.id_order, hd.thoi_gian, hd.trang_thai as trang_thai_hd
            FROM doanhthu dt
            LEFT JOIN hoadon hd ON dt.id_hoa_don = hd.id_hoa_don
            WHERE dt.id_doanh_thu = ?
        ");
        $stmt->bind_param("i", $id_doanh_thu);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($id_hoa_don, $tong_tien, $ngay_tinh, $ghi_chu) {
        // Kiểm tra xem id_hoa_don có tồn tại và chưa có trong doanhthu không
        $check_stmt = $this->conn->prepare("
            SELECT dt.id_doanh_thu, hd.trang_thai
            FROM hoadon hd
            LEFT JOIN doanhthu dt ON hd.id_hoa_don = dt.id_hoa_don
            WHERE hd.id_hoa_don = ?
        ");
        $check_stmt->bind_param("i", $id_hoa_don);
        $check_stmt->execute();
        $result = $check_stmt->get_result()->fetch_assoc();

        if (!$result) {
            return ['success' => false, 'message' => 'Hóa đơn không tồn tại!'];
        }

        if ($result['id_doanh_thu']) {
            return ['success' => false, 'message' => 'Hóa đơn này đã có doanh thu!'];
        }

        if ($result['trang_thai'] !== 'Đã thanh toán') {
            return ['success' => false, 'message' => 'Hóa đơn chưa được thanh toán!'];
        }

        $stmt = $this->conn->prepare("
            INSERT INTO doanhthu (id_hoa_don, tong_tien, ngay_tinh, ghi_chu)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("idss", $id_hoa_don, $tong_tien, $ngay_tinh, $ghi_chu);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return [
                'success' => true,
                'message' => 'Thêm doanh thu thành công!',
                'id_doanh_thu' => $this->conn->insert_id
            ];
        }
        return ['success' => false, 'message' => 'Thêm doanh thu thất bại!'];
    }

    public function update($id_doanh_thu, $id_hoa_don, $tong_tien, $ngay_tinh, $ghi_chu) {
        // Kiểm tra xem id_hoa_don có tồn tại không (nếu thay đổi)
        $current = $this->getById($id_doanh_thu);
        if (!$current) {
            return ['success' => false, 'message' => 'Doanh thu không tồn tại!'];
        }

        if ($id_hoa_don != $current['id_hoa_don']) {
            $check_stmt = $this->conn->prepare("
                SELECT dt.id_doanh_thu, hd.trang_thai
                FROM hoadon hd
                LEFT JOIN doanhthu dt ON hd.id_hoa_don = dt.id_hoa_don
                WHERE hd.id_hoa_don = ? AND dt.id_doanh_thu != ?
            ");
            $check_stmt->bind_param("ii", $id_hoa_don, $id_doanh_thu);
            $check_stmt->execute();
            $result = $check_stmt->get_result()->fetch_assoc();

            if (!$result) {
                return ['success' => false, 'message' => 'Hóa đơn không tồn tại!'];
            }

            if ($result['id_doanh_thu']) {
                return ['success' => false, 'message' => 'Hóa đơn này đã có doanh thu khác!'];
            }

            if ($result['trang_thai'] !== 'Đã thanh toán') {
                return ['success' => false, 'message' => 'Hóa đơn chưa được thanh toán!'];
            }
        }

        $stmt = $this->conn->prepare("
            UPDATE doanhthu
            SET id_hoa_don = ?, tong_tien = ?, ngay_tinh = ?, ghi_chu = ?
            WHERE id_doanh_thu = ?
        ");
        $stmt->bind_param("idssi", $id_hoa_don, $tong_tien, $ngay_tinh, $ghi_chu, $id_doanh_thu);
        $stmt->execute();

        return $stmt->affected_rows > 0 ?
            ['success' => true, 'message' => 'Cập nhật doanh thu thành công!'] :
            ['success' => false, 'message' => 'Cập nhật doanh thu thất bại!'];
    }

    public function delete($id_doanh_thu) {
        $stmt = $this->conn->prepare("DELETE FROM doanhthu WHERE id_doanh_thu = ?");
        $stmt->bind_param("i", $id_doanh_thu);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public function getDoanhThuTheoNgay($ngay_bat_dau = null, $ngay_ket_thuc = null) {
        $query = "
            SELECT DATE(ngay_tinh) as ngay, SUM(tong_tien) as tong_doanh_thu, COUNT(*) as so_hoa_don
            FROM doanhthu
            WHERE 1=1
        ";
        $params = [];
        $types = "";

        if ($ngay_bat_dau) {
            $query .= " AND ngay_tinh >= ?";
            $params[] = $ngay_bat_dau;
            $types .= "s";
        }

        if ($ngay_ket_thuc) {
            $query .= " AND ngay_tinh <= ?";
            $params[] = $ngay_ket_thuc;
            $types .= "s";
        }

        $query .= " GROUP BY DATE(ngay_tinh) ORDER BY ngay DESC";

        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getHoaDonChuaCoDoanhThu() {
        $stmt = $this->conn->prepare("
            SELECT hd.id_hoa_don, hd.id_order, hd.thoi_gian, hd.trang_thai
            FROM hoadon hd
            LEFT JOIN doanhthu dt ON hd.id_hoa_don = dt.id_hoa_don
            WHERE dt.id_doanh_thu IS NULL AND hd.trang_thai = 'Đã thanh toán'
            ORDER BY hd.thoi_gian DESC
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getThongKeTongQuan() {
        $stmt = $this->conn->prepare("
            SELECT
                COUNT(*) as tong_so_ban_ghi,
                SUM(tong_tien) as tong_doanh_thu,
                AVG(tong_tien) as doanh_thu_trung_binh,
                MIN(tong_tien) as doanh_thu_thap_nhat,
                MAX(tong_tien) as doanh_thu_cao_nhat,
                MIN(ngay_tinh) as ngay_dau_tien,
                MAX(ngay_tinh) as ngay_cuoi_cung
            FROM doanhthu
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>

