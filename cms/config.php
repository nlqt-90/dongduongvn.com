<?php
session_start();

// USER / PASS cho CMS
const CMS_USER = "admin";
const CMS_PASS = "123456"; // Đổi lại ngay khi dùng thật!

// ĐƯỜNG DẪN TỚI FOLDER CONTENT (markdown)
define("POPUP_DIR", realpath(__DIR__ . "/../src/content/popups/") . "/");
define("PROJECT_DIR", realpath(__DIR__ . "/../src/content/projects/") . "/");

// ĐƯỜNG DẪN LƯU FILE ẢNH TRÊN SERVER
define("UPLOAD_POPUP_DIR", realpath(__DIR__ . "/../uploads/popups/") . "/");
define("UPLOAD_PROJECT_DIR", realpath(__DIR__ . "/../uploads/projects/") . "/");

// URL public cho ảnh
const UPLOAD_POPUP_URL = "/uploads/popups/";
const UPLOAD_PROJECT_URL = "/uploads/projects/";
