// ==================== BIẾN TOÀN CỤC ====================
let audioContext = null;
window.banHienTai = null;
window.currentOrderId = null;

// ==================== API HELPER ====================
// Sử dụng đường dẫn API từ PHP nếu có, nếu không thì tính toán
const API_URL = window.API_BASE_URL || (function() {
  const path = window.location.pathname;
  
  // Từ Views/Home/Page.php -> ../../api/order_api.php
  if (path.includes('/Views/Home/') || path.includes('Views\\Home\\')) {
    return '../../api/order_api.php';
  }
  // Từ Views/Manager/ -> ../api/order_api.php
  if (path.includes('/Views/') || path.includes('Views\\')) {
    return '../api/order_api.php';
  }
  // Fallback
  return 'api/order_api.php';
})();

console.log('API URL được sử dụng:', API_URL);
console.log('Current pathname:', window.location.pathname);

async function callAPI(action, data = {}) {
  const formData = new FormData();
  formData.append('action', action);
  Object.keys(data).forEach(key => {
    const value = data[key];
    if (typeof value === 'object' && value !== null) {
      // Serialize object/array thành JSON string
      formData.append(key, JSON.stringify(value));
    } else {
      formData.append(key, value);
    }
  });

  try {
    console.log('Calling API:', API_URL, 'with action:', action);
    
    const response = await fetch(API_URL, {
      method: 'POST',
      body: formData
    });
    
    console.log('API Response status:', response.status);
    
    if (!response.ok) {
      const errorText = await response.text();
      console.error('API Error Response:', errorText);
      throw new Error(`HTTP error! status: ${response.status}, message: ${errorText.substring(0, 100)}`);
    }
    
    const data = await response.json();
    console.log('API Response data:', data);
    return data;
  } catch (error) {
    console.error('API Error Details:', error);
    console.error('API URL đang dùng:', API_URL);
    console.error('Current pathname:', window.location.pathname);
    console.error('Full error:', error.message);
    
    // Thử kiểm tra xem file có tồn tại không
    fetch(API_URL, { method: 'HEAD' })
      .then(r => console.log('API file exists check:', r.status))
      .catch(e => console.error('API file không tồn tại hoặc không truy cập được:', e));
    
    return { success: false, message: 'Lỗi kết nối! Vui lòng mở Console (F12) để xem chi tiết.' };
  }
}

// ==================== ORDER FUNCTIONS ====================
async function loadOrderByBan(id_ban) {
  const result = await callAPI('get_order_by_ban', { id_ban });
  const formThemMon = document.getElementById('formThemMon');
  const btnXoaOrder = document.getElementById('btnXoaOrder');
  const btnPrint = document.getElementById('btnPrint');
  const btnPay = document.getElementById('btnPay');
  
  if (result.success) {
    if (result.order) {
      // Có order: hiện danh sách order, ẩn form thực đơn, hiện nút xóa/sửa
      window.currentOrderId = result.order.id_order;
      document.getElementById('order-id').textContent = result.order.id_order;
      document.getElementById('order-status').textContent = 'Đang xử lý';
      renderOrderItems(result.order_detail);
      updateTotal(result.order_detail.tong_tien);
      
      // Ẩn form thực đơn khi đã có order
      if (formThemMon) {
        formThemMon.classList.add('hidden');
      }
      
      // Hiện nút xóa, in, thanh toán khi có order
      if (btnXoaOrder) btnXoaOrder.style.display = 'block';
      if (btnPrint) btnPrint.style.display = 'block';
      if (btnPay) btnPay.style.display = 'block';
    } else {
      // Chưa có order: hiện form thực đơn để thêm món, ẩn nút xóa/sửa
      window.currentOrderId = null;
      const orderIdEl = document.getElementById('order-id');
      const orderStatusEl = document.getElementById('order-status');
      if (orderIdEl) orderIdEl.textContent = '-';
      if (orderStatusEl) orderStatusEl.textContent = 'Chưa có đơn hàng';
      clearOrderItems();
      
      // Hiện form thực đơn khi chưa có order (luôn hiện nếu đã chọn bàn)
      if (formThemMon) {
        formThemMon.classList.remove('hidden');
      }
      
      // Ẩn nút xóa, in, thanh toán khi chưa có order
      if (btnXoaOrder) btnXoaOrder.style.display = 'none';
      if (btnPrint) btnPrint.style.display = 'none';
      if (btnPay) btnPay.style.display = 'none';
    }
  } else {
    console.error('Lỗi load order:', result.message);
    // Nếu lỗi, vẫn hiện form để có thể thêm món
    if (formThemMon && window.banHienTai) {
      formThemMon.classList.remove('hidden');
    }
    
    // Ẩn nút khi lỗi
    if (btnXoaOrder) btnXoaOrder.style.display = 'none';
    if (btnPrint) btnPrint.style.display = 'none';
    if (btnPay) btnPay.style.display = 'none';
  }
}

