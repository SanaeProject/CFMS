<?php

/**
 * 説明: セッションに紐付くユーザーの最終ハートビート時刻を更新します。
 * 入力: { csrf_token: "CSRFトークン" }（業務入力なし。セッションを使用）
 * 出力: { success, message, data: [] }
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
try {
    $session = new Lib\Session();
    $token = $session->get('user_id');
    if (!is_string($token) || $token === '') {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "ユーザーセッションがありません。", "data" => []], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $db = Lib\DB::connect();
    $stmt = $db->exec(
        "UPDATE users
         SET last_heartbeat_at = CURRENT_TIMESTAMP
         WHERE token = :token AND finished_at IS NULL",
        [':token' => $token]
    );

    if ($stmt->rowCount() === 0) {
        $check = $db->exec(
            "SELECT id, finished_at FROM users WHERE token = :token LIMIT 1",
            [':token' => $token]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$check) {
            $session->remove('user_id');
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "ユーザーが見つかりません。", "data" => []], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 終了済みユーザーはハートビート更新対象外。ページ表示自体は妨げない。
        if ($check['finished_at'] === null) {
            // 同じ秒に更新済みの場合、rowCount()が0になるDB設定を考慮し、
            // ユーザーが未終了なら正常として扱う。
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "ハートビートを受信しました。",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    error_log('User heartbeat API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "処理中にエラーが発生しました。", "data" => []], JSON_UNESCAPED_UNICODE);
}
//!SECTION
