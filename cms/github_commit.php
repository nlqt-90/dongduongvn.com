<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

// --- Nạp biến môi trường hoặc fallback .env.php (chỉ dùng khi DEV) ---
$envArr = [];
$envPath = __DIR__ . '/.env.php';
if (file_exists($envPath)) {
    $envArr = include $envPath;
    if (!is_array($envArr)) $envArr = [];
}

$repo   = getenv('GITHUB_REPO')   ?: ($envArr['GITHUB_REPO']   ?? '');
$token  = getenv('GITHUB_TOKEN')  ?: ($envArr['GITHUB_TOKEN']  ?? '');
$branch = getenv('GITHUB_BRANCH') ?: ($envArr['GITHUB_BRANCH'] ?? 'main');

if (!defined('GITHUB_REPO'))   define('GITHUB_REPO', $repo);
if (!defined('GITHUB_TOKEN'))  define('GITHUB_TOKEN', $token);
if (!defined('GITHUB_BRANCH')) define('GITHUB_BRANCH', $branch);

/**
 * Commit (hoặc tạo mới) 1 file vào repo.
 *
 * @param string $pathInRepo  Đường dẫn file trong repo (ví dụ: "src/content/popups/foo.md")
 * @param string $content     Nội dung file thuần text
 * @param string $message     Thông điệp commit
 * @return array              JSON trả về từ GitHub API hoặc ['skipped' => true]
 */
function github_commit_file(string $pathInRepo, string $content, string $message): array
{
    // ‑- BỎ QUA KHI LOCAL / thiếu token ‑-
    if (empty(GITHUB_TOKEN) || GITHUB_TOKEN === 'dummy') {
        error_log("[github_commit] SKIPPED $pathInRepo – lý do: token rỗng hoặc dummy");
        return ['skipped' => true];
    }

    $apiUrl = "https://api.github.com/repos/" . GITHUB_REPO . "/contents/" . $pathInRepo;

    // 1) Kiểm tra file tồn tại để lấy 'sha'
    $sha = null;
    $existing = curl_request('GET', $apiUrl);
    if ($existing['http_code'] === 200) {
        $body = json_decode($existing['body'], true);
        $sha  = $body['sha'] ?? null;
    }

    // 2) Gửi PUT
    $payload = [
        'message' => $message,
        'content' => base64_encode($content),
        'branch'  => GITHUB_BRANCH,
    ];
    if ($sha) $payload['sha'] = $sha;

    $put = curl_request('PUT', $apiUrl, json_encode($payload));

    if ($put['http_code'] >= 400) {
        throw new RuntimeException("GitHub commit thất bại: {$put['http_code']} – {$put['body']}");
    }

    return json_decode($put['body'], true);
}

/**
 * Hàm wrapper cho curl với header mặc định.
 */
function curl_request(string $method, string $url, string $body = null): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_USERAGENT      => 'CMS',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: token ' . GITHUB_TOKEN,
            'Content-Type: application/json',
        ],
        CURLOPT_CUSTOMREQUEST  => $method,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $responseBody = curl_exec($ch);
    $httpCode     = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    return ['body' => $responseBody, 'http_code' => $httpCode];
}