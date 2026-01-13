<?php
require_once __DIR__ . "/config.php";

if (empty($_SESSION['logged_in'])) {
    http_response_code(403);
    echo json_encode(["error" => "Not authorized"]);
    exit;
}

// Kiểm tra loại upload: popup hay project
$type = $_GET['type'] ?? 'popup';

if ($type === 'project') {
    $targetDir = UPLOAD_PROJECT_DIR;
    $publicUrl = UPLOAD_PROJECT_URL;
} else {
    $targetDir = UPLOAD_POPUP_DIR;
    $publicUrl = UPLOAD_POPUP_URL;
}

// kiểm tra có file upload không
if (!isset($_FILES['image'])) {
    echo json_encode(["error" => "No file uploaded"]);
    exit;
}

// file tạm
$tmpFile = $_FILES['image']['tmp_name'];

list($width, $height, $imageType) = getimagesize($tmpFile);

// max width
$maxWidth = 1200;

// nếu ảnh lớn hơn 1200px → resize
if ($width > $maxWidth) {
    $ratio = $maxWidth / $width;
    $newWidth = $maxWidth;
    $newHeight = (int)($height * $ratio);
} else {
    $newWidth = $width;
    $newHeight = $height;
}

// tạo ảnh nguồn
switch ($imageType) {
    case IMAGETYPE_JPEG:
        $source = imagecreatefromjpeg($tmpFile);
        break;
    case IMAGETYPE_PNG:
        $source = imagecreatefrompng($tmpFile);
        break;
    case IMAGETYPE_WEBP:
        $source = imagecreatefromwebp($tmpFile);
        break;
    default:
        echo json_encode(["error" => "unsupported format"]);
        exit;
}

// tạo ảnh mới resize
$resized = imagecreatetruecolor($newWidth, $newHeight);

// giữ nền trong suốt nếu PNG
imagealphablending($resized, false);
imagesavealpha($resized, true);

imagecopyresampled(
    $resized,
    $source,
    0, 0,
    0, 0,
    $newWidth, $newHeight,
    $width, $height
);

// tạo tên file
$filename = uniqid() . ".webp";
$savePath = $targetDir . $filename;

// lưu file webp
imagewebp($resized, $savePath, 80);

imagedestroy($source);
imagedestroy($resized);

// trả URL public để ghi vào Markdown
echo json_encode([
    "status" => "ok",
    "path" => $publicUrl . $filename
]);
