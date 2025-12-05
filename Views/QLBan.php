<?php
// Views/QLBan.php
session_start();
require_once __DIR__ . '/../Boundary/BanUI.php';

$ui = new BanUI();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    ob_clean();
    header('Content-Type: application/json');

    if ($_POST['action'] === 'add') {
        $result = $ui->addTable();
    } elseif ($_POST['action'] === 'update') {
        $result = $ui->editTable();
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
    <link rel="stylesheet" href="../Public/css/QLNV.css">
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
                        <div class="user-name">Admin</div>
                        <div class="user-role">Quản trị viên</div>
                    </div>
                    <i class="fa-solid fa-caret-down arrow"></i>

                    <div class="user-menu" id="userMenu">
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../Public/js/QLBan.js"></script>
</body>
</html>

