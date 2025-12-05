<?php
// Views/QLBan.php
session_start();
require_once __DIR__ . '/../../Boundary/BanUI.php';

// Lấy thông tin quản lý hiện tại
require_once __DIR__ . '/../../Controller/QLNVController.php';
$quanLyController = new QLNVController();
$quanLyInfo = $quanLyController->getUserInfo($_SESSION['user_id'] ?? 1);

$ui = new BanUI();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    ob_clean();
    header('Content-Type: application/json');

    if ($_POST['action'] === 'add') {
        $result = $ui->addTable();
    } elseif ($_POST['action'] === 'update') {
        $result = $ui->editTable();
    } elseif ($_POST['action'] === 'change_password') {
        // Sử dụng QLNVController để ResetPass
        $quanLyController = new QLNVController();
        $result = $quanLyController->doiMatKhau($_SESSION['user_id'] ?? 1, $_POST['old_password'] ?? '', $_POST['new_password'] ?? '');
    } else {
        $result = ['success' => false, 'message' => 'Hành động không hợp lệ'];
    }

    echo json_encode($result);
    exit;
}

if (isset($_GET['delete'])) {
    $result = $ui->delete((int)$_GET['delete']);
    echo "<script>
        alert('" . ($result['success'] ? 'Xóa bàn thành công!' : ($result['message'] ?? 'Xóa bàn thất bại!')) . "');
        window.location='QLBan.php';
    </script>";
    exit;
}

// XỬ LÝ AJAX PHÂN TRANG
if (isset($_GET['ajax']) && $_GET['ajax'] === 'pagination') {
    ob_clean();
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $result = $ui->banController->getTablePaginated($page);
    $bans = $result['data'];
    $pagination = $result['pagination'];

    // Trả về HTML cho table body và pagination
    ob_start();
    if (empty($bans)): ?>
        <tr>
            <td colspan="4" class="empty-state">
                <i class="fa-solid fa-table"></i>
                <p>Chưa có bàn nào</p>
                <small>Nhấn "Thêm bàn mới" để bắt đầu quản lý</small>
            </td>
        </tr>
    <?php else: foreach ($bans as $ban):
        $status = $ban['trang_thai'] ?? 'Trống';
        $isFree = $status === 'Trống';
        $statusClass = $isFree ? 'role-badge dish-available' : 'role-badge dish-soldout';
    ?>
        <tr>
            <td>
                <div class="user-info">
                    <div class="avatar"><?= (int)$ban['id_ban'] ?></div>
                    <div class="user-text">
                        <strong>Bàn #<?= (int)$ban['id_ban'] ?></strong>
                        <span>ID: <?= (int)$ban['id_ban'] ?></span>
                    </div>
                </div>
            </td>
            <td><span class="price-tag"><?= (int)$ban['suc_chua'] ?> chỗ</span></td>
            <td><span class="<?= $statusClass ?>"><?= htmlspecialchars($status) ?></span></td>
            <td>
                <a class="btn-action edit" href="javascript:void(0)"
                   onclick="openTableEditModal(
                       <?= (int)$ban['id_ban'] ?>,
                       <?= (int)$ban['suc_chua'] ?>,
                       '<?= htmlspecialchars($status, ENT_QUOTES) ?>'
                   )" title="Sửa bàn">
                    <i class="fa-solid fa-pen"></i>
                </a>
                <a class="btn-action delete" href="javascript:void(0)"
                   onclick="confirmTableDelete(<?= (int)$ban['id_ban'] ?>)"
                   title="Xóa bàn">
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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bàn - FQCloud</title>
    <link rel="stylesheet" href="../../Public/css/QLNV.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .modal { display: none; position: fixed; z-index: 1000; inset: 0; background: rgba(0,0,0,0.45); justify-content: center; align-items: center; padding: 20px; }
        .modal.active { display: flex; }
        .modal-content { background: #fff; border-radius: 20px; width: 90%; max-width: 480px; box-shadow: 0 20px 60px rgba(15, 23, 42, 0.25); animation: modalShow .25s ease-out; }
        @keyframes modalShow { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-header { padding: 20px 26px; background: linear-gradient(120deg, #1f6fff, #3b8bff); color: #fff; border-radius: 20px 20px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .modal-body { padding: 28px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #303952; }
        .form-group input, .form-group select { width: 100%; padding: 12px 14px; border-radius: 12px; border: 1px solid #dfe6fd; font-size: 15px; transition: border .2s; }
        .form-group input:focus, .form-group select:focus { border-color: #1f6fff; outline: none; }
        .table-panel .avatar { background: #eef3ff; color: #1f6fff; }
        .btn-action.delete { color: #e63946; }
        .close { font-size: 28px; cursor: pointer; opacity: 0.85; }
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

        /* Căn chỉnh cột trong table QLBan */
        .table-panel .employee-table th:nth-child(1) {
            width: 30%;
            text-align: left;
        }

        .table-panel .employee-table th:nth-child(2) {
            width: 25%;
            text-align: center;
        }

        .table-panel .employee-table th:nth-child(3) {
            width: 25%;
            text-align: center;
        }

        .table-panel .employee-table th:nth-child(4) {
            width: 20%;
            text-align: center;
        }

        .table-panel .employee-table td:nth-child(1) {
            text-align: left;
        }

        .table-panel .employee-table td:nth-child(2) {
            text-align: center;
        }

        .table-panel .employee-table td:nth-child(3) {
            text-align: center;
        }

        .table-panel .employee-table td:nth-child(4) {
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
            <li class="active"><a href="QLBan.php"><i class="fa-solid fa-table"></i> Quản lý bàn</a></li>
            <li><a href="QLMonAn.php"><i class="fa-solid fa-bowl-food"></i> Quản lý món ăn</a></li>
            <li><a href="QLDoanhThu.php"><i class="fa-solid fa-chart-line"></i> Doanh thu</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="page-header">
            <div class="page-header__info">
                <p class="eyebrow">Không gian phục vụ</p>
                <h1>Quản lý bàn</h1>
                <span>Kiểm soát tình trạng bàn và sức chứa theo thời gian thực</span>
            </div>
            <div class="page-header__actions">
                <button class="icon-button" aria-label="Thông báo">
                    <i class="fa-regular fa-bell"></i>
                </button>
                <button class="btn btn-primary" onclick="openTableAddModal()">
                    <i class="fa-solid fa-square-plus"></i> Thêm bàn mới
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

        <div id="danhSachBan">
            <?php $ui->hienThiDanhSach(); ?>
        </div>
    </main>
</div>

<!-- Modal quản lý bàn -->
<div id="tableModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="tableModalTitle">Thêm bàn mới</h3>
            <span class="close" onclick="closeTableModal()">×</span>
        </div>
        <div class="modal-body">
            <form id="tableForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id_ban" id="tableId">

                <div class="form-group">
                    <label>Sức chứa (chỗ) *</label>
                    <input type="number" name="suc_chua" id="suc_chua" min="1" required>
                </div>
                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="trang_thai" id="trang_thai">
                        <option value="Trống">Trống</option>
                        <option value="Đang phục vụ">Đang phục vụ</option>
                    </select>
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Lưu lại</button>
                    <button type="button" class="btn btn-secondary" onclick="closeTableModal()">Hủy</button>
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
<script src="../../Public/js/QLBan.js"></script>
</body>
</html>

