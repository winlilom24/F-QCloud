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

// AJAX đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    ob_clean();
    header('Content-Type: application/json');
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $result = $nvController->doiMatKhau($userId, $old, $new);
    echo json_encode($result);
    exit;
}

// Tính khoảng thời gian đông nhất và vắng nhất dựa trên số đơn theo giờ
$hourBuckets = [];
foreach ($donHangList as $dh) {
    if (empty($dh['thoi_gian_order'])) continue;
    $hour = (int)date('G', strtotime($dh['thoi_gian_order']));
    $hourBuckets[$hour] = ($hourBuckets[$hour] ?? 0) + 1;
}
ksort($hourBuckets);

$peakRange = 'Chưa có dữ liệu';
$offPeakRange = 'Chưa có dữ liệu';
if (!empty($hourBuckets)) {
    $maxHour = array_keys($hourBuckets, max($hourBuckets))[0];

    $formatRange = function ($h) {
        $start = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
        $endHour = ($h + 1) % 24;
        $end = str_pad($endHour, 2, '0', STR_PAD_LEFT) . ':00';
        return $start . ' - ' . $end;
    };
    $peakRange = $formatRange($maxHour);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo cuối ngày - FQCloud</title>
    <link rel="stylesheet" href="../../Public/css/BaoCaoCuoiNgay.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* CSS OVERRIDE để cải thiện hiển thị BaoCaoCuoiNgay */

        /* Form grids - tối ưu layout */
        .form-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important;
            gap: 12px !important;
        }

        /* Form groups - nhỏ gọn hơn */
        .form-group {
            display: flex !important;
            flex-direction: column !important;
            gap: 6px !important;
        }

        .form-group label {
            font-size: 13px !important;
            font-weight: 600 !important;
            color: #475569 !important;
            margin-bottom: 4px !important;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 8px 10px !important;
            border: 1px solid #d4d4d8 !important;
            border-radius: 8px !important;
            font-size: 13px !important;
            background: #fff !important;
            outline: none !important;
            transition: border 0.2s ease, box-shadow 0.2s ease !important;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1) !important;
        }

        .form-group textarea {
            resize: vertical !important;
            min-height: 80px !important;
        }

        /* Section cards - cải thiện spacing */
        .section-card {
            background: #f8fafc !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 12px !important;
            padding: 20px !important;
            margin-bottom: 16px !important;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05) !important;
        }

        .section-card h3 {
            margin: 0 0 8px 0 !important;
            font-size: 16px !important;
            color: #0f172a !important;
            font-weight: 600 !important;
        }

        .section-card p.helper {
            margin: 0 0 16px 0 !important;
            color: #64748b !important;
            font-size: 13px !important;
        }

        /* Actions - cải thiện button styling */
        .actions {
            margin-top: 20px !important;
            display: flex !important;
            gap: 12px !important;
            flex-wrap: wrap !important;
        }

        .actions .btn {
            padding: 10px 18px !important;
            border-radius: 8px !important;
            border: none !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease !important;
            font-size: 14px !important;
        }

        .btn-primary {
            background: #2563eb !important;
            color: #fff !important;
        }

        .btn-outline {
            background: #fff !important;
            color: #2563eb !important;
            border: 1px solid #2563eb !important;
        }

        .actions .btn:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.15) !important;
        }

        .print-hint {
            color: #64748b !important;
            font-size: 12px !important;
            margin-left: auto !important;
            align-self: center !important;
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr !important;
                gap: 10px !important;
            }

            .section-card {
                padding: 16px !important;
                margin-bottom: 12px !important;
            }

            .section-card h3 {
                font-size: 15px !important;
            }

            .form-group label {
                font-size: 12px !important;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                font-size: 12px !important;
                padding: 6px 8px !important;
            }

            .actions {
                flex-direction: column !important;
                gap: 8px !important;
            }

            .actions .btn {
                width: 100% !important;
                padding: 12px 16px !important;
            }
        }

        @media (max-width: 480px) {
            .section-card {
                padding: 12px !important;
            }

            .form-group {
                gap: 4px !important;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 8px 10px !important;
                font-size: 14px !important; /* Slightly larger for mobile touch */
            }
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
            <li><a href="QLMonAn.php"><i class="fa-solid fa-bowl-food"></i> Quản lý món ăn</a></li>
            <li><a href="PhieuBanGiao.php"><i class="fa-solid fa-handshake"></i> Phiếu bàn giao ca</a></li>
            <li class="active"><a href="BaoCaoCuoiNgay.php"><i class="fa-solid fa-file-lines"></i> Báo cáo cuối ngày</a></li>
            <li><a href="QLDoanhThu.php"><i class="fa-solid fa-chart-line"></i> Doanh thu</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="page-header">
            <div class="page-header__info">
                <p class="eyebrow">Kết nối nhân viên</p>
                <h1>Báo cáo cuối ngày</h1>
                <span>Tổng hợp hoạt động và doanh thu cuối ngày</span>
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
                        <a href="javascript:void(0)" onclick="openChangePasswordModal()">
                            <i class="fa-solid fa-key"></i> ResetPass
                        </a>
                        <a href="../index.php?action=logout" class="logout-item">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Đăng xuất
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="report-wrapper" id="endDayReport">
            <div class="section-card">
                <h3>1. Thông tin chung</h3>
                <p class="helper">Điền các thông tin cơ bản cho báo cáo cuối ngày.</p>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Tên quán</label>
                        <input id="rcn-store" type="text" value="<?= htmlspecialchars($storeName) ?>">
                    </div>
                    <div class="form-group">
                        <label>Ngày</label>
                        <input id="rcn-date" type="date" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Quản lý ghi nhận</label>
                        <input id="rcn-manager" type="text" value="<?= htmlspecialchars($userName) ?>">
                    </div>
                    <div class="form-group">
                        <label>Nhân viên làm việc trong ngày</label>
                        <select id="rcn-staff">
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

            <div class="section-card">
                <h3>2. Tổng quan hoạt động</h3>
                <p class="helper">Tóm tắt diễn biến trong ngày.</p>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Tổng số đơn trong ngày</label>
                        <input id="rcn-total-orders" type="text" value="<?= $tongDonHang ?> đơn">
                    </div>
                    <div class="form-group">
                        <label>Thời gian đông khách nhất</label>
                        <input id="rcn-peak-time" type="text" value="<?= htmlspecialchars($peakRange) ?>">
                    </div>
                    <div class="form-group">
                        <label>Món bán chạy nhất</label>
                        <input id="rcn-best-item" type="text" value="Chưa xác định (cần dữ liệu món)">
                    </div>
                    <div class="form-group">
                        <label>Món bán ít nhất</label>
                        <input id="rcn-worst-item" type="text" value="Chưa xác định (cần dữ liệu món)">
                    </div>
                </div>
            </div>

            <div class="section-card">
                <h3>3. Doanh thu cuối ngày</h3>
                <p class="helper">Tổng hợp kết quả doanh thu.</p>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Tổng doanh thu</label>
                        <input id="rcn-total-revenue" type="text" value="<?= number_format($thongKe['tong_doanh_thu'] ?? 0, 0, ',', '.') ?>₫">
                    </div>
                    <div class="form-group">
                        <label>Tiền mặt thu được</label>
                        <input id="rcn-cash" type="number" min="0" step="1000" placeholder="Nhập tiền mặt">
                    </div>
                    <div class="form-group">
                        <label>Chuyển khoản</label>
                        <input id="rcn-transfer" type="number" min="0" step="1000" placeholder="Nhập chuyển khoản">
                    </div>
                    <div class="form-group">
                        <label>Voucher/Giảm giá</label>
                        <input id="rcn-discount" type="number" min="0" step="1000" placeholder="Tổng voucher/giảm giá">
                    </div>
                    <div class="form-group">
                        <label>Doanh thu thực nhận</label>
                        <input id="rcn-net" type="text" placeholder="Sẽ tính = tiền mặt + chuyển khoản - voucher" readonly>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <h3>4. Tiền cuối ngày</h3>
                <p class="helper">Đối chiếu tiền mặt trong két.</p>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Tiền mặt phải có</label>
                        <input id="rcn-cash-expected" type="number" min="0" step="1000" placeholder="Số tiền dự kiến">
                    </div>
                    <div class="form-group">
                        <label>Tiền mặt thực tế trong két</label>
                        <input id="rcn-cash-actual" type="number" min="0" step="1000" placeholder="Số tiền đếm được">
                    </div>
                    <div class="form-group">
                        <label>Chênh lệch</label>
                        <input id="rcn-cash-diff" type="text" readonly>
                    </div>
                    <div class="form-group">
                        <label>Nếu lệch, ghi rõ lý do</label>
                        <textarea id="rcn-cash-note" placeholder="Mô tả lý do chênh lệch (nếu có)"></textarea>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <h3>5. Ký xác nhận</h3>
                <p class="helper">Thông tin ký nhận của nhân viên ca và quản lý.</p>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nhân viên ca</label>
                        <select id="rcn-staff-sign">
                            <option value="">Chọn nhân viên</option>
                            <?php foreach ($nhanVienList as $nv): ?>
                                <option value="<?= htmlspecialchars($nv['ten'] ?? '') ?>">
                                    <?= htmlspecialchars($nv['ten'] ?? 'Nhân viên') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quản lý cuối ngày</label>
                        <input id="rcn-manager-sign" type="text" value="<?= htmlspecialchars($userName) ?>">
                    </div>
                </div>
            </div>

            <div class="actions">
                <button class="btn btn-primary" onclick="printEndDayReport()">
                    <i class="fa-solid fa-print"></i> In báo cáo
                </button>
                <button class="btn btn-outline" onclick="alert('Đã lưu nháp (local).');">
                    <i class="fa-solid fa-save"></i> Lưu nháp
                </button>
                <span class="print-hint">Báo cáo sẽ in toàn bộ nội dung trên trang này.</span>
            </div>
        </div>
    </main>
</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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

function formatCurrency(num) {
    if (num === "" || num === null || isNaN(num)) return "0₫";
    return Number(num).toLocaleString("vi-VN") + "₫";
}

function recalcNet() {
    const cash = Number(document.getElementById("rcn-cash").value || 0);
    const transfer = Number(document.getElementById("rcn-transfer").value || 0);
    const discount = Number(document.getElementById("rcn-discount").value || 0);
    const net = cash + transfer - discount;
    document.getElementById("rcn-net").value = formatCurrency(net);
}

["rcn-cash", "rcn-transfer", "rcn-discount"].forEach((id) => {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener("input", recalcNet);
    }
});

