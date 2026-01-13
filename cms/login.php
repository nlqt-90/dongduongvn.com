<?php
require_once __DIR__ . "/config.php";

// Nếu đã login thì vào CMS
if (!empty($_SESSION['logged_in'])) {
    header("Location: index.php");
    exit;
}

// Nếu bấm submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if ($user === CMS_USER && $pass === CMS_PASS) {
        $_SESSION['logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "Sai tài khoản hoặc mật khẩu!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Đăng nhập CMS</title>
</head>
<body>
<h2>Đăng nhập CMS</h2>
<?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
<form method="post">
  <label>User:</label><br>
  <input type="text" name="username" /><br><br>

  <label>Password:</label><br>
  <input type="password" name="password" /><br><br>

  <button type="submit">Đăng nhập</button>
</form>
</body>
</html>
