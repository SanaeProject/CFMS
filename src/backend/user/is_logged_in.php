<?php

/**
 * 入力: {}
 *
 * 出力:
 * {
 *     success: true,
 *     isLoggedIn: true
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
        "isLoggedIn" => false
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

//!SECTION

//SECTION: 入力のバリデーション
try {

    $session = new Lib\Session();

    $token = $session->get('user_id');

    if (empty($token)) {
        echo json_encode([
            "success" => true,
            "isLoggedIn" => false
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
} catch (\Exception $e) {

    echo json_encode([
        "success" => true,
        "isLoggedIn" => false
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//!SECTION

//SECTION: 処理の実行
try {

    $db = Lib\DB::connect();

    $stmt = $db->exec(
        "SELECT
            token,
            is_active,
            finished_at
         FROM users
         WHERE token = ?",
        [$token]
    );

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$user ||
        !(bool)$user['is_active'] ||
        $user['finished_at'] !== null
    ) {

        $session->remove('user_id');

        echo json_encode([
            "success" => true,
            "isLoggedIn" => false
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    echo json_encode([
        "success" => true,
        "isLoggedIn" => true
    ], JSON_UNESCAPED_UNICODE);
} catch (\Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "isLoggedIn" => false
    ]);
}

//!SECTION