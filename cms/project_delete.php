<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . '/slug_util.php';

if (empty($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

$file = $_GET["file"] ?? null;

if ($file && file_exists(PROJECT_DIR . $file)) {
    // Xoá file markdown local
    unlink(PROJECT_DIR . $file);

    // Xoá thư mục ảnh liên quan
    $slug = basename($file, '.md');
    $dir  = UPLOAD_PROJECT_DIR . $slug;
    if (is_dir($dir)) {
        foreach (glob($dir . '/*') as $img) @unlink($img);
        @rmdir($dir);
    }

    // commit xoá file trên GitHub
    require_once __DIR__ . '/github_commit.php';
    github_delete_file('src/content/projects/' . $file, 'cms: delete project ' . $slug);
}

header("Location: projects.php");
exit;
