<?php
// models/Order.php
require_once __DIR__ . "/../Repository/Database.php";

class Order {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    // 1. LẤY THÔNG TIN ĐƠN HÀNG (bàn, nhân viên, thời gian...)
    public function getOrderInfo($id_order) {
        $id = (int)$id_order;

        $query = "SELECT o.*, b.suc_chua, u.ten AS ten_nhanvien 
                  FROM `order` o
                  LEFT JOIN ban b ON o.id_ban = b.id_ban
                  LEFT JOIN user u ON o.id_nhan_vien = u.user_id
                  WHERE o.id_order = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc(); // Trả về 1 dòng hoặc null
    }

    // 2. LẤY CHI TIẾT MÓN ĂN + TÍNH TỔNG TIỀN
    public function getOrderDetails($id_order) {
        $id = (int)$id_order;

        $query = "SELECT ct.id_mon, ct.so_luong, m.ten_mon, m.gia_tien AS gia
                  FROM chitietorder ct
                  JOIN monan m ON ct.id_mon = m.id_mon
                  WHERE ct.id_order = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $chitiet = [];
        $tong_tien = 0;

        while ($row = $result->fetch_assoc()) {
            $thanh_tien = $row['gia'] * $row['so_luong'];
            $row['thanh_tien'] = $thanh_tien;
            $chitiet[] = $row;
            $tong_tien += $thanh_tien;
        }

        return [
            'chitiet' => $chitiet,
            'tong_tien' => $tong_tien
        ];
    }

    // Lấy order theo id_ban (order đang xử lý)
    public function getOrderByBan($id_ban) {
        $id_ban = (int)$id_ban;
        $query = "SELECT o.*, b.suc_chua, u.ten AS ten_nhanvien 
                  FROM `order` o
                  LEFT JOIN ban b ON o.id_ban = b.id_ban
                  LEFT JOIN user u ON o.id_nhan_vien = u.user_id
                  WHERE o.id_ban = ? AND o.trang_thai = 'Đang xử lý'
                  ORDER BY o.thoi_gian DESC
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_ban);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Tạo order mới
    public function taoOrder($id_ban, $id_nhan_vien) {
        $this->conn->autocommit(false);
        try {
            // 1. Kiểm tra xem bàn đã có order chưa (nếu không phải bàn "Mang về")
            if ($id_ban > 0) {
                $checkQuery = "SELECT id_order FROM `order` WHERE id_ban = ? AND trang_thai = 'Đang xử lý' LIMIT 1";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->bind_param("i", $id_ban);
                $checkStmt->execute();
                $existingOrder = $checkStmt->get_result()->fetch_assoc();
                
                if ($existingOrder) {
                    // Đã có order, trả về order hiện có
                    $this->conn->commit();
                    return ['success' => true, 'id_order' => $existingOrder['id_order']];
                }
            }
            
            // 2. Tạo order mới
            $query = "INSERT INTO `order` (id_ban, id_nhan_vien, trang_thai) VALUES (?, ?, 'Đang xử lý')";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $id_ban, $id_nhan_vien);
            
            if (!$stmt->execute()) {
                throw new Exception("Lỗi tạo order: " . $stmt->error);
            }
            
            $id_order = $this->conn->insert_id;
            
            if (!$id_order) {
                throw new Exception("Không lấy được ID order mới tạo");
            }

            // 3. Cập nhật bàn thành Đang phục vụ (chỉ nếu không phải bàn "Mang về")
            if ($id_ban > 0) {
                $query2 = "UPDATE ban SET trang_thai = 'Đang phục vụ' WHERE id_ban = ?";
                $stmt2 = $this->conn->prepare($query2);
                $stmt2->bind_param("i", $id_ban);
                
                if (!$stmt2->execute()) {
                    throw new Exception("Lỗi cập nhật trạng thái bàn: " . $stmt2->error);
                }
            }

            $this->conn->commit();
            return ['success' => true, 'id_order' => $id_order];
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Lỗi tạo order: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi tạo order: ' . $e->getMessage()];
        } finally {
            $this->conn->autocommit(true);
        }
    }

    // Thêm món vào order
    public function themMonVaoOrder($id_order, $id_mon, $so_luong = 1) {
        $query = "INSERT INTO chitietorder (id_order, id_mon, so_luong) VALUES (?, ?, ?)
                  ON DUPLICATE KEY UPDATE so_luong = so_luong + VALUES(so_luong)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $id_order, $id_mon, $so_luong);
        return $stmt->execute();
    }

    // Cập nhật số lượng món trong order
    public function capNhatSoLuongMon($id_order, $id_mon, $so_luong) {
        $id_order = (int)$id_order;
        $id_mon = (int)$id_mon;
        $so_luong = (int)$so_luong;

        $this->conn->begin_transaction();
        try {
            if ($so_luong <= 0) {
                // Xóa món nếu số lượng <= 0
                $query = "DELETE FROM chitietorder WHERE id_order = ? AND id_mon = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("ii", $id_order, $id_mon);
                $stmt->execute();
            } else {
                // Cập nhật số lượng
                $query = "UPDATE chitietorder SET so_luong = ? WHERE id_order = ? AND id_mon = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("iii", $so_luong, $id_order, $id_mon);
                $stmt->execute();
            }

            // Kiểm tra xem order còn món nào không
            $query_check = "SELECT COUNT(*) as count FROM chitietorder WHERE id_order = ?";
            $stmt_check = $this->conn->prepare($query_check);
            $stmt_check->bind_param("i", $id_order);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            $row = $result->fetch_assoc();

            // Nếu không còn món nào, xóa order
            if ($row['count'] == 0) {
                // Lấy id_ban trước khi xóa
                $query_ban = "SELECT id_ban FROM `order` WHERE id_order = ?";
                $stmt_ban = $this->conn->prepare($query_ban);
                $stmt_ban->bind_param("i", $id_order);
                $stmt_ban->execute();
                $result_ban = $stmt_ban->get_result();
                $order = $result_ban->fetch_assoc();

                // Xóa order
                $query_del_order = "DELETE FROM `order` WHERE id_order = ?";
                $stmt_del_order = $this->conn->prepare($query_del_order);
                $stmt_del_order->bind_param("i", $id_order);
                $stmt_del_order->execute();

                // Giải phóng bàn nếu có
                if ($order && $order['id_ban'] > 0) {
                    $query_free_ban = "UPDATE ban SET trang_thai = 'Trống' WHERE id_ban = ?";
                    $stmt_free_ban = $this->conn->prepare($query_free_ban);
                    $stmt_free_ban->bind_param("i", $order['id_ban']);
                    $stmt_free_ban->execute();
                }

                $this->conn->commit();
                return ['success' => true, 'deleted_order' => true, 'message' => 'Đã xóa món và order vì không còn món nào'];
            }

            $this->conn->commit();
            return ['success' => true, 'deleted_order' => false];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Lỗi cập nhật số lượng món!'];
        }
    }

    public function capNhatOrder($id_order, $danh_sach_mon) {
        error_log("Order::capNhatOrder called with id_order=$id_order, danh_sach_mon=" . json_encode($danh_sach_mon));

        $this->conn->autocommit(false);
        try {
            // Xóa hết chi tiết cũ
            $query_del = "DELETE FROM chitietorder WHERE id_order = ?";
            $stmt_del = $this->conn->prepare($query_del);
            $stmt_del->bind_param("i", $id_order);
            $stmt_del->execute();
            error_log("Đã xóa chi tiết cũ cho order $id_order. Affected rows: " . $stmt_del->affected_rows);

            // Thêm lại các món mới (chỉ những món có số lượng > 0)
            $query_add = "INSERT INTO chitietorder (id_order, id_mon, so_luong) VALUES (?, ?, ?)";
            $stmt_add = $this->conn->prepare($query_add);
            $added_count = 0;

            foreach ($danh_sach_mon as $id_mon => $so_luong) {
                $id_mon = (int)$id_mon;
                $so_luong = (int)$so_luong;
                if ($so_luong > 0) {
                    $stmt_add->bind_param("iii", $id_order, $id_mon, $so_luong);
                    $stmt_add->execute();
                    $added_count++;
                    error_log("Đã thêm món $id_mon với số lượng $so_luong vào order $id_order");
                }
            }

            $this->conn->commit();
            error_log("Đã commit cập nhật order $id_order. Thêm $added_count món.");
            return ['success' => true, 'message' => 'Cập nhật order thành công!'];
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Lỗi cập nhật order $id_order: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi cập nhật order: ' . $e->getMessage()];
        } finally {
            $this->conn->autocommit(true);
        }
    }

