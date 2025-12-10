<?php
// Views/QLMonAn.php
session_start();
require_once __DIR__ . '/../../Boundary/MonAnUI.php';

// Lấy thông tin quản lý hiện tại
require_once __DIR__ . '/../../Controller/QLNVController.php';
$quanLyController = new QLNVController();
$quanLyInfo = $quanLyController->getUserInfo($_SESSION['user_id'] ?? 1);

$ui = new MonAnUI();

// Xử lý AJAX thêm/sửa/ResetPass
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    ob_clean();
    header('Content-Type: application/json');

    if ($_POST['action'] === 'add') {
        $result = $ui->xuLyThemMon();
        echo json_encode($result);
    } elseif ($_POST['action'] === 'update') {
        $result = $ui->xuLySuaMon();
        echo json_encode($result);
    } elseif ($_POST['action'] === 'change_password') {
        // Sử dụng QLNVController để ResetPass
        $quanLyController = new QLNVController();
        $result = $quanLyController->doiMatKhau($_SESSION['user_id'] ?? 1, $_POST['old_password'] ?? '', $_POST['new_password'] ?? '');
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ!']);
    }
    exit;
}

// Xử lý xóa
if (isset($_GET['delete'])) {
    $result = $ui->xuLyXoaMon((int)$_GET['delete']);
    echo "<script>
        alert('" . ($result['success'] ? 'Xóa món thành công!' : ($result['message'] ?? 'Xóa món thất bại!')) . "');
        window.location='QLMonAn.php';
    </script>";
    exit;
}

// XỬ LÝ AJAX PHÂN TRANG
if (isset($_GET['ajax']) && $_GET['ajax'] === 'pagination') {
    ob_clean();
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $result = $ui->controller->getDanhSachPaginated($page);
    $monan = $result['data'];
    $pagination = $result['pagination'];

    // Trả về HTML cho table body và pagination
    ob_start();
    if (empty($monan)): ?>
        <tr>
            <td colspan="6" class="empty-state">
                <i class="fa-solid fa-bowl-rice"></i>
                <p>Chưa có món ăn nào</p>
                <small>Nhấn "Thêm món ăn" để bổ sung thực đơn</small>
            </td>
        </tr>
    <?php else: foreach ($monan as $mon):
        $price = number_format((float)$mon['gia_tien'], 0, ',', '.');
        $statusClass = ($mon['trang_thai'] === 'Còn món') ? 'role-badge dish-available' : 'role-badge dish-soldout';
        $statusLabel = $mon['trang_thai'] ?? 'Còn món';
    ?>
        <tr>
            <td>
                <div class="user-info">
                    <div class="avatar"><?= strtoupper(mb_substr($mon['ten_mon'], 0, 1)) ?></div>
                    <div class="user-text">
                        <strong><?= htmlspecialchars($mon['ten_mon']) ?></strong>
                        <span>ID: <?= $mon['id_mon'] ?></span>
                    </div>
                </div>
            </td>
            <td><span class="price-tag"><?= $price ?>₫</span></td>
            <td><?= htmlspecialchars($mon['ten_nhom'] ?? 'Chưa phân nhóm') ?></td>
            <td><span class="<?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span></td>
            <td class="description-cell"><?= htmlspecialchars($mon['mo_ta'] ?? '—') ?></td>
            <td>
                <a class="btn-action edit" href="javascript:void(0)"
                   onclick="openDishEditModal(
                       <?= (int)$mon['id_mon'] ?>,
                       '<?= htmlspecialchars(addslashes($mon['ten_mon']), ENT_QUOTES) ?>',
                       '<?= htmlspecialchars($mon['gia_tien']) ?>',
                       '<?= htmlspecialchars(addslashes($mon['mo_ta'] ?? ''), ENT_QUOTES) ?>',
                       '<?= htmlspecialchars($mon['trang_thai']) ?>',
                       '<?= $mon['id_nhom'] !== null ? (int)$mon['id_nhom'] : '' ?>'
                   )" title="Sửa món">
                    <i class="fa-solid fa-pen"></i>
                </a>
                <a class="btn-action delete" href="javascript:void(0)"
                   onclick="confirmDishDelete(<?= (int)$mon['id_mon'] ?>, '<?= htmlspecialchars(addslashes($mon['ten_mon']), ENT_QUOTES) ?>')"
                   title="Xóa món">
                    <i class="fa-solid fa-trash"></i>
                </a>
            </td>
        </tr>
    <?php endforeach; endif;

    $tableBody = ob_get_clean();

    ob_start();
    if ($pagination->getTotalPages() > 1): ?>
        <div class="pagination-wrapper">
            <?= $pagination->render('javascript:loadPage(') ?>
        </div>
    <?php endif;
    $paginationHtml = ob_get_clean();

    echo json_encode([
        'tableBody' => $tableBody,
        'pagination' => $paginationHtml,
        'currentPage' => $pagination->getCurrentPage(),
        'totalPages' => $pagination->getTotalPages()
    ]);
    exit;
}

