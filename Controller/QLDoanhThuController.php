<?php
// Controller/QLDoanhThuController.php
require_once __DIR__ . '/../Models/DoanhThu.php';
require_once __DIR__ . '/../Models/HoaDon.php';

class QLDoanhThuController {
    private $doanhThuModel;
    private $hoaDonModel = null;

    public function __construct() {
        $this->doanhThuModel = new DoanhThu();
        $this->hoaDonModel = new HoaDon();
    }

    public function getAllDoanhThu() {
        return $this->doanhThuModel->getAll();
    }

    public function themDoanhThu($data) {
        // Validate dữ liệu đầu vào
        if (!isset($data['id_hoa_don']) || !is_numeric($data['id_hoa_don'])) {
            return ['success' => false, 'message' => 'ID hóa đơn không hợp lệ!'];
        }

        if (!isset($data['tong_tien']) || !is_numeric($data['tong_tien']) || $data['tong_tien'] <= 0) {
            return ['success' => false, 'message' => 'Tổng tiền phải là số dương!'];
        }

        if (!isset($data['ngay_tinh']) || empty($data['ngay_tinh'])) {
            return ['success' => false, 'message' => 'Ngày tính không được để trống!'];
        }

        // Validate định dạng ngày
        if (!strtotime($data['ngay_tinh'])) {
            return ['success' => false, 'message' => 'Định dạng ngày không hợp lệ!'];
        }

        return $this->doanhThuModel->create(
            (int)$data['id_hoa_don'],
            (float)$data['tong_tien'],
            $data['ngay_tinh'],
            $data['ghi_chu'] ?? null
        );
    }

    public function suaDoanhThu($id_doanh_thu, $data) {
        if (!isset($data['id_hoa_don']) || !is_numeric($data['id_hoa_don'])) {
            return ['success' => false, 'message' => 'ID hóa đơn không hợp lệ!'];
        }

        if (!isset($data['tong_tien']) || !is_numeric($data['tong_tien']) || $data['tong_tien'] <= 0) {
            return ['success' => false, 'message' => 'Tổng tiền phải là số dương!'];
        }

        if (!isset($data['ngay_tinh']) || empty($data['ngay_tinh'])) {
            return ['success' => false, 'message' => 'Ngày tính không được để trống!'];
        }

        // Validate định dạng ngày
        if (!strtotime($data['ngay_tinh'])) {
            return ['success' => false, 'message' => 'Định dạng ngày không hợp lệ!'];
        }

        return $this->doanhThuModel->update(
            $id_doanh_thu,
            (int)$data['id_hoa_don'],
            (float)$data['tong_tien'],
            $data['ngay_tinh'],
            $data['ghi_chu'] ?? null
        );
    }

    public function xoaDoanhThu($id_doanh_thu) {
        return $this->doanhThuModel->delete($id_doanh_thu);
    }

    public function getDoanhThuById($id_doanh_thu) {
        return $this->doanhThuModel->getById($id_doanh_thu);
    }

    public function getHoaDonChuaCoDoanhThu() {
        return $this->doanhThuModel->getHoaDonChuaCoDoanhThu();
    }

    public function getDoanhThuTheoNgay($ngay_bat_dau = null, $ngay_ket_thuc = null) {
        return $this->doanhThuModel->getDoanhThuTheoNgay($ngay_bat_dau, $ngay_ket_thuc);
    }

    public function getThongKeTongQuan() {
        return $this->doanhThuModel->getThongKeTongQuan();
    }

    public function getDoanhThuTheoFilter($filterType, $date = null, $month = null, $year = null) {
        $ngayBatDau = null;
        $ngayKetThuc = null;

        switch ($filterType) {
            case 'day':
                if ($date) {
                    $ngayBatDau = $date;
                    $ngayKetThuc = $date;
                }
                break;
            case 'month':
                if ($month) {
                    $ngayBatDau = $month . '-01';
                    $ngayKetThuc = date('Y-m-t', strtotime($ngayBatDau));
                }
                break;
            case 'year':
                if ($year) {
                    $ngayBatDau = $year . '-01-01';
                    $ngayKetThuc = $year . '-12-31';
                }
                break;
        }

        return $this->doanhThuModel->getDoanhThuTheoNgay($ngayBatDau, $ngayKetThuc);
    }

    public function getDonHangDaThanhToan() {
        return $this->hoaDonModel ? $this->hoaDonModel->getByStatus('Đã thanh toán') : [];
    }

    public function getDonHangDangSuDung() {
        return $this->hoaDonModel ? $this->hoaDonModel->getByStatus('Đang xử lý') : [];
    }

    public function getAllDonHang() {
        return $this->hoaDonModel ? $this->hoaDonModel->getAll() : [];
    }

    public function taoDoanhThuTuHoaDon($id_hoa_don) {
        return $this->doanhThuModel->taoDoanhThuTuHoaDon($id_hoa_don);
    }

    public function taoDoanhThuTuTatCaHoaDon() {
        return $this->doanhThuModel->taoDoanhThuTuTatCaHoaDon();
    }
}
?>