<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . '/slug_util.php';  // dùng hàm vi_slug

if (empty($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

// Lấy dữ liệu POST
$title      = $_POST['title'] ?? '';
$active     = isset($_POST['active']) ? 'true' : 'false';
$startDate  = $_POST['startDate'] ?? '';
$endDate    = $_POST['endDate'] ?? '';
$imagePath  = $_POST['image'] ?? '';
$file       = $_POST['file'] ?? null;

// Validation cơ bản
if ($title==='') { echo "<script>alert('Tiêu đề thông báo không được trống');history.back();</script>"; exit; }
if ($startDate==='' || $endDate==='') { echo "<script>alert('Ngày hiển thị và ngày tắt hiển thị không được trống');history.back();</script>"; exit; }
if (strtotime($startDate) > strtotime($endDate)) { echo "<script>alert('Ngày hiển thị không được lớn hơn ngày tắt hiển thị');history.back();</script>"; exit; }

// Tạo slug (duy nhất cho popup)
if ($file) {
    $slug = basename($file, '.md');
} else {
    $base = vi_slug($title);
    $slug = $base;
    $i    = 2;
    while (file_exists(POPUP_DIR . $slug . '.md')) {
        $slug = $base . '-' . $i;
        $i++;
    }
}

// Upload ảnh nếu có
if (!empty($_FILES['image_upload']['tmp_name'])) {
    require_once __DIR__ . '/upload.php';
    $_POST['slug'] = $slug;
    $res = cms_upload_image('popup', $_FILES['image_upload']['tmp_name'], $_FILES['image_upload']['name'], $_FILES['image_upload']['type'], $slug);
    if (!empty($res['path'])) $imagePath = $res['path'];
}

if ($imagePath==='') { echo "<script>alert('Vui lòng chọn ảnh popup');history.back();</script>"; exit; }

$markdown = "---\n".
            "title: $title\n".
            "active: $active\n".
            "startDate: $startDate\n".
            "endDate: $endDate\n".
            "image: $imagePath\n".
            "---\n";

file_put_contents(POPUP_DIR.$slug.'.md', $markdown);
require_once __DIR__ . '/github_commit.php';
github_commit_file('src/content/popups/'.$slug.'.md', $markdown, 'cms: update popup '.$slug);
header('Location: popups.php');
exit;
