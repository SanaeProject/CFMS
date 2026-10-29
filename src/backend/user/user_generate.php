<?php

/**
 * 説明: ユーザーを生成します。既存セッションにユーザーがいる場合は既存ユーザーを返します。
 * 入力: { csrf_token: "CSRFトークン" }（業務入力なし。セッションを使用）
 * 出力: { success, message, data: { userId, token, isActive, finishedAt } }
 */

//SECTION: 共通 - 入力の取得
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
    echo json_encode(["success" => false, "message" => "POSTメソッドを使用してください。", "data" => []], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

$rawData = file_get_contents('php://input');
$data = $rawData === '' ? [] : json_decode($rawData, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "JSONのデコードに失敗しました。", "data" => []], JSON_UNESCAPED_UNICODE);
    exit;
}
//!SECTION

//SECTION: 入力のバリデーション
$csrfToken = $data['csrf_token'] ?? null;
$csrf = new Lib\CSRFToken();
if (!is_string($csrfToken) || !$csrf->validateToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "CSRFトークンが無効です。", "data" => []], JSON_UNESCAPED_UNICODE);
    exit;
}
//!SECTION

//SECTION: 処理の実行
$db = null;
try {
    $session = new Lib\Session();
    $db = Lib\DB::connect();
    $db->begin();

    $token = $session->get('user_id');
    $user = false;

    if (is_string($token) && $token !== '') {
        $stmt = $db->exec(
            "SELECT id, token, is_active, finished_at FROM users WHERE token = :token LIMIT 1",
            [':token' => $token]
        );
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$user) {
        $session->remove('user_id');

        // 視認性の高い文字種から、暗号学的に安全な10文字トークンを生成する。
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $token = '';
            for ($i = 0; $i < 10; $i++) {
                $token .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $check = $db->exec(
                "SELECT id FROM users WHERE token = :token LIMIT 1",
                [':token' => $token]
            );
        } while ($check->fetch(PDO::FETCH_ASSOC) !== false);

        $db->exec(
            "INSERT INTO users (token, is_active, finished_at, created_at) VALUES (:token, 0, NULL, CURRENT_TIMESTAMP)",
            [':token' => $token]
        );

        $stmt = $db->exec(
            "SELECT id, token, is_active, finished_at FROM users WHERE token = :token LIMIT 1",
            [':token' => $token]
        );
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            throw new RuntimeException('生成したユーザーを取得できませんでした。');
        }
    }

    $db->commit();
    $session->set('user_id', (string)$user['token']);

    echo json_encode([
        "success" => true,
        "message" => "ユーザー情報を取得しました。",
        "data" => [
            "userId" => (int)$user['id'],
            "token" => (string)$user['token'],
            "isActive" => (bool)$user['is_active'],
            "finishedAt" => $user['finished_at']
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (\Throwable $e) {
    if ($db !== null) {
        $db->rollback();
    }
    error_log('User generate API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "処理中にエラーが発生しました。", "data" => []], JSON_UNESCAPED_UNICODE);
}
//!SECTION
