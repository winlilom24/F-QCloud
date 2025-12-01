<?php
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../Repository/Database.php';

class User {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    private function sendMail($to, $subject, $content) {
        $mail = new PHPMailer(true);

        try {
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'fb.cloud.team@gmail.com';
            $mail->Password = 'lxdxurpgpszxcldn';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $mail->setFrom('fb.cloud.team@gmail.com', 'F-QCloud System');

            if (is_array($to)) {
                foreach ($to as $mailAddress) {
                    $mail->addAddress($mailAddress);
                }
            } else {
                $mail->addAddress($to);
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = nl2br($content);
            $mail->AltBody = $content;
            $mail->SMTPDebug = 0;
            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $e->getMessage());
            return false;
        }
    }

    public function createAccount($ten, $ten_quan, $sdt, $email, $tai_khoan, $mat_khau) {
        foreach (['sdt'=>$sdt, 'email'=>$email, 'tai_khoan'=>$tai_khoan] as $field => $value) {
            $table = ($field === 'tai_khoan') ? 'taikhoan' : 'user';
            $col = ($field === 'tai_khoan') ? 'tai_khoan' : $field;

            $stmt = $this->conn->prepare("SELECT user_id FROM $table WHERE $col = ?");
            $stmt->bind_param("s", $value);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $msg = ($field==='tai_khoan') ? 'Tên tài khoản đã tồn tại!' : ucfirst($field).' đã được sử dụng!';
                return ['success'=>false, 'message'=>$msg];
            }
        }

        $this->conn->begin_transaction();

        try {
            $stmt1 = $this->conn->prepare(
                "INSERT INTO user (ten, ten_quan, sdt, email, role) 
                 VALUES (?, ?, ?, ?, 'Quản lý')"
            );
            $stmt1->bind_param("ssss", $ten, $ten_quan, $sdt, $email);
            $stmt1->execute();
            $user_id = $this->conn->insert_id;

            $hash = password_hash($mat_khau, PASSWORD_DEFAULT);

            $stmt2 = $this->conn->prepare(
                "INSERT INTO taikhoan (user_id, tai_khoan, mat_khau) 
                 VALUES (?, ?, ?)"
            );
            $stmt2->bind_param("iss", $user_id, $tai_khoan, $hash);
            $stmt2->execute();

            $this->conn->commit();

            $emails = [
                "volengocson19@gmail.com",
                "ducnhat2425@gmail.com",
                "hoang1234098@gmail.com"
            ];

            $subject = "Thông báo đăng ký tài khoản cửa hàng mới";
            $content = "
<b>Một cửa hàng mới vừa đăng ký tài khoản hệ thống:</b><br>
▸ Số điện thoại: <b>$sdt</b><br>
▸ Email: <b>$email</b><br>
▸ Tên quán: <b>$ten_quan</b><br>
";
            $this->sendMail($emails, $subject, $content);

            return [
                'success' => true,
                'message' => 'Đăng ký quản lý thành công!',
                'user_id' => $user_id
            ];

        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại!'];
        }
    }

    public function getUserById($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getNhanVienByQuanLy($id_quan_ly) {
        $stmt = $this->conn->prepare(
            "SELECT tk.user_id, tk.tai_khoan, u.ten, u.sdt, u.email, u.role, u.ten_quan
             FROM taikhoan tk
             INNER JOIN user u ON u.user_id = tk.user_id
             WHERE u.role = 'Nhân viên' AND u.id_quan_ly = ?"
        );
        $stmt->bind_param("i", $id_quan_ly);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function createNhanVien($id_quan_ly, $ten, $sdt, $email, $tai_khoan, $mat_khau) {
        $check_sdt = $this->conn->prepare("SELECT user_id FROM user WHERE sdt = ?");
        $check_sdt->bind_param("s", $sdt);
        $check_sdt->execute();
        if ($check_sdt->get_result()->num_rows > 0)
            return ['success'=>false,'message'=>'Số điện thoại đã được sử dụng!'];

        $check_email = $this->conn->prepare("SELECT user_id FROM user WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        if ($check_email->get_result()->num_rows > 0)
            return ['success'=>false,'message'=>'Email đã được sử dụng!'];

        $check_acc = $this->conn->prepare("SELECT user_id FROM taikhoan WHERE tai_khoan = ?");
        $check_acc->bind_param("s", $tai_khoan);
        $check_acc->execute();
        if ($check_acc->get_result()->num_rows > 0)
            return ['success'=>false,'message'=>'Tên tài khoản đã tồn tại!'];

        $this->conn->begin_transaction();

        try {
            $stmt1 = $this->conn->prepare(
                "INSERT INTO user (ten, sdt, email, role, id_quan_ly)
                 VALUES (?, ?, ?, 'Nhân viên', ?)"
            );
            $stmt1->bind_param("sssi", $ten, $sdt, $email, $id_quan_ly);
            $stmt1->execute();
            $user_id = $this->conn->insert_id;

            $hash = password_hash($mat_khau, PASSWORD_DEFAULT);

            $stmt2 = $this->conn->prepare(
                "INSERT INTO taikhoan (user_id, tai_khoan, mat_khau) 
                 VALUES (?, ?, ?)"
            );
            $stmt2->bind_param("iss", $user_id, $tai_khoan, $hash);
            $stmt2->execute();

            $this->conn->commit();
            return ['success' => true, 'message' => 'Thêm nhân viên thành công!', 'user_id' => $user_id];

        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại!'];
        }
    }

    public function update($user_id, $ten, $sdt, $email) {
        $stmt = $this->conn->prepare(
            "UPDATE user SET ten = ?, sdt = ?, email = ? 
             WHERE user_id = ? AND role = 'Nhân viên'"
        );
        $stmt->bind_param("sssi", $ten, $sdt, $email, $user_id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public function delete($user_id) {
        $this->conn->begin_transaction();
        try {
            $stmt1 = $this->conn->prepare("DELETE FROM taikhoan WHERE user_id = ?");
            $stmt1->bind_param("i", $user_id);
            $stmt1->execute();

            $stmt2 = $this->conn->prepare("DELETE FROM user WHERE user_id = ? AND role = 'Nhân viên'");
            $stmt2->bind_param("i", $user_id);
            $stmt2->execute();

            $this->conn->commit();
            return $stmt2->affected_rows > 0;

        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
}
?>