$categories = $ui->getDanhSachNhom();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý món ăn - FQCloud</title>
    <link rel="stylesheet" href="../../Public/css/QLNV.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .modal.active { display: flex; }
        .modal-content {
            background: white; border-radius: 16px; width: 90%; max-width: 520px; box-shadow: 0 15px 40px rgba(0,0,0,0.25);
            animation: modalShow 0.3s ease-out;
        }
        @keyframes modalShow { from { transform: scale(0.85); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal-header {
            padding: 20px 25px; background: #1f6fff; color: white; border-radius: 16px 16px 0 0;
            display: flex; justify-content: space-between; align-items: center; font-size: 1.4rem;
        }
        .modal-body { padding: 30px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 12px 14px; border: 1px solid #ddd; border-radius: 10px; font-size: 15px;
            transition: border 0.3s;
        }
        .form-group textarea { resize: vertical; min-height: 90px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #1f6fff; outline: none; }
        .btn { padding: 12px 28px; border: none; border-radius: 10px; cursor: pointer; font-size: 16px; font-weight: 600; }
        .btn-primary { background: #1f6fff; color: white; }
        .btn-secondary { background: #6c757d; color: white; margin-left: 12px; }
        .close { font-size: 32px; cursor: pointer; opacity: 0.8; }
        .close:hover { opacity: 1; }

        /* Styles cho modal ResetPass */
        .password-modal .modal-content { max-width: 480px; }
        .password-input-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-input-container input {
            padding-right: 50px;
        }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 16px;
            padding: 5px;
            transition: color 0.3s;
        }
        .password-toggle:hover { color: #1f6fff; }
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
            display: none;
        }
        .password-strength.weak { color: #dc3545; }
        .password-strength.medium { color: #ffc107; }
        .password-strength.strong { color: #28a745; }
        .password-requirements {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            font-size: 13px;
        }
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }
        .password-requirements li {
            margin-bottom: 3px;
            color: #666;
        }
        .password-requirements li.valid { color: #28a745; }

        /* Căn chỉnh cột trong table QLMonAn */
        .dish-panel .employee-table th:nth-child(1) {
            width: 20%;
            text-align: left;
        }

        .dish-panel .employee-table th:nth-child(2) {
            width: 15%;
            text-align: center;
        }

        .dish-panel .employee-table th:nth-child(3) {
            width: 15%;
            text-align: center;
        }

        .dish-panel .employee-table th:nth-child(4) {
            width: 15%;
            text-align: center;
        }

        .dish-panel .employee-table th:nth-child(5) {
            width: 20%;
            text-align: left;
        }

        .dish-panel .employee-table th:nth-child(6) {
            width: 15%;
            text-align: center;
        }

        .dish-panel .employee-table td:nth-child(1) {
            text-align: left;
        }

        .dish-panel .employee-table td:nth-child(2) {
            text-align: center;
        }

        .dish-panel .employee-table td:nth-child(3) {
            text-align: center;
        }

        .dish-panel .employee-table td:nth-child(4) {
            text-align: center;
        }

        .dish-panel .employee-table td:nth-child(5) {
            text-align: left;
        }

        .dish-panel .employee-table td:nth-child(6) {
            text-align: center;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <aside class="sidebar">
        <div class="logo"><i class="fa-solid fa-cloud"></i> FQCloud</div>
        <ul class="menu">
            <li><a href="QLNV.php"><i class="fa-solid fa-users"></i> Quản lý nhân viên</a></li>
            <li><a href="QLBan.php"><i class="fa-solid fa-table"></i> Quản lý bàn</a></li>
            <li class="active"><a href="QLMonAn.php"><i class="fa-solid fa-bowl-food"></i> Quản lý món ăn</a></li>
            <li><a href="PhieuBanGiao.php"><i class="fa-solid fa-handshake"></i> Phiếu bàn giao ca</a></li>
            <li><a href="BaoCaoCuoiNgay.php"><i class="fa-solid fa-file-lines"></i> Báo cáo cuối ngày</a></li>
            <li><a href="QLDoanhThu.php"><i class="fa-solid fa-chart-line"></i> Doanh thu</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="page-header">
            <div class="page-header__info">
                <p class="eyebrow">Thực đơn F-QCloud</p>
                <h1>Quản lý món ăn</h1>
                <span>Tối ưu hóa quy trình sắp xếp thực đơn và theo dõi thực đơn hiện có của quán. </span>
            </div>
            <div class="page-header__actions">
                <button class="icon-button" aria-label="Thông báo">
                    <i class="fa-regular fa-bell"></i>
                </button>
                <button class="btn btn-primary" onclick="openDishAddModal()">
                    <i class="fa-solid fa-bowl-food"></i> Thêm món ăn
                </button>
                <div class="user-profile" onclick="toggleUserMenu(event)">
                    <div class="user-avatar-circle">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($quanLyInfo['ten'] ?? 'Admin') ?></div>
                        <div class="user-role">Quản lý</div>
                    </div>
                    <i class="fa-solid fa-caret-down arrow"></i>

                    <div class="user-menu" id="userMenu">
                        <a href="../../Views/Home/Page.php" class="employee-item">
                            <i class="fa-solid fa-users"></i> Nhân viên
                        </a>
                        <a href="javascript:void(0)" onclick="openChangePasswordModal()">
                            <i class="fa-solid fa-key"></i> ResetPass
                        </a>
                        <a href="logout.php" class="logout-item">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Đăng xuất
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div id="danhSachMonAn">
            <?php $ui->hienThiDanhSach(); ?>
        </div>
    </main>
</div>

<!-- MODAL THÊM & SỬA MÓN ĂN -->
<div id="dishModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="dishModalTitle">Thêm món ăn mới</h3>
            <span class="close" onclick="closeDishModal()">×</span>
        </div>
        <div class="modal-body">
            <form id="dishForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id_mon" id="dishId">

                <div class="form-group">
                    <label>Tên món *</label>
                    <input type="text" name="ten_mon" id="ten_mon" required>
                </div>
                <div class="form-group">
                    <label>Giá tiền (₫) *</label>
                    <input type="number" name="gia_tien" id="gia_tien" min="1000" step="500" required>
                </div>
                <div class="form-group">
                    <label>Nhóm món</label>
                    <select name="id_nhom" id="id_nhom">
                        <option value="">Không phân nhóm</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id_nhom'] ?>"><?= htmlspecialchars($cat['ten_nhom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="trang_thai" id="trang_thai">
                        <option value="Còn món">Còn món</option>
                        <option value="Hết món">Hết món</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="mo_ta" id="mo_ta" placeholder="Giới thiệu ngắn gọn về món..."></textarea>
                </div>

                <div style="text-align: center; margin-top: 25px;">
                    <button type="submit" class="btn btn-primary">Lưu lại</button>
                    <button type="button" class="btn btn-secondary" onclick="closeDishModal()">Hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL ĐỔI MẬT KHẨU -->
<div id="passwordModal" class="modal password-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa-solid fa-key"></i> ResetPass</h3>
            <span class="close" onclick="closePasswordModal()">×</span>
        </div>
        <div class="modal-body">
            <form id="passwordForm">
                <input type="hidden" name="action" value="change_password">

                <div class="form-group">
                    <label>Mật khẩu hiện tại *</label>
                    <div class="password-input-container">
                        <input type="password" name="old_password" id="old_password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('old_password')">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Mật khẩu mới *</label>
                    <div class="password-input-container">
                        <input type="password" name="new_password" id="new_password" minlength="6" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div id="passwordStrength" class="password-strength"></div>
                </div>

                <div class="form-group">
                    <label>Nhập lại mật khẩu mới *</label>
                    <div class="password-input-container">
                        <input type="password" name="confirm_password" id="confirm_password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="password-requirements">
                    <strong>Yêu cầu mật khẩu:</strong>
                    <ul>
                        <li id="req-length">Ít nhất 6 ký tự</li>
                        <li id="req-match">Mật khẩu xác nhận phải khớp</li>
                        <li id="req-different">Khác với mật khẩu hiện tại</li>
                    </ul>
                </div>

                <div style="text-align: center; margin-top: 25px;">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fa-solid fa-save"></i> Cập nhật mật khẩu
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closePasswordModal()">
                        <i class="fa-solid fa-times"></i> Hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../Public/js/QLMonAn.js"></script>
</body>
</html>

