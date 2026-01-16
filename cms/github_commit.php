<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

// --- Load env or fallback .env.php ---
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
 * Call GitHub API via cURL
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
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    $responseBody = curl_exec($ch);
    $httpCode     = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return ['body'=>$responseBody,'http_code'=>$httpCode];
}

/**
 * Add / update file content (skips if identical)
 */
function github_commit_file(string $path, string $content, string $message): array
{
    if (empty(GITHUB_TOKEN) || GITHUB_TOKEN==='dummy') return ['skipped'=>true];
    $api = "https://api.github.com/repos/".GITHUB_REPO."/contents/".$path;

    // fetch existing to get sha & compare
    $sha=null; $identical=false;
    $get = curl_request('GET',$api);
    if($get['http_code']==200){
        $data=json_decode($get['body'],true);
        $sha=$data['sha']??null;
        $cur=base64_decode(str_replace("\n","",$data['content']??''));
        $identical=($cur===$content);
    }
    if($identical) return ['skipped'=>true,'reason'=>'no changes'];

    $payload=[
        'message'=>$message,
        'content'=>base64_encode($content),
        'branch'=>GITHUB_BRANCH,
    ];
    if($sha) $payload['sha']=$sha;
    $put=curl_request('PUT',$api,json_encode($payload));
    if($put['http_code']>=400) throw new RuntimeException('GitHub commit failed '.$put['http_code']);
    return json_decode($put['body'],true);
}

/**
 * Delete a file from repo
 */
function github_delete_file(string $path,string $message):array{
    if (empty(GITHUB_TOKEN)||GITHUB_TOKEN==='dummy') return ['skipped'=>true];
    $api="https://api.github.com/repos/".GITHUB_REPO."/contents/".$path;
    $get=curl_request('GET',$api);
    if($get['http_code']!==200) return ['skipped'=>true,'reason'=>'not found'];
    $sha=json_decode($get['body'],true)['sha']??null;
    if(!$sha) return ['error'=>'sha missing'];
    $payload=[
        'message'=>$message,
        'sha'=>$sha,
        'branch'=>GITHUB_BRANCH
    ];
    $del=curl_request('DELETE',$api,json_encode($payload));
    if($del['http_code']>=400) throw new RuntimeException('GitHub delete failed '.$del['http_code']);
    return json_decode($del['body'],true);
}