async function createOrder(id_ban) {
  const result = await callAPI('tao_order', { id_ban });
  
  if (result.success) {
    window.currentOrderId = result.id_order;
    document.getElementById('order-id').textContent = result.id_order;
    document.getElementById('order-status').textContent = 'Đang xử lý';
    renderOrderItems(result.order_detail);
    updateTotal(result.order_detail.tong_tien);
    return true;
  } else {
    alert(result.message || 'Lỗi tạo order!');
    return false;
  }
}

async function addMonToOrder(id_order, id_mon, so_luong) {
  const result = await callAPI('them_mon', { id_order, id_mon, so_luong });
  
  if (result.success) {
    renderOrderItems(result.order_detail);
    updateTotal(result.order_detail.tong_tien);
    return true;
  } else {
    alert(result.message || 'Lỗi thêm món!');
    return false;
  }
}

async function updateMonQuantity(id_order, id_mon, so_luong) {
  const result = await callAPI('cap_nhat_mon', { id_order, id_mon, so_luong });
  
  if (result.success) {
    if (result.deleted_order) {
      // Order đã bị xóa vì không còn món
      window.currentOrderId = null;
      document.getElementById('order-id').textContent = '-';
      document.getElementById('order-status').textContent = 'Chưa có đơn hàng';
      clearOrderItems();
      alert(result.message || 'Đã xóa order vì không còn món');
    } else {
      renderOrderItems(result.order_detail);
      updateTotal(result.order_detail.tong_tien);
    }
    return true;
  } else {
    alert(result.message || 'Lỗi cập nhật!');
    return false;
  }
}

async function deleteOrder(id_order) {
  if (!window.Swal) {
    // Fallback nếu SweetAlert2 chưa load
    if (!confirm('Bạn có chắc muốn xóa đơn hàng này?')) return;
  } else {
    const result = await Swal.fire({
      title: 'Xóa đơn hàng?',
      text: 'Đơn hàng và tất cả món trong đơn sẽ bị xóa. Bàn sẽ được giải phóng.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Xóa',
      cancelButtonText: 'Hủy'
    });
    
    if (!result.isConfirmed) return;
  }
  
  const apiResult = await callAPI('xoa_order', { id_order });
  
  if (apiResult.success) {
    window.currentOrderId = null;
    document.getElementById('order-id').textContent = '-';
    document.getElementById('order-status').textContent = 'Chưa có đơn hàng';
    clearOrderItems();
    
    // Ẩn nút xóa, in, thanh toán
    const btnXoaOrder = document.getElementById('btnXoaOrder');
    const btnPrint = document.getElementById('btnPrint');
    const btnPay = document.getElementById('btnPay');
    if (btnXoaOrder) btnXoaOrder.style.display = 'none';
    if (btnPrint) btnPrint.style.display = 'none';
    if (btnPay) btnPay.style.display = 'none';
    
    // Hiện form thực đơn
    const formThemMon = document.getElementById('formThemMon');
    if (formThemMon && window.banHienTai) {
      formThemMon.classList.remove('hidden');
    }
    
    if (window.Swal) {
      Swal.fire({
        icon: 'success',
        title: 'Đã xóa!',
        text: apiResult.message || 'Đã xóa đơn hàng thành công!',
        timer: 1600,
        showConfirmButton: false
      }).then(() => {
        // Reload trang để cập nhật trạng thái bàn
        location.reload();
      });
    } else {
      alert(apiResult.message || 'Đã xóa order thành công!');
      location.reload();
    }
  } else {
    if (window.Swal) {
      Swal.fire('Lỗi!', apiResult.message || 'Không thể xóa đơn hàng.', 'error');
    } else {
      alert(apiResult.message || 'Lỗi xóa order!');
    }
  }
}

