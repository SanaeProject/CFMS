<?php
declare(strict_types=1);

//TODO: 入出力構造を定義する

/**
 * 管理者が指定したユーザーに対して6桁のパスコードを発行します。
 *
 * 入力:
 * {
 *     "userId": "ユーザーID",
 *     "csrfToken": "CSRFトークン"
 * }
 *
 * 出力:
 * {
 *     "success": true/false,
 *     "message": "処理結果のメッセージ",
 *     "passcode": "発行された6桁のパスコード",
 *     "expiresAt": "パスコードの有効期限"
 * }
 */

//SECTION: 共通 - 入力の取得

// Composerオートローダー読み込み
require_once __DIR__ . '/../../../vendor/autoload.php';

// セッション初期化
// header() やレスポンス出力より前に実行する
$session = new Lib\Session();

// CORS設定
$appUrl = getenv("APP_URL");

if ($appUrl === false || $appUrl === '') {
    $appUrl = 'http://localhost';
}

$appUrl = rtrim($appUrl, '/');

// レスポンスヘッダー設定
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: " . $appUrl);
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

// CORSプリフライトリクエスト
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// POST以外のリクエストを拒否
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header("Allow: POST, OPTIONS");

    echo json_encode([
        "success" => false,
        "message" => "許可されていないHTTPメソッドです。"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// JSON形式のリクエストを取得
$rawData = file_get_contents('php://input');

// JSONを配列に変換
$data = json_decode($rawData, true);

// JSON形式確認
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "リクエスト形式が正しくありません。"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//!SECTION

//SECTION: 入力のバリデーション

// リクエストパラメータ取得
$userId = $data['userId'] ?? null;
$csrfToken = $data['csrfToken'] ?? null;

// 必須項目確認
if ($userId === null || $csrfToken === null) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "必須パラメータが不足しています。"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// userIdの型を確認
if (!is_int($userId) && !is_string($userId)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "ユーザーIDの形式が正しくありません。"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// 文字列の場合は前後の空白を除去
if (is_string($userId)) {
    $userId = trim($userId);
}

// 空文字確認
if ($userId === '') {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "ユーザーIDを入力してください。"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// userIdを整数として検証
$validatedUserId = filter_var(
    $userId,
    FILTER_VALIDATE_INT
);

if ($validatedUserId === false || $validatedUserId <= 0) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "ユーザーIDが正しくありません。"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// CSRFトークンの型確認
if (!is_string($csrfToken)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "CSRFトークンの形式が正しくありません。"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// CSRFトークンの空文字確認
$csrfToken = trim($csrfToken);

if ($csrfToken === '') {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "CSRFトークンが指定されていません。"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//!SECTION

//SECTION: 処理の実行

//ANCHOR - セッション認証

// セッションからスタッフIDを取得
$staffId = $session->get('staff_id');

// ログイン状態確認
if ($staffId === null || $staffId === '') {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "ログインしてください。"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//ANCHOR - CSRF検証

$csrf = new Lib\CSRFToken();

if (!$csrf->validateToken($csrfToken)) {
    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "CSRFトークンが正しくありません。"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//ANCHOR - メイン処理

try {

    // データベース接続
    $db = Lib\DB::connect();

    // 管理者権限確認
    $stmt = $db->exec(
        "
        SELECT
            role
        FROM
            staff
        WHERE
            id = ?
        LIMIT 1
        ",
        [$staffId]
    );

    $staff = $stmt->fetch(\PDO::FETCH_ASSOC);

    // スタッフ情報確認
    if (!$staff) {
        http_response_code(403);

        echo json_encode([
            "success" => false,
            "message" => "スタッフ情報を確認できません。"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // 管理者権限確認
    if ($staff['role'] !== 'admin') {
        http_response_code(403);

        echo json_encode([
            "success" => false,
            "message" => "管理者権限がありません。"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // ユーザー確認
    $stmt = $db->exec(
        "
        SELECT
            id,
            finished_at
        FROM
            users
        WHERE
            id = ?
        LIMIT 1
        ",
        [$validatedUserId]
    );

    $user = $stmt->fetch(\PDO::FETCH_ASSOC);

    // ユーザー存在確認
    if (!$user) {
        http_response_code(404);

        echo json_encode([
            "success" => false,
            "message" => "指定されたユーザーが存在しません。"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // 利用終了済み確認
    if ($user['finished_at'] !== null) {
        http_response_code(409);

        echo json_encode([
            "success" => false,
            "message" => "利用終了済みのユーザーにはパスコードを発行できません。"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // 6桁のランダムなパスコードを生成
    $passcode = str_pad(
        (string) random_int(0, 999999),
        6,
        '0',
        STR_PAD_LEFT
    );

    // パスコードをハッシュ化
    $hashedPasscode = password_hash(
        $passcode,
        PASSWORD_DEFAULT
    );

    // ハッシュ化失敗確認
    if ($hashedPasscode === false) {
        throw new \RuntimeException(
            'パスコードのハッシュ化に失敗しました。'
        );
    }

    // 有効期限を10分後に設定
    $passcodeExpiredAt = date(
        'Y-m-d H:i:s',
        time() + (10 * 60)
    );

    // パスコードと有効期限をデータベースに保存
    $db->exec(
        "
        UPDATE
            users
        SET
            passcode = ?,
            passcode_expired_at = ?
        WHERE
            id = ?
        ",
        [
            $hashedPasscode,
            $passcodeExpiredAt,
            $validatedUserId
        ]
    );

    // 成功レスポンス
    http_response_code(200);

    echo json_encode([
        "success" => true,
        "message" => "パスコードを発行しました。",
        "passcode" => $passcode,
        "expiresAt" => $passcodeExpiredAt
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {

    //ANCHOR - エラー処理

    // エラーログ出力
    error_log(
        'password_issuance.php Error: ' . $e->getMessage()
    );

    error_log($e->getTraceAsString());

    // エラーレスポンス
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "サーバー内部でエラーが発生しました。"
    ], JSON_UNESCAPED_UNICODE);
}

//!SECTION
