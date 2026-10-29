<?php

/**
 * スタッフログイン
 *
 * 入力:
 * {
 *     "staffId": 1,
 *     "name": "山田太郎",
 *     "password": "password",
 *     "csrf_token": "xxxx"
 * }
 *
 * 出力:
 * {
 *     "success": true,
 *     "message": "ログインしました。",
 *     "data": {
 *          "id": 1,
 *          "name": "山田太郎",
 *          "role": "admin"
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


//SECTION: 入力バリデーション

$staffId = $data['staffId'] ?? null;
$name = $data['name'] ?? null;
$password = $data['password'] ?? null;
$csrfToken = $data['csrf_token'] ?? null;

$sessionCsrfToken = new Lib\CSRFToken();

if (
    $staffId === null ||
    $name === null ||
    $password === null ||
    $csrfToken === null
) {
    echo json_encode([
        "success" => false,
        "message" => "必要なパラメータが不足しています。",
        "data" => []
    ]);

    exit;
}

$staffId = filter_var(
    $staffId,
    FILTER_VALIDATE_INT
);

if ($staffId === false || $staffId < 1) {

    echo json_encode([
        "success" => false,
        "message" => "スタッフIDが不正です。",
        "data" => []
    ]);

    exit;
}

if (mb_strlen($name) > 100) {

    echo json_encode([
        "success" => false,
        "message" => "スタッフ名が長すぎます。",
        "data" => []
    ]);

    exit;
}

if (mb_strlen($password) > 255) {

    echo json_encode([
        "success" => false,
        "message" => "パスワードが不正です。",
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


//SECTION: ログイン処理
try {

    $db = Lib\DB::connect();

    $stmt = $db->exec(
        "
        SELECT
            id,
            name,
            password,
            role
        FROM
            staff
        WHERE
            id = :id
        LIMIT 1
        ",
        [
            ':id' => $staffId
        ]
    );

    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$staff) {

        echo json_encode([
            "success" => false,
            "message" => "スタッフが存在しません。",
            "data" => []
        ]);

        exit;
    }

    if ($staff['name'] !== $name) {

        echo json_encode([
            "success" => false,
            "message" => "ログイン情報が正しくありません。",
            "data" => []
        ]);

        exit;
    }

    if (
        !password_verify(
            $password,
            $staff['password']
        )
    ) {

        echo json_encode([
            "success" => false,
            "message" => "ログイン情報が正しくありません。",
            "data" => []
        ]);

        exit;
    }

    $session = new Lib\Session();

    $session->regenerateId();

    $session->set(
        'staff_id',
        $staff['id']
    );

    $session->set(
        'staff_name',
        $staff['name']
    );

    $session->set(
        'staff_role',
        $staff['role']
    );

    $session->set(
        'login_time',
        time()
    );

    echo json_encode([
        "success" => true,
        "message" => "ログインしました。",
        "data" => [
            "id" => $staff['id'],
            "name" => $staff['name'],
            "role" => $staff['role']
        ]
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
