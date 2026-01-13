<?php
require_once __DIR__ . "/config.php";

if (empty($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

$file = $_GET["file"] ?? null;

if ($file && file_exists(PROJECT_DIR . $file)) {
    unlink(PROJECT_DIR . $file);
}

require_once __DIR__ . "/github_commit.php";

$remotePath = "src/content/popups/" . $file;
github_commit_file($remotePath, "", "cms: delete popup $file");


header("Location: projects.php");
exit;
