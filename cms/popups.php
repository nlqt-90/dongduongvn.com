<?php
require_once __DIR__ . "/config.php";

if (empty($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

// Lấy danh sách file popup
$files = glob(POPUP_DIR . "*.md");

// Hàm parse frontmatter
function parseFrontmatter($content) {
    if (preg_match('/^---\s*(.*?)\s*---\s*(.*)$/s', $content, $matches)) {
        $yaml = $matches[1];
        $body = $matches[2];

        // Chuyển YAML sang array (simple)
        $lines = explode("\n", trim($yaml));
        $data = [];
        foreach ($lines as $line) {
            if (strpos($line, ":") !== false) {
                list($key, $value) = explode(":", $line, 2);
                $data[trim($key)] = trim($value);
            }
        }
        return [$data, $body];
    }
    return [[], $content];
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title>Quản lý Popups</title>
</head>
<body>
<h2>Danh sách Popup</h2>
<a href="popup_edit.php">+ Thêm Popup mới</a>
<br><br>

<table border="1" cellpadding="8">
  <tr>
    <th>File</th>
    <th>Tiêu đề</th>
    <th>Hình</th>
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
    <td><img src="<?= $fm['image'] ?? '' ?>" width="80" /></td>
    <td>
        <a href="popup_edit.php?file=<?= $filename ?>">Sửa</a> |
        <a href="popup_delete.php?file=<?= $filename ?>" onclick="return confirm('Xóa popup này?')">Xóa</a>
    </td>

  </tr>
<?php endforeach; ?>

</table>

<br>
<a href="index.php">← Về Dashboard</a>
</body>
</html>
