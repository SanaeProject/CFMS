<?php

/**
 * 説明: 受付スタッフまたは管理者がユーザーをアクティベートします。
 * 入力: { token: "10文字のユーザートークン", csrfToken: "CSRFトークン" }
 * 出力: { success, message, data: { token, isActive } }
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: " . (getenv("APP_URL") ?: ""));
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
        'success' => false,
        'message' => 'POSTメソッドを使用してください。',
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

$rawData = file_get_contents('php://input');
$data = $rawData === '' ? [] : json_decode($rawData, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'JSONのデコードに失敗しました。',
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$csrfToken = $data['csrf_token'] ?? null;
$csrf = new Lib\CSRFToken();
if (!is_string($csrfToken) || !$csrf->validateToken($csrfToken)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'CSRFトークンが無効です。',
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$token = $data['token'] ?? null;
if (!is_string($token) || !preg_match('/^[A-Z0-9]{10}$/', $token)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'トークンは英大文字と数字の10文字で指定してください。',
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$db = null;
try {
    $session = new Lib\Session();

    // 認証情報はリクエスト本文ではなくサーバーセッションから取得する。
    // スタッフログインAPIが使用するセッションキーは staff_id を想定。
    $staffId = $session->get('staff_id');
    if ((!is_int($staffId) && !(is_string($staffId) && ctype_digit($staffId))) || (int)$staffId <= 0) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'スタッフログインが必要です。',
            'data' => []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $db = Lib\DB::connect();
    $db->begin();

    $staffStmt = $db->exec(
        'SELECT id, role FROM staff WHERE id = :staffId LIMIT 1',
        [':staffId' => (int)$staffId]
    );
    $staff = $staffStmt->fetch(PDO::FETCH_ASSOC);

    if (!$staff) {
        $db->rollback();
        $session->remove('staff_id');
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'ログイン中のスタッフが見つかりません。再ログインしてください。',
            'data' => []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 管理者と受付担当のみ許可する。role はDBの値を使用し、クライアントから受け取らない。
    if (!in_array((string)$staff['role'], ['admin', 'reception'], true)) {
        $db->rollback();
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'アクティベートを行う権限がありません。',
            'data' => []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 同時リクエストによる二重アクティベートを防ぐため、対象行をロックする。
    $userStmt = $db->exec(
        'SELECT id, token, is_active, finished_at FROM users WHERE token = :token LIMIT 1 FOR UPDATE',
        [':token' => $token]
    );
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $db->rollback();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => '該当するユーザーが見つかりません。',
            'data' => []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($user['finished_at'] !== null) {
        $db->rollback();
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => '終了済みのアカウントはアクティベートできません。',
            'data' => ['token' => (string)$user['token'], 'isActive' => (bool)$user['is_active']]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ((bool)$user['is_active']) {
        $db->rollback();
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'このユーザーはすでにアクティベートされています。',
            'data' => ['token' => (string)$user['token'], 'isActive' => true]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $update = $db->exec(
        'UPDATE users SET is_active = 1 WHERE id = :userId AND is_active = 0 AND finished_at IS NULL',
        [':userId' => (int)$user['id']]
    );

    if ($update->rowCount() !== 1) {
        $db->rollback();
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'ユーザー状態が変更されたため、アクティベートできませんでした。状態を再確認してください。',
            'data' => []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $db->commit();
    echo json_encode([
        'success' => true,
        'message' => 'ユーザーをアクティベートしました。',
        'data' => [
            'token' => (string)$user['token'],
            'isActive' => true
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (\Throwable $e) {
    if ($db !== null) {
        $db->rollback();
    }
    error_log('User activate API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '処理中にエラーが発生しました。',
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
}
