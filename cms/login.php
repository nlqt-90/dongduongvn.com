<?php
require_once __DIR__ . "/config.php";

// session timeout 30 phút
if(isset($_SESSION['expires']) && time() > $_SESSION['expires']){
  session_unset();
  session_destroy();
  header("Location: login.php");
  exit;
}

// Nếu đã login thì vào CMS
if (!empty($_SESSION['logged_in'])) {
    header("Location: index.php");
    exit;
}

// rate limit: tối đa 5 lần sai / phiên
$fail = &$_SESSION['fail'];
if(!isset($fail)) $fail = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if ($user === CMS_USER && password_verify($pass, CMS_PASS_HASH)) {
        // reset fail count
        $fail = 0;
        session_regenerate_id(true);
        $_SESSION['logged_in'] = true;
        $_SESSION['expires'] = time() + 1800; // 30 phút
        header("Location: index.php");
        exit;
    } else {
        $fail++;
        if($fail > 5) usleep(500000); // nghỉ 0.5s làm chậm brute-force
        $error = "Sai tài khoản hoặc mật khẩu!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Đăng nhập CMS</title>
  <link rel="stylesheet" href="https://unpkg.com/@picocss/pico@1.*/css/pico.min.css" />
  <style>
    body{min-height:100vh;display:flex;justify-content:center;align-items:center;background:var(--pico-background-color);padding:1rem;}
    .card{width:100%;max-width:420px;padding:2rem 2.25rem;background:#fff;border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.08);}  
    h2{margin:0 0 1.75rem;text-align:center;}label{margin-bottom:0.25rem;}input{margin-bottom:1rem;}button[type="submit"]{width:100%; margin-top:0.25rem;}
    .error{color:#d33;margin-bottom:1rem;text-align:center;}
  </style>
</head>
<body>
  <main class="card">
<h2>Đăng nhập CMS</h2>

    <?php if(isset($error)):?>
      <div class="error" role="alert"><?=$error?></div>
    <?php endif; ?>

    <form method="post">
      <label for="user">Tên đăng nhập</label>
      <input id="user" name="username" type="text" placeholder="Tên đăng nhập" required autofocus />
      <label for="pass">Mật khẩu</label>
      <input id="pass" name="password" type="password" placeholder="••••••••" required />
      <button type="submit" class="contrast">🔒 Đăng nhập</button>
</form>
  </main>
</body>
</html>
