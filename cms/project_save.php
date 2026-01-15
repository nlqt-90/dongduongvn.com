<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . '/slug_util.php';

if (empty($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

$title      = $_POST['title'] ?? '';
$location   = $_POST['location'] ?? '';
$startDate  = $_POST['startDate'] ?? '';
$categories = $_POST['categories'] ?? [];
$thumbnail  = $_POST['thumbnail'] ?? '';
$mainImage  = $_POST['mainImage'] ?? '';
$descRaw    = $_POST['description'] ?? '';
$infoLoc    = $_POST['info_location'] ?? '';
$infoScope  = $_POST['info_scope'] ?? '';

$file = $_POST['file'] ?? null;

// Tạo slug cơ bản
$baseSlug = vi_slug($title);

// Nếu là tạo mới → đảm bảo slug duy nhất
if (!$file) {
    $slug = $baseSlug;
    $i = 2;
    while (file_exists(PROJECT_DIR . $slug . '.md')) {
        $slug = $baseSlug . '-' . $i;
        $i++;
    }
} else {
    $slug = basename($file, '.md');
}

require_once __DIR__ . '/upload.php';
$cleanSlug = $slug;               // thư mục ảnh dự án
$_POST['project_slug'] = $cleanSlug;

// hàm upload gọn
function up(string $field, string $suffix): ?string {
    if (empty($_FILES[$field]['tmp_name'])) return null;
    return cms_upload_image('project', $_FILES[$field]['tmp_name'], $_FILES[$field]['name'], $_FILES[$field]['type'], $suffix)['path'] ?? null;
}

if ($p = up('thumbnail_upload', $cleanSlug . '_cover')) $thumbnail = $p;
if ($p = up('mainImage_upload', $cleanSlug . '_main'))   $mainImage = $p;

// ----- GALLERY HANDLING (unique filenames, no ordering) -----

// 1. Gallery cũ (để biết ảnh nào sẽ xoá)
$oldGallery = [];
if ($file && file_exists(PROJECT_DIR . $file)) {
    $lines = file(PROJECT_DIR . $file);
    $cur = null;
    foreach ($lines as $ln) {
        $t = trim($ln);
        if ($cur==='gallery') {
            if (preg_match('/^-\s*(.+)$/',$t,$m)) { $oldGallery[] = trim($m[1]); continue; }
            if ($t==='' || preg_match('/^[A-Za-z0-9_]+:/',$t)) $cur=null;
        }
        if ($t==='gallery:') $cur='gallery';
    }
}

// 2. Gallery giữ lại do người dùng không xoá
$gallery = [];
if (!empty($_POST['gallery']) && is_array($_POST['gallery'])) {
    foreach ($_POST['gallery'] as $p) {
        $p = trim($p);
        if ($p!=='') $gallery[] = $p;
    }
}

// 3. Xoá file thật với link bị loại bỏ
if ($file) {
    $removed = array_diff($oldGallery, $gallery);
    foreach ($removed as $url) {
        if (strpos($url, UPLOAD_PROJECT_URL)!==0) continue; // an toàn
        $rel = substr($url, strlen(UPLOAD_PROJECT_URL));
        $full = rtrim(UPLOAD_PROJECT_DIR,'/\\').DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rel);
        if (is_file($full)) @unlink($full);
    }
}

// 4. Upload ảnh mới → tên duy nhất, thêm vào cuối
if (!empty($_FILES['gallery_upload']['tmp_name'][0])) {
    for ($i=0,$n=count($_FILES['gallery_upload']['tmp_name']);$i<$n;$i++) {
        if (!$_FILES['gallery_upload']['tmp_name'][$i]) continue;
        $unique = uniqid($cleanSlug.'_');
        $path = cms_upload_image('project', $_FILES['gallery_upload']['tmp_name'][$i], $_FILES['gallery_upload']['name'][$i], $_FILES['gallery_upload']['type'][$i], $unique)['path'] ?? null;
        if ($path) $gallery[] = $path;
    }
}
// đảm bảo không trùng link
$gallery = array_values(array_unique($gallery));

// fallback cho dữ liệu legacy nếu gallery rỗng
if (empty($gallery) && !empty($_POST['gallery_old'])) {
    $gallery = array_values(array_filter(explode("\n", trim($_POST['gallery_old']))));
}


// Nếu là sửa project: xóa file ảnh đã bị remove khỏi gallery
if ($file) {
    $removed = array_values(array_diff($oldGallery, $gallery));
    foreach ($removed as $url) {
        $url = trim($url);
        if ($url === '') continue;
        // chỉ cho phép xóa trong thư mục uploads/projects/
        if (strpos($url, UPLOAD_PROJECT_URL) !== 0) continue;

        $rel = substr($url, strlen(UPLOAD_PROJECT_URL)); // ví dụ: kiem-tra/kiem-tra-1.webp
        $full = rtrim(UPLOAD_PROJECT_DIR, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rel);

        if (is_file($full)) {
            @unlink($full);
        }
    }
}

// xử lý categories array → YAML
if (!is_array($categories)) $categories = array_map('trim', explode(',', $categories));
if (empty($categories)) {
    echo "<script>alert('Vui lòng chọn ít nhất một danh mục');history.back();</script>";
    exit;
}
$catsYaml = "";
foreach ($categories as $c) {
    $c = trim($c);
    if ($c !== '') $catsYaml .= "  - $c\n";
}

// description YAML
$descYaml = "";
foreach (array_filter(array_map('trim', explode("\n", $descRaw))) as $d) {
    $descYaml .= "  - \"$d\"\n";
}

// gallery YAML
$galleryYaml = "";
foreach ($gallery as $g) {
    $galleryYaml .= "  - $g\n";
}

// ----- VALIDATION BẮT BUỘC KHI THÊM MỚI -----
if (!$file) {                                // chỉ khi thêm dự án
    $miss = [];
    if ($title==='')      $miss[]='Tiêu đề';
    if ($location==='')   $miss[]='Vị trí';
    if ($startDate==='')  $miss[]='Ngày bắt đầu';
    if (empty($categories)) $miss[]='Danh mục';
    if ($thumbnail==='')  $miss[]='Thumbnail';
    if ($mainImage==='')  $miss[]='Main image';
    if ($descRaw==='')    $miss[]='Mô tả';
    if ($infoLoc==='' || $infoScope==='') $miss[]='Thông tin dự án';

    if ($miss) {
        echo "<script>alert('Vui lòng nhập: ".implode(', ',$miss)."');history.back();</script>";
        exit;
    }
}



$markdown = "---\n" .
           "title: \"$title\"\n" .
           "location: \"$location\"\n" .
           "startDate: \"$startDate\"\n" .
           "categories:\n$catsYaml" .
           "thumbnail: $thumbnail\n" .
           "mainImage: $mainImage\n" .
           "description:\n$descYaml" .
           "info:\n  location: \"$infoLoc\"\n  scope: \"$infoScope\"\n" .
           "gallery:\n$galleryYaml" .
           "---\n";

file_put_contents(PROJECT_DIR . $slug . '.md', $markdown);
require_once __DIR__ . '/github_commit.php';
github_commit_file('src/content/projects/' . $slug . '.md', $markdown, 'cms: update project ' . $slug);
header('Location: projects.php');
exit;
