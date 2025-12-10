<?php
session_start();

require_once __DIR__ . '/../../Controller/QLNVController.php';
require_once __DIR__ . '/../../Controller/QLDoanhThuController.php';

$_SESSION['user_id'] = $_SESSION['user_id'] ?? 1;
$userId = $_SESSION['user_id'];

$nvController = new QLNVController();
$dtController = new QLDoanhThuController();

$quanLyInfo = $nvController->getUserInfo($userId);
$nhanVienList = $nvController->getNhanVienCuaQuanLy($userId);

$thongKe = $dtController->getThongKeTongQuan();
$donHangList = $dtController->getAllDonHang();
$tongDonHang = count($donHangList);

$storeName = $_SESSION['ten_quan'] ?? ($quanLyInfo['ten_quan'] ?? 'F-QCloud');
$userName  = $quanLyInfo['ten'] ?? ($_SESSION['ten'] ?? 'Quản lý');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu bàn giao ca - FQCloud</title>
    <link rel="stylesheet" href="../../Public/css/PhieuBanGiao.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="wrapper">
    <aside class="sidebar">
        <div class="logo"><i class="fa-solid fa-cloud"></i> FQCloud</div>
        <ul class="menu">
            <li><a href="QLNV.php"><i class="fa-solid fa-users"></i> Quản lý nhân viên</a></li>
            <li><a href="QLBan.php"><i class="fa-solid fa-table"></i> Quản lý bàn</a></li>
            <li><a href="QLMonAn.php"><i class="fa-solid fa-bowl-food"></i> Quản lý món ăn</a></li>
            <li class="active"><a href="PhieuBanGiao.php"><i class="fa-solid fa-handshake"></i> Phiếu bàn giao ca</a></li>
            <li><a href="BaoCaoCuoiNgay.php"><i class="fa-solid fa-file-lines"></i> Báo cáo cuối ngày</a></li>
            <li><a href="QLDoanhThu.php"><i class="fa-solid fa-chart-line"></i> Doanh thu</a></li>
            
        </ul>
    </aside>

    <main class="main-content">
        <header class="page-header">
            <div class="page-header__info">
                <p class="eyebrow">Kết nối nhân viên</p>
                <h1>Phiếu bàn giao ca</h1>
                <span>Ghi nhận ca làm và tổng quan doanh thu để bàn giao</span>
            </div>
            <div class="page-header__actions">
                <button class="icon-button" aria-label="Thông báo">
                    <i class="fa-regular fa-bell"></i>
                </button>
                <div class="user-profile" onclick="toggleUserMenu(event)">
                    <div class="user-avatar-circle">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                        <div class="user-role">Quản lý</div>
                    </div>
                    <i class="fa-solid fa-caret-down arrow"></i>

                    <div class="user-menu" id="userMenu">
                        <a href="../../Views/Home/Page.php" class="employee-item">
                            <i class="fa-solid fa-users"></i> Nhân viên
                        </a>
                        <a href="javascript:void(0)">
                            <i class="fa-solid fa-key"></i> ResetPass
                        </a>
                        <a href="../index.php?action=logout" class="logout-item">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Đăng xuất
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="handover-wrapper" id="handoverPrintable">
            <div class="handover-grid">
                <div class="handover-card">
                    <h3>1. Thông tin ca làm / ngày</h3>
                    <p class="helper">Điền các thông tin sẽ xuất hiện trên phiếu bàn giao ca.</p>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Tên quán</label>
                            <input id="hg-store" type="text" value="<?= htmlspecialchars($storeName) ?>">
                        </div>
                        <div class="form-group">
                            <label>Ngày báo cáo</label>
                            <input id="hg-date" type="date" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label>Ca làm</label>
                            <select id="hg-shift">
                                <option>Ca sáng</option>
                                <option>Ca chiều</option>
                                <option>Ca tối</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nhân viên lập báo cáo</label>
                            <select id="hg-owner">
                                <option value=""><?= htmlspecialchars($userName) ?> (bạn)</option>
                                <?php foreach ($nhanVienList as $nv): ?>
                                    <option value="<?= htmlspecialchars($nv['ten'] ?? '') ?>">
                                        <?= htmlspecialchars($nv['ten'] ?? 'Nhân viên') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Giờ mở ca</label>
                            <input id="hg-open" type="time" value="08:00">
                        </div>
                        <div class="form-group">
                            <label>Giờ đóng ca</label>
                            <input id="hg-close" type="time" value="16:00">
                        </div>
                        <div class="form-group">
                            <label>Nhân viên bàn giao</label>
                            <select id="hg-handover">
                                <option value="">Chọn nhân viên</option>
                                <?php foreach ($nhanVienList as $nv): ?>
                                    <option value="<?= htmlspecialchars($nv['ten'] ?? '') ?>">
                                        <?= htmlspecialchars($nv['ten'] ?? 'Nhân viên') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nhân viên nhận ca</label>
                            <select id="hg-receiver">
                                <option value="">Chọn nhân viên</option>
                                <?php foreach ($nhanVienList as $nv): ?>
                                    <option value="<?= htmlspecialchars($nv['ten'] ?? '') ?>">
                                        <?= htmlspecialchars($nv['ten'] ?? 'Nhân viên') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="handover-card">
                    <h3>2. Tổng quan doanh thu</h3>
                    <p class="helper">Nhìn nhanh kết quả ca. Có thể bổ sung tiền mặt / chuyển khoản.</p>

                    <div class="summary-box">
                        <div class="summary-item">
                            <div class="label">Tổng doanh thu</div>
                            <div class="value" id="hg-total-revenue">
                                <?= number_format($thongKe['tong_doanh_thu'] ?? 0, 0, ',', '.') ?>₫
                            </div>
                        </div>
                        <div class="summary-item">
                            <div class="label">Tổng số đơn hàng</div>
                            <div class="value" id="hg-total-orders"><?= $tongDonHang ?> đơn</div>
                        </div>
                        <div class="summary-item">
                            <div class="label">Doanh thu tiền mặt</div>
                            <div class="value" id="hg-cash-display">0₫</div>
                        </div>
                        <div class="summary-item">
                            <div class="label">Doanh thu chuyển khoản</div>
                            <div class="value" id="hg-transfer-display">0₫</div>
                        </div>
                    </div>

                    <div class="form-grid" style="margin-top:14px;">
                        <div class="form-group">
                            <label>Doanh thu tiền mặt (đ)</label>
                            <input id="hg-cash" type="number" min="0" step="1000" placeholder="Nhập tổng tiền mặt">
                        </div>
                        <div class="form-group">
                            <label>Doanh thu chuyển khoản (đ)</label>
                            <input id="hg-transfer" type="number" min="0" step="1000" placeholder="Nhập tổng chuyển khoản">
                        </div>
                        <div class="form-group">
                            <label>Ghi chú bàn giao</label>
                            <textarea id="hg-note" placeholder="Các lưu ý, tồn quỹ, công nợ, hàng hóa..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="actions">
                <button class="btn btn-primary" onclick="printHandover()">
                    <i class="fa-solid fa-print"></i> In phiếu
                </button>
                <button class="btn btn-outline" onclick="alert('Đã lưu nháp (local).');">
                    <i class="fa-solid fa-save"></i> Lưu nháp
                </button>
                <span class="print-hint">Phiếu sẽ in toàn bộ nội dung trên trang này.</span>
            </div>
        </div>
    </main>
</div>

<script>
// Toggle user menu (reuse minimal code)
function toggleUserMenu(event) {
    event?.stopPropagation();
    document.getElementById("userMenu")?.classList.toggle("active");
    document.querySelector(".user-profile .arrow")?.classList.toggle("active");
}

document.addEventListener("click", function (e) {
    const profile = document.querySelector(".user-profile");
    if (profile && !profile.contains(e.target)) {
        document.getElementById("userMenu")?.classList.remove("active");
        document.querySelector(".user-profile .arrow")?.classList.remove("active");
    }
});

// Format number to VND style
function formatCurrency(num) {
    if (!num || isNaN(num)) return "0₫";
    return Number(num).toLocaleString("vi-VN") + "₫";
}

// Update display when input changes
["hg-cash", "hg-transfer"].forEach((id) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener("input", () => {
        if (id === "hg-cash") {
            document.getElementById("hg-cash-display").textContent = formatCurrency(el.value);
        } else {
            document.getElementById("hg-transfer-display").textContent = formatCurrency(el.value);
        }
    });
});

function printHandover() {
    window.print();
}
</script>
</body>
</html>

