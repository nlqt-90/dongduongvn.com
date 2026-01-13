<?php
require_once __DIR__ . "/config.php";

if (empty($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
?>
<h2>CHỌN CHỨC NĂNG</h2>

<ul>
  <li><a href="popups.php">Quản lý Popups</a></li>
  <li><a href="projects.php">Quản lý Dự án</a></li>
  <li><a href="logout.php">Đăng xuất</a></li>
</ul>