function recalcDiff() {
    const expected = Number(document.getElementById("rcn-cash-expected").value || 0);
    const actual = Number(document.getElementById("rcn-cash-actual").value || 0);
    const diff = actual - expected;
    const label = diff === 0 ? "0₫" : `${diff > 0 ? "+" : ""}${diff.toLocaleString("vi-VN")}₫`;
    document.getElementById("rcn-cash-diff").value = label;
}

["rcn-cash-expected", "rcn-cash-actual"].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.addEventListener("input", recalcDiff);
});

function printEndDayReport() {
    window.print();
}

// ResetPass handlers
function openChangePasswordModal() {
    document.getElementById("passwordModal").classList.add("active");
    document.getElementById("passwordForm").reset();
    resetPasswordValidation();
}
function closePasswordModal() {
    document.getElementById("passwordModal").classList.remove("active");
}
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.parentElement.querySelector('.password-toggle i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-solid fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa-solid fa-eye';
    }
}
function checkPasswordStrength(password) {
    const strengthIndicator = document.getElementById('passwordStrength');
    if (!strengthIndicator) return;
    if (password.length === 0) {
        strengthIndicator.style.display = 'none';
        return;
    }
    strengthIndicator.style.display = 'block';
    let strength = 0;
    if (password.length >= 6) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    if (strength <= 2) {
        strengthIndicator.textContent = 'Mật khẩu yếu';
        strengthIndicator.className = 'password-strength weak';
    } else if (strength <= 4) {
        strengthIndicator.textContent = 'Mật khẩu trung bình';
        strengthIndicator.className = 'password-strength medium';
    } else {
        strengthIndicator.textContent = 'Mật khẩu mạnh';
        strengthIndicator.className = 'password-strength strong';
    }
}
document.getElementById('new_password')?.addEventListener('input', function() {
    checkPasswordStrength(this.value);
});

