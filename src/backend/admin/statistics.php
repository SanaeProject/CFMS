<?php

/**
 * 管理者向けの集計データを返します。
 * 管理者(Staff.role = admin)のみ実行できます。
 * 入力: {
 *      csrf_token: "CSRFトークン"
 * }
 * 出力: {
 *      success: true/false,
 *      message: "処理結果のメッセージ",
 *      data: [],
 *      totalUsers: 総ユーザー数,
 *      activeUsers: アクティベート済みユーザー数(is_active = 1),
 *      finishedUsers: 終了済みユーザー数(finished_atが設定済み),
 *      recentUserRanking: [ // 今日を含む暦日3日分の利用者ランキング(利用回数の多い順)
 *          {userId: ユーザーID, userName: "利用者名", useCount: 利用回数},
 *          ...
 *      ],
 *      storeUserRanking: [ // 店舗別利用者数ランキング(同一ユーザーは1人として換算。利用者0人の店舗は含めない)
 *          {storeId: 店舗ID, storeName: "店舗名", userCount: 利用者数},
 *          ...
 *      ]
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
    echo json_encode(["success" => false, "message" => "POSTメソッドを使用してください。", "data" => []], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if ($data === null) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "JSONのデコードに失敗しました。",
        "data" => []
    ]);
    exit;
}
//!SECTION

//SECTION: 入力のバリデーション
$csrfToken = $data['csrf_token'] ?? null;
$sessionCsrfToken = new Lib\CSRFToken();

if ($csrfToken === null) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "必要なパラメータが不足しています。",
        "data" => []
    ]);
    exit;
}

if (!$sessionCsrfToken->validateToken($csrfToken)) {
    http_response_code(403);
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
    //ANCHOR - 管理者権限の確認（セッションのstaff_idからStaff.roleを検索）
    $session = new Lib\Session();
    $staffId = $session->get('staff_id');
    if ($staffId === null) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "ログインしていません。",
            "data" => []
        ]);
        exit;
    }

    $db = Lib\DB::connect();
    $stmt = $db->exec("SELECT role FROM Staff WHERE id = :id", [':id' => $staffId]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($staff === false || $staff['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "管理者権限がありません。",
            "data" => []
        ]);
        exit;
    }

    //ANCHOR - ユーザー数の集計
    $stmt = $db->exec(
        "SELECT
            COUNT(*) AS total_users,
            COALESCE(SUM(is_active = 1), 0) AS active_users,
            COALESCE(SUM(finished_at IS NOT NULL), 0) AS finished_users
         FROM Users"
    );
    $userCounts = $stmt->fetch(PDO::FETCH_ASSOC);

    //ANCHOR - 今日を含む暦日3日分の利用者ランキング（同姓同名もuser_idで区別する）
    $stmt = $db->exec(
        "SELECT u.id AS user_id, u.name AS user_name, COUNT(sc.id) AS use_count
         FROM Scores sc
         INNER JOIN Users u ON u.id = sc.user_id
         WHERE sc.created_at >= DATE_SUB(CURDATE(), INTERVAL 2 DAY)
         GROUP BY u.id, u.name
         ORDER BY use_count DESC, u.id ASC"
    );
    $recentUserRanking = array_map(function ($row) {
        return [
            "userId" => (int)$row['user_id'],
            "userName" => $row['user_name'],
            "useCount" => (int)$row['use_count']
        ];
    }, $stmt->fetchAll(PDO::FETCH_ASSOC));

    //ANCHOR - 店舗別利用者数ランキング（同一ユーザーは1人として換算。利用者0人の店舗は除外）
    $stmt = $db->exec(
        "SELECT st.id AS store_id, st.name AS store_name, COUNT(DISTINCT sc.user_id) AS user_count
         FROM Stores st
         INNER JOIN Scores sc ON sc.store_id = st.id
         GROUP BY st.id, st.name
         ORDER BY user_count DESC, st.id ASC"
    );
    $storeUserRanking = array_map(function ($row) {
        return [
            "storeId" => (int)$row['store_id'],
            "storeName" => $row['store_name'],
            "userCount" => (int)$row['user_count']
        ];
    }, $stmt->fetchAll(PDO::FETCH_ASSOC));

    echo json_encode([
        "success" => true,
        "message" => "集計データを取得しました。",
        "data" => [],
        "totalUsers" => (int)$userCounts['total_users'],
        "activeUsers" => (int)$userCounts['active_users'],
        "finishedUsers" => (int)$userCounts['finished_users'],
        "recentUserRanking" => $recentUserRanking,
        "storeUserRanking" => $storeUserRanking
    ]);
} catch (\Exception $e) {
    error_log("Statistics error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "処理中にエラーが発生しました。",
        "data" => []
    ]);
}
//!SECTION