async function thanhToan(id_order) {
  if (!id_order) {
    if (window.Swal) {
      Swal.fire('Cảnh báo', 'Chưa có đơn hàng để thanh toán!', 'warning');
    } else {
      alert('Chưa có đơn hàng để thanh toán!');
    }
    return;
  }
  
  let confirmed = false;
  if (window.Swal) {
    const result = await Swal.fire({
      title: 'Xác nhận thanh toán?',
      text: 'Đơn hàng sẽ được thanh toán và tạo hóa đơn.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Thanh toán',
      cancelButtonText: 'Hủy'
    });
    confirmed = result.isConfirmed;
  } else {
    confirmed = confirm('Xác nhận thanh toán?');
  }
  
  if (!confirmed) return;
  
  const result = await callAPI('tao_hoa_don', { id_order });
  
  if (result.success) {
    // Hiển thị hóa đơn để in
    showInvoiceModal(result.invoice_html);
    // Reset order
    window.currentOrderId = null;
    document.getElementById('order-id').textContent = '-';
    document.getElementById('order-status').textContent = 'Đã thanh toán';
    clearOrderItems();
    // Reload trang để cập nhật trạng thái bàn
    setTimeout(() => location.reload(), 2000);
  } else {
    alert(result.message || 'Lỗi thanh toán!');
  }
}

// ==================== RENDER FUNCTIONS ====================
function renderOrderItems(orderDetail) {
  const orderItemsContainer = document.getElementById('order-items');
  if (!orderItemsContainer) return;
  
  // Tìm container để hiển thị items (không phải form thêm món)
  let itemsList = orderItemsContainer.querySelector('.order-items-list');
  if (!itemsList) {
    itemsList = document.createElement('div');
    itemsList.className = 'order-items-list';
    // Chèn vào trước form thêm món
    const formThemMon = document.getElementById('formThemMon');
    if (formThemMon) {
      orderItemsContainer.insertBefore(itemsList, formThemMon);
    } else {
      orderItemsContainer.appendChild(itemsList);
    }
  }
  
  // Ẩn form thực đơn khi đã có order items
  const formThemMon = document.getElementById('formThemMon');
  if (formThemMon) {
    formThemMon.classList.add('hidden');
  }
  
  itemsList.innerHTML = orderDetail.html;
  
  // Gán sự kiện cho các nút +/-
  itemsList.querySelectorAll('.btn-qty.minus').forEach(btn => {
    btn.addEventListener('click', async function() {
      const idMon = parseInt(this.getAttribute('data-id-mon'));
      const input = this.nextElementSibling;
      const newQty = Math.max(0, parseInt(input.value) - 1);
      input.value = newQty;
      await updateMonQuantity(window.currentOrderId, idMon, newQty);
    });
  });
  
  itemsList.querySelectorAll('.btn-qty.plus').forEach(btn => {
    btn.addEventListener('click', async function() {
      const idMon = parseInt(this.getAttribute('data-id-mon'));
      const input = this.previousElementSibling;
      const newQty = parseInt(input.value) + 1;
      input.value = newQty;
      await updateMonQuantity(window.currentOrderId, idMon, newQty);
    });
  });
  
  itemsList.querySelectorAll('.qty-input').forEach(input => {
    input.addEventListener('change', async function() {
      const idMon = parseInt(this.getAttribute('data-id-mon'));
      const newQty = Math.max(0, parseInt(this.value) || 0);
      this.value = newQty;
      await updateMonQuantity(window.currentOrderId, idMon, newQty);
    });
  });
  
  // Hiển thị order detail, ẩn empty
  document.getElementById('order-empty').classList.add('hidden');
  document.getElementById('order-detail').classList.remove('hidden');
}

