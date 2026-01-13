<?php
require_once __DIR__ . "/config.php";

// load env
$env = require __DIR__ . '/.env.php';

define("GITHUB_REPO", $env["GITHUB_REPO"]);
define("GITHUB_TOKEN", $env["GITHUB_TOKEN"]);

function github_commit_file($pathInRepo, $content, $message) {

    $apiUrl = "https://api.github.com/repos/" . GITHUB_REPO . "/contents/" . $pathInRepo;

    // Check if file exists to get sha
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "User-Agent: CMS",
        "Authorization: token " . GITHUB_TOKEN
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $existing = curl_exec($ch);
    curl_close($ch);

    $existingJson = json_decode($existing, true);
    $sha = $existingJson['sha'] ?? null;

    // Prepare data for PUT
    $data = [
        "message" => $message,
        "content" => base64_encode($content),
        "branch"  => "main"
    ];

    if ($sha) {
        $data["sha"] = $sha;
    }

    // Send file to GitHub
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "User-Agent: CMS",
        "Authorization: token " . GITHUB_TOKEN,
        "Content-Type: application/json",
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}
