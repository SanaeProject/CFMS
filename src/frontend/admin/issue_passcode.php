<?php
// PasswordIssuancePage - パスコード発行画面のモック
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="author" content="乙成隼士,松本浩成,永島愛貴,塩川海響,星川誠弥,中島悠翔">
    <meta name="keywords" content="nkc,名古屋工学院専門学校,文化祭">
    <meta name="description" content="ユーザーのパスコード発行と応答停止ユーザーの確認画面">

    <link rel="icon" href="/images/CFMS.ico">
    <link rel="icon" type="image/svg+xml" href="/images/CFMS.svg">
    <link rel="apple-touch-icon" href="/images/CFMS.png">

    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">

    <title>パスコード発行 | CFMS</title>

    <style>
        .page-container {
            max-width: 1100px;
            margin: 0 auto;
        }

        .section-card {
            border: 1px solid #dee2e6;
            border-radius: 12px;
            background-color: #fff;
        }

        .passcode-display {
            min-height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            border: 1px dashed #adb5bd;
            border-radius: 8px;
            background-color: #f8f9fa;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            overflow-wrap: anywhere;
        }

        .table-responsive {
            border-radius: 8px;
        }

        .status-badge {
            white-space: nowrap;
        }
    </style>
    <input class="csrf_token" type="hidden" value="">
</head>

