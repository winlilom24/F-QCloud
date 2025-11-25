<?php
require_once __DIR__ . '/../../Controller/RegisterController.php';

$controller = new RegisterController();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $controller->register($_POST);
}

$error = $controller->error;
$success = $controller->success;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>F-QCloud - Đăng ký</title>
<link rel="stylesheet" href="../../Public/css/Form.css?v=<?=time()?>">
</head>
<body>

<div class="container">
  <div class="left">
    <div class="hero" style="background-image: url('../../Public/images/Trang-chu-2.jpg')"></div>
  </div>

  <main class="right">
    <div class="box">

      <div class="brand">
        <div class="logo">☁️</div>
        <h1>F-QCloud</h1>
      </div>
      <p class="sub">Bar - Cafe, Nhà hàng, Karaoke & Software</p>

      <?php 
        if($error) echo "<p class='msg error'>$error</p>"; 
        if($success) echo "<p class='msg success'>$success</p>";
      ?>

      <form class="form" method="post" autocomplete="off">

        <label class="field">
          <span class="label">Họ và tên</span>
          <input type="text" name="ten" placeholder="Nhập họ và tên" required />
        </label>

        <label class="field">
          <span class="label">Tên gian hàng</span>
          <input type="text" name="ten_quan" placeholder="Nhập tên gian hàng" required />
        </label>

        <label class="field">
          <span class="label">Số điện thoại</span>
          <input type="text" name="sdt" placeholder="Nhập số điện thoại" required />
        </label>

        <label class="field">
          <span class="label">Email</span>
          <input type="email" name="email" placeholder="Nhập email" required />
        </label>

        <label class="field">
          <span class="label">Tên đăng nhập</span>
          <input type="text" name="tai_khoan" placeholder="Nhập tên đăng nhập" required />
        </label>

        <label class="field">
          <span class="label">Mật khẩu</span>
          <div class="passwrap">
            <input type="password" name="mat_khau" placeholder="Nhập mật khẩu" required />
            <button type="button" class="eye">👁️</button>
          </div>
        </label>

        <button class="btn primary" type="submit">🛒 Đăng ký</button>

        <div class="signup-link">
          Đã có tài khoản? <a href="../Login/Form.php">Đăng nhập</a>
        </div>
      </form>

      <div class="footerline">
        <div>📞 0857551919</div>
        <div>🌐 Tiếng Việt (VN)</div>
      </div>

    </div>
  </main>
</div>


<script>
const eyes = document.querySelectorAll('.eye');
eyes.forEach(eye => {
  eye.addEventListener('click', () => {
    const input = eye.previousElementSibling;
    input.type = input.type === "password" ? "text" : "password";
  });
});
</script>

</body>
</html>