function clearOrderItems() {
  const itemsList = document.querySelector('.order-items-list');
  if (itemsList) {
    itemsList.remove();
  }
  const orderEmpty = document.getElementById('order-empty');
  const orderDetail = document.getElementById('order-detail');
  // Không ẩn order-detail vì form thực đơn nằm trong đó
  if (orderEmpty) orderEmpty.classList.add('hidden');
  if (orderDetail) orderDetail.classList.remove('hidden');
  updateTotal(0);
  
  // Ẩn nút xóa, in, thanh toán
  const btnXoaOrder = document.getElementById('btnXoaOrder');
  const btnPrint = document.getElementById('btnPrint');
  const btnPay = document.getElementById('btnPay');
  if (btnXoaOrder) btnXoaOrder.style.display = 'none';
  if (btnPrint) btnPrint.style.display = 'none';
  if (btnPay) btnPay.style.display = 'none';
}

function updateTotal(tongTien) {
  const formatted = new Intl.NumberFormat('vi-VN').format(tongTien) + 'đ';
  document.getElementById('sum').textContent = formatted;
  document.getElementById('total').textContent = formatted;
  document.getElementById('discount').textContent = '0đ';
}

// ==================== INVOICE MODAL ====================
function showInvoiceModal(html) {
  // Tạo modal nếu chưa có
  let modal = document.getElementById('invoice-modal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'invoice-modal';
    modal.className = 'invoice-modal';
    modal.innerHTML = `
      <div class="invoice-modal-content">
        <div class="invoice-modal-header">
          <h3>Hóa đơn</h3>
          <button class="invoice-close" onclick="closeInvoiceModal()">×</button>
        </div>
        <div class="invoice-modal-body" id="invoice-modal-body"></div>
        <div class="invoice-modal-footer">
          <button onclick="printInvoice()" class="btn btn-primary">In hóa đơn</button>
          <button onclick="closeInvoiceModal()" class="btn btn-secondary">Đóng</button>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
  }
  
  document.getElementById('invoice-modal-body').innerHTML = html;
  modal.style.display = 'flex';
}

function closeInvoiceModal() {
  const modal = document.getElementById('invoice-modal');
  if (modal) {
    modal.style.display = 'none';
  }
}

function printInvoice() {
  const content = document.getElementById('invoice-modal-body').innerHTML;
  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
    <html>
      <head>
        <title>In hóa đơn</title>
        <style>
          body { font-family: Arial, sans-serif; padding: 20px; }
          .invoice-print { max-width: 600px; margin: 0 auto; }
          .invoice-header { text-align: center; margin-bottom: 20px; }
          .invoice-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
          .invoice-table th, .invoice-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
          .invoice-table th { background-color: #f2f2f2; }
          .text-center { text-align: center; }
          .text-end { text-align: right; }
          .invoice-footer { text-align: center; margin-top: 20px; }
          @media print {
            button { display: none; }
          }
        </style>
      </head>
      <body>
        ${content}
      </body>
    </html>
  `);
  printWindow.document.close();
  printWindow.print();
}

