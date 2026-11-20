<?php

declare(strict_types=1);

/**
 * CFMS API Fuzzing Test
 *
 * 説明:
 * APIに異常なデータを送信し、入力値検証やエラー処理を確認する。
 *
 * 実行例:
 * php fuzz.php http://localhost
 * php fuzz.php http://localhost --allow-write
 *
 * 注意:
 * --allow-write を指定すると登録・更新・削除APIにも送信する。
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');
set_time_limit(0);

// ==================================================
// 設定
// ==================================================

// APIのURLを指定
$baseUrl = $argv[1] ?? 'http://localhost';
$allowWrite = in_array('--allow-write', $argv, true);

// リモート環境への送信を許可する場合のみ指定
$allowRemote = in_array('--allow-remote', $argv, true);

// CSRFトークンとCookieは必要な場合に環境変数から取得
$csrfToken = getenv('FUZZ_CSRF_TOKEN') ?: '';
$cookie = getenv('FUZZ_COOKIE') ?: '';

// タイムアウト
$timeout = 10;

// 結果保存先
$resultFile = __DIR__ . '/fuzz_result.csv';

// ==================================================
// 対象API
// ==================================================

// URLは実際のルーティングに合わせて変更する
$endpoints = [
    [
        'name' => 'bulk_add',
        'path' => '/backend/bulk_add.php',
        'method' => 'POST',
    ],
    [
        'name' => 'expired',
        'path' => '/backend/expired.php',
        'method' => 'GET',
    ],
    [
        'name' => 'export',
        'path' => '/backend/export.php',
        'method' => 'GET',
    ],
    [
        'name' => 'issue_passcode',
        'path' => '/backend/issue_passcode.php',
        'method' => 'POST',
    ],
    [
        'name' => 'lookup_by_token',
        'path' => '/backend/lookup_by_token.php',
        'method' => 'POST',
    ],
    [
        'name' => 'staff',
        'path' => '/backend/staff.php',
        'method' => 'POST',
    ],
    [
        'name' => 'staff_delete',
        'path' => '/backend/staff_delete.php',
        'method' => 'POST',
    ],
    [
        'name' => 'staffs',
        'path' => '/backend/staffs.php',
        'method' => 'GET',
    ],
    [
        'name' => 'statistics',
        'path' => '/backend/statistics.php',
        'method' => 'GET',
    ],
    [
        'name' => 'store',
        'path' => '/backend/store.php',
        'method' => 'POST',
    ],
    [
        'name' => 'store_delete',
        'path' => '/backend/store_delete.php',
        'method' => 'POST',
    ],
    [
        'name' => 'stores',
        'path' => '/backend/stores.php',
        'method' => 'GET',
    ],
    [
        'name' => 'user_finish',
        'path' => '/backend/user_finish.php',
        'method' => 'POST',
    ],
];

// ==================================================
// 接続先チェック
// ==================================================

$parsedUrl = parse_url($baseUrl);

if (
    $parsedUrl === false ||
    !isset($parsedUrl['scheme'], $parsedUrl['host']) ||
    !in_array($parsedUrl['scheme'], ['http', 'https'], true)
) {
    exit("ERROR: 正しいURLを指定してください。\n");
}

$host = strtolower($parsedUrl['host']);

$isLocal = in_array($host, [
    'localhost',
    '127.0.0.1',
    '::1',
], true);

if (!$isLocal && !$allowRemote) {
    exit("ERROR: 初期設定ではlocalhost以外への送信は禁止されています。\n" .
        "自分が管理するテスト環境に限り --allow-remote を指定してください。\n");
}

if (!extension_loaded('curl')) {
    exit("ERROR: PHPのcURL拡張が必要です。\n");
}

// ==================================================
// ファジング用データ
// ==================================================

$testCases = [
    [
        'name' => 'empty_object',
        'body' => '{}',
    ],
    [
        'name' => 'empty_json',
        'body' => '',
    ],
    [
        'name' => 'broken_json',
        'body' => '{"token":',
    ],
    [
        'name' => 'json_null',
        'body' => 'null',
    ],
    [
        'name' => 'json_string',
        'body' => '"test"',
    ],
    [
        'name' => 'json_number',
        'body' => '123456789',
    ],
    [
        'name' => 'json_array',
        'body' => '[]',
    ],
    [
        'name' => 'nested_array',
        'body' => '{"token":[[]]}',
    ],
    [
        'name' => 'nested_object',
        'body' => '{"token":{"test":"value"}}',
    ],
    [
        'name' => 'null_values',
        'body' => '{"token":null,"userId":null,"name":null}',
    ],
    [
        'name' => 'empty_values',
        'body' => '{"token":"","userId":"","name":""}',
    ],
    [
        'name' => 'negative_values',
        'body' => '{"userId":-1,"storeId":-1,"score":-999}',
    ],
    [
        'name' => 'huge_number',
        'body' => '{"userId":999999999999999999999999999}',
    ],
    [
        'name' => 'boolean_values',
        'body' => '{"token":true,"name":false}',
    ],
    [
        'name' => 'invalid_types',
        'body' => '{"token":[],"name":[],"userId":{}}',
    ],
    [
        'name' => 'invalid_store_ids',
        'body' => '{"storeIds":"invalid"}',
    ],
    [
        'name' => 'invalid_stores',
        'body' => '{"stores":"invalid"}',
    ],
    [
        'name' => 'empty_stores',
        'body' => '{"stores":[]}',
    ],
    [
        'name' => 'invalid_store_element',
        'body' => '{"stores":[null,[],{}, "test", -1]}',
    ],
    [
        'name' => 'special_characters',
        'body' => '{"name":"<script>alert(1)</script>","token":"\\u0000"}',
    ],
    [
        'name' => 'long_string',
        'body' => json_encode([
            'token' => str_repeat('A', 100000),
            'name' => str_repeat('B', 100000),
        ]),
    ],
    [
        'name' => 'unicode',
        'body' => json_encode([
            'name' => str_repeat('あ', 10000),
            'token' => 'テスト🍀',
        ]),
    ],
];

// ==================================================
// CSV初期化
// ==================================================

$csv = fopen($resultFile, 'w');

if ($csv === false) {
    exit("ERROR: CSVファイルを作成できません。\n");
}

fwrite($csv, "\xEF\xBB\xBF");

fputcsv($csv, [
    'API',
    'テスト名',
    'HTTPメソッド',
    'HTTPステータス',
    'レスポンス時間(ms)',
    '判定',
    'エラー内容',
]);

// ==================================================
// リクエスト送信
// ==================================================

function sendRequest(
    string $url,
    string $method,
    string $body,
    string $cookie,
    int $timeout
): array {
    $ch = curl_init();

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_POSTFIELDS => $body,
    ]);

    if ($cookie !== '') {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }

    $start = microtime(true);

    $response = curl_exec($ch);

    $elapsed = round(
        (microtime(true) - $start) * 1000,
        2
    );

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    curl_close($ch);

    return [
        'status' => $httpCode,
        'response' => $response === false ? '' : $response,
        'elapsed' => $elapsed,
        'curl_error' => $curlError,
    ];
}

// ==================================================
// 異常レスポンス判定
// ==================================================

function checkResponse(array $result): array
{
    $status = $result['status'];
    $response = $result['response'];
    $curlError = $result['curl_error'];

    $errors = [];

    if ($curlError !== '') {
        $errors[] = '通信エラー: ' . $curlError;
    }

    if ($status >= 500) {
        $errors[] = 'サーバー内部エラー';
    }

    if (
        preg_match(
            '/Fatal error|Uncaught TypeError|Uncaught Error|Parse error/i',
            $response
        )
    ) {
        $errors[] = 'PHP実行時エラーの疑い';
    }

    if (
        preg_match(
            '/Stack trace:|Call Stack:/i',
            $response
        )
    ) {
        $errors[] = 'スタックトレースがレスポンスに含まれている';
    }

    return [
        'vulnerable' => count($errors) > 0,
        'errors' => $errors,
    ];
}

// ==================================================
// テスト実行
// ==================================================

$total = 0;
$success = 0;
$problems = 0;
$skipped = 0;

echo "====================================\n";
echo " CFMS FUZZING TEST\n";
echo "====================================\n";
echo "Target: {$baseUrl}\n";
echo "Write test: " . ($allowWrite ? 'ON' : 'OFF') . "\n";
echo "====================================\n\n";

foreach ($endpoints as $endpoint) {

    $name = $endpoint['name'];
    $path = $endpoint['path'];
    $method = $endpoint['method'];

    // 書き込みAPIは明示的な許可が必要
    if ($method !== 'GET' && !$allowWrite) {
        echo "[SKIP] {$name} : 書き込みテスト無効\n";
        $skipped += count($testCases);
        continue;
    }

    echo "\n------------------------------------\n";
    echo "API: {$name}\n";
    echo "METHOD: {$method}\n";
    echo "------------------------------------\n";

    foreach ($testCases as $test) {

        $total++;

        $body = $test['body'];

        // CSRFトークンを指定した場合、
        // JSONオブジェクトに追加する
        if ($csrfToken !== '') {
            $decoded = json_decode($body, true);

            if (is_array($decoded)) {
                $decoded['csrf_token'] = $csrfToken;
                $body = json_encode($decoded);
            }
        }

        $url = rtrim($baseUrl, '/') . $path;

        $result = sendRequest(
            $url,
            $method,
            $body,
            $cookie,
            $timeout
        );

        $check = checkResponse($result);

        if ($check['vulnerable']) {
            $problems++;

            $judge = '要確認';
            $errorText = implode(' / ', $check['errors']);

            echo "[WARNING] {$test['name']}";
            echo " HTTP: {$result['status']}";
            echo " {$errorText}\n";
        } else {
            $success++;

            $judge = '異常検出なし';
            $errorText = '';

            echo "[OK] {$test['name']}";
            echo " HTTP: {$result['status']}";
            echo " Time: {$result['elapsed']}ms\n";
        }

        fputcsv($csv, [
            $name,
            $test['name'],
            $method,
            $result['status'],
            $result['elapsed'],
            $judge,
            $errorText,
        ]);
    }
}

// ==================================================
// 結果表示
// ==================================================

fclose($csv);

echo "\n====================================\n";
echo " TEST RESULT\n";
echo "====================================\n";
echo "実行数: {$total}\n";
echo "異常検出なし: {$success}\n";
echo "要確認: {$problems}\n";
echo "スキップ: {$skipped}\n";
echo "====================================\n";
echo "CSV出力: {$resultFile}\n";
echo "====================================\n";
