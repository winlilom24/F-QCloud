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
      const tenHienThi = banId == 0 ? "Mang về" : "Bàn " + banSo;
      document.getElementById("order-table-name").textContent = tenHienThi;

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

document.addEventListener("DOMContentLoaded", function () {
  // --- 1. Lấy dữ liệu bàn từ PHP nếu cần ---
  // Nếu bạn in ra HTML trực tiếp thì không cần phần này
  // const danhSachBan = JSON.parse(document.getElementById("data-ban").textContent);

  const tableGrid = document.getElementById("table-grid");
  const orderEmpty = document.getElementById("order-empty");
  const orderDetail = document.getElementById("order-detail");
  const formThemMon = document.getElementById("formThemMon");

  // --- 2. Hàm tạo bàn ---
  function taoBanElement(ban) {
    const div = document.createElement("div");
    div.classList.add("ban");
    div.classList.add(
      ban.trang_thai === "dang_su_dung" ? "ban-dang-su-dung" : "ban-trong"
    );
    div.setAttribute("data-id", ban.id);

    div.innerHTML = `
      <div class="ban-so">${ban.so_ban}</div>
      <div class="ban-icon">🪑</div>
      <div class="ban-trang-thai">${
        ban.trang_thai === "dang_su_dung" ? "Đang sử dụng" : "Còn trống"
      }</div>
    `;
    return div;
  }

  // --- 3. Render danh sách bàn ---
  function renderBan(danhSachBan) {
    tableGrid.innerHTML = "";
    danhSachBan.forEach((ban) => {
      const banEl = taoBanElement(ban);
      tableGrid.appendChild(banEl);
    });
    addBanEvent(); // Gán sự kiện click
  }

  // --- 4. Thêm sự kiện click cho từng bàn ---
  function addBanEvent() {
    const tableElements = document.querySelectorAll(".ban");
    tableElements.forEach(function (ban) {
      ban.addEventListener("click", function () {
        // Xóa chọn cũ
        tableElements.forEach((b) => b.classList.remove("ban-chon"));

        // Chọn bàn hiện tại
        this.classList.add("ban-chon");

        const banId = this.getAttribute("data-id");
        const banSo = this.querySelector(".ban-so").textContent;
        const trangThai = this.querySelector(".ban-trang-thai").textContent;

        // Cập nhật panel bên phải
        const tenHienThi = banId == 0 ? "Mang về" : "Bàn " + banSo;
        document.getElementById("order-table-name").textContent = tenHienThi;

        orderEmpty.classList.add("hidden");
        orderDetail.classList.remove("hidden");
        formThemMon.classList.remove("hidden");

        window.banHienTai = { id: banId, soBan: banSo };
      });
    });
  }

  // --- 5. Xử lý đóng / hủy form ---
  document.getElementById("btnDongForm").addEventListener("click", () => {
    formThemMon.classList.add("hidden");
  });

  document.getElementById("btnHuy").addEventListener("click", () => {
    formThemMon.classList.add("hidden");
  });

  // --- 6. Nếu có danh sách bàn từ DB ---
  if (typeof danhSachBan !== "undefined") {
    renderBan(danhSachBan);
  }
});
document.addEventListener("DOMContentLoaded", function () {
  const tableElements = document.querySelectorAll(".ban");
  const orderEmpty = document.getElementById("order-empty");
  const orderDetail = document.getElementById("order-detail");
  const formThemMon = document.getElementById("formThemMon");

  // Ẩn form thực đơn khi load trang
  formThemMon.classList.add("hidden");

  // --- XỬ LÝ CLICK BÀN ---
  function addBanEvent() {
    tableElements.forEach(function (ban) {
      ban.addEventListener("click", function () {
        // Xóa chọn cũ
        tableElements.forEach((b) => b.classList.remove("ban-chon"));

        // Chọn bàn hiện tại
        this.classList.add("ban-chon");

        const banId = this.getAttribute("data-id");
        const banSo = this.querySelector(".ban-so").textContent;

        // Cập nhật panel bên phải
        const tenHienThi = banId == 0 ? "Mang về" : "Bàn " + banSo;
        document.getElementById("order-table-name").textContent = tenHienThi;

        orderEmpty.classList.add("hidden");
        orderDetail.classList.remove("hidden");

        // Nếu auto mở thực đơn, bật luôn form
        const autoOpen = document.getElementById("autoOpenMenu").checked;
        if (autoOpen) formThemMon.classList.remove("hidden");

        window.banHienTai = { id: banId, soBan: banSo };
      });
    });
  }

  addBanEvent();

  // --- TAB THỰC ĐƠN ---
  const tabMenu = document.getElementById("tab-menu");
  const tabBan = document.getElementById("tab-ban");
  const workspaceTabs = document.querySelectorAll(".workspace-tab");

  tabMenu.addEventListener("click", function () {
    if (!window.banHienTai) {
      alert("Vui lòng chọn bàn trước khi thêm món!");
      return;
    }
    formThemMon.classList.remove("hidden");

    workspaceTabs.forEach((t) => t.classList.remove("active"));
    this.classList.add("active");
  });

  tabBan.addEventListener("click", function () {
    workspaceTabs.forEach((t) => t.classList.remove("active"));
    this.classList.add("active");

    // Ẩn form khi quay về tab Bàn
    formThemMon.classList.add("hidden");
  });

  // --- ĐÓNG / HỦY FORM ---
  document.getElementById("btnDongForm").addEventListener("click", () => {
    formThemMon.classList.add("hidden");
  });

  document.getElementById("btnHuy").addEventListener("click", () => {
    formThemMon.classList.add("hidden");
  });
});

