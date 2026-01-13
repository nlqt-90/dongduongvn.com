<?php
require_once __DIR__ . "/config.php";

if (empty($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

// Lấy danh sách file project
$files = glob(PROJECT_DIR . "*.md");

// Hàm parse frontmatter
function parseFrontmatter($content) {
    if (preg_match('/^---\s*(.*?)\s*---\s*(.*)$/s', $content, $m)) {
        $yaml = $m[1];
        $lines = explode("\n", trim($yaml));
        $data = [];
        foreach ($lines as $line) {
            if (strpos($line, ":") !== false) {
                list($k, $v) = explode(":", $line, 2);
                $data[trim($k)] = trim($v);
            }
        }
        return [$data, $m[2]];
    }
    return [[], $content];
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Quản lý Dự án</title>
</head>
<body>

<h2>Danh sách Dự án</h2>
<a href="project_edit.php">+ Thêm dự án mới</a>
<br><br>

<table border="1" cellpadding="8" cellspacing="0">
<tr>
  <th>File</th>
  <th>Tiêu đề</th>
  <th>Vị trí</th>
  <th>Thumbnail</th>
  <th>Actions</th>
</tr>

<?php foreach ($files as $file): ?>
<?php
  $content = file_get_contents($file);
  list($fm, $body) = parseFrontmatter($content);
  $filename = basename($file);
?>
<tr>
  <td><?= $filename ?></td>
  <td><?= $fm['title'] ?? '(không có)' ?></td>
  <td><?= $fm['location'] ?? '' ?></td>
  <td>
      <img src="<?= $fm['thumbnail'] ?? '' ?>" width="80">
  </td>
  <td>
    <a href="project_edit.php?file=<?= $filename ?>">Sửa</a>
    <a href="project_delete.php?file=<?= $filename ?>" onclick="return confirm('Xóa dự án này?')">Xóa</a>
  </td>

</td>
  
</tr>
<?php endforeach; ?>

</table>

<br>
<a href="index.php">← Về Dashboard</a>

</body>
</html>