<body class="bg-light">
    <main class="container py-4 py-md-5">
        <div class="page-container">
            <header class="mb-4">
                <h1 class="h3 fw-bold mb-2">パスコード発行</h1>
                <p class="text-secondary mb-0">
                    ユーザーの再ログイン用パスコードを発行し、応答停止中のユーザーを確認します。
                </p>
            </header>

            <!-- SECTION: パスコード発行 -->
            <section class="section-card p-3 p-md-4 mb-4"
                aria-labelledby="issuance-heading">
                <h2 id="issuance-heading" class="h5 fw-bold mb-3">
                    パスコードの発行
                </h2>

                <form id="issuance-form">
                    <div class="mb-3">
                        <label for="user-id" class="form-label">
                            トークン
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="user-id"
                            name="token"
                            maxlength="10"
                            minlength="10"
                            placeholder="10文字のトークン"
                            required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        パスコードを発行
                    </button>
                </form>

                <div id="issuance-message"
                    class="mt-3"
                    role="status"
                    aria-live="polite"
                    hidden></div>

                <div id="passcode-area" class="mt-4" hidden>
                    <label class="form-label fw-bold" for="passcode-value">
                        発行されたパスコード
                    </label>

                    <output id="passcode-value" class="passcode-display">
                    </output>

                    <button type="button"
                        id="copy-passcode-button"
                        class="btn btn-outline-secondary btn-sm mt-2">
                        パスコードをコピー
                    </button>

                    <span id="copy-message"
                        class="small text-secondary ms-2"
                        role="status"
                        aria-live="polite"></span>
                </div>
            </section>
            <!-- !SECTION -->

            <!-- SECTION: 応答停止ユーザー一覧 -->
            <section class="section-card p-3 p-md-4"
                aria-labelledby="expired-heading">
                <div class="d-flex flex-wrap justify-content-between
                            align-items-center gap-2 mb-3">
                    <div>
                        <h2 id="expired-heading" class="h5 fw-bold mb-1">
                            応答停止ユーザー
                        </h2>
                        <p class="text-secondary small mb-0">
                            最終ハートビートから2分以上経過した想定のユーザーです。
                        </p>
                    </div>

                    <button type="button"
                        id="reload-users-button"
                        class="btn btn-outline-primary btn-sm">
                        一覧を再読み込み
                    </button>
                </div>

                <div id="list-message"
                    class="small text-secondary mb-2"
                    role="status"
                    aria-live="polite"></div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover
                                  align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ユーザートークン</th>
                                <th scope="col">最終利用店舗</th>
                                <th scope="col">最終応答日時</th>
                                <th scope="col">状態</th>
                            </tr>
                        </thead>
                        <tbody id="expired-users-table">
                        </tbody>
                    </table>
                </div>
            </section>
            <!-- !SECTION -->
        </div>
    </main>

    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

    <script>
        'use strict';

        const API_BASE_URL =
            '<?= getenv('API_URL') ?>';

        const ISSUE_PASSCODE_API =
            `${API_BASE_URL}/admin/issue_passcode.php`;

        const EXPIRED_USERS_API =
            `${API_BASE_URL}/admin/expired.php`;

        const issuanceForm =
            document.getElementById(
                'issuance-form'
            );

        const userIdInput =
            document.getElementById(
                'user-id'
            );

        const issuanceMessage =
            document.getElementById(
                'issuance-message'
            );

        const passcodeArea =
            document.getElementById(
                'passcode-area'
            );

        const passcodeValue =
            document.getElementById(
                'passcode-value'
            );

        const copyPasscodeButton =
            document.getElementById(
                'copy-passcode-button'
            );

        const copyMessage =
            document.getElementById(
                'copy-message'
            );

        const expiredUsersTable =
            document.getElementById(
                'expired-users-table'
            );

        const listMessage =
            document.getElementById(
                'list-message'
            );

        const reloadUsersButton =
            document.getElementById(
                'reload-users-button'
            );

        function showMessage(
            element,
            message,
            type
        ) {

            element.textContent =
                message;

            element.className =
                `alert alert-${type} mt-3`;

            element.hidden = false;
        }

        function getCsrfToken() {

            const csrfInput =
                document.querySelector(
                    '.csrf_token'
                );

            return csrfInput ?
                csrfInput.value :
                '';
        }

        async function waitForCsrfToken(
            timeoutMs = 10000
        ) {

            const start =
                Date.now();

            while (
                Date.now() - start <
                timeoutMs
            ) {

                const token =
                    getCsrfToken();

                if (token) {
                    return token;
                }

                await new Promise(
                    resolve =>
                    setTimeout(
                        resolve,
                        100
                    )
                );
            }

            throw new Error(
                'CSRFトークンの取得に失敗しました。'
            );
        }

        async function callApi(
            url,
            requestData = {}
        ) {

            await waitForCsrfToken();

            const response =
                await fetch(url, {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        ...requestData,
                        csrf_token: getCsrfToken()
                    })
                });

            let data;

            try {

                data =
                    await response.json();

            } catch {

                throw new Error(
                    'サーバーから正しいJSONが返されませんでした。'
                );
            }

            if (!response.ok) {

                throw new Error(
                    data.message ||
                    '通信エラーが発生しました。'
                );
            }

            if (
                data.success !== true
            ) {

                throw new Error(
                    data.message ||
                    '処理に失敗しました。'
                );
            }

            return data;
        }

        issuanceForm.addEventListener(
            'submit',
            async function(event) {

                event.preventDefault();

                passcodeArea.hidden = true;

                copyMessage.textContent = '';

                const token =
                    userIdInput.value.trim();

                if (
                    token.length !== 10
                ) {

                    showMessage(
                        issuanceMessage,
                        'トークンは10文字で入力してください。',
                        'danger'
                    );

                    return;
                }

                try {

                    const data =
                        await callApi(
                            ISSUE_PASSCODE_API, {
                                token
                            }
                        );

                    passcodeValue.textContent =
                        data.passcode;

                    passcodeArea.hidden =
                        false;

                    showMessage(
                        issuanceMessage,
                        'パスコードを発行しました。',
                        'success'
                    );

                } catch (error) {

                    showMessage(
                        issuanceMessage,
                        error.message,
                        'danger'
                    );
                }
            }
        );

        copyPasscodeButton.addEventListener(
            'click',
            async function() {

                const passcode =
                    passcodeValue.textContent;

                try {

                    await navigator.clipboard.writeText(
                        passcode
                    );

                    copyMessage.textContent =
                        'コピーしました。';

                } catch (error) {

                    copyMessage.textContent =
                        'コピーできませんでした。';
                }
            }
        );

        async function renderExpiredUsers() {

            expiredUsersTable.replaceChildren();

            try {

                const data =
                    await callApi(
                        EXPIRED_USERS_API
                    );

                const expiredUsers =
                    data.expiredUsers || [];

                if (
                    expiredUsers.length === 0
                ) {

                    const row =
                        document.createElement(
                            'tr'
                        );

                    const cell =
                        document.createElement(
                            'td'
                        );

                    cell.colSpan = 4;

                    cell.className =
                        'text-center text-secondary py-4';

                    cell.textContent =
                        '応答停止ユーザーはいません。';

                    row.appendChild(
                        cell
                    );

                    expiredUsersTable.appendChild(
                        row
                    );

                    listMessage.textContent =
                        '0件です。';

                    return;
                }

                expiredUsers.forEach(
                    user => {

                        const row =
                            document.createElement(
                                'tr'
                            );

                        const tokenCell =
                            document.createElement(
                                'td'
                            );

                        tokenCell.textContent =
                            user.userToken;

                        const storeCell =
                            document.createElement(
                                'td'
                            );

                        storeCell.textContent =
                            user.lastStoreName ??
                            '-';

                        const heartbeatCell =
                            document.createElement(
                                'td'
                            );

                        heartbeatCell.textContent =
                            user.lastHeartbeatAt;

                        const statusCell =
                            document.createElement(
                                'td'
                            );

                        const badge =
                            document.createElement(
                                'span'
                            );

                        badge.className =
                            'badge text-bg-danger status-badge';

                        badge.textContent =
                            '応答停止';

                        statusCell.appendChild(
                            badge
                        );

                        row.append(
                            tokenCell,
                            storeCell,
                            heartbeatCell,
                            statusCell
                        );

                        expiredUsersTable.appendChild(
                            row
                        );
                    }
                );

                listMessage.textContent =
                    `${expiredUsers.length}件のユーザーが見つかりました。`;

            } catch (error) {

                listMessage.textContent =
                    error.message;
            }
        }
        reloadUsersButton.addEventListener(
            'click',
            async function() {

                reloadUsersButton.disabled = true;

                listMessage.textContent =
                    '再読み込み中...';

                try {

                    await renderExpiredUsers();

                } finally {

                    reloadUsersButton.disabled =
                        false;
                }
            }
        );

        (async function() {

            try {

                listMessage.textContent =
                    '初期化中...';

                await waitForCsrfToken();

                await renderExpiredUsers();

            } catch (error) {

                listMessage.textContent =
                    error.message ||
                    '一覧の取得に失敗しました。';
            }

        })();
    </script>
</body>

</html>