// --- CHỨC NĂNG CHUÔNG ---
let audioContext = null;

document.addEventListener("DOMContentLoaded", function () {
  const bellBtn = document.getElementById("bellBtn");

  if (bellBtn) {
    bellBtn.addEventListener("click", function () {
      playBellSound();
    });

    // Thêm hiệu ứng visual khi hover
    bellBtn.addEventListener("mouseenter", function() {
      this.style.transform = "scale(1.1)";
    });

    bellBtn.addEventListener("mouseleave", function() {
      this.style.transform = "scale(1)";
    });
  }
});

// Khởi tạo AudioContext khi user tương tác với trang (để tránh bị chặn)
document.addEventListener("click", function initAudioOnFirstClick() {
  if (!audioContext) {
    initAudioContext();
  }
  document.removeEventListener("click", initAudioOnFirstClick);
});

// --- DROPDOWN MENU ---
document.addEventListener("DOMContentLoaded", function () {
  const hamburgerMenu = document.getElementById("hamburgerMenu");
  const dropdownMenu = document.getElementById("dropdownMenu");

  // Toggle dropdown khi click hamburger menu
  hamburgerMenu.addEventListener("click", function (e) {
    e.stopPropagation();
    dropdownMenu.classList.toggle("show");
  });

  // Đóng dropdown khi click ra ngoài
  document.addEventListener("click", function (e) {
    if (!hamburgerMenu.contains(e.target) && !dropdownMenu.contains(e.target)) {
      dropdownMenu.classList.remove("show");
    }
  });

  // Đóng dropdown khi nhấn ESC
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      dropdownMenu.classList.remove("show");
    }
  });
});

// Hàm khởi tạo AudioContext (để tránh bị chặn bởi browser policies)
function initAudioContext() {
  if (!audioContext) {
    audioContext = new (window.AudioContext || window.webkitAudioContext)();
  }
  return audioContext;
}

// Hàm tạo âm thanh chuông (tín ton)
function playBellSound() {
  try {
    const ctx = initAudioContext();

    // Resume context nếu đang suspended (do browser policies)
    if (ctx.state === 'suspended') {
      ctx.resume();
    }

    // Tạo oscillator cho âm thanh chính
    const oscillator = ctx.createOscillator();
    const gainNode = ctx.createGain();

    // Kết nối
    oscillator.connect(gainNode);
    gainNode.connect(ctx.destination);

    // Cấu hình âm thanh - tạo hiệu ứng "tín ton"
    oscillator.frequency.setValueAtTime(1000, ctx.currentTime); // Bắt đầu cao
    oscillator.frequency.exponentialRampToValueAtTime(600, ctx.currentTime + 0.05); // Giảm xuống
    oscillator.frequency.exponentialRampToValueAtTime(400, ctx.currentTime + 0.15); // Tiếp tục giảm

    // Âm lượng với envelope
    gainNode.gain.setValueAtTime(0, ctx.currentTime);
    gainNode.gain.linearRampToValueAtTime(0.5, ctx.currentTime + 0.01); // Attack nhanh
    gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3); // Decay chậm

    // Phát âm
    oscillator.start(ctx.currentTime);
    oscillator.stop(ctx.currentTime + 0.3);

    console.log("Chuông đã kêu! 🔔");

  } catch (error) {
    console.error("Lỗi khi phát âm thanh chuông:", error);

    // Phương pháp dự phòng: tạo âm thanh đơn giản bằng cách khác
    try {
      // Sử dụng beep sound đơn giản
      const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmQdBzaQ1fLNeSsFJXTH8N2QRQoUXrTp66hVFApGn+DyvmQdBzaQ1fLNeSsFJXTH8N2Q');
      audio.volume = 0.3;
      audio.play();
    } catch (fallbackError) {
      console.log("Không thể phát âm thanh dự phòng");
    }
  }
}