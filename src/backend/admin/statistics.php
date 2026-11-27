<?php

/**
 * 管理者向けの集計データを返します。
 * 管理者(staff.role = admin)のみ実行できます。
 *
 * 入力:
 * {
 *     csrf_token: "CSRFトークン"
 * }
 *
 * 出力:
 * {
 *     success: true/false,
 *     message: "処理結果のメッセージ",
 *     data: [利用履歴],
 *     totalUsers: 総ユーザー数,
 *     activeUsers: アクティベート済みユーザー数,
 *     finishedUsers: 終了済みユーザー数,
 *     recentUserRanking: 利用者ランキング,
 *     storeUserRanking: 店舗別利用者数ランキング,
 *     usageHistory: 利用履歴,
 *     day1: 1日目の利用履歴,
 *     day2: 2日目の利用履歴,
 *     popular: 店舗別利用状況,
 *     stayTimes: 滞在時間一覧
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

if (!is_array($data)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "JSONのデコードに失敗しました。",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}
//!SECTION

//SECTION: 入力のバリデーション
$csrfToken = $data['csrf_token'] ?? null;
$sessionCsrfToken = new Lib\CSRFToken();

if (!is_string($csrfToken) || $csrfToken === '') {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "必要なパラメータが不足しています。",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if (!$sessionCsrfToken->validateToken($csrfToken)) {
    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "CSRFトークンが無効です。",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}
//!SECTION

//SECTION: 処理の実行
try {

    //ANCHOR - ログイン確認
    $session = new Lib\Session();
    $staffId = $session->get('staff_id');

    if ($staffId === null) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "ログインしていません。",
            "data" => []
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $db = Lib\DB::connect();

    //ANCHOR - 管理者権限確認
    $stmt = $db->exec(
        "SELECT role
         FROM staff
         WHERE id = :id",
        [':id' => $staffId]
    );

    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($staff === false || $staff['role'] !== 'admin') {
        http_response_code(403);

        echo json_encode([
            "success" => false,
            "message" => "管理者権限がありません。",
            "data" => []
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    //ANCHOR - ユーザー数の集計
    $stmt = $db->exec(
        "SELECT
            COUNT(*) AS total_users,
            COALESCE(
                SUM(is_active = 1 AND finished_at IS NULL),
                0
            ) AS active_users,
            COALESCE(
                SUM(finished_at IS NOT NULL),
                0
            ) AS finished_users
         FROM users"
    );

    $userCounts = $stmt->fetch(PDO::FETCH_ASSOC);

    //ANCHOR - 利用者得点ランキング
    $stmt = $db->exec(
        "SELECT
            u.token AS user_id,
            SUM(sc.score) AS use_count
         FROM scores sc
         INNER JOIN users u
            ON u.id = sc.user_id
         GROUP BY u.token
         ORDER BY use_count DESC, u.token ASC"
    );

    $recentUserRanking = array_map(
        function ($row) {
            return [
                "userId" => $row['user_id'],
                "useCount" => (int)$row['use_count']
            ];
        },
        $stmt->fetchAll(PDO::FETCH_ASSOC)
    );

    //ANCHOR - 店舗別利用者数ランキング
    $stmt = $db->exec(
        "SELECT
            st.id AS store_id,
            st.name AS store_name,
            COUNT(DISTINCT sc.user_id) AS user_count
         FROM stores st
         INNER JOIN scores sc
            ON sc.store_id = st.id
         GROUP BY st.id, st.name
         ORDER BY user_count DESC, st.id ASC"
    );

    $storeUserRanking = array_map(
        function ($row) {
            return [
                "storeId" => (int)$row['store_id'],
                "storeName" => $row['store_name'],
                "userCount" => (int)$row['user_count']
            ];
        },
        $stmt->fetchAll(PDO::FETCH_ASSOC)
    );

    //ANCHOR - 利用履歴
    $stmt = $db->exec(
        "SELECT
            u.id AS user_id,
            u.token AS user_token,
            u.created_at AS user_created_at,
            u.finished_at AS user_finished_at,
            st.id AS store_id,
            st.name AS store_name,
            sc.score,
            sc.created_at AS score_created_at,
            sf.name AS staff_name
         FROM scores sc
         INNER JOIN users u
            ON u.id = sc.user_id
         INNER JOIN stores st
            ON st.id = sc.store_id
         LEFT JOIN staff sf
            ON sf.id = sc.staff_id
         ORDER BY sc.created_at ASC, sc.id ASC"
    );

    $usageHistory = [];

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {

        $usageHistory[] = [
            "userId" => (int)$row['user_id'],
            "userToken" => $row['user_token'],
            "userCreatedAt" => $row['user_created_at'],
            "userFinishedAt" => $row['user_finished_at'],
            "storeId" => (int)$row['store_id'],
            "storeName" => $row['store_name'],
            "score" => (int)$row['score'],
            "scoreCreatedAt" => $row['score_created_at'],
            "staffName" => $row['staff_name'] ?? ''
        ];
    }

    //ANCHOR - 滞在時間
    $stmt = $db->exec(
        "SELECT
            id AS user_id,
            token AS user_token,
            created_at,
            finished_at,
            CASE
                WHEN finished_at IS NOT NULL
                THEN GREATEST(
                    TIMESTAMPDIFF(
                        SECOND,
                        created_at,
                        finished_at
                    ),
                    0
                )
                ELSE NULL
            END AS stay_seconds
         FROM users
         ORDER BY created_at ASC"
    );

    $stayTimes = [];

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {

        $stayTimes[] = [
            "userId" => (int)$row['user_id'],
            "userToken" => $row['user_token'],
            "createdAt" => $row['created_at'],
            "finishedAt" => $row['finished_at'],
            "staySeconds" => $row['stay_seconds'] !== null
                ? (int)$row['stay_seconds']
                : null
        ];
    }

    //ANCHOR - 1日目・2日目の自動判定
    $usageDates = [];

    foreach ($usageHistory as $history) {

        // 利用日時が文字列でない場合はスキップ
        if (
            !isset($history['scoreCreatedAt']) ||
            !is_string($history['scoreCreatedAt']) ||
            $history['scoreCreatedAt'] === ''
        ) {
            continue;
        }

        // YYYY-MM-DD形式の日付を取得
        $usageDate = substr($history['scoreCreatedAt'], 0, 10);

        // 日付形式を確認
        $dateObject = DateTime::createFromFormat(
            '!Y-m-d',
            $usageDate
        );

        if (
            $dateObject === false ||
            $dateObject->format('Y-m-d') !== $usageDate
        ) {
            continue;
        }

        $usageDates[$usageDate] = true;
    }

    // 利用日を古い順に並べる
    $usageDates = array_keys($usageDates);
    sort($usageDates, SORT_STRING);

    // 1日目・2日目の利用履歴
    $day1 = [];
    $day2 = [];

    $day1Date = $usageDates[0] ?? null;
    $day2Date = $usageDates[1] ?? null;

    foreach ($usageHistory as $history) {

        $scoreCreatedAt = $history['scoreCreatedAt'] ?? null;

        if (
            !is_string($scoreCreatedAt) ||
            $scoreCreatedAt === ''
        ) {
            continue;
        }

        $usageDate = substr($scoreCreatedAt, 0, 10);

        if (
            $day1Date !== null &&
            $usageDate === $day1Date
        ) {
            $day1[] = $history;
        } elseif (
            $day2Date !== null &&
            $usageDate === $day2Date
        ) {
            $day2[] = $history;
        }
    }

    //ANCHOR - 店舗別利用状況
    $popularData = [];

    foreach ($usageHistory as $history) {

        // 必須データの確認
        if (
            !isset($history['storeId']) ||
            !isset($history['storeName']) ||
            !isset($history['userId'])
        ) {
            continue;
        }

        $storeId = (int)$history['storeId'];
        $userId = (int)$history['userId'];

        // 店舗データを初期化
        if (!isset($popularData[$storeId])) {
            $popularData[$storeId] = [
                "storeId" => $storeId,
                "storeName" => (string)$history['storeName'],
                "useCount" => 0,
                "userIds" => []
            ];
        }

        // 利用回数を加算
        $popularData[$storeId]['useCount']++;

        // 重複しないユーザーを記録
        $popularData[$storeId]['userIds'][$userId] = true;
    }

    // 店舗別利用状況を作成
    $popular = [];

    foreach ($popularData as $store) {
        $popular[] = [
            "storeId" => (int)$store['storeId'],
            "storeName" => $store['storeName'],
            "useCount" => (int)$store['useCount'],
            "userCount" => count($store['userIds'])
        ];
    }

    // 利用回数が多い店舗順に並べる
    usort($popular, function ($a, $b) {

        if ($a['useCount'] === $b['useCount']) {
            return $a['storeId'] <=> $b['storeId'];
        }

        return $b['useCount'] <=> $a['useCount'];
    });

    //ANCHOR - JSON返却
    echo json_encode([
        "success" => true,
        "message" => "集計データを取得しました。",
        "data" => $usageHistory,
        "totalUsers" => (int)$userCounts['total_users'],
        "activeUsers" => (int)$userCounts['active_users'],
        "finishedUsers" => (int)$userCounts['finished_users'],
        "recentUserRanking" => $recentUserRanking,
        "storeUserRanking" => $storeUserRanking,
        "usageHistory" => $usageHistory,
        "day1" => $day1,
        "day2" => $day2,
        "popular" => $popular,
        "stayTimes" => $stayTimes
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {

    error_log("Statistics error: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "処理中にエラーが発生しました。",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);
}
//!SECTION
