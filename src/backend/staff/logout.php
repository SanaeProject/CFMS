<?php

/**
 * 説明: ログイン中のスタッフをログアウトさせるAPI
 * 入力: { "csrf_token": "CSRFトークン" }
 * 出力: success, message, data
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: " . getenv("APP_URL"));
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST, OPTIONS');
    echo json_encode([
        "success" => false,
        "message" => "POSTメソッドを使用してください。",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);
if (!is_array($data)) {
    echo json_encode([
        "success" => false,
        "message" => "JSONのデコードに失敗しました。",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

//SECTION: 入力のバリデーション
//TODO: 必要なパラメータのバリデーションを行う
$csrfToken = $data['csrf_token'] ?? null;
if (!is_string($csrfToken) || $csrfToken === '') {
    echo json_encode([
        "success" => false,
        "message" => "必要なパラメータが不足しています。",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$sessionCsrfToken = new Lib\CSRFToken();
if (!$sessionCsrfToken->validateToken($csrfToken)) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "CSRFトークンが無効です。",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
//!SECTION

//SECTION: 処理の実行
//TODO: 入力に基づく処理を実行する
try {
    $session = new Lib\Session();
    $session->clear();
    $session->destroy();

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax'
        ]);
    }

    echo json_encode([
        "success" => true,
        "message" => "ログアウトしました。",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);
} catch (\Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "ログアウト中にエラーが発生しました。",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);
}
//!SECTION
