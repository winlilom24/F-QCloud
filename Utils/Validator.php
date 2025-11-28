<?php
class Validator
{
    //  Kiểm tra số điện thoại có hợp lệ không
    //  Số điện thoại phải có đúng 10 chữ số và bắt đầu bằng số 0
    public static function validateSoDienThoai($sdt)
    {
        if (empty($sdt)) {
            return ['success' => false, 'message' => 'Số điện thoại không được để trống'];
        }

        // Kiểm tra xem có đúng 10 chữ số và bắt đầu bằng 0 không
        if (!preg_match('/^0[0-9]{9}$/', $sdt)) {
            return [
                'success' => false, 
                'message' => 'Số điện thoại phải có đúng 10 chữ số và bắt đầu bằng số 0'
            ];
        }

        return ['success' => true, 'message' => 'Số điện thoại hợp lệ'];
    }

    /**
     * Kiểm tra tên có hợp lệ không
     */
    public static function validateTen($ten)
    {
        if (empty($ten)) {
            return ['success' => false, 'message' => 'Tên không được để trống'];
        }

        if (strlen($ten) < 2 || strlen($ten) > 100) {
            return ['success' => false, 'message' => 'Tên phải có từ 2 đến 100 ký tự'];
        }

        // Chỉ cho phép các ký tự chữ, dấu cách và một số ký tự tiếng Việt
        if (!preg_match('/^[\p{L}\s]+$/u', $ten)) {
            return ['success' => false, 'message' => 'Tên chỉ được chứa các ký tự chữ và dấu cách'];
        }

        return ['success' => true, 'message' => 'Tên hợp lệ'];
    }
}