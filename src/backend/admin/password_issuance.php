<?php

/**
 * 入力:
 * {
 *     "userId": 1,
 *     "csrfToken": "CSRFトークン"
 * }
 *
 * 出力:
 * {
 *     "success": true,
 *     "message": "パスコードを発行しました。",
 *     "passcode": "123456"
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

    echo json_encode([
        "success" => false,
        "message" => "JSONのデコードに失敗しました。",
        "data" => []
    ]);

    exit;
}
//!SECTION

//SECTION: 入力のバリデーション
$userId = $data['userId'] ?? null;
$csrfToken = $data['csrfToken'] ?? null;

$sessionCsrfToken = new Lib\CSRFToken();

if (
    $userId === null ||
    $csrfToken === null
) {

    echo json_encode([
        "success" => false,
        "message" => "必要なパラメータが不足しています。",
        "data" => []
    ]);

    exit;
}

$userId = filter_var(
    $userId,
    FILTER_VALIDATE_INT
);

if (
    $userId === false ||
    $userId <= 0
) {

    echo json_encode([
        "success" => false,
        "message" => "ユーザーIDが不正です。",
        "data" => []
    ]);

    exit;
}

if (
    !$sessionCsrfToken->validateToken(
        $csrfToken
    )
) {

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

    $stmt = $db->exec(
        "
        SELECT
            id,
            finished_at
        FROM
            users
        WHERE
            id = ?
        LIMIT 1
        ",
        [
            $userId
        ]
    );

    $user = $stmt->fetch(
        PDO::FETCH_ASSOC
    );

    if (!$user) {

        echo json_encode([
            "success" => false,
            "message" => "対象ユーザーが存在しません。",
            "data" => []
        ]);

        exit;
    }

    if (
        $user['finished_at']
        !== null
    ) {

        echo json_encode([
            "success" => false,
            "message" => "対象ユーザーは終了済みです。",
            "data" => []
        ]);

        exit;
    }

    $passcode = str_pad(
        (string) random_int(
            0,
            999999
        ),
        6,
        '0',
        STR_PAD_LEFT
    );

    $db->exec(
        "
        UPDATE
            users
        SET
            passcode = ?
        WHERE
            id = ?
        ",
        [
            $passcode,
            $userId
        ]
    );

    echo json_encode([
        "success" => true,
        "message" => "パスコードを発行しました。",
        "passcode" => $passcode
    ]);

} catch (\Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "処理中にエラーが発生しました。",
        "data" => []
    ]);

}
//!SECTION