// ==================== BAN SELECTION ====================
document.addEventListener("DOMContentLoaded", function () {
  const tableElements = document.querySelectorAll(".ban");
  const orderEmpty = document.getElementById("order-empty");
  const orderDetail = document.getElementById("order-detail");
  const formThemMon = document.getElementById("formThemMon");

  // Ẩn form thực đơn khi load trang
  if (formThemMon) formThemMon.classList.add("hidden");

  // Xử lý click bàn - dùng event delegation để đảm bảo hoạt động với bàn được render động
  const tableGrid = document.getElementById("table-grid");
  
  async function handleBanClick(banElement) {
    // Xóa chọn cũ
    document.querySelectorAll(".ban").forEach((b) => b.classList.remove("ban-chon"));

    // Chọn bàn hiện tại
    banElement.classList.add("ban-chon");

    const banId = parseInt(banElement.getAttribute("data-id"));
    const banSo = banElement.querySelector(".ban-so")?.textContent || '';

    // Cập nhật panel bên phải
    const tenHienThi = banId == 0 ? "Mang về" : "Bàn " + banSo;
    const orderTableName = document.getElementById("order-table-name");
    if (orderTableName) {
      orderTableName.textContent = tenHienThi;
    }

    // Đảm bảo order-detail được hiển thị
    if (orderEmpty) orderEmpty.classList.add("hidden");
    if (orderDetail) orderDetail.classList.remove("hidden");

    // Set banHienTai TRƯỚC khi load order
    window.banHienTai = { id: banId, soBan: banSo };

    // Load order của bàn này (sẽ tự động quyết định hiện form hay danh sách order)
    await loadOrderByBan(banId);
  }

  // Gán event cho các bàn hiện có
  tableElements.forEach(function (ban) {
    ban.addEventListener("click", function () {
      handleBanClick(this);
    });
  });

  // Event delegation cho các bàn được thêm sau
  if (tableGrid) {
    tableGrid.addEventListener("click", function (e) {
      const banElement = e.target.closest(".ban");
      if (banElement) {
        handleBanClick(banElement);
      }
    });
  }

  // TAB THỰC ĐƠN
  const tabMenu = document.getElementById("tab-menu");
  const tabBan = document.getElementById("tab-ban");
  const workspaceTabs = document.querySelectorAll(".workspace-tab");

  if (tabMenu) {
    tabMenu.addEventListener("click", function () {
      if (!window.banHienTai) {
        alert("Vui lòng chọn bàn trước khi thêm món!");
        return;
      }
      if (formThemMon) formThemMon.classList.remove("hidden");

      workspaceTabs.forEach((t) => t.classList.remove("active"));
      this.classList.add("active");
    });
  }

  if (tabBan) {
    tabBan.addEventListener("click", function () {
      workspaceTabs.forEach((t) => t.classList.remove("active"));
      this.classList.add("active");

      if (formThemMon) formThemMon.classList.add("hidden");
    });
  }

  // ĐÓNG / HỦY FORM
  const btnDongForm = document.getElementById("btnDongForm");
  const btnHuy = document.getElementById("btnHuy");
  
  if (btnDongForm) {
    btnDongForm.addEventListener("click", () => {
      if (formThemMon) formThemMon.classList.add("hidden");
    });
  }

  if (btnHuy) {
    btnHuy.addEventListener("click", () => {
      if (formThemMon) formThemMon.classList.add("hidden");
    });
  }

  // XÁC NHẬN THÊM MÓN
  const btnXacNhan = document.getElementById("btnXacNhan");
  if (btnXacNhan) {
    btnXacNhan.addEventListener("click", async function(e) {
      e.preventDefault();
      
      if (!window.banHienTai) {
        if (window.Swal) {
          Swal.fire('Cảnh báo', 'Vui lòng chọn bàn trước!', 'warning');
        } else {
          alert("Vui lòng chọn bàn trước!");
        }
        return;
      }

      // Lấy danh sách món đã chọn
      const inputs = document.querySelectorAll('#tbodyMonAn input[type="number"]');
      const monSelected = {};
      let hasMon = false;

      console.log('Đang lấy danh sách món đã chọn...');
      inputs.forEach(input => {
        const idMon = parseInt(input.getAttribute('data-id'));
        const soLuong = parseInt(input.value) || 0;
        console.log(`Món ${idMon}: số lượng ${soLuong}`);
        if (soLuong > 0) {
          monSelected[idMon] = soLuong;
          hasMon = true;
        }
      });

      console.log('monSelected:', monSelected);
      console.log('hasMon:', hasMon);

      if (!hasMon) {
        if (window.Swal) {
          Swal.fire('Cảnh báo', 'Vui lòng chọn ít nhất một món!', 'warning');
        } else {
          alert("Vui lòng chọn ít nhất một món!");
        }
        return;
      }

      try {
        // Tạo order nếu chưa có, hoặc cập nhật order nếu đã có
        if (!window.currentOrderId) {
          const created = await createOrder(window.banHienTai.id);
          if (!created) return;
        }

        console.log('currentOrderId:', window.currentOrderId);
        console.log('Gửi API cap_nhat_order với:', {
          id_order: window.currentOrderId,
          mon: monSelected,
          merge: true
        });

        // Kiểm tra dữ liệu trước khi gửi
        if (!window.currentOrderId || window.currentOrderId <= 0) {
          console.error('currentOrderId không hợp lệ:', window.currentOrderId);
          if (window.Swal) {
            Swal.fire('Lỗi!', 'ID đơn hàng không hợp lệ!', 'error');
          } else {
            alert('ID đơn hàng không hợp lệ!');
          }
          return;
        }

        if (!monSelected || Object.keys(monSelected).length === 0) {
          console.error('monSelected rỗng:', monSelected);
          if (window.Swal) {
            Swal.fire('Lỗi!', 'Không có món nào được chọn!', 'error');
          } else {
            alert('Không có món nào được chọn!');
          }
          return;
        }

        // Cập nhật order với danh sách món mới (merge với món cũ)
        const result = await callAPI('cap_nhat_order', {
          id_order: window.currentOrderId,
          mon: monSelected,
          merge: true  // Merge với món cũ
        });

        if (result.success) {
          console.log('Thêm món thành công! Debug data:', result.debug_saved_data);

          // Load lại order để hiển thị
          await loadOrderByBan(window.banHienTai.id);

          // Reset form
          inputs.forEach(input => input.value = 0);
          if (formThemMon) formThemMon.classList.add("hidden");

          if (window.Swal) {
            Swal.fire({
              icon: 'success',
              title: 'Đã thêm món!',
              text: 'Dữ liệu đã được lưu vào cơ sở dữ liệu',
              timer: 2000,
              showConfirmButton: false
            });
          }
        } else {
          console.error('Lỗi thêm món:', result);
          console.error('Errors:', result.errors);
          console.error('Debug info:', result.debug);

          let errorMessage = result.message || 'Không thể thêm món!';
          if (result.errors && result.errors.length > 0) {
            errorMessage += '\n\nChi tiết lỗi:\n' + result.errors.join('\n');
          }
          if (result.debug) {
            errorMessage += '\n\nDebug: ' + JSON.stringify(result.debug, null, 2);
          }

          if (window.Swal) {
            Swal.fire('Lỗi!', errorMessage, 'error');
          } else {
            alert(errorMessage);
          }
        }
      } catch (error) {
        console.error('Lỗi khi thêm món:', error);
        if (window.Swal) {
          Swal.fire('Lỗi!', 'Có lỗi xảy ra khi thêm món. Vui lòng thử lại!', 'error');
        } else {
          alert('Có lỗi xảy ra khi thêm món. Vui lòng thử lại!');
        }
      }
    });
  }


  // THANH TOÁN
  const btnPay = document.getElementById("btnPay");
  if (btnPay) {
    btnPay.addEventListener("click", function() {
      if (window.currentOrderId) {
        thanhToan(window.currentOrderId);
      } else {
        if (window.Swal) {
          Swal.fire('Cảnh báo', 'Chưa có đơn hàng để thanh toán!', 'warning');
        } else {
          alert("Chưa có đơn hàng để thanh toán!");
        }
      }
    });
  }

  // IN TẠM TÍNH
  const btnPrint = document.getElementById("btnPrint");
  if (btnPrint) {
    btnPrint.addEventListener("click", async function() {
      if (window.currentOrderId) {
        const result = await callAPI('in_hoa_don', { id_order: window.currentOrderId });
        if (result.success) {
          showInvoiceModal(result.invoice_html);
        }
      } else {
        if (window.Swal) {
          Swal.fire('Cảnh báo', 'Chưa có đơn hàng để in!', 'warning');
        } else {
          alert("Chưa có đơn hàng để in!");
        }
      }
    });
  }

  // XÓA ĐƠN HÀNG
  const btnXoaOrder = document.getElementById("btnXoaOrder");
  if (btnXoaOrder) {
    btnXoaOrder.addEventListener("click", function() {
      if (window.currentOrderId) {
        deleteOrder(window.currentOrderId);
      } else {
        if (window.Swal) {
          Swal.fire('Cảnh báo', 'Chưa có đơn hàng để xóa!', 'warning');
        } else {
          alert("Chưa có đơn hàng để xóa!");
        }
      }
    });
  }

  // Nút "Thêm món" trong danh sách order (dùng event delegation vì được tạo động)
  document.addEventListener('click', function(e) {
    if (e.target && (e.target.id === 'btnThemMonTrongOrder' || e.target.closest('#btnThemMonTrongOrder'))) {
      if (!window.banHienTai) {
        if (window.Swal) {
          Swal.fire('Cảnh báo', 'Vui lòng chọn bàn trước!', 'warning');
        } else {
          alert("Vui lòng chọn bàn trước!");
        }
        return;
      }
      const formThemMon = document.getElementById("formThemMon");
      if (formThemMon) {
        formThemMon.classList.remove("hidden");
      }
    }
  });
});

