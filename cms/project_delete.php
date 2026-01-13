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

header("Location: projects.php");
exit;
