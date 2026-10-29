<?php

/**
 * 説明: ユーザー再ログインAPI
 *
 * 入力:
 * {
 *     "token": "ABCDEFGHIJ",
 *     "passcode": "123456",
 *     "csrf_token": "xxxxxxxx"
 * }
 *
 * 出力:
 * {
 *     "success": true,
 *     "message": "ログインに成功しました。",
 *     "data": []
 * }
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

if ($data === null) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "JSONのデコードに失敗しました。",
        "data" => []
    ]);

    exit;
}
//!SECTION

//SECTION: 入力のバリデーション

$token = trim($data['token'] ?? '');
$passcode = $data['passcode'] ?? '';
$csrfToken = $data['csrf_token'] ?? null;

$sessionCsrfToken = new Lib\CSRFToken();

if (
    empty($token) ||
    empty($passcode) ||
    empty($csrfToken)
) {
    echo json_encode([
        "success" => false,
        "message" => "必要なパラメータが不足しています。",
        "data" => []
    ]);

    exit;
}

if (strlen($token) !== 10) {
    echo json_encode([
        "success" => false,
        "message" => "トークンは10文字で入力してください。",
        "data" => []
    ]);

    exit;
}

if (!$sessionCsrfToken->validateToken($csrfToken)) {
    echo json_encode([
        "success" => false,
        "message" => "CSRFトークンが無効です。",
        "data" => []
    ]);

    exit;
}

//!SECTION

//SECTION: 処理の実行

try {

    $db = Lib\DB::connect();

    //ANCHOR - ユーザー情報取得

    $stmt = $db->exec(
        "
        SELECT
            id,
            name,
            token,
            passcode,
            is_active,
            finished_at
        FROM users
        WHERE token = :token
        LIMIT 1
        ",
        [
            ':token' => $token
        ]
    );

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    //ANCHOR - ユーザー存在確認

    if (!$user) {
        echo json_encode([
            "success" => false,
            "message" => "トークンまたはパスコードが正しくありません。",
            "data" => []
        ]);

        exit;
    }

    //ANCHOR - アクティブ状態確認

    if (!(bool)$user['is_active']) {
        echo json_encode([
            "success" => false,
            "message" => "このアカウントは現在利用できません。",
            "data" => []
        ]);

        exit;
    }

    //ANCHOR - パスコード発行確認

    if ($user['passcode'] === null) {
        echo json_encode([
            "success" => false,
            "message" => "パスコードが発行されていません。",
            "data" => []
        ]);

        exit;
    }

    //ANCHOR - 終了済み確認

    if ($user['finished_at'] !== null) {
        echo json_encode([
            "success" => false,
            "message" => "このアカウントは終了しています。",
            "data" => []
        ]);

        exit;
    }

    //ANCHOR - パスコード認証

    if (!password_verify($passcode, $user['passcode'])) {
        echo json_encode([
            "success" => false,
            "message" => "トークンまたはパスコードが正しくありません。",
            "data" => []
        ]);

        exit;
    }

    //ANCHOR - ユーザーセッション作成

    $session = new Lib\Session();
    $session->regenerateId(true);

    $session->set(
        'user_token',
        $user['token']
    );
    //ANCHOR - ログイン成功レスポンス

    echo json_encode([
        "success" => true,
        "message" => "ログインに成功しました。",
        "data" => []
    ]);

} catch (\Exception $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "処理中にエラーが発生しました。",
        "data" => []
    ]);
}

//!SECTION