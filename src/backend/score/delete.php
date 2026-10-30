<?php

/**
 * 入力:
 * {
 *     score_id: 1,
 *     csrf_token: "..."
 * }
 *
 * 出力:
 * {
 *     success: true/false,
 *     message: "..."
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
$scoreId = $data['score_id'] ?? null;
$csrfToken = $data['csrf_token'] ?? null;

$session = new Lib\Session();
$sessionCsrfToken = new Lib\CSRFToken();

if (
    $scoreId === null ||
    $csrfToken === null
) {
    echo json_encode([
        "success" => false,
        "message" => "必要なパラメータが不足しています。"
    ]);
    exit;
}

$scoreId = filter_var(
    $scoreId,
    FILTER_VALIDATE_INT
);

if (
    $scoreId === false ||
    $scoreId <= 0
) {
    echo json_encode([
        "success" => false,
        "message" => "score_idが不正です。"
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
        "message" => "CSRFトークンが無効です。"
    ]);
    exit;
}
//!SECTION

//SECTION: 処理の実行
try {
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
        "SELECT
            id,
            store_id
         FROM scores
         WHERE id = ?",
        [$scoreId]
    );

    $score = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$score) {
        echo json_encode([
            "success" => false,
            "message" => "対象得点が存在しません。"
        ]);
        exit;
    }

    if ($staff['role'] === 'store') {

        $stmt = $db->exec(
            "SELECT 1
             FROM staff_stores
             WHERE staff_id = ?
               AND store_id = ?",
            [
                $staffId,
                $score['store_id']
            ]
        );

        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode([
                "success" => false,
                "message" => "この得点を削除する権限がありません。"
            ]);
            exit;
        }
    }

    $db->begin();

    $db->exec(
        "DELETE FROM scores
         WHERE id = ?",
        [$scoreId]
    );

    $db->commit();

    echo json_encode([
        "success" => true,
        "message" => "得点を削除しました。"
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