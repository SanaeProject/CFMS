<?php

/**
 * メニュー情報を取得します。
 *
 * 入力:
 * {
 *     csrf_token: "CSRFトークン"
 * }
 *
 * 出力:
 * {
 *     success: true/false,
 *     message: "処理結果",
 *     data: {
 *         accessiblePages: [],
 *         staff: {
 *             id: 1,
 *             name: "スタッフ名",
 *             role: "admin"
 *         },
 *         stores: []
 *     }
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
        'success' => false,
        'message' => 'POSTメソッドを使用してください。',
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

$rawData = file_get_contents('php://input');
$requestData = json_decode($rawData, true);

if ($requestData === null) {
    echo json_encode([
        'success' => false,
        'message' => 'JSONのデコードに失敗しました。',
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
//!SECTION

//SECTION: 入力値のバリデーション
use Lib\CSRFToken;

$csrfToken = $requestData['csrf_token'] ?? null;
$sessionCsrfToken = new CSRFToken();

if ($csrfToken === null) {
    echo json_encode([
        'success' => false,
        'message' => '必要なパラメータが不足しています。',
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!$sessionCsrfToken->validateToken($csrfToken)) {
    echo json_encode([
        'success' => false,
        'message' => 'CSRFトークンが無効です。',
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
//!SECTION

//SECTION: メイン処理
use Lib\DB;
use Lib\Session;

try {
    $session = new Session();
    $staffId = $session->get('staff_id');

    if (!$staffId) {
        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => 'ログインが必要です。',
            'data' => []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $db = DB::connect();

    $stmt = $db->exec(
        'SELECT id, name, role
         FROM staff
         WHERE id = :id',
        [
            ':id' => $staffId
        ]
    );

    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$staff) {
        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => 'スタッフ情報が見つかりません。',
            'data' => []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pageMap = [
        'admin' => ['reception', 'store', 'prize'],
        'reception' => ['reception'],
        'store' => ['store'],
        'prize' => ['prize']
    ];

    if (!array_key_exists($staff['role'], $pageMap)) {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'アクセス権限がありません。',
            'data' => []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $data = [
        'accessiblePages' => $pageMap[$staff['role']],
        'staff' => [
            'id' => (int)$staff['id'],
            'name' => $staff['name'],
            'role' => $staff['role']
        ],
        'stores' => []
    ];

    if ($staff['role'] === 'admin') {
        $stmt = $db->exec(
            'SELECT id, name
             FROM stores
             ORDER BY id',
            []
        );

        $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data['stores'] = array_map(
            static function ($store) {
                return [
                    'id' => (int)$store['id'],
                    'name' => $store['name']
                ];
            },
            $stores
        );
    } elseif ($staff['role'] === 'store') {
        $stmt = $db->exec(
            'SELECT s.id, s.name
             FROM stores AS s
             INNER JOIN staff_stores AS ss
                 ON ss.store_id = s.id
             WHERE ss.staff_id = :staff_id
             ORDER BY s.id',
            [
                ':staff_id' => $staffId
            ]
        );

        $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data['stores'] = array_map(
            static function ($store) {
                return [
                    'id' => (int)$store['id'],
                    'name' => $store['name']
                ];
            },
            $stores
        );
    }

    echo json_encode([
        'success' => true,
        'message' => 'メニュー情報を取得しました。',
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'サーバーエラーが発生しました。',
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
}
//!SECTION