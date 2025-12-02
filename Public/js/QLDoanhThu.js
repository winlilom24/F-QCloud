// Public/js/QLDoanhThu.js
// Quản lý doanh thu - Modal + SweetAlert2 + AJAX + Tab Navigation + Stats Details

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
  Swal.fire({
    title: "Đổi mật khẩu",
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
    confirmButtonText: "Cập nhật",
    cancelButtonText: "Hủy",
    preConfirm: () => {
      const oldPass = document.getElementById("oldPass").value;
      const newPass = document.getElementById("newPass").value;
      const confirmPass = document.getElementById("confirmPass").value;

      if (newPass !== confirmPass) {
        Swal.showValidationMessage("Mật khẩu mới không khớp!");
        return false;
      }
      if (newPass.length < 6) {
        Swal.showValidationMessage("Mật khẩu phải ít nhất 6 ký tự!");
        return false;
      }
      // Gọi AJAX đổi mật khẩu ở đây nếu cần
      return { oldPass, newPass };
    },
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire("Thành công!", "Mật khẩu đã được thay đổi.", "success");
    }
  });
}

// TAB NAVIGATION
function showTab(tabName) {
  // Hide all tabs
  document.querySelectorAll(".tab-content").forEach((tab) => {
    tab.classList.remove("active");
  });

  // Remove active class from all buttons
  document.querySelectorAll(".tab-btn").forEach((btn) => {
    btn.classList.remove("active");
  });

  // Show selected tab
  document.getElementById(tabName + "-tab").classList.add("active");
  event.target.classList.add("active");
}

// STATS DETAIL FUNCTIONS
function showRevenueDetails() {
  document.getElementById("statsModalTitle").textContent =
    "Chi tiết tổng doanh thu";
  document.getElementById("filterSection").style.display = "block";
  document.getElementById("printBtn").style.display = "inline-block";
  document.getElementById("statsDetailModal").classList.add("active");
  loadRevenueDetails();
}

function showInvoiceDetails() {
  document.getElementById("statsModalTitle").textContent = "Chi tiết hóa đơn";
  document.getElementById("filterSection").style.display = "none";
  document.getElementById("printBtn").style.display = "none";
  document.getElementById("statsDetailModal").classList.add("active");
  loadInvoiceDetails();
}

function showPaidOrders() {
  document.getElementById("statsModalTitle").textContent =
    "Đơn hàng đã thanh toán";
  document.getElementById("filterSection").style.display = "none";
  document.getElementById("printBtn").style.display = "none";
  document.getElementById("statsDetailModal").classList.add("active");
  loadPaidOrders();
}

function showActiveOrders() {
  document.getElementById("statsModalTitle").textContent =
    "Đơn hàng đang sử dụng";
  document.getElementById("filterSection").style.display = "none";
  document.getElementById("printBtn").style.display = "none";
  document.getElementById("statsDetailModal").classList.add("active");
  loadActiveOrders();
}

function closeStatsModal() {
  document.getElementById("statsDetailModal").classList.remove("active");
}

function changeTimeFilter() {
  const filterType = document.getElementById("timeFilter").value;
  document.getElementById("dateFilter").style.display =
    filterType === "day" ? "block" : "none";
  document.getElementById("monthFilter").style.display =
    filterType === "month" ? "block" : "none";
  document.getElementById("yearFilter").style.display =
    filterType === "year" ? "block" : "none";
}

function applyFilter() {
  loadRevenueDetails();
}

function loadRevenueDetails() {
  const filterType = document.getElementById("timeFilter").value;
  let date = null,
    month = null,
    year = null;

  if (filterType === "day") {
    date = document.getElementById("selectedDate").value;
  } else if (filterType === "month") {
    month = document.getElementById("selectedMonth").value;
  } else if (filterType === "year") {
    year = document.getElementById("selectedYear").value;
  }

  fetch("QLDoanhThu.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `action=get_revenue_details&filter_type=${filterType}&date=${date}&month=${month}&year=${year}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        renderRevenueDetails(data.data);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      Swal.fire("Lỗi!", "Không thể tải dữ liệu doanh thu.", "error");
    });
}