// === XÓA ORDER HOÀN TOÀN ===
    public function xoaOrder($id_order) {
        $this->conn->autocommit(false);
        try {
            // 1. Lấy id_ban để cập nhật lại trạng thái bàn
            $query_ban = "SELECT id_ban FROM `order` WHERE id_order = ?";
            $stmt_ban = $this->conn->prepare($query_ban);
            $stmt_ban->bind_param("i", $id_order);
            $stmt_ban->execute();
            $result = $stmt_ban->get_result();
            $order = $result->fetch_assoc();

            if (!$order) {
                $this->conn->commit();
                return ['success' => false, 'message' => 'Order không tồn tại!'];
            }

            // 2. Xóa chi tiết order
            $query1 = "DELETE FROM chitietorder WHERE id_order = ?";
            $stmt1 = $this->conn->prepare($query1);
            $stmt1->bind_param("i", $id_order);
            if (!$stmt1->execute()) {
                throw new Exception("Lỗi xóa chi tiết order: " . $stmt1->error);
            }

            // 3. Xóa order
            $query2 = "DELETE FROM `order` WHERE id_order = ?";
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bind_param("i", $id_order);
            if (!$stmt2->execute()) {
                throw new Exception("Lỗi xóa order: " . $stmt2->error);
            }

            // 4. Cập nhật bàn về Trống (chỉ nếu không phải bàn "Mang về")
            if ($order['id_ban'] > 0) {
                $query3 = "UPDATE ban SET trang_thai = 'Trống' WHERE id_ban = ?";
                $stmt3 = $this->conn->prepare($query3);
                $stmt3->bind_param("i", $order['id_ban']);
                if (!$stmt3->execute()) {
                    throw new Exception("Lỗi cập nhật trạng thái bàn: " . $stmt3->error);
                }
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Xóa order và giải phóng bàn thành công!'];
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Lỗi xóa order: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi xóa order: ' . $e->getMessage()];
        } finally {
            $this->conn->autocommit(true);
        }
    }
}
?>