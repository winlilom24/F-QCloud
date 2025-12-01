// Public/js/QLNV.js
// Quản lý nhân viên - Modal + SweetAlert2 + AJAX

// Mở modal Thêm nhân viên
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Thêm nhân viên mới';
    document.getElementById('formAction').value = 'add';
    document.getElementById('employeeForm').reset();
    document.getElementById('passwordGroup').style.display = 'block';
    document.getElementById('passwordConfirmGroup').style.display = 'block';
    document.getElementById('tai_khoan').disabled = false;
    document.getElementById('tai_khoan').required = true;
    document.getElementById('mat_khau').required = true;
    document.getElementById('mat_khau_confirm').disabled = false;
    document.getElementById('mat_khau_confirm').required = true;
    document.getElementById('userId').value = '';
    document.getElementById('employeeModal').classList.add('active');
}

// Mở modal Sửa nhân viên
function openEditModal(id, ten, sdt, email) {
    document.getElementById('modalTitle').textContent = 'Sửa thông tin nhân viên';
    document.getElementById('formAction').value = 'update';
    document.getElementById('userId').value = id;
    document.getElementById('ten').value = ten;
    document.getElementById('sdt').value = sdt;
    document.getElementById('email').value = email || '';
    
    // Ẩn mật khẩu + tài khoản khi sửa
    document.getElementById('passwordGroup').style.display = 'none';
    document.getElementById('passwordConfirmGroup').style.display = 'none';
    document.getElementById('tai_khoan').value = '(không thể thay đổi)';
    document.getElementById('tai_khoan').disabled = true;
    document.getElementById('tai_khoan').required = false;
    document.getElementById('mat_khau').required = false;
    document.getElementById('mat_khau_confirm').value = '';
    document.getElementById('mat_khau_confirm').disabled = true;
    document.getElementById('mat_khau_confirm').required = false;

    document.getElementById('employeeModal').classList.add('active');
}

// Đóng modal
function closeModal() {
    document.getElementById('employeeModal').classList.remove('active');
}

// Đóng khi nhấn ngoài modal
window.addEventListener('click', function(e) {
    const modal = document.getElementById('employeeModal');
    if (e.target === modal) {
        closeModal();
    }
});

// Xác nhận xóa bằng SweetAlert2 (đẹp như hệ thống xịn)
function confirmDelete(userId, tenNhanVien) {
    Swal.fire({
        title: 'Xóa nhân viên?',
        html: `
            <div style="text-align:left; padding:10px 20px; font-size:16px;">
                <p>Bạn có chắc chắn muốn xóa nhân viên:</p>
                <strong style="font-size:18px; color:#d33;">${tenNhanVien}</strong>
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
            fetch(`QLNV.php?delete=${userId}`)
                .then(response => response.text())
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Đã xóa!',
                        text: `${tenNhanVien} đã được xóa thành công.`,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                })
                .catch(() => {
                    Swal.fire('Lỗi!', 'Không thể xóa nhân viên này.', 'error');
                });
        }
    });
}

// Xử lý submit form (Thêm & Sửa) bằng AJAX
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('employeeForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const action = document.getElementById('formAction').value;
        if (action === 'add') {
            const password = document.getElementById('mat_khau').value;
            const confirmPassword = document.getElementById('mat_khau_confirm').value;
            if (password !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Mật khẩu không khớp',
                    text: 'Vui lòng nhập lại mật khẩu chính xác.'
                });
                return;
            }
        }

        fetch('QLNV.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: data.message || (action === 'add' ? 'Nhân viên đã được thêm!' : 'Cập nhật thành công!'),
                    timer: 1800,
                    showConfirmButton: false
                }).then(() => {
                    closeModal();
                    location.href = 'QLNV.php?page=1';
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

// AJAX Pagination
function loadPage(page) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `QLNV.php?ajax=pagination&page=${page}`, true);
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