// ==================== CHỨC NĂNG CHUÔNG ====================
document.addEventListener("DOMContentLoaded", function () {
  const bellBtn = document.getElementById("bellBtn");

  if (bellBtn) {
    bellBtn.addEventListener("click", function () {
      playBellSound();
    });

    bellBtn.addEventListener("mouseenter", function() {
      this.style.transform = "scale(1.1)";
    });

    bellBtn.addEventListener("mouseleave", function() {
      this.style.transform = "scale(1)";
    });
  }
});

document.addEventListener("click", function initAudioOnFirstClick() {
  if (!audioContext) {
    initAudioContext();
  }
  document.removeEventListener("click", initAudioOnFirstClick);
});

function initAudioContext() {
  if (!audioContext) {
    audioContext = new (window.AudioContext || window.webkitAudioContext)();
  }
  return audioContext;
}

function playBellSound() {
  try {
    const ctx = initAudioContext();

    if (ctx.state === 'suspended') {
      ctx.resume();
    }

    const oscillator = ctx.createOscillator();
    const gainNode = ctx.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(ctx.destination);

    oscillator.frequency.setValueAtTime(1000, ctx.currentTime);
    oscillator.frequency.exponentialRampToValueAtTime(600, ctx.currentTime + 0.05);
    oscillator.frequency.exponentialRampToValueAtTime(400, ctx.currentTime + 0.15);

    gainNode.gain.setValueAtTime(0, ctx.currentTime);
    gainNode.gain.linearRampToValueAtTime(0.5, ctx.currentTime + 0.01);
    gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);

    oscillator.start(ctx.currentTime);
    oscillator.stop(ctx.currentTime + 0.3);

    console.log("Chuông đã kêu! 🔔");

  } catch (error) {
    console.error("Lỗi khi phát âm thanh chuông:", error);
    try {
      const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmQdBzaQ1fLNeSsFJXTH8N2QRQoUXrTp66hVFApGn+DyvmQdBzaQ1fLNeSsFJXTH8N2Q');
      audio.volume = 0.3;
      audio.play();
    } catch (fallbackError) {
      console.log("Không thể phát âm thanh dự phòng");
    }
  }
}

// ==================== DROPDOWN MENU ====================
document.addEventListener("DOMContentLoaded", function () {
  const hamburgerMenu = document.getElementById("hamburgerMenu");
  const dropdownMenu = document.getElementById("dropdownMenu");

  if (hamburgerMenu && dropdownMenu) {
    hamburgerMenu.addEventListener("click", function (e) {
      e.stopPropagation();
      dropdownMenu.classList.toggle("show");
    });

    document.addEventListener("click", function (e) {
      if (!hamburgerMenu.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove("show");
      }
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") {
        dropdownMenu.classList.remove("show");
      }
    });
  }
});
