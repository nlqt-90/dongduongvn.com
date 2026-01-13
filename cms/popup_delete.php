<?php
require_once __DIR__ . "/config.php";

if (empty($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

$file = $_GET["file"] ?? null;

if ($file && file_exists(POPUP_DIR . $file)) {
    unlink(POPUP_DIR . $file);
}

header("Location: popups.php");
exit;
