document.addEventListener("DOMContentLoaded", function () {
  // Xử lý sự kiện click vào các bàn
  const tableElements = document.querySelectorAll(".ban");

  tableElements.forEach(function (tableElement) {
    tableElement.addEventListener("click", function () {
      // Xóa trạng thái được chọn của tất cả các bàn khác
      document.querySelectorAll(".ban").forEach(function (ban) {
        ban.classList.remove("ban-chon");
      });

      // Thêm trạng thái được chọn cho bàn hiện tại
      this.classList.add("ban-chon");

      // Lấy thông tin bàn
      const banId = this.getAttribute("data-id");
      const banSo = this.querySelector(".ban-so").textContent;
      const trangThai = this.querySelector(".ban-trang-thai").textContent;

      // Cập nhật thông tin bàn trong panel bên phải
      document.getElementById("order-table-name").textContent = "Bàn " + banSo;

      // Hiển thị form thêm món khi chọn bàn
      const orderEmpty = document.getElementById("order-empty");
      const orderDetail = document.getElementById("order-detail");
      const formThemMon = document.getElementById("formThemMon");

      // Ẩn trạng thái trống và hiển thị chi tiết đơn
      orderEmpty.classList.add("hidden");
      orderDetail.classList.remove("hidden");

      // Hiển thị form thêm món
      formThemMon.classList.remove("hidden");

      // Lưu thông tin bàn được chọn để sử dụng khi thêm món
      window.banHienTai = {
        id: banId,
        soBan: banSo,
      };
    });
  });

  // Xử lý sự kiện đóng form
  document.getElementById("btnDongForm").addEventListener("click", function () {
    document.getElementById("formThemMon").classList.add("hidden");
  });

  // Xử lý sự kiện hủy form
  document.getElementById("btnHuy").addEventListener("click", function () {
    document.getElementById("formThemMon").classList.add("hidden");
  });
});
