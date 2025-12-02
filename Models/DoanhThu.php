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

    public function taoDoanhThuTuHoaDon($id_hoa_don) {
        // Kiểm tra hóa đơn có tồn tại và đã thanh toán không
        $check_stmt = $this->conn->prepare("
            SELECT hd.id_hoa_don, hd.trang_thai, hd.thoi_gian,
                   (SELECT SUM(m.gia_tien * ct.so_luong)
                    FROM chitietorder ct
                    JOIN monan m ON ct.id_mon = m.id_mon
                    JOIN `order` o ON ct.id_order = o.id_order
                    WHERE o.id_order = hd.id_order) as tong_tien
            FROM hoadon hd
            WHERE hd.id_hoa_don = ?
        ");
        $check_stmt->bind_param("i", $id_hoa_don);
        $check_stmt->execute();
        $hoaDon = $check_stmt->get_result()->fetch_assoc();

        if (!$hoaDon) {
            return ['success' => false, 'message' => 'Hóa đơn không tồn tại!'];
        }

        if ($hoaDon['trang_thai'] !== 'Đã thanh toán') {
            return ['success' => false, 'message' => 'Hóa đơn chưa được thanh toán!'];
        }

        // Kiểm tra xem đã có doanh thu chưa
        $check_doanhthu = $this->conn->prepare("
            SELECT id_doanh_thu FROM doanhthu WHERE id_hoa_don = ?
        ");
        $check_doanhthu->bind_param("i", $id_hoa_don);
        $check_doanhthu->execute();
        if ($check_doanhthu->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Hóa đơn này đã có doanh thu!'];
        }

        // Tính tổng tiền
        $tong_tien = $hoaDon['tong_tien'] ?? 0;
        if ($tong_tien <= 0) {
            return ['success' => false, 'message' => 'Không thể tính tổng tiền từ hóa đơn!'];
        }

        // Lấy ngày từ hóa đơn
        $ngay_tinh = date('Y-m-d', strtotime($hoaDon['thoi_gian']));

        // Tạo doanh thu
        $stmt = $this->conn->prepare("
            INSERT INTO doanhthu (id_hoa_don, tong_tien, ngay_tinh, ghi_chu)
            VALUES (?, ?, ?, ?)
        ");
        $ghi_chu = "Tự động tạo từ hóa đơn #" . $id_hoa_don;
        $stmt->bind_param("idss", $id_hoa_don, $tong_tien, $ngay_tinh, $ghi_chu);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return [
                'success' => true,
                'message' => 'Đã tạo doanh thu thành công!',
                'id_doanh_thu' => $this->conn->insert_id,
                'tong_tien' => $tong_tien
            ];
        }
        return ['success' => false, 'message' => 'Không thể tạo doanh thu!'];
    }

    public function taoDoanhThuTuTatCaHoaDon() {
        // Lấy tất cả hóa đơn đã thanh toán nhưng chưa có doanh thu
        $hoaDonList = $this->getHoaDonChuaCoDoanhThu();
        
        if (empty($hoaDonList)) {
            return ['success' => true, 'message' => 'Không có hóa đơn nào cần tạo doanh thu!', 'count' => 0];
        }

        $successCount = 0;
        $failCount = 0;
        $totalRevenue = 0;

        foreach ($hoaDonList as $hoaDon) {
            $result = $this->taoDoanhThuTuHoaDon($hoaDon['id_hoa_don']);
            if ($result['success']) {
                $successCount++;
                $totalRevenue += $result['tong_tien'];
            } else {
                $failCount++;
            }
        }

        return [
            'success' => true,
            'message' => "Đã tạo doanh thu cho {$successCount} hóa đơn. Tổng doanh thu: " . number_format($totalRevenue, 0, ',', '.') . "₫",
            'count' => $successCount,
            'fail_count' => $failCount,
            'total_revenue' => $totalRevenue
        ];
    }

    public function getChiTietDonHangByHoaDon($id_hoa_don) {
        $stmt = $this->conn->prepare("
            SELECT
                ctdh.id_mon,
                ma.ten_mon,
                ctdh.so_luong,
                ma.gia_tien,
                (ctdh.so_luong * ma.gia_tien) as thanh_tien
            FROM chitietdonhang ctdh
            JOIN monan ma ON ctdh.id_mon = ma.id_mon
            WHERE ctdh.id_order = (
                SELECT hd.id_order
                FROM hoadon hd
                WHERE hd.id_hoa_don = ?
            )
            ORDER BY ctdh.id_mon
        ");
        $stmt->bind_param("i", $id_hoa_don);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Methods cho chi tiết thống kê
    public function getRevenueDetails($filterType, $date, $month, $year) {
        $whereClause = "";
        $params = [];
        $paramTypes = "";

        switch ($filterType) {
            case 'day':
                if ($date) {
                    $whereClause = "WHERE DATE(dt.ngay_tinh) = ?";
                    $params[] = $date;
                    $paramTypes .= "s";
                }
                break;
            case 'month':
                if ($month) {
                    $whereClause = "WHERE DATE_FORMAT(dt.ngay_tinh, '%Y-%m') = ?";
                    $params[] = $month;
                    $paramTypes .= "s";
                }
                break;
            case 'year':
                if ($year) {
                    $whereClause = "WHERE YEAR(dt.ngay_tinh) = ?";
                    $params[] = $year;
                    $paramTypes .= "i";
                }
                break;
        }

        $stmt = $this->conn->prepare("
            SELECT dt.*, hd.id_order, hd.thoi_gian, hd.trang_thai as trang_thai_hd
            FROM doanhthu dt
            LEFT JOIN hoadon hd ON dt.id_hoa_don = hd.id_hoa_don
            $whereClause
            ORDER BY dt.ngay_tinh DESC, dt.id_doanh_thu DESC
        ");

        if (!empty($params)) {
            $stmt->bind_param($paramTypes, ...$params);
        }

        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'success' => true,
            'data' => $data,
            'total' => array_sum(array_column($data, 'tong_tien'))
        ];
    }

    public function getPaidOrders() {
        $stmt = $this->conn->prepare("
            SELECT o.*, u.ten as ten_nhan_vien,
                   GROUP_CONCAT(CONCAT(ma.ten_mon, ' (', ctdh.so_luong, ')') SEPARATOR ', ') as danh_sach_mon
            FROM `order` o
            LEFT JOIN user u ON o.id_nhan_vien = u.user_id
            LEFT JOIN chitietdonhang ctdh ON o.id_order = ctdh.id_order
            LEFT JOIN monan ma ON ctdh.id_mon = ma.id_mon
            WHERE o.trang_thai = 'Đã thanh toán'
            GROUP BY o.id_order
            ORDER BY o.thoi_gian_order DESC
        ");
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'success' => true,
            'data' => $data
        ];
    }

    public function getActiveOrders() {
        $stmt = $this->conn->prepare("
            SELECT o.*, u.ten as ten_nhan_vien,
                   GROUP_CONCAT(CONCAT(ma.ten_mon, ' (', ctdh.so_luong, ')') SEPARATOR ', ') as danh_sach_mon
            FROM `order` o
            LEFT JOIN user u ON o.id_nhan_vien = u.user_id
            LEFT JOIN chitietdonhang ctdh ON o.id_order = ctdh.id_order
            LEFT JOIN monan ma ON ctdh.id_mon = ma.id_mon
            WHERE o.trang_thai != 'Đã thanh toán'
            GROUP BY o.id_order
            ORDER BY o.thoi_gian_order DESC
        ");
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'success' => true,
            'data' => $data
        ];
    }

    public function getRevenueStatsForDate($date) {
        // Lấy tổng doanh thu trong ngày
        $stmt = $this->conn->prepare("
            SELECT COALESCE(SUM(tong_tien), 0) as total_revenue,
                   COUNT(*) as total_invoices
            FROM doanhthu
            WHERE DATE(ngay_tinh) = ?
        ");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $revenueData = $stmt->get_result()->fetch_assoc();

        // Lấy số đơn hàng đã thanh toán trong ngày
        $stmt2 = $this->conn->prepare("
            SELECT COUNT(*) as paid_orders
            FROM `order`
            WHERE DATE(thoi_gian_order) = ?
            AND trang_thai = 'Đã thanh toán'
        ");
        $stmt2->bind_param("s", $date);
        $stmt2->execute();
        $paidOrdersData = $stmt2->get_result()->fetch_assoc();

        // Lấy số đơn hàng đang sử dụng (tạo trong ngày và chưa thanh toán)
        $stmt3 = $this->conn->prepare("
            SELECT COUNT(*) as active_orders
            FROM `order`
            WHERE DATE(thoi_gian_order) = ?
            AND trang_thai != 'Đã thanh toán'
        ");
        $stmt3->bind_param("s", $date);
        $stmt3->execute();
        $activeOrdersData = $stmt3->get_result()->fetch_assoc();

        return [
            'totalRevenue' => (float)$revenueData['total_revenue'],
            'totalInvoices' => (int)$revenueData['total_invoices'],
            'paidOrders' => (int)$paidOrdersData['paid_orders'],
            'activeOrders' => (int)$activeOrdersData['active_orders']
        ];
    }
}
?>

