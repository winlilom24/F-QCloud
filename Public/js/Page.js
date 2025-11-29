class PageUI {
  constructor() {
    this.apiBan = "../../Controller/BanController.php";
    this.apiOrder = "../../Controller/OrderController.php";

    this.currentPage = 1;
    this.itemsPerPage = 17;
    this.allTables = [];
    this.filteredTables = [];
    this.viewMode = "grid";

    this.initEvents();
    this.loadTables();
  }

  //-------------------------------------------------------------
  // LOAD DANH SÁCH BÀN
  //-------------------------------------------------------------
  loadTables(floor = "all", status = "all") {
    fetch(this.apiBan + "?action=getAll")
      .then((res) => res.json())
      .then((data) => {
        this.allTables = data;
        this.renderTables(data, floor, status);
        this.updateStatusCounts(data);
      })
      .catch(() => console.error("Không lấy được danh sách bàn!"));
  }

  //-------------------------------------------------------------
  // RENDER BÀN
  //-------------------------------------------------------------
  renderTables(list, floor = "all", status = "all") {
    this.filteredTables = list.filter((b) => {
      if (floor !== "all" && b.Tang != floor) return false;
      if (status === "used" && b.TrangThai == 0) return false;
      if (status === "free" && b.TrangThai == 1) return false;
      return true;
    });

    const totalPages = Math.ceil(
      this.filteredTables.length / this.itemsPerPage
    );
    const start = (this.currentPage - 1) * this.itemsPerPage;
    const end = start + this.itemsPerPage;
    const pageTables = this.filteredTables.slice(start, end);

    const grid = document.getElementById("table-grid");
    grid.className = this.viewMode === "list" ? "table-list" : "table-grid";
    grid.innerHTML = "";

    pageTables.forEach((b) => {
      const div = document.createElement("div");
      div.className = "table-item " + (b.TrangThai == 1 ? "used" : "");

      const icon =
        b.TenBan.toLowerCase().includes("mang về") ||
        b.TenBan.toLowerCase().includes("takeaway")
          ? "📦"
          : "🪑";

      div.innerHTML = `
          <div class="icon">${icon}</div>
          <div class="name">${b.TenBan}</div>
      `;

      div.onclick = () => this.openTable(b);
      grid.appendChild(div);
    });

    this.updatePagination(totalPages);
  }

  //-------------------------------------------------------------
  // PHÂN TRANG
  //-------------------------------------------------------------
  updatePagination(totalPages) {
    document.getElementById("pageInfo").innerText = `${this.currentPage}/${
      totalPages || 1
    }`;
    document.getElementById("prevPage").disabled = this.currentPage === 1;
    document.getElementById("nextPage").disabled =
      this.currentPage >= totalPages;
  }

  //-------------------------------------------------------------
  // ĐẾM TRẠNG THÁI BÀN
  //-------------------------------------------------------------
  updateStatusCounts(list) {
    const all = list.length;
    const used = list.filter((b) => b.TrangThai == 1).length;
    const free = list.filter((b) => b.TrangThai == 0).length;

    document.getElementById("count-all").innerText = `(${all})`;
    document.getElementById("count-used").innerText = `(${used})`;
    document.getElementById("count-free").innerText = `(${free})`;

    document.getElementById("stat-all").innerText = all;
    document.getElementById("stat-used").innerText = used;
    document.getElementById("stat-free").innerText = free;
  }

  //-------------------------------------------------------------
  // CLICK BÀN
  //-------------------------------------------------------------
  openTable(ban) {
    // Animation click
    const tableName = document.getElementById("order-table-name");
    tableName.classList.add("pulse");
    setTimeout(() => tableName.classList.remove("pulse"), 300);

    document.getElementById("order-empty").classList.add("hidden");
    document.getElementById("order-detail").classList.remove("hidden");

    tableName.innerText = ban.TenBan;
    document.getElementById("order-status").innerText =
      ban.TrangThai == 1 ? "Đang sử dụng" : "Còn trống";

    if (document.getElementById("autoOpenMenu").checked) {
      document.getElementById("tab-menu").click();
    }

    this.loadOrder(ban.MaBan);
  }

  //-------------------------------------------------------------
  // LOAD HÓA ĐƠN
  //-------------------------------------------------------------
  loadOrder(maBan) {
    fetch(`${this.apiOrder}?action=getByTable&maBan=${maBan}`)
      .then((res) => res.json())
      .then((data) => this.renderOrder(data))
      .catch(() => console.error("Không lấy được hóa đơn!"));
  }

  //-------------------------------------------------------------
  // RENDER HÓA ĐƠN
  //-------------------------------------------------------------
  renderOrder(order) {
    const wrap = document.getElementById("order-items");
    wrap.innerHTML = "";
    let sum = 0;

    if (!order || !order.items) return;

    order.items.forEach((i) => {
      const total = i.SoLuong * i.Gia;
      sum += total;

      const row = document.createElement("div");
      row.className = "order-item";

      row.innerHTML = `
            <span>${i.TenMon} x${i.SoLuong}</span>
            <span>${total.toLocaleString()}đ</span>
        `;
      wrap.appendChild(row);
    });

    document.getElementById("sum").innerText = sum.toLocaleString() + "đ";
    document.getElementById("discount").innerText = "0đ";
    document.getElementById("total").innerText = sum.toLocaleString() + "đ";
  }

  //-------------------------------------------------------------
  // TÌM KIẾM
  //-------------------------------------------------------------
  searchTable(text) {
    text = text.toLowerCase();
    this.filteredTables = this.allTables.filter((t) =>
      t.TenBan.toLowerCase().includes(text)
    );

    this.currentPage = 1;
    this.renderTables(this.filteredTables);
  }

  //-------------------------------------------------------------
  // SỰ KIỆN GIAO DIỆN
  //-------------------------------------------------------------
  initEvents() {
    //---------------------------------------------------------
    // TAB MENU
    //---------------------------------------------------------
    document.getElementById("tab-menu").onclick = () => {
      window.location.href = "../../Boundary/MenuUI.php";
    };
    document.getElementById("tab-ban").onclick = () => {
      window.location.href = "page.php";
    };

    //---------------------------------------------------------
    // HAMBURGER MENU ĐẸP HƠN + HOẠT ĐỘNG MƯỢT
    //---------------------------------------------------------
    const sideMenu = document.getElementById("sideMenu");
    const overlay = document.getElementById("menuOverlay");
    const hamburger = document.getElementById("hamburgerMenu");

    hamburger.onclick = () => {
      sideMenu.classList.add("open-menu");
      overlay.classList.add("fade-overlay");
      hamburger.classList.add("active");
    };

    overlay.onclick = () => {
      sideMenu.classList.remove("open-menu");
      overlay.classList.remove("fade-overlay");
      hamburger.classList.remove("active");
    };

    //---------------------------------------------------------
    // FILTER TẦNG
    //---------------------------------------------------------
    document.querySelectorAll(".floor-btn").forEach((btn) => {
      btn.onclick = () => {
        document.querySelector(".floor-btn.active").classList.remove("active");
        btn.classList.add("active");

        this.currentPage = 1;

        const status =
          document.querySelector("input[name='statusFilter']:checked")?.value ||
          "all";

        this.renderTables(this.allTables, btn.dataset.floor, status);
      };
    });

    //---------------------------------------------------------
    // FILTER TRẠNG THÁI
    //---------------------------------------------------------
    document.querySelectorAll("input[name='statusFilter']").forEach((r) => {
      r.onchange = () => {
        this.currentPage = 1;
        const floor =
          document.querySelector(".floor-btn.active")?.dataset.floor;
        this.renderTables(this.allTables, floor, r.value);
      };
    });

    //---------------------------------------------------------
    // PHÂN TRANG
    //---------------------------------------------------------
    document.getElementById("prevPage").onclick = () => {
      if (this.currentPage > 1) {
        this.currentPage--;
        const floor =
          document.querySelector(".floor-btn.active")?.dataset.floor;
        const status = document.querySelector(
          "input[name='statusFilter']:checked"
        )?.value;
        this.renderTables(this.allTables, floor, status);
      }
    };

    document.getElementById("nextPage").onclick = () => {
      const totalPages = Math.ceil(
        this.filteredTables.length / this.itemsPerPage
      );
      if (this.currentPage < totalPages) {
        this.currentPage++;
        const floor =
          document.querySelector(".floor-btn.active")?.dataset.floor;
        const status = document.querySelector(
          "input[name='statusFilter']:checked"
        )?.value;
        this.renderTables(this.allTables, floor, status);
      }
    };

    //---------------------------------------------------------
    // TÌM KIẾM
    //---------------------------------------------------------
    const search = document.getElementById("tableSearch");
    document.getElementById("searchBtn").onclick = () => {
      search.value = "";
      this.renderTables(this.allTables);
    };

    search.oninput = () => this.searchTable(search.value);

    //---------------------------------------------------------
    // GRID / LIST
    //---------------------------------------------------------
    document.getElementById("viewToggle").onclick = () => {
      this.viewMode = this.viewMode === "grid" ? "list" : "grid";
      this.renderTables(this.filteredTables);
    };

    //---------------------------------------------------------
    // PHÍM TẮT
    //---------------------------------------------------------
    document.addEventListener("keydown", (e) => {
      if (e.key === "F3") search.focus();
      if (e.key === "F9") document.getElementById("btnPay").click();
      if (e.key === "F10") document.getElementById("btnNotify").click();
    });

    //---------------------------------------------------------
    // NÚT THAO TÁC
    //---------------------------------------------------------
    document.getElementById("btnNotify").onclick = () => {
      alert("Đã gửi thông báo đến nhà bếp!");
    };

    document.getElementById("btnPrint").onclick = () => {
      alert("Đang in tạm tính...");
    };

    document.getElementById("btnPay").onclick = () => {
      alert("Mở popup thanh toán!");
    };
  }
}

new PageUI();
