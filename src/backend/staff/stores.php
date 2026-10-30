<?php

/**
 * 入力: {}
 *
 * 出力: {
 *     success: true/false,
 *     stores: [
 *         {
 *             id: 1,
 *             name: "1組店舗"
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
        "message" => "POSTメソッドを使用してください。",
        "stores" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if ($data === null && $rawData !== '') {
    echo json_encode([
        "success" => false,
        "message" => "JSONのデコードに失敗しました。",
        "stores" => []
    ]);
    exit;
}

//!SECTION

//SECTION: 入力のバリデーション
$csrfToken = $data['csrf_token'] ?? null;

$sessionCsrfToken = new Lib\CSRFToken();

if ($csrfToken === null) {
    echo json_encode([
        "success" => false,
        "message" => "必要なパラメータが不足しています。",
        "stores" => []
    ]);
    exit;
}

if (!$sessionCsrfToken->validateToken($csrfToken)) {
    echo json_encode([
        "success" => false,
        "message" => "CSRFトークンが無効です。",
        "stores" => []
    ]);
    exit;
}

try {
    $session = new Lib\Session();

    $staffId = $session->get('staff_id');

    if (empty($staffId)) {
        echo json_encode([
            "success" => false,
            "message" => "ログインしてください。",
            "stores" => []
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

    if (!$staff || $staff['role'] !== 'admin' && $staff['role'] !== 'store') {
        echo json_encode([
            "success" => false,
            "message" => "権限がありません。",
            "stores" => []
        ]);
        exit;
    }
} catch (\Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "認証に失敗しました。",
        "stores" => []
    ]);
    exit;
}

//!SECTION

//SECTION: 処理の実行
try {

    $db = Lib\DB::connect();
    $stmt = null;

    if ($staff['role'] === 'store') {
        $stmt = $db->exec(
            "SELECT s.id, s.name
             FROM stores s
             JOIN staff_stores ss ON s.id = ss.store_id
             WHERE ss.staff_id = ?",
            [$staffId]
        );
    } else {
        $stmt = $db->exec(
            "SELECT id, name
             FROM stores"
        );
    }
    $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "stores" => array_map(
            static function ($store) {
                return [
                    "id" => (int)$store['id'],
                    "name" => $store['name']
                ];
            },
            $stores
        )
    ], JSON_UNESCAPED_UNICODE);
} catch (\Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "処理中にエラーが発生しました。",
        "stores" => []
    ]);
}

//!SECTION