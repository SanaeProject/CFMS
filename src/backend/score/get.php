<?php

/**
 * 入力:
 * {
 *     token: "xxxxxxxxxx",
 *     store_id: 1,
 *     csrf_token: "..."
 * }
 *
 * 出力:
 * {
 *     success: true,
 *     token: "...",
 *     user: {
 *         isActive: true,
 *         isFinished: false
 *     },
 *     totalScore: 100,
 *     scores: [],
 *     storeUsageCount: 1,
 *     storeUsageLimit: 3,
 *     canAddScore: true
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
$csrfToken = $data['csrf_token'] ?? null;

$sessionCsrfToken = new Lib\CSRFToken();

if ($token === '' || $storeId === null || $csrfToken === null) {
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

$storeId = filter_var($storeId, FILTER_VALIDATE_INT);

if ($storeId === false || $storeId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "store_idが不正です。"
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
        "SELECT id, role
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

    $stmt = $db->exec(
        "SELECT
            s.id,
            s.created_at,
            s.store_id,
            st.name AS store_name,
            s.score
         FROM scores s
         INNER JOIN stores st
             ON st.id = s.store_id
         WHERE s.user_id = ?
         ORDER BY s.created_at DESC",
        [$user['id']]
    );

    $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->exec(
        "SELECT COALESCE(SUM(score), 0) AS total_score
         FROM scores
         WHERE user_id = ?",
        [$user['id']]
    );

    $totalScore = (int)$stmt
        ->fetch(PDO::FETCH_ASSOC)['total_score'];

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

    $storeUsageCount = (int)$stmt
        ->fetch(PDO::FETCH_ASSOC)['cnt'];

    $storeUsageLimit = (int)(
        getenv('GAME_CNT') ?: 3
    );

    $isActive = (bool)$user['is_active'];
    $isFinished = $user['finished_at'] !== null;

    $canAddScore =
        $isActive &&
        !$isFinished &&
        (
            $storeUsageCount <
            $storeUsageLimit
        );

    echo json_encode([
        "success" => true,
        "token" => $user['token'],
        "user" => [
            "isActive" => $isActive,
            "isFinished" => $isFinished
        ],
        "totalScore" => $totalScore,
        "scores" => array_map(
            static function ($score) {
                return [
                    "scoreId" => (int)$score['id'],
                    "createdAt" => $score['created_at'],
                    "storeId" => (int)$score['store_id'],
                    "storeName" => $score['store_name'],
                    "score" => (int)$score['score']
                ];
            },
            $scores
        ),
        "storeUsageCount" => $storeUsageCount,
        "storeUsageLimit" => $storeUsageLimit,
        "canAddScore" => $canAddScore
    ], JSON_UNESCAPED_UNICODE);
} catch (\Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "処理中にエラーが発生しました。"
    ]);
}

//!SECTION