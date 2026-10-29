<?php

/**
 * 説明: セッションに紐付くユーザー情報とポイント獲得履歴を取得します。
 * 入力: { csrf_token: "CSRFトークン" }（業務入力なし。セッションを使用）
 * 出力: { success, message, data: { userId, token, scores, totalScore, isActive, finishedAt } }
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
        "SELECT id, token, is_active, finished_at FROM users WHERE token = :token LIMIT 1",
        [':token' => $token]
    );
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        $session->remove('user_id');
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "ユーザーが見つかりません。", "data" => []], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $db->exec(
        "SELECT scores.id AS scoreId,
                scores.score AS score,
                scores.store_id AS storeId,
                stores.name AS storeName,
                scores.created_at AS createdAt
         FROM scores
         INNER JOIN stores ON stores.id = scores.store_id
         WHERE scores.user_id = :userId
         ORDER BY scores.created_at DESC, scores.id DESC",
        [':userId' => (int)$user['id']]
    );
    $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalScore = 0;

    foreach ($scores as &$score) {
        $score['scoreId'] = (int)$score['scoreId'];
        $score['score'] = (int)$score['score'];
        $score['storeId'] = (int)$score['storeId'];
        $totalScore += $score['score'];
    }
    unset($score);

    echo json_encode([
        "success" => true,
        "message" => "ユーザー情報を取得しました。",
        "data" => [
            "userId" => (int)$user['id'],
            "token" => (string)$user['token'],
            "scores" => $scores,
            "totalScore" => $totalScore,
            "isActive" => (bool)$user['is_active'],
            "finishedAt" => $user['finished_at']
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (\Throwable $e) {
    error_log('User info API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "処理中にエラーが発生しました。", "data" => []], JSON_UNESCAPED_UNICODE);
}
//!SECTION
