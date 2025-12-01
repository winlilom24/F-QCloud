// Public/js/QLDoanhThu.js
// Quản lý doanh thu - Modal + SweetAlert2 + AJAX + Tab Navigation + Stats Details

// Mở modal Thêm doanh thu
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Thêm doanh thu mới';
    document.getElementById('formAction').value = 'add';
    document.getElementById('revenueForm').reset();
    document.getElementById('revenueId').value = '';
    document.getElementById('revenueModal').classList.add('active');
}

// Mở modal Sửa doanh thu
function openEditModal(id, id_hoa_don, tong_tien, ngay_tinh, ghi_chu) {
    document.getElementById('modalTitle').textContent = 'Sửa thông tin doanh thu';
    document.getElementById('formAction').value = 'update';
    document.getElementById('revenueId').value = id;
    document.getElementById('id_hoa_don').value = id_hoa_don;
    document.getElementById('tong_tien').value = tong_tien;
    document.getElementById('ngay_tinh').value = ngay_tinh;
    document.getElementById('ghi_chu').value = ghi_chu || '';

    document.getElementById('revenueModal').classList.add('active');
}

// Đóng modal
function closeModal() {
    document.getElementById('revenueModal').classList.remove('active');
}

// Đóng khi nhấn ngoài modal
window.addEventListener('click', function(e) {
    const modal = document.getElementById('revenueModal');
    if (e.target === modal) {
        closeModal();
    }
});

// Xác nhận xóa bằng SweetAlert2
function confirmDelete(revenueId, billCode) {
    Swal.fire({
        title: 'Xóa doanh thu?',
        html: `
            <div style="text-align:left; padding:10px 20px; font-size:16px;">
                <p>Bạn có chắc chắn muốn xóa doanh thu của:</p>
                <strong style="font-size:18px; color:#d33;">${billCode}</strong>
                <br><br>
                <small style="color:#888;">Hành động này <strong>không thể hoàn tác</strong>!</small>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Xóa ngay',
        cancelButtonText: 'Hủy bỏ',
        reverseButtons: true,
        buttonsStyling: true,
        padding: '2rem',
        width: '500px'
    }).then((result) => {
        if (result.isConfirmed) {
            // Gửi yêu cầu xóa
            fetch(`QLDoanhThu.php?delete=${revenueId}`)
                .then(response => response.text())
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Đã xóa!',
                        text: `Doanh thu của ${billCode} đã được xóa thành công.`,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                })
                .catch(() => {
                    Swal.fire('Lỗi!', 'Không thể xóa doanh thu này.', 'error');
                });
        }
    });
}

// Xử lý submit form (Thêm & Sửa) bằng AJAX
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('revenueForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const action = document.getElementById('formAction').value;

        // Validate dữ liệu
        const idHoaDon = document.getElementById('id_hoa_don').value;
        const tongTien = parseFloat(document.getElementById('tong_tien').value);
        const ngayTinh = document.getElementById('ngay_tinh').value;

        if (!idHoaDon) {
            Swal.fire({
                icon: 'error',
                title: 'Thiếu thông tin',
                text: 'Vui lòng nhập ID hóa đơn.'
            });
            return;
        }

        if (!tongTien || tongTien <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Dữ liệu không hợp lệ',
                text: 'Tổng tiền phải là số dương.'
            });
            return;
        }

        if (!ngayTinh) {
            Swal.fire({
                icon: 'error',
                title: 'Thiếu thông tin',
                text: 'Vui lòng chọn ngày tính.'
            });
            return;
        }

        fetch('QLDoanhThu.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: data.message || (action === 'add' ? 'Doanh thu đã được thêm!' : 'Cập nhật thành công!'),
                    timer: 1800,
                    showConfirmButton: false
                }).then(() => {
                    closeModal();
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Thất bại!',
                    text: data.message || 'Đã có lỗi xảy ra. Vui lòng thử lại.'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Lỗi!', 'Không thể kết nối đến server.', 'error');
        });
    });

    // Format số tiền khi nhập
    const tongTienInput = document.getElementById('tong_tien');
    if (tongTienInput) {
        tongTienInput.addEventListener('input', function(e) {
            // Chỉ cho phép số và dấu chấm
            let value = e.target.value.replace(/[^0-9.]/g, '');
            // Đảm bảo chỉ có một dấu chấm
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            e.target.value = value;
        });
    }
});

// HIỂN THỊ / ẨN MENU NGƯỜI DÙNG
function toggleUserMenu(event) {
    event?.stopPropagation();
    const menu = document.getElementById('userMenu');
    const arrow = document.querySelector('.user-profile .arrow');
    menu.classList.toggle('active');
    arrow?.classList.toggle('active');
}

// ĐÓNG MENU KHI NHẤN RA NGOÀI
document.addEventListener('click', function(e) {
    const profile = document.querySelector('.user-profile');
    if (profile && !profile.contains(e.target)) {
        document.getElementById('userMenu')?.classList.remove('active');
        document.querySelector('.user-profile .arrow')?.classList.remove('active');
    }
});

// MỞ MODAL ĐỔI MẬT KHẨU
function openChangePasswordModal() {
    Swal.fire({
        title: 'Đổi mật khẩu',
        html: `
            <div style="text-align:left;">
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Mật khẩu hiện tại</label>
                    <input type="password" id="oldPass" class="swal2-input" required>
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Mật khẩu mới</label>
                    <input type="password" id="newPass" class="swal2-input" minlength="6" required>
                </div>
                <div class="form-group">
                    <label>Nhập lại mật khẩu mới</label>
                    <input type="password" id="confirmPass" class="swal2-input" required>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Cập nhật',
        cancelButtonText: 'Hủy',
        preConfirm: () => {
            const oldPass = document.getElementById('oldPass').value;
            const newPass = document.getElementById('newPass').value;
            const confirmPass = document.getElementById('confirmPass').value;

            if (newPass !== confirmPass) {
                Swal.showValidationMessage('Mật khẩu mới không khớp!');
                return false;
            }
            if (newPass.length < 6) {
                Swal.showValidationMessage('Mật khẩu phải ít nhất 6 ký tự!');
                return false;
            }
            // Gọi AJAX đổi mật khẩu ở đây nếu cần
            return { oldPass, newPass };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Thành công!', 'Mật khẩu đã được thay đổi.', 'success');
        }
    });
}

