<?php
// Bật error reporting để debug (tắt trong production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Không hiển thị lỗi ra màn hình, chỉ log

session_start();
header('Content-Type: application/json');

// Bắt đầu output buffering để bắt lỗi
ob_start();

try {
    require_once __DIR__ . '/../Boundary/OrderUI.php';
    require_once __DIR__ . '/../Boundary/HoaDonUI.php';
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Lỗi load file: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập!']);
    exit;
}

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $orderUI = new OrderUI();
    $hoaDonUI = new HoaDonUI();
    $id_nhan_vien = $_SESSION['user_id'];
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Lỗi khởi tạo: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$response = ['success' => false, 'message' => 'Action không hợp lệ!'];

switch ($action) {
    case 'test':
        // Endpoint test để kiểm tra API hoạt động
        $response = [
            'success' => true,
            'message' => 'API hoạt động bình thường!',
            'session_user_id' => $_SESSION['user_id'] ?? null,
            'server_time' => date('Y-m-d H:i:s')
        ];
        break;
        
    case 'get_order_by_ban':
        $id_ban = (int)($_POST['id_ban'] ?? $_GET['id_ban'] ?? 0);
        $order = $orderUI->getOrderByBan($id_ban);
        
        if ($order) {
            $orderDetail = $orderUI->hienThiChiTietOrder($order['id_order']);
            $response = [
                'success' => true,
                'order' => $order,
                'order_detail' => $orderDetail
            ];
        } else {
            $response = ['success' => true, 'order' => null];
        }
        break;

    case 'tao_order':
        $id_ban = (int)($_POST['id_ban'] ?? 0);
        $result = $orderUI->taoOrder($id_ban, $id_nhan_vien);
        
        if ($result['success']) {
            // Lấy lại order detail
            $orderDetail = $orderUI->hienThiChiTietOrder($result['id_order']);
            $response = [
                'success' => true,
                'id_order' => $result['id_order'],
                'order_detail' => $orderDetail
            ];
        } else {
            $response = $result;
        }
        break;

    case 'them_mon':
        $id_order = (int)($_POST['id_order'] ?? 0);
        $id_mon = (int)($_POST['id_mon'] ?? 0);
        $so_luong = (int)($_POST['so_luong'] ?? 1);
        
        if ($id_order > 0 && $id_mon > 0) {
            $result = $orderUI->themMon($id_order, $id_mon, $so_luong);
            if ($result) {
                $orderDetail = $orderUI->hienThiChiTietOrder($id_order);
                $response = [
                    'success' => true,
                    'order_detail' => $orderDetail
                ];
            } else {
                $response = ['success' => false, 'message' => 'Lỗi thêm món!'];
            }
        } else {
            $response = ['success' => false, 'message' => 'Dữ liệu không hợp lệ!'];
        }
        break;

    case 'cap_nhat_mon':
        $id_order = (int)($_POST['id_order'] ?? 0);
        $id_mon = (int)($_POST['id_mon'] ?? 0);
        $so_luong = (int)($_POST['so_luong'] ?? 0);
        
        if ($id_order > 0 && $id_mon > 0) {
            $result = $orderUI->capNhatSoLuongMon($id_order, $id_mon, $so_luong);
            
            if ($result['success']) {
                if ($result['deleted_order']) {
                    // Order đã bị xóa
                    $response = [
                        'success' => true,
                        'deleted_order' => true,
                        'message' => $result['message']
                    ];
                } else {
                    // Cập nhật thành công, lấy lại order detail
                    $orderDetail = $orderUI->hienThiChiTietOrder($id_order);
                    $response = [
                        'success' => true,
                        'deleted_order' => false,
                        'order_detail' => $orderDetail
                    ];
                }
            } else {
                $response = $result;
            }
        } else {
            $response = ['success' => false, 'message' => 'Dữ liệu không hợp lệ!'];
        }
        break;

    case 'cap_nhat_order':
        $id_order = (int)($_POST['id_order'] ?? 0);
        $mon_raw = $_POST['mon'] ?? '[]';
        $merge = isset($_POST['merge']) ? (bool)$_POST['merge'] : false;

        // Nếu mon là JSON string, parse thành array
        if (is_string($mon_raw)) {
            $mon = json_decode($mon_raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $mon = [];
            }
        } elseif (is_array($mon_raw)) {
            $mon = $mon_raw;
        } else {
            $mon = [];
        }

        // Debug log
        error_log("API cap_nhat_order: id_order=$id_order, merge=$merge, mon=" . json_encode($mon));
        error_log("POST data: " . json_encode($_POST));

        // Validation chi tiết
        $isValid = true;
        $errors = [];

        if ($id_order <= 0) {
            $isValid = false;
            $errors[] = "id_order không hợp lệ: $id_order";
        }

        if (!is_array($mon)) {
            $isValid = false;
            $errors[] = "mon không phải array: " . gettype($mon);
        } elseif (empty($mon)) {
            $isValid = false;
            $errors[] = "mon array rỗng";
        }

        if (!$isValid) {
            $response = [
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ!',
                'errors' => $errors,
                'debug' => [
                    'id_order' => $id_order,
                    'mon_type' => gettype($mon),
                    'mon_count' => is_array($mon) ? count($mon) : 'not_array',
                    'mon_values' => $mon,
                    'merge' => $merge,
                    'post_data' => $_POST
                ]
            ];
            break;
        }

        if ($id_order > 0 && is_array($mon) && !empty($mon)) {
            // Nếu merge = true, thêm món vào order hiện có
            if ($merge) {
                error_log("Merge mode: true. ID order: $id_order");
                // Lấy danh sách món hiện tại
                $orderData = $orderUI->getChiTietOrder($id_order);
                $monHienTai = [];
                if ($orderData && !empty($orderData['chitiet'])) {
                    foreach ($orderData['chitiet'] as $item) {
                        $monHienTai[$item['id_mon']] = $item['so_luong'];
                    }
                }
                error_log("Món hiện tại: " . json_encode($monHienTai));
                error_log("Món mới: " . json_encode($mon));

                // Merge với món mới
                foreach ($mon as $idMon => $soLuong) {
                    $idMon = (int)$idMon;
                    $soLuong = (int)$soLuong;
                    if (isset($monHienTai[$idMon])) {
                        $monHienTai[$idMon] += $soLuong; // Cộng thêm số lượng
                    } else {
                        $monHienTai[$idMon] = $soLuong; // Thêm món mới
                    }
                }
                error_log("Sau khi merge: " . json_encode($monHienTai));

                // Cập nhật order với danh sách đã merge
                $result = $orderUI->capNhatOrder($id_order, $monHienTai);
            } else {
                error_log("Merge mode: false. ID order: $id_order");
                // Thay thế hoàn toàn
                $result = $orderUI->capNhatOrder($id_order, $mon);
            }
            
            if ($result['success']) {
                // Debug: Kiểm tra dữ liệu sau khi cập nhật
                $orderDataSauCapNhat = $orderUI->getChiTietOrder($id_order);
                error_log("Dữ liệu sau cập nhật: " . json_encode($orderDataSauCapNhat));

                $orderDetail = $orderUI->hienThiChiTietOrder($id_order);
                $response = [
                    'success' => true,
                    'order_detail' => $orderDetail,
                    'debug_saved_data' => $orderDataSauCapNhat
                ];
            } else {
                $response = $result;
            }
        } else {
            $response = ['success' => false, 'message' => 'Dữ liệu không hợp lệ!'];
        }
        break;

    case 'xoa_order':
        $id_order = (int)($_POST['id_order'] ?? $_GET['id_order'] ?? 0);
        
        if ($id_order > 0) {
            $result = $orderUI->xoaOrder($id_order);
            $response = $result;
        } else {
            $response = ['success' => false, 'message' => 'ID order không hợp lệ!'];
        }
        break;

    case 'tao_hoa_don':
        $id_order = (int)($_POST['id_order'] ?? 0);
        $ghi_chu = $_POST['ghi_chu'] ?? null;
        
        if ($id_order > 0) {
            $result = $hoaDonUI->taoHoaDon($id_order, $ghi_chu);
            
            if ($result) {
                // Lấy thông tin hóa đơn để in
                $invoiceHtml = $hoaDonUI->inHoaDon($id_order);
                $response = [
                    'success' => true,
                    'message' => 'Tạo hóa đơn thành công!',
                    'invoice_html' => $invoiceHtml
                ];
            } else {
                $response = ['success' => false, 'message' => 'Lỗi tạo hóa đơn!'];
            }
        } else {
            $response = ['success' => false, 'message' => 'ID order không hợp lệ!'];
        }
        break;

    case 'in_hoa_don':
        $id_order = (int)($_POST['id_order'] ?? $_GET['id_order'] ?? 0);
        
        if ($id_order > 0) {
            $invoiceHtml = $hoaDonUI->inHoaDon($id_order);
            $response = [
                'success' => true,
                'invoice_html' => $invoiceHtml
            ];
        } else {
            $response = ['success' => false, 'message' => 'ID order không hợp lệ!'];
        }
        break;
}

// Xóa output buffer và trả về response
ob_clean();
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>

