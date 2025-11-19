<?php
session_start();
require_once "Boundary/BanUI.php";

$ui = new BanUI();  // Tạo đối tượng UI

// ==================== XỬ LÝ HÀNH ĐỘNG ====================
// Thêm bàn
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $result = $ui->banController->addTable();
    $_SESSION['msg'] = $result['message'];
    header("Location: index.php"); exit;
}

// Sửa bàn
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $result = $ui->banController->editTable();
    $_SESSION['msg'] = $result['message'];
    header("Location: index.php"); exit;
}

// Xóa bàn
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $result = $ui->banController->delete($_GET['id']);
    $_SESSION['msg'] = $result['message'];
    header("Location: index.php"); exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý bàn - Nhà hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 20px 0; }
        .card { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .badge { font-size: 0.9em; padding: 0.5em 1em; }
    </style>
</head>
<body>
<div class="container">

    <h1 class="text-center my-4 text-primary">Quản lý bàn ăn</h1>

    <!-- Thông báo thành công / lỗi -->
    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>

    <!-- Nút thêm bàn -->
    <?php if (!isset($_GET['action'])): ?>
        <div class="mb-4 text-end">
            <a href="?action=add" class="btn btn-success btn-lg">
                Thêm bàn mới
            </a>
        </div>
    <?php endif; ?>

    <!-- Form Thêm bàn -->
    <?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Thêm bàn mới</h4>
            </div>
            <div class="card-body">
                <?php $ui->showAddForm(); ?>
                <a href="index.php" class="btn btn-secondary mt-3">Quay lại danh sách</a>
            </div>
        </div>

    <!-- Form Sửa bàn -->
    <?php elseif (isset($_GET['action']) && $_GET['action'] === 'edit' && !empty($_GET['id'])): ?>
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">Sửa thông tin bàn</h4>
            </div>
            <div class="card-body">
                <?php $ui->showEditForm(); ?>
                <a href="index.php" class="btn btn-secondary mt-3">Quay lại</a>
            </div>
        </div>

    <!-- Danh sách bàn (mặc định) -->
    <?php else: ?>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Danh sách bàn ăn</h4>
            </div>
            <div class="card-body p-0">
                <?php $ui->showTable(); ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>