// TAB NAVIGATION
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    event.target.classList.add('active');
}

// STATS DETAIL FUNCTIONS
function showRevenueDetails() {
    document.getElementById('statsModalTitle').textContent = 'Chi tiết tổng doanh thu';
    document.getElementById('filterSection').style.display = 'block';
    document.getElementById('statsDetailModal').classList.add('active');
    loadRevenueDetails();
}

function showInvoiceDetails() {
    document.getElementById('statsModalTitle').textContent = 'Chi tiết hóa đơn';
    document.getElementById('filterSection').style.display = 'none';
    document.getElementById('statsDetailModal').classList.add('active');
    loadInvoiceDetails();
}

function showPaidOrders() {
    document.getElementById('statsModalTitle').textContent = 'Đơn hàng đã thanh toán';
    document.getElementById('filterSection').style.display = 'none';
    document.getElementById('statsDetailModal').classList.add('active');
    loadPaidOrders();
}

function showActiveOrders() {
    document.getElementById('statsModalTitle').textContent = 'Đơn hàng đang sử dụng';
    document.getElementById('filterSection').style.display = 'none';
    document.getElementById('statsDetailModal').classList.add('active');
    loadActiveOrders();
}

function closeStatsModal() {
    document.getElementById('statsDetailModal').classList.remove('active');
}

function changeTimeFilter() {
    const filterType = document.getElementById('timeFilter').value;
    document.getElementById('dateFilter').style.display = filterType === 'day' ? 'block' : 'none';
    document.getElementById('monthFilter').style.display = filterType === 'month' ? 'block' : 'none';
    document.getElementById('yearFilter').style.display = filterType === 'year' ? 'block' : 'none';
}

function applyFilter() {
    loadRevenueDetails();
}

function loadRevenueDetails() {
    const filterType = document.getElementById('timeFilter').value;
    let date = null, month = null, year = null;

    if (filterType === 'day') {
        date = document.getElementById('selectedDate').value;
    } else if (filterType === 'month') {
        month = document.getElementById('selectedMonth').value;
    } else if (filterType === 'year') {
        year = document.getElementById('selectedYear').value;
    }

    fetch('QLDoanhThu.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=get_revenue_details&filter_type=${filterType}&date=${date}&month=${month}&year=${year}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderRevenueDetails(data.data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Lỗi!', 'Không thể tải dữ liệu doanh thu.', 'error');
    });
}

function loadInvoiceDetails() {
    fetch('QLDoanhThu.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_invoice_details'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderInvoiceDetails(data.data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Lỗi!', 'Không thể tải dữ liệu hóa đơn.', 'error');
    });
}

function loadPaidOrders() {
    fetch('QLDoanhThu.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_paid_orders'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderOrderDetails(data.data, 'paid');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Lỗi!', 'Không thể tải dữ liệu đơn hàng đã thanh toán.', 'error');
    });
}

