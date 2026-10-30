
<?php

/**
 * 入力:
 * {
 *     "userId": 1,
 *     "csrfToken": "CSRFトークン"
 * }
 *
 * 出力:
 * {
 *     "success": true,
 *     "message": "パスコードを発行しました。",
 *     "passcode": "123456"
 * }
 */


//SECTION: 共通 - 入力の取得

// アプリケーションURLの取得
$appUrl = getenv("APP_URL");

// APP_URLが未設定の場合
if (
    $appUrl === false ||
    $appUrl === ''
) {
    $appUrl = 'http://localhost';
}

// 末尾のスラッシュを削除
$appUrl = rtrim($appUrl, '/');

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: " . $appUrl);
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");


// OPTIONSリクエストへの対応
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {

    http_response_code(204);

    exit;
}


// POSTメソッドの確認
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

//!SECTION



//SECTION: 処理の実行

try {

    //ANCHOR - ライブラリ読み込み
    require_once __DIR__ . '/../../../vendor/autoload.php';


    //ANCHOR - セッション初期化
    // レスポンス本文を出力する前にセッションを開始する
    $session = new Lib\Session();


    //ANCHOR - ログイン確認
    $staffId = $session->get('staff_id');

    if ($staffId === null) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "ログインしてください。",
            "data" => []
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    //ANCHOR - JSONデータの取得
    $rawData = file_get_contents(
        'php://input'
    );

    $data = json_decode(
        $rawData,
        true
    );


    //ANCHOR - JSON形式の確認
    if (
        json_last_error() !== JSON_ERROR_NONE ||
        !is_array($data)
    ) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "JSONの形式が不正です。",
            "data" => []
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    //ANCHOR - 入力値の取得
    $userId = $data['userId'] ?? null;

    $csrfToken = $data['csrfToken'] ?? null;


    //ANCHOR - 必須項目の確認
    if (
        $userId === null ||
        $csrfToken === null
    ) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "必要なパラメータが不足しています。",
            "data" => []
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    //ANCHOR - ユーザーIDの型確認
    if (
        !is_int($userId) &&
        !is_string($userId)
    ) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "ユーザーIDの形式が不正です。",
            "data" => []
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    //ANCHOR - ユーザーIDの空文字確認
    if (is_string($userId)) {

        $userId = trim($userId);

        if ($userId === '') {

            http_response_code(400);

            echo json_encode([
                "success" => false,
                "message" => "ユーザーIDを入力してください。",
                "data" => []
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }
    }


    //ANCHOR - ユーザーIDのバリデーション
    $userId = filter_var(
        $userId,
        FILTER_VALIDATE_INT
    );

    if (
        $userId === false ||
        $userId <= 0
    ) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "ユーザーIDが不正です。",
            "data" => []
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    //ANCHOR - CSRFトークンの型確認
    if (!is_string($csrfToken)) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "CSRFトークンの形式が不正です。",
            "data" => []
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    //ANCHOR - CSRFトークンの空文字確認
    $csrfToken = trim($csrfToken);

    if ($csrfToken === '') {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "CSRFトークンが入力されていません。",
            "data" => []
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    //ANCHOR - CSRFトークンの初期化
    $sessionCsrfToken = new Lib\CSRFToken();


    //ANCHOR - CSRFトークンの検証
    if (
        !$sessionCsrfToken->validateToken(
            $csrfToken
        )
    ) {

        http_response_code(403);

        echo json_encode([
            "success" => false,
            "message" => "CSRFトークンが無効です。",
            "data" => []
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    //ANCHOR - データベース接続
    $db = Lib\DB::connect();


    //ANCHOR - 管理者確認
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
        [
            $staffId
        ]
    );

    $staff = $stmt->fetch(
        \PDO::FETCH_ASSOC
    );

    if (
        !$staff ||
        ($staff['role'] ?? null) !== 'admin'
    ) {

        http_response_code(403);

        echo json_encode([
            "success" => false,
            "message" => "権限がありません。",
            "data" => []
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    //ANCHOR - ユーザー取得
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
        [
            $userId
        ]
    );

    $user = $stmt->fetch(
        \PDO::FETCH_ASSOC
    );

    if (!$user) {

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "message" => "対象ユーザーが存在しません。",
            "data" => []
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    //ANCHOR - 終了済みユーザーの確認
    if (
        ($user['finished_at'] ?? null) !== null
    ) {

        http_response_code(409);

        echo json_encode([
            "success" => false,
            "message" => "対象ユーザーは終了済みです。",
            "data" => []
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    //ANCHOR - パスコード生成
    $passcode = str_pad(
        (string) random_int(
            0,
            999999
        ),
        6,
        '0',
        STR_PAD_LEFT
    );


    //ANCHOR - パスコードのハッシュ化
    $hashedPasscode = password_hash(
        $passcode,
        PASSWORD_DEFAULT
    );

    if ($hashedPasscode === false) {

        throw new \RuntimeException(
            'パスコードのハッシュ化に失敗しました。'
        );
    }


    //ANCHOR - パスコード有効期限の設定
    // 発行時刻から10分後を有効期限にする
    // PHPとデータベースのタイムゾーンを統一すること
    $passcodeExpiredAt = date(
        'Y-m-d H:i:s',
        time() + (10 * 60)
    );


    //ANCHOR - パスコード保存
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
            $userId
        ]
    );


    //ANCHOR - 成功レスポンス
    echo json_encode([
        "success" => true,
        "message" => "パスコードを発行しました。",
        "passcode" => $passcode
    ], JSON_UNESCAPED_UNICODE);


} catch (\Throwable $e) {

    //ANCHOR - エラーログ出力
    error_log(
        '[password_issuance] '
        . $e->getMessage()
        . PHP_EOL
        . $e->getTraceAsString()
    );


    //ANCHOR - エラーレスポンス
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "処理中にエラーが発生しました。",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

}

//!SECTION
