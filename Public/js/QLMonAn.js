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
                        location.href = 'QLMonAn.php?page=1';
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

// MỞ MODAL ĐỔI MẬT KHẨU
function openChangePasswordModal() {
    document.getElementById("passwordModal").classList.add("active");
    document.getElementById("passwordForm").reset();
    resetPasswordValidation();
}

// ĐÓNG MODAL ĐỔI MẬT KHẨU
function closePasswordModal() {
    document.getElementById("passwordModal").classList.remove("active");
}

// TOGGLE HIỆN/ẨN MẬT KHẨU
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const toggleBtn = input.parentElement.querySelector('.password-toggle i');

    if (input.type === 'password') {
        input.type = 'text';
        toggleBtn.className = 'fa-solid fa-eye-slash';
    } else {
        input.type = 'password';
        toggleBtn.className = 'fa-solid fa-eye';
    }
}

// CHECK ĐỘ MẠNH MẬT KHẨU
function checkPasswordStrength(password) {
    const strengthIndicator = document.getElementById('passwordStrength');
    const lengthReq = document.getElementById('req-length');

    if (password.length === 0) {
        strengthIndicator.style.display = 'none';
        lengthReq.classList.remove('valid');
        return;
    }

    strengthIndicator.style.display = 'block';
    let strength = 0;
    let feedback = [];

    if (password.length >= 6) {
        strength += 1;
        lengthReq.classList.add('valid');
    } else {
        lengthReq.classList.remove('valid');
    }

    if (password.length >= 8) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/[a-z]/.test(password)) strength += 1;
    if (/[0-9]/.test(password)) strength += 1;
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;

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

// RESET PASSWORD VALIDATION
function resetPasswordValidation() {
    document.getElementById('passwordStrength').style.display = 'none';
    document.querySelectorAll('.password-requirements li').forEach(li => {
        li.classList.remove('valid');
    });
}

// VALIDATE PASSWORD FORM
function validatePasswordForm() {
    const oldPassword = document.getElementById('old_password').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    const matchReq = document.getElementById('req-match');
    const differentReq = document.getElementById('req-different');

    let isValid = true;
    let errors = [];

    // Check if passwords match
    if (newPassword !== confirmPassword) {
        matchReq.classList.remove('valid');
        errors.push('Mật khẩu xác nhận không khớp!');
        isValid = false;
    } else {
        matchReq.classList.add('valid');
    }

    // Check minimum length
    if (newPassword.length < 6) {
        errors.push('Mật khẩu phải ít nhất 6 ký tự!');
        isValid = false;
    }

    // Check if new password is different from old
    if (oldPassword === newPassword && oldPassword.length > 0) {
        differentReq.classList.remove('valid');
        errors.push('Mật khẩu mới không được trùng với mật khẩu cũ!');
        isValid = false;
    } else {
        differentReq.classList.add('valid');
    }

    return { isValid, errors };
}

// AJAX Pagination
function loadPage(page) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `QLMonAn.php?ajax=pagination&page=${page}`, true);
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

// XỬ LÝ FORM ĐỔI MẬT KHẨU
document.addEventListener("DOMContentLoaded", function () {
    // Password strength checker
    const newPasswordInput = document.getElementById('new_password');
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });
    }

    // Password form submission
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
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

            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';
            submitBtn.disabled = true;

            const formData = new FormData(this);

            fetch('QLMonAn.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        closePasswordModal();
                        // Có thể logout user sau khi ResetPass thành công
                        // window.location.href = 'logout.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Thất bại!',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Không thể kết nối đến server. Vui lòng thử lại.'
                });
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});

// ĐÓNG MODAL KHI NHẤN NGOÀI
window.addEventListener("click", function (e) {
    const passwordModal = document.getElementById("passwordModal");
    if (e.target === passwordModal) {
        closePasswordModal();
    }
});

