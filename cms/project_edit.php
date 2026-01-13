<?php
require_once __DIR__ . "/config.php";

if (empty($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

// Nếu có file => EDIT mode
$file = $_GET['file'] ?? null;

// Default data
$data = [
    "title" => "",
    "location" => "",
    "startDate" => "",
    "categories" => "",
    "thumbnail" => "",
    "mainImage" => "",
    "description" => "",
    "info_location" => "",
    "info_scope" => "",
    "gallery" => []
];

// Load dữ liệu nếu EDIT
if ($file && file_exists(PROJECT_DIR . $file)) {
    $content = file_get_contents(PROJECT_DIR . $file);

    if (preg_match('/^---(.*?)---/s', $content, $m)) {
        $yaml = trim($m[1]);
        $lines = explode("\n", $yaml);

        foreach ($lines as $line) {
            if (strpos($line, ":") !== false) {
                list($k, $v) = explode(":", $line, 2);
                $key = trim($k);
                $val = trim($v);

                if ($key === "categories") {
                    $data["categories"] = str_replace(["[", "]"], "", $val);
                } else {
                    $data[$key] = $val;
                }
            }
        }

        // parse gallery manually (list form)
        if (preg_match('/gallery:\s*(.*)/s', $yaml, $gMatch)) {
            preg_match_all('/-\s*(.*)/', $yaml, $imgs);
            $data["gallery"] = $imgs[1];
        }

        // info object
        if (isset($data["info"])) {
            $info = json_decode(str_replace("'", '"', $data["info"]), true);
            if (is_array($info)) {
                $data["info_location"] = $info["location"] ?? "";
                $data["info_scope"] = $info["scope"] ?? "";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title><?= $file ? "Sửa dự án" : "Thêm dự án mới" ?></title>
</head>
<body>

<h2><?= $file ? "Sửa dự án" : "Thêm dự án mới" ?></h2>

<form action="project_save.php" method="post" enctype="multipart/form-data">

  <?php if ($file): ?>
    <input type="hidden" name="file" value="<?= htmlspecialchars($file) ?>">
  <?php endif; ?>

  <label>Tiêu đề:</label><br>
  <input type="text" name="title" value="<?= $data['title'] ?>"><br><br>

  <label>Vị trí (location):</label><br>
  <input type="text" name="location" value="<?= $data['location'] ?>"><br><br>

  <label>Ngày bắt đầu:</label><br>
  <input type="text" name="startDate" value="<?= $data['startDate'] ?>"><br><br>

  <label>Danh mục (categories, phân cách bằng dấu phẩy):</label><br>
  <input type="text" name="categories" value="<?= $data['categories'] ?>"><br><br>

  <label>Mô tả (mỗi dòng là 1 đoạn):</label><br>
  <textarea name="description" rows="5" cols="60"><?= $data['description'] ?></textarea><br><br>

  <label>Thông tin dự án:</label><br>
  <input type="text" name="info_location" placeholder="Địa điểm" value="<?= $data['info_location'] ?>"><br>
  <input type="text" name="info_scope" placeholder="Quy mô" value="<?= $data['info_scope'] ?>"><br><br>

  <hr>

  <h3>Thumbnail</h3>
  <?php if ($data['thumbnail']): ?>
      <img src="<?= $data['thumbnail'] ?>" width="120"><br>
  <?php endif; ?>

  <input type="text" name="thumbnail" value="<?= $data['thumbnail'] ?>" readonly><br>
  <input type="file" name="thumbnail_upload" accept="image/*"><br><br>

  <hr>

  <h3>Main Image</h3>
  <?php if ($data['mainImage']): ?>
      <img src="<?= $data['mainImage'] ?>" width="120"><br>
  <?php endif; ?>

  <input type="text" name="mainImage" value="<?= $data['mainImage'] ?>" readonly><br>
  <input type="file" name="mainImage_upload" accept="image/*"><br><br>

  <hr>

  <h3>Gallery</h3>

  <?php foreach ($data["gallery"] as $img): ?>
      <img src="<?= trim($img) ?>" width="80">
      <br>
  <?php endforeach; ?>

  <p>Thêm ảnh gallery (có thể chọn nhiều):</p>
  <input type="file" name="gallery_upload[]" accept="image/*" multiple><br><br>

  <button type="submit">Lưu dự án</button>
</form>

<br>
<a href="projects.php">← Quay lại danh sách dự án</a>

</body>
</html>
