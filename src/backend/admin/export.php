<?php

/**
 * 集計データをCSV形式で出力します。
 * 管理者(Staff.role = admin)のみ実行できます。
 * 成功時はCSVファイル(UTF-8 BOM付き)を返し、失敗時のみJSONを返します。
 * typeの種類:
 *      user_history: 利用履歴(Scoresの全レコード。トークン・パスコードは出力しない)
 *      store_statistics: 店舗別集計(利用者0人の店舗は含めない)
 *      redemption_history: 景品交換履歴(Redemptionsの全レコード)
 * 入力: {
 *      type: "user_history" / "store_statistics" / "redemption_history",
 *      csrf_token: "CSRFトークン"
 * }
 * 出力(成功): CSVファイル
 * 出力(失敗): {
 *      success: false,
 *      message: "エラーメッセージ",
 *      data: []
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
$type = $data['type'] ?? null;
$csrfToken = $data['csrf_token'] ?? null;
$sessionCsrfToken = new Lib\CSRFToken();

if ($type === null || $csrfToken === null) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "必要なパラメータが不足しています。",
        "data" => []
    ]);
    exit;
}

if (!in_array($type, ['user_history', 'store_statistics', 'redemption_history'], true)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "出力種別が正しくありません。",
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
/**
 * CSVインジェクション対策（=, +, -, @ などで始まる文字列の先頭に ' を付ける）
 * @param mixed $value セルの値
 * @return mixed
 */
function sanitizeCsvCell($value)
{
    if (is_string($value) && preg_match('/^[=+\-@\t\r]/', $value)) {
        return "'" . $value;
    }
    return $value;
}

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

    //ANCHOR - 出力するデータの取得
    if ($type === 'user_history') {
        // 利用履歴（Scoresの全レコード。Usersのtoken・passcodeは出力しない）
        $csvHeader = ['得点ID', 'ユーザーID', 'ユーザー名', '店舗ID', '店舗名', '担当スタッフID', '獲得ポイント', '登録日時'];
        $stmt = $db->exec(
            "SELECT sc.id, sc.user_id, u.name AS user_name, sc.store_id, st.name AS store_name,
                    sc.staff_id, sc.score, sc.created_at
             FROM Scores sc
             INNER JOIN Users u ON u.id = sc.user_id
             INNER JOIN Stores st ON st.id = sc.store_id
             ORDER BY sc.created_at ASC, sc.id ASC"
        );
    } elseif ($type === 'redemption_history') {
        // 景品交換履歴（Redemptionsの全レコード）
        $csvHeader = ['交換ID', 'ユーザーID', 'ユーザー名', '担当スタッフID', '景品名', '交換日時'];
        $stmt = $db->exec(
            "SELECT r.id, r.user_id, u.name AS user_name, r.staff_id, r.prize_name, r.created_at
             FROM Redemptions r
             INNER JOIN Users u ON u.id = r.user_id
             ORDER BY r.created_at ASC, r.id ASC"
        );
    } else {
        // 店舗別集計（利用者0人の店舗は含めない）
        $csvHeader = ['店舗ID', '店舗名', '利用者数', 'プレイ回数', '付与ポイント合計'];
        $stmt = $db->exec(
            "SELECT st.id AS store_id, st.name AS store_name,
                    COUNT(DISTINCT sc.user_id) AS user_count,
                    COUNT(sc.id) AS play_count,
                    COALESCE(SUM(sc.score), 0) AS total_score
             FROM Stores st
             INNER JOIN Scores sc ON sc.store_id = st.id
             GROUP BY st.id, st.name
             ORDER BY st.id ASC"
        );
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //ANCHOR - CSV出力（Excelでの文字化け防止のためBOMを付与）
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $type . '_' . date('Ymd_His') . '.csv"');
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');
    fputcsv($out, $csvHeader, ',', '"', '');
    foreach ($rows as $row) {
        fputcsv($out, array_map('sanitizeCsvCell', array_values($row)), ',', '"', '');
    }
    fclose($out);
} catch (\Exception $e) {
    error_log("Export error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "処理中にエラーが発生しました。",
        "data" => []
    ]);
}
//!SECTION