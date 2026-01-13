<?php
require_once __DIR__ . "/config.php";

if (empty($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

// Nếu có file=abc.md thì là edit
$file = $_GET['file'] ?? null;
$data = [
    "title" => "",
    "active" => "false",
    "startDate" => "",
    "endDate" => "",
    "image" => ""
];

// Nếu EDIT: load frontmatter từ file
if ($file && file_exists(POPUP_DIR . $file)) {
    $content = file_get_contents(POPUP_DIR . $file);

    // Regex improved
    if (preg_match('/^---\s*(.*?)\s*---/s', $content, $m)) {

        $front = trim($m[1]);
        $lines = explode("\n", $front);

        foreach ($lines as $line) {
            if (strpos($line, ":") !== false) {
                list($k, $v) = explode(":", $line, 2);
                $data[trim($k)] = trim($v);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title><?= $file ? "Sửa Popup" : "Thêm Popup" ?></title>
</head>
<body>
<h2><?= $file ? "Sửa Popup" : "Thêm Popup" ?></h2>

<form action="popup_save.php" method="post" enctype="multipart/form-data">

  <?php if ($file): ?>
    <input type="hidden" name="file" value="<?= htmlspecialchars($file) ?>">
  <?php endif; ?>

  <label>Tiêu đề:</label><br>
  <input type="text" name="title" value="<?= htmlspecialchars($data['title']) ?>"><br><br>

  <label>Kích hoạt:</label>
  <input type="checkbox" name="active" <?= ($data['active'] == "true") ? "checked" : "" ?>>
  <br><br>

  <label>Ngày bắt đầu:</label><br>
  <input type="date" name="startDate" value="<?= htmlspecialchars($data['startDate']) ?>"><br><br>

  <label>Ngày kết thúc:</label><br>
  <input type="date" name="endDate" value="<?= htmlspecialchars($data['endDate']) ?>"><br><br>

  <label>Ảnh Popup:</label><br>
  <?php if (!empty($data['image'])): ?>
      <img src="<?= $data['image'] ?>" width="120"><br>
  <?php endif; ?>

  <input type="text" name="image" value="<?= htmlspecialchars($data['image']) ?>" readonly><br>
  <input type="file" name="image_upload" accept="image/*"><br><br>

  <button type="submit">Lưu</button>
</form>

<br>
<a href="popups.php">← Quay lại danh sách popup</a>

</body>
</html>
