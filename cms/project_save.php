<?php
require_once __DIR__ . "/config.php";

if (empty($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

// Tạo slug từ title
function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]+/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

// Lấy dữ liệu POST
$title      = $_POST["title"] ?? "";
$location   = $_POST["location"] ?? "";
$startDate  = $_POST["startDate"] ?? "";
$categories = $_POST["categories"] ?? "";
$thumbnail  = $_POST["thumbnail"] ?? "";
$mainImage  = $_POST["mainImage"] ?? "";
$descRaw    = $_POST["description"] ?? "";
$infoLoc    = $_POST["info_location"] ?? "";
$infoScope  = $_POST["info_scope"] ?? "";

$gallery = []; // chứa URL gallery cuối cùng

// File edit hay new?
$file = $_POST["file"] ?? null;

if ($file) {
    $slug = basename($file, ".md");
} else {
    $slug = slugify($title) . "-" . time();
}

// Hàm gọi upload.php
function uploadImage($fieldName, $type = "project") {
    if (empty($_FILES[$fieldName]["tmp_name"])) return null;

    $tmp = $_FILES[$fieldName]["tmp_name"];

    $url = "upload.php?type=" . $type;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        "image" => new CURLFile(
            $tmp,
            $_FILES[$fieldName]["type"],
            $_FILES[$fieldName]["name"]
        )
    ]);

    $resp = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($resp, true);
    return $json["path"] ?? null;
}

// Upload Thumbnail
$thumbUploaded = uploadImage("thumbnail_upload", "project");
if ($thumbUploaded) {
    $thumbnail = $thumbUploaded;
}

// Upload Main Image
$mainUploaded = uploadImage("mainImage_upload", "project");
if ($mainUploaded) {
    $mainImage = $mainUploaded;
}

// Upload Gallery (nhiều ảnh)
if (!empty($_FILES["gallery_upload"]["tmp_name"][0])) {
    $files = $_FILES["gallery_upload"];

    for ($i = 0; $i < count($files["tmp_name"]); $i++) {
        if (empty($files["tmp_name"][$i])) continue;

        $tmpFile = $files["tmp_name"][$i];

        // CURL từng ảnh
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "upload.php?type=project");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            "image" => new CURLFile(
                $tmpFile,
                $files["type"][$i],
                $files["name"][$i]
            )
        ]);

        $resp = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($resp, true);
        if (!empty($json["path"])) {
            $gallery[] = $json["path"];
        }
    }
}

// Nếu không upload mới, lấy gallery cũ
if (empty($gallery) && !empty($_POST["gallery_old"])) {
    $gallery = explode("\n", trim($_POST["gallery_old"]));
}

// format categories thành YAML list
$catsArray = array_map('trim', explode(",", $categories));
$catsYaml = "";
foreach ($catsArray as $c) {
    if ($c !== "") $catsYaml .= "  - $c\n";
}

// format description thành YAML list
$descArray = array_filter(array_map("trim", explode("\n", $descRaw)));
$descYaml = "";
foreach ($descArray as $d) {
    $descYaml .= "  - \"" . addslashes($d) . "\"\n";
}

// format gallery list YAML
$galleryYaml = "";
foreach ($gallery as $g) {
    $galleryYaml .= "  - $g\n";
}

// tạo Markdown
$markdown =
"---\n" .
"title: $title\n" .
"location: \"$location\"\n" .
"startDate: \"$startDate\"\n" .
"categories:\n$catsYaml" .
"thumbnail: $thumbnail\n" .
"mainImage: $mainImage\n" .
"description:\n$descYaml" .
"info:\n" .
"  location: \"$infoLoc\"\n" .
"  scope: \"$infoScope\"\n" .
"gallery:\n$galleryYaml" .
"---\n";

// Ghi file
$fullPath = PROJECT_DIR . $slug . ".md";
file_put_contents($fullPath, $markdown);

// Redirect
header("Location: projects.php");
exit;
