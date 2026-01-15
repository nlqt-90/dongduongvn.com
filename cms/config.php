<?php
// Load local env array file if present (.env.php returns associative array)
$envFile = __DIR__ . '/.env.php';
if (file_exists($envFile)) {
  $vars = include $envFile;
  if (is_array($vars)) {
    foreach ($vars as $k => $v) {
      if (!getenv($k)) { // only set if not already in real env
        putenv("{$k}={$v}");
        $_ENV[$k] = $v;
      }
    }
  }
}

// Secure session cookie params
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
  'lifetime' => 0,
  'path'     => '/',
  'domain'   => '',
  'secure'   => $secure,
  'httponly' => true,
  'samesite' => 'Strict'
]);
session_start();

// USER / PASS HASH (bắt buộc phải có biến môi trường)
define('CMS_USER', getenv('CMS_USER') ?: 'admin');
$hash = getenv('CMS_PASS_HASH');
if(!$hash){
  http_response_code(500);
  die('Biến môi trường CMS_PASS_HASH chưa được thiết lập.');
}
define('CMS_PASS_HASH', $hash);

// ĐƯỜNG DẪN TỚI FOLDER CONTENT (markdown)
define("POPUP_DIR", realpath(__DIR__ . "/../src/content/popups/") . "/");
define("PROJECT_DIR", realpath(__DIR__ . "/../src/content/projects/") . "/");

// ĐƯỜNG DẪN LƯU FILE ẢNH TRÊN SERVER
define("UPLOAD_POPUP_DIR", __DIR__ . "/../uploads/popups/");
define("UPLOAD_PROJECT_DIR", __DIR__ . "/../uploads/projects/");

// URL public cho ảnh
const UPLOAD_POPUP_URL = "/uploads/popups/";
const UPLOAD_PROJECT_URL = "/uploads/projects/";
