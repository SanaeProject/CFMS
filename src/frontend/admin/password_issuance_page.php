
<!DOCTYPE html>

<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="乙成隼士,松本浩成,永島愛貴,塩川海響,星川誠弥,中島悠翔">

    <link rel="icon" href="/images/CFMS.ico" sizes="any">
    <link rel="icon" href="/images/CFMS.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/images/CFMS.png">

    <meta name="keywords" content="nkc,名古屋工学院専門学校,文化祭">

    <title>パスコード発行</title>

    <meta name="description"
          content="ユーザーのパスコード発行と無応答ユーザー確認">

    <link rel="stylesheet"
          href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">

    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <main>
        <div class="container">

            <h1>パスコード発行</h1>

            <div class="card mt-4">

                <div class="card-header">
                    パスコード発行
                </div>

                <div class="card-body">

                    <form id="passcodeForm">

                        <input type="hidden"
                               class="csrf_token"
                               name="csrf_token"
                               value="">

                        <div class="mb-3">

                            <label for="userId"
                                   class="form-label">
                                ユーザーID
                            </label>

                            <input type="number"
                                   class="form-control"
                                   id="userId"
                                   name="userId"
                                   min="1"
                                   required>

                        </div>

                        <button type="submit"
                                id="issueButton"
                                class="btn btn-primary"
                                disabled>
                            パスコード発行
                        </button>

                    </form>

                    <div id="issueResult"
                         class="mt-3">
                    </div>

                    <div class="mt-3">

                        <label for="passcode"
                               class="form-label">
                            発行されたパスコード
                        </label>

                        <input type="text"
                               class="form-control"
                               id="passcode"
                               readonly>

                    </div>

                </div>

            </div>

            <div class="card mt-4">

                <div class="card-header">
                    無応答ユーザー一覧
                </div>

                <div class="card-body">

                    <button type="button"
                            id="reloadButton"
                            class="btn btn-secondary mb-3">
                        再読み込み
                    </button>

                    <div id="expiredResult"
                         class="mb-3">
                    </div>

                    <div class="table-responsive">

                        <table class="table table-striped">

                            <thead>
                                <tr>
                                    <th>ユーザーID</th>
                                    <th>最後に利用した店舗</th>
                                    <th>最終Heartbeat時刻</th>
                                </tr>
                            </thead>

                            <tbody id="expiredUsersTable">
                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>
    </main>

    <script>
        'use strict';

        const apiUrl = <?= json_encode(
            rtrim((string) (getenv('API_URL') ?: ''), '/'),
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        ) ?>;

        // ANCHOR - パスコード発行結果表示
        function renderIssueResult(data) {

            const resultDiv =
                document.getElementById(
                    'issueResult'
                );

            const passcodeInput =
                document.getElementById(
                    'passcode'
                );

            resultDiv.replaceChildren();

            const alertDiv =
                document.createElement(
                    'div'
                );

            if (data.success) {

                alertDiv.className =
                    'alert alert-success';

                alertDiv.textContent =
                    'パスコードを発行しました。';

                passcodeInput.value =
                    data.passcode ?? '';

            } else {

                alertDiv.className =
                    'alert alert-danger';

                alertDiv.textContent =
                    data.message ??
                    'パスコードの発行に失敗しました。';

                passcodeInput.value = '';

            }

            resultDiv.appendChild(
                alertDiv
            );

        }

        // ANCHOR - パスコード発行
        document.getElementById('passcodeForm')
            .addEventListener(
                'submit',
                function(event) {

                    event.preventDefault();

                    const userId =
                        document.getElementById(
                            'userId'
                        ).value;

                    const csrfToken =
                        document.querySelector(
                            '.csrf_token'
                        ).value;

                    if (!csrfToken) {
                        renderIssueResult({
                            success: false,
                            message: 'CSRFトークンを取得できませんでした。ページを再読み込みしてください。'
                        });
                        return;
                    }

                    fetch(
                        `${apiUrl}/api/admin/password_issuance`,
                        {
                            method: 'POST',
                            credentials: 'include',
                            headers: {
                                'Content-Type':
                                    'application/json'
                            },
                            body: JSON.stringify({
                                userId,
                                csrfToken
                            })
                        }
                    )
                    .then(
                        response =>
                        response.json()
                    )
                    .then(
                        data =>
                        renderIssueResult(data)
                    )
                    .catch(
                        error => {

                            console.error(
                                'Error:',
                                error
                            );

                            const resultDiv =
                                document.getElementById(
                                    'issueResult'
                                );

                            resultDiv.replaceChildren();

                            const alertDiv =
                                document.createElement(
                                    'div'
                                );

                            alertDiv.className =
                                'alert alert-danger';

                            alertDiv.textContent =
                                '通信エラーが発生しました。';

                            resultDiv.appendChild(
                                alertDiv
                            );

                        }
                    );

                }
            );

        // ANCHOR - CSRFトークン取得完了
        window.addEventListener(
            'csrf-token-ready',
            function() {

                document.getElementById(
                    'issueButton'
                ).disabled = false;

            },
            { once: true }
        );

        // ANCHOR - CSRFトークン取得失敗
        window.addEventListener(
            'csrf-token-error',
            function() {

                renderIssueResult({
                    success: false,
                    message: 'CSRFトークンを取得できませんでした。ページを再読み込みしてください。'
                });

            },
            { once: true }
        );

        // ANCHOR - 無応答ユーザー一覧取得
        function loadExpiredUsers() {

            fetch(
                `${apiUrl}/api/admin/session/expired`,
                {
                    method: 'GET',
                    credentials: 'include'
                }
            )
            .then(
                response =>
                response.json()
            )
            .then(
                data => {

                    const table =
                        document.getElementById(
                            'expiredUsersTable'
                        );

                    table.replaceChildren();

                    const resultDiv =
                        document.getElementById(
                            'expiredResult'
                        );

                    resultDiv.replaceChildren();

                    if (!data.success) {

                        const alertDiv =
                            document.createElement(
                                'div'
                            );

                        alertDiv.className =
                            'alert alert-danger';

                        alertDiv.textContent =
                            data.message ??
                            '一覧の取得に失敗しました。';

                        resultDiv.appendChild(
                            alertDiv
                        );

                        return;

                    }

                    data.expiredUsers.forEach(
                        function(user) {

                            const row =
                                document.createElement(
                                    'tr'
                                );

                            const userIdCell =
                                document.createElement(
                                    'td'
                                );

                            userIdCell.textContent =
                                user.userId;

                            const storeNameCell =
                                document.createElement(
                                    'td'
                                );

                            storeNameCell.textContent =
                                user.lastStoreName;

                            const heartbeatCell =
                                document.createElement(
                                    'td'
                                );

                            heartbeatCell.textContent =
                                user.lastHeartbeatAt;

                            row.appendChild(
                                userIdCell
                            );

                            row.appendChild(
                                storeNameCell
                            );

                            row.appendChild(
                                heartbeatCell
                            );

                            table.appendChild(
                                row
                            );

                        }
                    );

                }
            )
            .catch(
                error => {

                    console.error(
                        'Error:',
                        error
                    );

                    const resultDiv =
                        document.getElementById(
                            'expiredResult'
                        );

                    resultDiv.replaceChildren();

                    const alertDiv =
                        document.createElement(
                            'div'
                        );

                    alertDiv.className =
                        'alert alert-danger';

                    alertDiv.textContent =
                        '通信エラーが発生しました。';

                    resultDiv.appendChild(
                        alertDiv
                    );

                }
            );

        }

        // ANCHOR - 一覧再読み込み
        document.getElementById('reloadButton')
            .addEventListener(
                'click',
                function() {

                    loadExpiredUsers();

                }
            );

        // ANCHOR - 初期表示
        loadExpiredUsers();

    </script>

    <!-- フッター読み込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

    <!-- CSRFトークンの自動埋め込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>

</body>

</html>