function loadActiveOrders() {
    fetch('QLDoanhThu.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_active_orders'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderOrderDetails(data.data, 'active');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Lỗi!', 'Không thể tải dữ liệu đơn hàng đang sử dụng.', 'error');
    });
}

function renderRevenueDetails(data) {
    // Calculate summary
    const totalRevenue = data.reduce((sum, item) => sum + parseFloat(item.tong_doanh_thu || 0), 0);
    const totalInvoices = data.reduce((sum, item) => sum + parseInt(item.so_hoa_don || 0), 0);

    document.getElementById('statsSummary').innerHTML = `
        <div class="stats-summary-item">
            <span class="stats-summary-value">${totalInvoices}</span>
            <span class="stats-summary-label">Tổng hóa đơn</span>
        </div>
        <div class="stats-summary-item">
            <span class="stats-summary-value">${totalRevenue.toLocaleString()}₫</span>
            <span class="stats-summary-label">Tổng doanh thu</span>
        </div>
    `;

    const tableHead = document.getElementById('detailTableHead');
    const tableBody = document.getElementById('detailTableBody');

    tableHead.innerHTML = `
        <tr>
            <th>Ngày</th>
            <th>Tổng doanh thu</th>
            <th>Số hóa đơn</th>
        </tr>
    `;

    tableBody.innerHTML = data.map(item => `
        <tr>
            <td>${new Date(item.ngay).toLocaleDateString('vi-VN')}</td>
            <td><span class="amount">${parseFloat(item.tong_doanh_thu).toLocaleString()}₫</span></td>
            <td>${item.so_hoa_don}</td>
        </tr>
    `).join('');
}

function renderInvoiceDetails(data) {
    // Calculate summary
    const totalRevenue = data.reduce((sum, item) => sum + parseFloat(item.tong_tien), 0);
    const totalInvoices = data.length;

    document.getElementById('statsSummary').innerHTML = `
        <div class="stats-summary-item">
            <span class="stats-summary-value">${totalInvoices}</span>
            <span class="stats-summary-label">Tổng hóa đơn</span>
        </div>
        <div class="stats-summary-item">
            <span class="stats-summary-value">${totalRevenue.toLocaleString()}₫</span>
            <span class="stats-summary-label">Tổng doanh thu</span>
        </div>
    `;

    const tableHead = document.getElementById('detailTableHead');
    const tableBody = document.getElementById('detailTableBody');

    tableHead.innerHTML = `
        <tr>
            <th>ID</th>
            <th>Hóa đơn</th>
            <th>Tổng tiền</th>
            <th>Ngày tính</th>
            <th>Ghi chú</th>
        </tr>
    `;

    tableBody.innerHTML = data.map(item => `
        <tr>
            <td><strong>#${item.id_doanh_thu}</strong></td>
            <td>HD${String(item.id_hoa_don).padStart(3, '0')}</td>
            <td><span class="amount">${parseFloat(item.tong_tien).toLocaleString()}₫</span></td>
            <td>${new Date(item.ngay_tinh).toLocaleDateString('vi-VN')}</td>
            <td>${item.ghi_chu || 'Không có ghi chú'}</td>
        </tr>
    `).join('');
}

function renderOrderDetails(data, type) {
    // Calculate summary
    const totalOrders = data.length;

    document.getElementById('statsSummary').innerHTML = `
        <div class="stats-summary-item">
            <span class="stats-summary-value">${totalOrders}</span>
            <span class="stats-summary-label">Tổng đơn hàng</span>
        </div>
    `;

    const tableHead = document.getElementById('detailTableHead');
    const tableBody = document.getElementById('detailTableBody');

    tableHead.innerHTML = `
        <tr>
            <th>ID Đơn</th>
            <th>Bàn</th>
            <th>Nhân viên</th>
            <th>Thời gian</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
        </tr>
    `;

    tableBody.innerHTML = data.map(item => `
        <tr>
            <td><strong>#${item.id_order}</strong></td>
            <td>Bàn ${item.id_ban}</td>
            <td>${item.ten || 'Chưa phân công'}</td>
            <td>${new Date(item.thoi_gian).toLocaleString('vi-VN')}</td>
            <td><span class="amount">${item.tong_tien ? parseFloat(item.tong_tien).toLocaleString() + '₫' : 'Chưa tính'}</span></td>
            <td>
                <span class="status-badge status-${type === 'paid' ? 'paid' : 'using'}">
                    <i class="fa-solid fa-${type === 'paid' ? 'check-circle' : 'clock'}"></i>
                    ${type === 'paid' ? 'Đã thanh toán' : 'Đang sử dụng'}
                </span>
            </td>
        </tr>
    `).join('');
}