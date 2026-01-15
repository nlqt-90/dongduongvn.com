<?php
require_once __DIR__ . "/config.php";

if (empty($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

$file = $_GET["file"] ?? null;

if ($file && file_exists(POPUP_DIR . $file)) {
    // đọc file để lấy đường dẫn ảnh
    $content = file_get_contents(POPUP_DIR . $file);
    if (preg_match('/image:\\s*(.+)/', $content, $m)) {
        $imgPath = trim($m[1]);
        $imgFs   = __DIR__ . "/.." . $imgPath; // /uploads/... relative to project root
        if (is_file($imgFs)) {
            @unlink($imgFs);
        }
    }
    unlink(POPUP_DIR . $file);

    require_once __DIR__ . "/github_commit.php";
    $remotePath = "src/content/popups/" . $file;
    github_commit_file($remotePath, "", "cms: delete popup $file");
}

header("Location: popups.php");
exit;