function loadInvoiceDetails() {
  fetch("QLDoanhThu.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "action=get_invoice_details",
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        renderInvoiceDetails(data.data);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      Swal.fire("Lỗi!", "Không thể tải dữ liệu hóa đơn.", "error");
    });
}

function loadPaidOrders() {
  fetch("QLDoanhThu.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "action=get_paid_orders",
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        renderOrderDetails(data.data, "paid");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      Swal.fire(
        "Lỗi!",
        "Không thể tải dữ liệu đơn hàng đã thanh toán.",
        "error"
      );
    });
}

function loadActiveOrders() {
  fetch("QLDoanhThu.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "action=get_active_orders",
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        renderOrderDetails(data.data, "active");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      Swal.fire(
        "Lỗi!",
        "Không thể tải dữ liệu đơn hàng đang sử dụng.",
        "error"
      );
    });
}

function renderRevenueDetails(data) {
  // Calculate summary
  const totalRevenue = data.reduce(
    (sum, item) => sum + parseFloat(item.tong_doanh_thu || 0),
    0
  );
  const totalInvoices = data.reduce(
    (sum, item) => sum + parseInt(item.so_hoa_don || 0),
    0
  );

  document.getElementById("statsSummary").innerHTML = `
        <div class="stats-summary-item">
            <span class="stats-summary-value">${totalInvoices}</span>
            <span class="stats-summary-label">Tổng hóa đơn</span>
        </div>
        <div class="stats-summary-item">
            <span class="stats-summary-value">${totalRevenue.toLocaleString()}₫</span>
            <span class="stats-summary-label">Tổng doanh thu</span>
        </div>
    `;

  const tableHead = document.getElementById("detailTableHead");
  const tableBody = document.getElementById("detailTableBody");

  tableHead.innerHTML = `
        <tr>
            <th>Ngày</th>
            <th>Tổng doanh thu</th>
            <th>Số hóa đơn</th>
        </tr>
    `;

  tableBody.innerHTML = data
    .map(
      (item) => `
        <tr>
            <td>${new Date(item.ngay).toLocaleDateString("vi-VN")}</td>
            <td><span class="amount">${parseFloat(
              item.tong_doanh_thu
            ).toLocaleString()}₫</span></td>
            <td>${item.so_hoa_don}</td>
        </tr>
    `
    )
    .join("");

  // Hiển thị button in nếu có dữ liệu
  if (data.length > 0) {
    document.getElementById("printBtn").style.display = "inline-block";
  }
}

function renderInvoiceDetails(data) {
  // Calculate summary
  const totalRevenue = data.reduce(
    (sum, item) => sum + parseFloat(item.tong_tien),
    0
  );
  const totalInvoices = data.length;

  document.getElementById("statsSummary").innerHTML = `
        <div class="stats-summary-item">
            <span class="stats-summary-value">${totalInvoices}</span>
            <span class="stats-summary-label">Tổng hóa đơn</span>
        </div>
        <div class="stats-summary-item">
            <span class="stats-summary-value">${totalRevenue.toLocaleString()}₫</span>
            <span class="stats-summary-label">Tổng doanh thu</span>
        </div>
    `;

  const tableHead = document.getElementById("detailTableHead");
  const tableBody = document.getElementById("detailTableBody");

  tableHead.innerHTML = `
        <tr>
            <th>ID</th>
            <th>Hóa đơn</th>
            <th>Tổng tiền</th>
            <th>Ngày tính</th>
            <th>Ghi chú</th>
        </tr>
    `;

  tableBody.innerHTML = data
    .map(
      (item) => `
        <tr>
            <td><strong>#${item.id_doanh_thu}</strong></td>
            <td>HD${String(item.id_hoa_don).padStart(3, "0")}</td>
            <td><span class="amount">${parseFloat(
              item.tong_tien
            ).toLocaleString()}₫</span></td>
            <td>${new Date(item.ngay_tinh).toLocaleDateString("vi-VN")}</td>
            <td>${item.ghi_chu || "Không có ghi chú"}</td>
        </tr>
    `
    )
    .join("");
}

