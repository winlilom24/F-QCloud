// Public/js/QLNV.js
// Quản lý nhân viên - Modal + SweetAlert2 + AJAX

// Mở modal Thêm nhân viên
function openAddModal() {
  document.getElementById("modalTitle").textContent = "Thêm nhân viên mới";
  document.getElementById("formAction").value = "add";
  document.getElementById("employeeForm").reset();
  document.getElementById("passwordGroup").style.display = "block";
  document.getElementById("passwordConfirmGroup").style.display = "block";
  document.getElementById("tai_khoan").disabled = false;
  document.getElementById("tai_khoan").required = true;
  document.getElementById("mat_khau").required = true;
  document.getElementById("mat_khau_confirm").disabled = false;
  document.getElementById("mat_khau_confirm").required = true;
  document.getElementById("userId").value = "";

  // SĐT bắt buộc, Email không bắt buộc
  document.getElementById("sdt").required = true;
  document.getElementById("email").required = false;

  document.getElementById("employeeModal").classList.add("active");
}

// Mở modal Sửa nhân viên
function openEditModal(id, ten, sdt, email) {
  document.getElementById("modalTitle").textContent = "Sửa thông tin nhân viên";
  document.getElementById("formAction").value = "update";
  document.getElementById("userId").value = id;
  document.getElementById("ten").value = ten;
  document.getElementById("sdt").value = sdt;
  document.getElementById("email").value = email || "";

  // Ẩn mật khẩu + tài khoản khi sửa
  document.getElementById("passwordGroup").style.display = "none";
  document.getElementById("passwordConfirmGroup").style.display = "none";
  document.getElementById("tai_khoan").value = "(không thể thay đổi)";
  document.getElementById("tai_khoan").disabled = true;
  document.getElementById("tai_khoan").required = false;
  document.getElementById("mat_khau").required = false;
  document.getElementById("mat_khau_confirm").value = "";
  document.getElementById("mat_khau_confirm").disabled = true;
  document.getElementById("mat_khau_confirm").required = false;

  // SĐT bắt buộc khi sửa, Email không bắt buộc
  document.getElementById("sdt").required = true;
  document.getElementById("email").required = false;

  document.getElementById("employeeModal").classList.add("active");
}

// Đóng modal
function closeModal() {
  document.getElementById("employeeModal").classList.remove("active");
}

// Đóng khi nhấn ngoài modal
window.addEventListener("click", function (e) {
  const modal = document.getElementById("employeeModal");
  if (e.target === modal) {
    closeModal();
  }
});

// Xác nhận xóa
function confirmDelete(id, tenNhanVien) {
    Swal.fire({
        title: 'Xóa nhân viên?',
        html: `<p>Bạn có chắc chắn muốn xóa nhân viên: <strong>${tenNhanVien}</strong>?</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Xóa ngay',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`QLNV.php?delete=${id}`)
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Đã xóa!',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                })
                .catch(() => {
                    Swal.fire('Lỗi!', 'Không thể xóa nhân viên này.', 'error');
                });
        }
    });
}

// Xử lý submit form (Thêm & Sửa) bằng AJAX
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("employeeForm");
  if (!form) return;

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const action = document.getElementById("formAction").value;
    if (action === "add") {
      const password = document.getElementById("mat_khau").value;
      const confirmPassword = document.getElementById("mat_khau_confirm").value;
      if (password !== confirmPassword) {
        Swal.fire({
          icon: "error",
          title: "Mật khẩu không khớp",
          text: "Vui lòng nhập lại mật khẩu chính xác.",
        });
        return;
      }
    }

    fetch("QLNV.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          Swal.fire({
            icon: "success",
            title: "Thành công!",
            text:
              data.message ||
              (action === "add"
                ? "Nhân viên đã được thêm!"
                : "Cập nhật thành công!"),
            timer: 1800,
            showConfirmButton: false,
          }).then(() => {
            closeModal();
            location.reload();
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Thất bại!",
            text: data.message || "Đã có lỗi xảy ra. Vui lòng thử lại.",
          });
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        Swal.fire("Lỗi!", "Không thể kết nối đến server.", "error");
      });
  });
});
// HIỂN THỊ / ẨN MENU NGƯỜI DÙNG
function toggleUserMenu(event) {
  event?.stopPropagation();
  const menu = document.getElementById("userMenu");
  const arrow = document.querySelector(".user-profile .arrow");
  menu.classList.toggle("active");
  arrow?.classList.toggle("active");
}

// ĐÓNG MENU KHI NHẤN RA NGOÀI
document.addEventListener("click", function (e) {
  const profile = document.querySelector(".user-profile");
  if (profile && !profile.contains(e.target)) {
    document.getElementById("userMenu")?.classList.remove("active");
    document.querySelector(".user-profile .arrow")?.classList.remove("active");
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
  xhr.open("GET", `QLNV.php?ajax=pagination&page=${page}`, true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      const response = JSON.parse(xhr.responseText);

      // Cập nhật table body
      const tbody = document.querySelector(".employee-table tbody");
      tbody.innerHTML = response.tableBody;

      // Cập nhật pagination
      const paginationWrapper = document.querySelector(".pagination-wrapper");
      if (paginationWrapper) {
        paginationWrapper.innerHTML = response.pagination;
      } else if (response.pagination) {
        // Nếu chưa có pagination wrapper, thêm vào
        const tableWrapper = document.querySelector(".table-wrapper");
        const newPagination = document.createElement("div");
        newPagination.className = "pagination-wrapper";
        newPagination.innerHTML = response.pagination;
        tableWrapper.parentNode.insertBefore(
          newPagination,
          tableWrapper.nextSibling
        );
      }

      // Scroll to top of table
      document.querySelector(".table-wrapper").scrollIntoView({
        behavior: "smooth",
        block: "start",
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

      fetch(window.location.href, {
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
            // Có thể logout user sau khi đổi mật khẩu thành công
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

