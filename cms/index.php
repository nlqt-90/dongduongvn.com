<?php
require_once __DIR__ . "/config.php";
if (empty($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard CMS</title>
  <link rel="stylesheet" href="https://unpkg.com/@picocss/pico@1.*/css/pico.min.css" />
  <style>
    body{min-height:100vh;display:flex;justify-content:center;align-items:center;background:var(--pico-background-color);padding:1rem;}
    .card{width:100%;max-width:560px;padding:2.5rem 3rem;background:#fff;border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.07);}
    h1{margin:0 0 2rem;text-align:center;font-weight:700;}

    /* Menu list */
    ul.menu{list-style:none!important;margin:0;padding:0;display:flex;flex-direction:column;gap:1.1rem;}
    ul.menu li{list-style:none;}
    ul.menu a{display:block;padding:1rem 1.25rem;border:1px solid var(--pico-border);border-radius:8px;text-decoration:none;transition:color .2s,border-color .2s;}
    ul.menu a:hover{color:var(--pico-primary);border-color:var(--pico-primary);}
    ul.menu .emoji{margin-right:.6rem;font-size:1.25rem;line-height:1;}

    footer{margin-top:2.5rem;text-align:center;font-size:.8rem;color:var(--pico-muted-color);} 
    @media (max-width:480px){.card{padding:2rem 1.5rem;}}
  </style>
</head>
<body>
  <main class="card">
    <h1>Bảng điều khiển</h1>
    <ul class="menu">
      <li><a href="popups.php"><span class="emoji">📢</span>Quản lý Popups</a></li>
      <li><a href="projects.php"><span class="emoji">🏗️</span>Quản lý Dự án</a></li>
      <li><a href="logout.php"><span class="emoji">🔒</span>Đăng xuất</a></li>
    </ul>
    <footer>© <?=date('Y')?> TuanT. All rights reserved.</footer>
  </main>
</body>
</html>