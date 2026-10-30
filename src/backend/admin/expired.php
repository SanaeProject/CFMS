<?php

/**
 * 入力: {}
 *
 * 出力: {
 *     success: true,
 *     expiredUsers: [
 *         {
 *             userToken: "abcdefghij",
 *             lastStoreId: 1,
 *             lastStoreName: "1組店舗",
 *             lastHeartbeatAt: "2026-10-30 12:00:00"
 *         }
 *     ]
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

if ($data === null && $rawData !== '') {
    echo json_encode([
        "success" => false,
        "message" => "JSONのデコードに失敗しました。"
    ]);
    exit;
}

//!SECTION

//SECTION: 入力のバリデーション
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
        !in_array(
            $staff['role'],
            ['reception', 'admin'],
            true
        )
    ) {
        echo json_encode([
            "success" => false,
            "message" => "権限がありません。"
        ]);
        exit;
    }
} catch (\Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => "認証に失敗しました。"
    ]);

    exit;
}

//!SECTION

//SECTION: 処理の実行
try {

    $stmt = $db->exec(
        "SELECT
            id,
            token,
            last_heartbeat_at
         FROM users
         WHERE is_active = 1
           AND finished_at IS NULL
           AND last_heartbeat_at IS NOT NULL
           AND last_heartbeat_at < DATE_SUB(NOW(), INTERVAL 2 MINUTE)
         ORDER BY last_heartbeat_at DESC"
    );

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $expiredUsers = [];

    foreach ($users as $user) {

        $stmt = $db->exec(
            "SELECT
                s.store_id,
                st.name
             FROM scores s
             INNER JOIN stores st
                ON st.id = s.store_id
             WHERE s.user_id = ?
             ORDER BY s.created_at DESC
             LIMIT 1",
            [$user['id']]
        );

        $lastStore = $stmt->fetch(PDO::FETCH_ASSOC);

        $expiredUsers[] = [
            "userToken" => $user['token'],
            "lastStoreId" =>
            $lastStore
                ? (int)$lastStore['store_id']
                : null,
            "lastStoreName" =>
            $lastStore
                ? $lastStore['name']
                : null,
            "lastHeartbeatAt" =>
            $user['last_heartbeat_at']
        ];
    }

    echo json_encode([
        "success" => true,
        "expiredUsers" => $expiredUsers
    ], JSON_UNESCAPED_UNICODE);
} catch (\Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "処理中にエラーが発生しました。"
    ]);
}

//!SECTION