function resetPasswordValidation() {
    document.getElementById('passwordStrength').style.display = 'none';
    document.querySelectorAll('.password-requirements li').forEach(li => {
        li.classList.remove('valid');
    });
}

function validatePasswordForm() {
    const oldPassword = document.getElementById('old_password').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    const matchReq = document.getElementById('req-match');
    const differentReq = document.getElementById('req-different');

    let isValid = true;
    let errors = [];

    if (newPassword !== confirmPassword) {
        matchReq.classList.remove('valid');
        errors.push('Mật khẩu xác nhận không khớp!');
        isValid = false;
    } else {
        matchReq.classList.add('valid');
    }

    if (newPassword.length < 6) {
        errors.push('Mật khẩu phải ít nhất 6 ký tự!');
        isValid = false;
    } else {
        document.getElementById('req-length').classList.add('valid');
    }

    if (oldPassword === newPassword && oldPassword.length > 0) {
        differentReq.classList.remove('valid');
        errors.push('Mật khẩu mới không được trùng với mật khẩu cũ!');
        isValid = false;
    } else {
        differentReq.classList.add('valid');
    }

    return { isValid, errors };
}

document.getElementById("passwordForm")?.addEventListener("submit", function (e) {
    e.preventDefault();
    const validation = validatePasswordForm();
    if (!validation.isValid) {
        Swal.fire({
            icon: 'error',
            title: 'Lỗi xác thực',
            html: validation.errors.join('<br>')
        });
        return;
    }
    const formData = new FormData(this);
    fetch("BaoCaoCuoiNgay.php", {
        method: "POST",
        body: formData,
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: data.message || 'Đổi mật khẩu thành công!',
                    timer: 1800,
                    showConfirmButton: false
                }).then(() => closePasswordModal());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Thất bại!',
                    text: data.message || 'Đổi mật khẩu thất bại!'
                });
            }
        })
        .catch(() => Swal.fire("Lỗi!", "Không thể kết nối server.", "error"));
});

</script>
</body>
</html>

