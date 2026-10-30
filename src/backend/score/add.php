<?php

/**
 * 入力:
 * {
 *     token: "xxxxxxxxxx",
 *     store_id: 1,
 *     score: 100,
 *     csrf_token: "..."
 * }
 *
 * 出力:
 * {
 *     success: true/false,
 *     message: "処理結果",
 *     storeUsageCount: 1
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
$token = trim($data['token'] ?? '');
$storeId = $data['store_id'] ?? null;
$score = $data['score'] ?? null;
$csrfToken = $data['csrf_token'] ?? null;

$sessionCsrfToken = new Lib\CSRFToken();

if (
    $token === '' ||
    $storeId === null ||
    $score === null ||
    $csrfToken === null
) {
    echo json_encode([
        "success" => false,
        "message" => "必要なパラメータが不足しています。"
    ]);
    exit;
}

if (strlen($token) !== 10) {
    echo json_encode([
        "success" => false,
        "message" => "トークン形式が不正です。"
    ]);
    exit;
}

$storeId = filter_var(
    $storeId,
    FILTER_VALIDATE_INT
);

if ($storeId === false || $storeId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "store_idが不正です。"
    ]);
    exit;
}

$score = filter_var(
    $score,
    FILTER_VALIDATE_INT
);

if ($score === false || $score <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "ポイント値が不正です。"
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
        "SELECT
            id,
            role
         FROM staff
         WHERE id = ?",
        [$staffId]
    );

    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$staff ||
        !in_array(
            $staff['role'],
            ['store', 'admin'],
            true
        )
    ) {
        echo json_encode([
            "success" => false,
            "message" => "権限がありません。"
        ]);
        exit;
    }

    $stmt = $db->exec(
        "SELECT id
         FROM stores
         WHERE id = ?",
        [$storeId]
    );

    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode([
            "success" => false,
            "message" => "店舗が存在しません。"
        ]);
        exit;
    }

    $stmt = $db->exec(
        "SELECT 1
         FROM staff_stores
         WHERE staff_id = ?
           AND store_id = ?",
        [
            $staffId,
            $storeId
        ]
    );

    if (
        $staff['role'] !== 'admin' &&
        !$stmt->fetch(PDO::FETCH_ASSOC)
    ) {
        echo json_encode([
            "success" => false,
            "message" => "担当店舗ではありません。"
        ]);
        exit;
    }

    $stmt = $db->exec(
        "SELECT
            id,
            token,
            is_active,
            finished_at
         FROM users
         WHERE token = ?",
        [$token]
    );

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            "success" => false,
            "message" => "ユーザーが存在しません。"
        ]);
        exit;
    }

    if (!(bool)$user['is_active']) {
        echo json_encode([
            "success" => false,
            "message" => "ユーザーが未アクティベートです。"
        ]);
        exit;
    }

    if ($user['finished_at'] !== null) {
        echo json_encode([
            "success" => false,
            "message" => "終了済みユーザーです。"
        ]);
        exit;
    }

    $usageLimit = (int)(
        getenv('GAME_CNT') ?: 3
    );

    $db->begin();

    $stmt = $db->exec(
        "SELECT COUNT(*) AS cnt
         FROM scores
         WHERE user_id = ?
           AND store_id = ?",
        [
            $user['id'],
            $storeId
        ]
    );

    $usageCount = (int)$stmt
        ->fetch(PDO::FETCH_ASSOC)['cnt'];

    if ($usageCount >= $usageLimit) {

        $db->rollback();

        echo json_encode([
            "success" => false,
            "message" => "利用回数上限に達しています。"
        ]);
        exit;
    }

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
            $user['id'],
            $staffId,
            $storeId,
            $score
        ]
    );

    $usageCount++;

    $db->commit();

    echo json_encode([
        "success" => true,
        "message" => "得点を登録しました。",
        "storeUsageCount" => $usageCount
    ], JSON_UNESCAPED_UNICODE);
} catch (\Exception $e) {

    if (isset($db)) {
        $db->rollback();
    }

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "処理中にエラーが発生しました。"
    ]);
}

//!SECTION