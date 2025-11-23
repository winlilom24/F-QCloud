<?php
// QuanLyUI.php - Đầu file
session_start();

require_once '../Controller/QLNVController.php';

// Giả sử bạn đang đăng nhập với quản lý id = 1 (sau này lấy từ session)
$id_quan_ly = $_SESSION['user_id'] ?? 1;  // đổi thành 1 nếu bạn test với quản lý Nguyen Van A

try {
    $controller = new QLNVController();
    $nhanVien = $controller->getNhanVienCuaQuanLy($id_quan_ly);
} catch (Exception $e) {
    echo "<h3 style='color:red; text-align:center;'>Lỗi kết nối: " . htmlspecialchars($e->getMessage()) . "</h3>";
    $nhanVien = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách nhân viên - FQCloud</title>
    <link rel="stylesheet" href="../Public/css/QLNV.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="wrapper">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
            <i class="fa-solid fa-cloud"></i> FQCloud
        </div>
        <ul class="menu">
            <li class="active"><a href="#"><i class="fa-solid fa-users"></i> Quản lý nhân viên</a></li>
            <li><a href="#"><i class="fa-solid fa-table"></i> Quản lý bàn</a></li>
            <li><a href="#"><i class="fa-solid fa-bowl-food"></i> Quản lý món ăn</a></li>
        </ul>
    </aside>

    <!-- Main -->
    <main class="main-content">

        <!-- Header -->
        <header class="header">
            <h2>Danh sách nhân viên</h2>
            <div class="header-right">
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fa-solid fa-user-plus"></i> Thêm nhân viên
                </button>
                <div class="admin-box">
                    <i class="fa-solid fa-bell"></i>
                    <div class="admin-info">
                        <span class="admin-name">Quản lý</span>
                        <i class="fa-solid fa-circle-user"></i>
                    </div>
                </div>
            </div>
        </header>

        <!-- Table -->
        <div class="table-wrapper">
            <table class="employee-table">
                <thead>
                <tr>
                    <th>Tên nhân viên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Tên quán</th>
                    <th>Vai trò</th>
                    <th>Thao tác</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($nhanVien)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:50px 20px; color:#999;">
                            <i class="fa-solid fa-users-slash" style="font-size:48px; display:block; margin-bottom:16px; opacity:0.5;"></i>
                            <strong>Chưa có nhân viên nào</strong><br>
                            <small>Hãy nhấn nút "Thêm nhân viên" để bắt đầu</small>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($nhanVien as $nv): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="avatar">
                                        <?= strtoupper(mb_substr($nv['ten'], 0, 2)) ?>
                                    </div>
                                    <div class="user-text">
                                        <strong><?= htmlspecialchars($nv['ten']) ?></strong>
                                        <span>ID: <?= $nv['user_id'] ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <i class="fa-regular fa-envelope"></i>
                                <?= htmlspecialchars($nv['email'] ?? 'Chưa có') ?>
                            </td>
                            <td>
                                <i class="fa-solid fa-phone"></i>
                                <?= htmlspecialchars($nv['sdt'] ?? 'Chưa có') ?>
                            </td>
                            <td><?= htmlspecialchars($nv['ten_quan'] ?? 'Chưa đặt tên quán') ?></td>
                            <td><span class="role-badge"><?= $nv['role'] ?></span></td>
                            <td>
                                <a class="btn-action edit" href="edit_nhanvien.php?id=<?= $nv['user_id'] ?>" title="Sửa">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a class="btn-action delete" href="delete_nhanvien.php?id=<?= $nv['user_id'] ?>" 
                                   onclick="return confirm('Bạn chắc chắn muốn xóa nhân viên \"<?= htmlspecialchars($nv['ten']) ?>\"?')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Modal thêm nhân viên (tạm để trống, bạn làm sau cũng được) -->
<script>
function openAddModal() {
    alert("Chức năng thêm nhân viên sẽ được làm ở bước tiếp theo nhé!");
}
</script>
</body>
</html>