function renderOrderDetails(data, type) {
  // Calculate summary
  const totalOrders = data.length;

  document.getElementById("statsSummary").innerHTML = `
        <div class="stats-summary-item">
            <span class="stats-summary-value">${totalOrders}</span>
            <span class="stats-summary-label">Tổng đơn hàng</span>
        </div>
    `;

  const tableHead = document.getElementById("detailTableHead");
  const tableBody = document.getElementById("detailTableBody");

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

  tableBody.innerHTML = data
    .map(
      (item) => `
        <tr>
            <td><strong>#${item.id_order}</strong></td>
            <td>Bàn ${item.id_ban}</td>
            <td>${item.ten || "Chưa phân công"}</td>
            <td>${new Date(item.thoi_gian).toLocaleString("vi-VN")}</td>
            <td><span class="amount">${
              item.tong_tien
                ? parseFloat(item.tong_tien).toLocaleString() + "₫"
                : "Chưa tính"
            }</span></td>
            <td>
                <span class="status-badge status-${
                  type === "paid" ? "paid" : "using"
                }">
                    <i class="fa-solid fa-${
                      type === "paid" ? "check-circle" : "clock"
                    }"></i>
                    ${type === "paid" ? "Đã thanh toán" : "Đang sử dụng"}
                </span>
            </td>
        </tr>
    `
    )
    .join("");
}

// PRINT REVENUE FUNCTION
function printRevenue() {
  const modalTitle = document.getElementById("statsModalTitle").textContent;
  const filterType = document.getElementById("timeFilter").value;
  const statsSummary = document.getElementById("statsSummary").innerHTML;
  const tableHead = document.getElementById("detailTableHead").innerHTML;
  const tableBody = document.getElementById("detailTableBody").innerHTML;

  // Lấy thông tin filter
  let filterText = "";
  if (filterType === "day") {
    const date = document.getElementById("selectedDate").value;
    if (date) {
      const dateObj = new Date(date);
      filterText = `Ngày: ${dateObj.toLocaleDateString("vi-VN")}`;
    }
  } else if (filterType === "month") {
    const month = document.getElementById("selectedMonth").value;
    if (month) {
      const dateObj = new Date(month + "-01");
      filterText = `Tháng: ${dateObj.toLocaleDateString("vi-VN", {
        month: "long",
        year: "numeric",
      })}`;
    }
  } else if (filterType === "year") {
    const year = document.getElementById("selectedYear").value;
    if (year) {
      filterText = `Năm: ${year}`;
    }
  } else {
    filterText = "Tất cả thời gian";
  }

  // Tạo nội dung in
  const printContent = `
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <title>Báo cáo doanh thu - ${filterText}</title>
            <style>
                @page {
                    size: A4 landscape;
                    margin: 20mm;
                }
                body {
                    font-family: 'Arial', sans-serif;
                    margin: 0;
                    padding: 20px;
                    color: #333;
                }
                .print-header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 3px solid #1f6fff;
                    padding-bottom: 20px;
                }
                .print-header h1 {
                    color: #1f6fff;
                    margin: 0 0 10px 0;
                    font-size: 28px;
                }
                .print-header .subtitle {
                    color: #666;
                    font-size: 16px;
                }
                .print-filter {
                    text-align: center;
                    margin-bottom: 20px;
                    font-size: 18px;
                    font-weight: 600;
                    color: #555;
                }
                .print-summary {
                    background: linear-gradient(135deg, #1f6fff, #3b82f6);
                    color: white;
                    padding: 20px;
                    border-radius: 10px;
                    margin-bottom: 25px;
                    display: flex;
                    justify-content: space-around;
                    align-items: center;
                }
                .print-summary-item {
                    text-align: center;
                }
                .print-summary-value {
                    font-size: 32px;
                    font-weight: 700;
                    display: block;
                    margin-bottom: 5px;
                }
                .print-summary-label {
                    font-size: 14px;
                    opacity: 0.9;
                }
                .print-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                .print-table thead th {
                    background: #1f6fff;
                    color: white;
                    padding: 12px;
                    text-align: left;
                    font-weight: 600;
                    border: 1px solid #1f6fff;
                }
                .print-table tbody td {
                    padding: 10px 12px;
                    border: 1px solid #ddd;
                }
                .print-table tbody tr:nth-child(even) {
                    background: #f8fafc;
                }
                .print-footer {
                    margin-top: 30px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                    border-top: 1px solid #ddd;
                    padding-top: 15px;
                }
                .amount {
                    font-weight: 600;
                    color: #059669;
                }
                @media print {
                    body {
                        padding: 0;
                    }
                    .no-print {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            <div class="print-header">
                <h1>BÁO CÁO DOANH THU</h1>
                <div class="subtitle">FQCloud Restaurant Management System</div>
            </div>
            <div class="print-filter">
                ${filterText}
            </div>
            <div class="print-summary">
                ${statsSummary}
            </div>
            <table class="print-table">
                ${tableHead}
                ${tableBody}
            </table>
            <div class="print-footer">
                <p>Báo cáo được in vào: ${new Date().toLocaleString(
                  "vi-VN"
                )}</p>
                <p>FQCloud - Hệ thống quản lý nhà hàng</p>
            </div>
        </body>
        </html>
    `;

  // Mở cửa sổ in
  const printWindow = window.open("", "_blank");
  printWindow.document.write(printContent);
  printWindow.document.close();

  // Đợi nội dung load xong rồi in
  printWindow.onload = function () {
    setTimeout(() => {
      printWindow.print();
    }, 250);
  };
}

