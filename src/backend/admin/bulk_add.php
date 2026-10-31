<?php

/**
 * 入力:
 * {
 *     stores: [
 *         {
 *             storeId: 1,
 *             score: 100
 *         },
 *         {
 *             storeId: 2,
 *             score: 200
 *         }
 *     ],
 *     csrf_token: "..."
 * }
 *
 * 出力:
 * {
 *     success: true,
 *     token: "Ab3K9LmX2Q",
 *     message: "ユーザーを作成し得点を登録しました。"
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
        "message" => "POSTメソッドを使用してください。"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if ($data === null) {
    echo json_encode([
        "success" => false,
        "message" => "JSONのデコードに失敗しました。"
    ]);
    exit;
}

//!SECTION

//SECTION: 入力のバリデーション
$stores = $data['stores'] ?? null;
$csrfToken = $data['csrf_token'] ?? null;

$sessionCsrfToken = new Lib\CSRFToken();

if (
    !is_array($stores) ||
    count($stores) === 0 ||
    $csrfToken === null
) {
    echo json_encode([
        "success" => false,
        "message" => "必要なパラメータが不足しています。"
    ]);
    exit;
}

if (!$sessionCsrfToken->validateToken($csrfToken)) {
    echo json_encode([
        "success" => false,
        "message" => "CSRFトークンが無効です。"
    ]);
    exit;
}

//!SECTION

//SECTION: 処理の実行
try {

    $session = new Lib\Session();

    $staffId = $session->get('staff_id');

    if (empty($staffId)) {
        echo json_encode([
            "success" => false,
            "message" => "ログインしてください。"
        ]);
        exit;
    }

    $db = Lib\DB::connect();

    $stmt = $db->exec(
        "SELECT role
         FROM staff
         WHERE id = ?",
        [$staffId]
    );

    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$staff ||
        $staff['role'] !== 'admin'
    ) {
        echo json_encode([
            "success" => false,
            "message" => "権限がありません。"
        ]);
        exit;
    }

    foreach ($stores as $storeData) {

        $storeId = filter_var(
            $storeData['storeId'] ?? null,
            FILTER_VALIDATE_INT
        );

        $score = filter_var(
            $storeData['score'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            $storeId === false ||
            $storeId <= 0
        ) {
            throw new Exception(
                'storeIdが不正です。'
            );
        }

        if (
            $score === false ||
            $score <= 0
        ) {
            throw new Exception(
                'scoreが不正です。'
            );
        }

        $stmt = $db->exec(
            "SELECT id
             FROM stores
             WHERE id = ?",
            [$storeId]
        );

        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            throw new Exception(
                '対象店舗が存在しません。'
            );
        }
    }

    $characters =
        'abcdefghijklmnopqrstuvwxyz' .
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
        '0123456789';

    do {

        $token = '';

        for ($i = 0; $i < 10; $i++) {

            $token .= $characters[random_int(
                0,
                strlen($characters) - 1
            )];
        }

        $stmt = $db->exec(
            "SELECT id
             FROM users
             WHERE token = ?",
            [$token]
        );

        $exists =
            $stmt->fetch(PDO::FETCH_ASSOC);
    } while ($exists);

    $db->begin();

    $finishedAt = date(
        'Y-m-d H:i:s'
    );

    $db->exec(
        "INSERT INTO users (
            token,
            passcode,
            is_active,
            finished_at,
            last_heartbeat_at
        ) VALUES (
            ?, ?, ?, ?, ?
        )",
        [
            $token,
            null,
            1,
            $finishedAt,
            null
        ]
    );

    $stmt = $db->exec(
        "SELECT LAST_INSERT_ID() AS id"
    );

    $userId = (int)$stmt
        ->fetch(PDO::FETCH_ASSOC)['id'];

    foreach ($stores as $storeData) {

        $db->exec(
            "INSERT INTO scores (
                user_id,
                staff_id,
                store_id,
                score
            ) VALUES (
                ?, ?, ?, ?
            )",
            [
                $userId,
                $staffId,
                (int)$storeData['storeId'],
                (int)$storeData['score']
            ]
        );
    }

    $db->commit();

    echo json_encode([
        "success" => true,
        "token" => $token,
        "message" => "ユーザーを作成し得点を登録しました。"
    ], JSON_UNESCAPED_UNICODE);
} catch (\Exception $e) {

    if (isset($db)) {
        $db->rollback();
    }

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}

//!SECTION