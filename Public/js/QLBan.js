// Public/js/QLBan.js

function openTableAddModal() {
    document.getElementById('tableModalTitle').textContent = 'Thêm bàn mới';
    document.getElementById('formAction').value = 'add';
    document.getElementById('tableForm').reset();
    document.getElementById('tableId').value = '';
    document.getElementById('trang_thai').value = 'Trống';
    document.getElementById('tableModal').classList.add('active');
}

function openTableEditModal(id, sucChua, trangThai) {
    document.getElementById('tableModalTitle').textContent = 'Cập nhật thông tin bàn';
    document.getElementById('formAction').value = 'update';
    document.getElementById('tableId').value = id;
    document.getElementById('suc_chua').value = sucChua;
    document.getElementById('trang_thai').value = trangThai || 'Trống';
    document.getElementById('tableModal').classList.add('active');
}

function closeTableModal() {
    document.getElementById('tableModal').classList.remove('active');
}

window.addEventListener('click', function (e) {
    const modal = document.getElementById('tableModal');
    if (e.target === modal) {
        closeTableModal();
    }
});

function confirmTableDelete(id) {
    Swal.fire({
        title: 'Xóa bàn?',
        text: 'Bàn sẽ bị xóa nếu đang ở trạng thái trống.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`QLBan.php?delete=${id}`)
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Đã xóa!',
                        timer: 1600,
                        showConfirmButton: false
                    }).then(() => location.reload());
                })
                .catch(() => {
                    Swal.fire('Lỗi!', 'Không thể xóa bàn này.', 'error');
                });
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('tableForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const sucChua = parseInt(document.getElementById('suc_chua').value, 10) || 0;
        if (sucChua < 1) {
            Swal.fire('Cảnh báo', 'Sức chứa phải lớn hơn 0.', 'warning');
            return;
        }

        const formData = new FormData(this);
        const action = document.getElementById('formAction').value;

        fetch('QLBan.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: action === 'add' ? 'Đã thêm bàn' : 'Cập nhật thành công',
                        text: data.message || '',
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => {
                        closeTableModal();
                        location.href = 'QLBan.php?page=1';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Thao tác thất bại',
                        text: data.message || 'Vui lòng thử lại.'
                    });
                }
            })
            .catch(() => {
                Swal.fire('Lỗi!', 'Không thể kết nối đến server.', 'error');
            });
    });
});

function toggleUserMenu(event) {
    event?.stopPropagation();
    const menu = document.getElementById('userMenu');
    const arrow = document.querySelector('.user-profile .arrow');
    menu?.classList.toggle('active');
    arrow?.classList.toggle('active');
}

document.addEventListener('click', function (e) {
    const profile = document.querySelector('.user-profile');
    if (profile && !profile.contains(e.target)) {
        document.getElementById('userMenu')?.classList.remove('active');
        document.querySelector('.user-profile .arrow')?.classList.remove('active');
    }
});

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
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Thành công!', 'Mật khẩu đã được thay đổi.', 'success');
        }
    });
}

// AJAX Pagination
function loadPage(page) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `QLBan.php?ajax=pagination&page=${page}`, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);

            // Cập nhật table body
            const tbody = document.querySelector('.employee-table tbody');
            tbody.innerHTML = response.tableBody;

            // Cập nhật pagination
            const paginationWrapper = document.querySelector('.pagination-wrapper');
            if (paginationWrapper) {
                paginationWrapper.innerHTML = response.pagination;
            } else if (response.pagination) {
                // Nếu chưa có pagination wrapper, thêm vào
                const tableWrapper = document.querySelector('.table-wrapper');
                const newPagination = document.createElement('div');
                newPagination.className = 'pagination-wrapper';
                newPagination.innerHTML = response.pagination;
                tableWrapper.parentNode.insertBefore(newPagination, tableWrapper.nextSibling);
            }

            // Scroll to top of table
            document.querySelector('.table-wrapper').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    };
    xhr.send();
}

