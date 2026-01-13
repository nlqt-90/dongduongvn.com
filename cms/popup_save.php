<?php
require_once __DIR__ . "/config.php";

if (empty($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

// Helper tạo slug từ title
function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]+/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

// Lấy dữ liệu POST
$title      = $_POST['title'] ?? "";
$active     = isset($_POST['active']) ? "true" : "false";
$startDate  = $_POST['startDate'] ?? "";
$endDate    = $_POST['endDate'] ?? "";   // ← FIX LỖI 500 TẠI ĐÂY
$imagePath  = $_POST['image'] ?? "";

// Kiểm tra chế độ edit (có file cũ hay không)
$file = $_POST['file'] ?? null;

if ($file) {
    // EDIT MODE
    $slug = basename($file, ".md");
} else {
    // NEW MODE → tự tạo slug mới
    $slug = slugify($title) . "-" . time();
}

// Xử lý upload ảnh (nếu có)
if (!empty($_FILES['image_upload']['tmp_name'])) {

    $tmpFile = $_FILES['image_upload']['tmp_name'];
    $uploadUrl = "upload.php?type=popup";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uploadUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $data = [
        'image' => new CURLFile(
            $tmpFile,
            $_FILES['image_upload']['type'],
            $_FILES['image_upload']['name']
        ),
    ];

    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $resp = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($resp, true);

    if (!empty($json["path"])) {
        $imagePath = $json["path"];
    }
}

// Tạo Markdown frontmatter
$markdown =
"---\n" .
"title: " . $title . "\n" .
"active: " . $active . "\n" .
"startDate: " . $startDate . "\n" .
"endDate: " . $endDate . "\n" .
"image: " . $imagePath . "\n" .
"---\n";

// Ghi file Markdown
$fullPath = POPUP_DIR . $slug . ".md";
file_put_contents($fullPath, $markdown);

// Commit lên GitHub
require_once __DIR__ . "/github_commit.php";

$remotePath = "src/content/popups/" . $slug . ".md";
github_commit_file($remotePath, $markdown, "cms: update popup $slug");

// Redirect
header("Location: popups.php");
exit;