// TẠO DOANH THU TỰ ĐỘNG TỪ HÓA ĐƠN
function taoDoanhThuTuTatCaHoaDon() {
  Swal.fire({
    title: "Tạo doanh thu tự động?",
    html: `
            <div style="text-align:left; padding:10px 20px; font-size:16px;">
                <p>Bạn có muốn tự động tạo doanh thu từ tất cả các hóa đơn đã thanh toán nhưng chưa có doanh thu không?</p>
                <br>
                <small style="color:#888;">Hệ thống sẽ tự động tính tổng tiền và tạo doanh thu cho các hóa đơn này.</small>
            </div>
        `,
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#f59e0b",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Tạo ngay",
    cancelButtonText: "Hủy bỏ",
    reverseButtons: true,
    buttonsStyling: true,
    padding: "2rem",
    width: "500px",
  }).then((result) => {
    if (result.isConfirmed) {
      // Hiển thị loading
      Swal.fire({
        title: "Đang xử lý...",
        text: "Vui lòng chờ trong giây lát",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      // Gửi yêu cầu tạo doanh thu
      fetch("QLDoanhThu.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "action=tao_doanh_thu_tu_hoa_don",
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            Swal.fire({
              icon: "success",
              title: "Thành công!",
              html: `
                            <div style="text-align:left; padding:10px;">
                                <p><strong>${data.message}</strong></p>
                                ${
                                  data.count > 0
                                    ? `<p>Đã tạo doanh thu cho <strong>${data.count}</strong> hóa đơn</p>`
                                    : ""
                                }
                                ${
                                  data.fail_count > 0
                                    ? `<p style="color:#dc2626;">Có ${data.fail_count} hóa đơn không thể tạo doanh thu</p>`
                                    : ""
                                }
                            </div>
                        `,
              confirmButtonText: "OK",
              timer: 3000,
            }).then(() => {
              location.reload();
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Thất bại!",
              text:
                data.message || "Không thể tạo doanh thu. Vui lòng thử lại.",
            });
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          Swal.fire("Lỗi!", "Không thể kết nối đến server.", "error");
        });
    }
  });
}

// IN BÁO CÁO DOANH THU TỪ TRANG CHÍNH
function printRevenueReport() {
  // Lấy dữ liệu từ bảng doanh thu hiện tại
  const revenueTable = document.querySelector(".revenue-table tbody");
  if (!revenueTable) {
    Swal.fire("Thông báo", "Không có dữ liệu để in!", "info");
    return;
  }

  const rows = revenueTable.querySelectorAll("tr");
  if (rows.length === 0 || rows[0].querySelector(".empty-state")) {
    Swal.fire("Thông báo", "Chưa có dữ liệu doanh thu để in!", "info");
    return;
  }

  // Lấy thông tin thống kê
  const statsCards = document.querySelectorAll(".stat-card");
  let totalRevenue = "0₫";
  let totalInvoices = "0";
  let paidOrders = "0";
  let activeOrders = "0";

  if (statsCards.length >= 4) {
    totalRevenue = statsCards[0].querySelector(".stat-value").textContent;
    totalInvoices = statsCards[1].querySelector(".stat-value").textContent;
    paidOrders = statsCards[2].querySelector(".stat-value").textContent;
    activeOrders = statsCards[3].querySelector(".stat-value").textContent;
  }

  // Tạo bảng dữ liệu và tính tổng
  let tableRows = "";
  let totalAmount = 0;
  let validRows = 0;

  rows.forEach((row, index) => {
    const cells = row.querySelectorAll("td");
    if (cells.length >= 5) {
      const id = cells[0].textContent.trim();
      const invoice = cells[1].textContent.trim();
      const amountText = cells[2].textContent.trim();
      const date = cells[3].textContent.trim();
      const note = cells[4].textContent.trim();

      // Tính tổng tiền
      const amountValue = amountText.replace(/[^\d]/g, "");
      if (amountValue) {
        totalAmount += parseFloat(amountValue);
      }
      validRows++;

      tableRows += `
                <tr>
                    <td>${validRows}</td>
                    <td>${id}</td>
                    <td>${invoice}</td>
                    <td class="text-right amount">${amountText}</td>
                    <td class="text-center">${date}</td>
                    <td>${note}</td>
                </tr>
            `;
    }
  });

  // Thêm dòng tổng kết
  const totalFormatted = totalAmount.toLocaleString("vi-VN") + "₫";
  tableRows += `
        <tr style="background: #e0f2fe; font-weight: 700;">
            <td colspan="3" class="text-right" style="padding: 15px 8px; font-size: 14px;">TỔNG CỘNG:</td>
            <td class="text-right amount" style="padding: 15px 8px; font-size: 14px; color: #059669;">${totalFormatted}</td>
            <td colspan="2"></td>
        </tr>
    `;

  // Tạo nội dung in
  const printContent = `
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <title>Báo cáo doanh thu - FQCloud</title>
            <style>
                @page {
                    size: A4 landscape;
                    margin: 15mm;
                }
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body {
                    font-family: 'Arial', 'Times New Roman', sans-serif;
                    color: #000;
                    background: #fff;
                    padding: 20px;
                }
                .print-container {
                    width: 100%;
                }
                .print-header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 4px solid #1f6fff;
                    padding-bottom: 20px;
                }
                .print-header h1 {
                    color: #1f6fff;
                    font-size: 32px;
                    font-weight: 700;
                    margin-bottom: 10px;
                    text-transform: uppercase;
                    letter-spacing: 2px;
                }
                .print-header .company-name {
                    font-size: 18px;
                    color: #333;
                    font-weight: 600;
                    margin-bottom: 5px;
                }
                .print-header .subtitle {
                    font-size: 14px;
                    color: #666;
                }
                .print-info {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 25px;
                    padding: 15px;
                    background: #f8fafc;
                    border-radius: 8px;
                }
                .print-info-item {
                    text-align: center;
                    flex: 1;
                }
                .print-info-label {
                    font-size: 12px;
                    color: #666;
                    margin-bottom: 5px;
                    text-transform: uppercase;
                }
                .print-info-value {
                    font-size: 20px;
                    font-weight: 700;
                    color: #1f6fff;
                }
                .print-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                    font-size: 12px;
                }
                .print-table thead {
                    background: #1f6fff;
                    color: #fff;
                }
                .print-table thead th {
                    padding: 12px 8px;
                    text-align: left;
                    font-weight: 600;
                    border: 1px solid #1f6fff;
                    text-transform: uppercase;
                    font-size: 11px;
                }
                .print-table tbody td {
                    padding: 10px 8px;
                    border: 1px solid #ddd;
                    vertical-align: top;
                }
                .print-table tbody tr:nth-child(even) {
                    background: #f8fafc;
                }
                .print-table tbody tr:hover {
                    background: #e0f2fe;
                }
                .text-right {
                    text-align: right;
                }
                .text-center {
                    text-align: center;
                }
                .amount {
                    font-weight: 600;
                    color: #059669;
                }
                .print-footer {
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 2px solid #ddd;
                    display: flex;
                    justify-content: space-between;
                    font-size: 11px;
                    color: #666;
                }
                .print-signature {
                    margin-top: 40px;
                    display: flex;
                    justify-content: space-between;
                }
                .signature-box {
                    text-align: center;
                    width: 200px;
                }
                .signature-line {
                    border-top: 1px solid #000;
                    margin-top: 50px;
                    padding-top: 5px;
                }
                @media print {
                    body {
                        padding: 0;
                    }
                    .no-print {
                        display: none !important;
                    }
                    .print-table {
                        page-break-inside: auto;
                    }
                    .print-table tr {
                        page-break-inside: avoid;
                        page-break-after: auto;
                    }
                }
            </style>
        </head>
        <body>
            <div class="print-container">
                <div class="print-header">
                    <h1>Báo cáo doanh thu</h1>
                    <div class="company-name">FQCloud Restaurant Management System</div>
                    <div class="subtitle">Hệ thống quản lý nhà hàng chuyên nghiệp</div>
                </div>

                <div class="print-info">
                    <div class="print-info-item">
                        <div class="print-info-label">Tổng doanh thu</div>
                        <div class="print-info-value">${totalRevenue}</div>
                    </div>
                    <div class="print-info-item">
                        <div class="print-info-label">Số hóa đơn</div>
                        <div class="print-info-value">${totalInvoices}</div>
                    </div>
                    <div class="print-info-item">
                        <div class="print-info-label">Đã thanh toán</div>
                        <div class="print-info-value">${paidOrders}</div>
                    </div>
                    <div class="print-info-item">
                        <div class="print-info-label">Đang sử dụng</div>
                        <div class="print-info-value">${activeOrders}</div>
                    </div>
                </div>

                <table class="print-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">STT</th>
                            <th style="width: 10%;">ID</th>
                            <th style="width: 15%;">Hóa đơn</th>
                            <th style="width: 15%;" class="text-right">Tổng tiền</th>
                            <th style="width: 15%;" class="text-center">Ngày tính</th>
                            <th style="width: 40%;">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tableRows}
                    </tbody>
                </table>

                <div class="print-footer">
                    <div>
                        <strong>Ngày in:</strong> ${new Date().toLocaleString(
                          "vi-VN"
                        )}
                    </div>
                    <div>
                        <strong>Tổng số bản ghi:</strong> ${validRows}
                    </div>
                    <div>
                        <strong>Tổng doanh thu:</strong> <span style="color: #059669; font-weight: 700;">${totalFormatted}</span>
                    </div>
                </div>

                <div class="print-signature">
                    <div class="signature-box">
                        <div class="signature-line">Người lập</div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line">Người duyệt</div>
                    </div>
                </div>
            </div>
        </body>
        </html>
    `;

  // Mở cửa sổ in
  const printWindow = window.open("", "_blank");
  printWindow.document.write(printContent);
  printWindow.document.close();

  // Đợi nội dung load xong rồi in
  printWindow.onload = function () {
    setTimeout(() => {
      printWindow.print();
    }, 300);
  };
}
