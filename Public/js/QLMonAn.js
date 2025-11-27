// Public/js/QLMonAn.js

// Mở modal thêm món
function openDishAddModal() {
    document.getElementById('dishModalTitle').textContent = 'Thêm món ăn mới';
    document.getElementById('formAction').value = 'add';
    document.getElementById('dishForm').reset();
    document.getElementById('dishId').value = '';
    document.getElementById('trang_thai').value = 'Còn món';
    document.getElementById('dishModal').classList.add('active');
}

// Mở modal sửa món
function openDishEditModal(id, ten, gia, moTa, trangThai, idNhom) {
    document.getElementById('dishModalTitle').textContent = 'Sửa thông tin món ăn';
    document.getElementById('formAction').value = 'update';
    document.getElementById('dishId').value = id;
    document.getElementById('ten_mon').value = ten;
    document.getElementById('gia_tien').value = gia;
    document.getElementById('mo_ta').value = moTa || '';
    document.getElementById('trang_thai').value = trangThai || 'Còn món';
    document.getElementById('id_nhom').value = idNhom || '';
    document.getElementById('dishModal').classList.add('active');
}

// Đóng modal
function closeDishModal() {
    document.getElementById('dishModal').classList.remove('active');
}

// Đóng khi click ra ngoài
window.addEventListener('click', function(e) {
    const modal = document.getElementById('dishModal');
    if (e.target === modal) {
        closeDishModal();
    }
});

// Xác nhận xóa
function confirmDishDelete(id, tenMon) {
    Swal.fire({
        title: 'Xóa món ăn?',
        html: `<p>Bạn có chắc chắn muốn xóa món: <strong>${tenMon}</strong>?</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Xóa ngay',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`QLMonAn.php?delete=${id}`)
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Đã xóa!',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                })
                .catch(() => {
                    Swal.fire('Lỗi!', 'Không thể xóa món ăn này.', 'error');
                });
        }
    });
}

// Submit form
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('dishForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const giaTien = parseFloat(document.getElementById('gia_tien').value || 0);
        if (giaTien < 1000) {
            Swal.fire('Cảnh báo', 'Giá tiền phải lớn hơn 1.000₫', 'warning');
            return;
        }

        const formData = new FormData(this);
        const action = document.getElementById('formAction').value;

        fetch('QLMonAn.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: data.message || (action === 'add' ? 'Món ăn đã được thêm.' : 'Cập nhật món thành công.'),
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => {
                        closeDishModal();
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
            .catch(() => {
                Swal.fire('Lỗi!', 'Không thể kết nối đến server.', 'error');
            });
    });
});

// User menu
function toggleUserMenu(event) {
    event?.stopPropagation();
    const menu = document.getElementById('userMenu');
    const arrow = document.querySelector('.user-profile .arrow');
    menu.classList.toggle('active');
    arrow?.classList.toggle('active');
}

document.addEventListener('click', function(e) {
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

