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

    <!-- OGPタグ -->
    <meta property="og:title" content="ユーザーメインページ">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nkc.sanae.dev">
    <meta property="og:image" content="/images/CFMS.png">
    <meta property="og:image:alt" content="CFMS">
    <meta property="og:description" content="ユーザー情報と獲得ポイントを確認するページ">

    <!-- タイトル・説明文 -->
    <title>ユーザーメインページ</title>
    <meta name="description" content="ユーザー情報、QRコード、獲得ポイント、アカウント状態を確認できます。">

    <!-- Bootstrap読み込み -->
    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

    <!-- QRコード生成ライブラリ -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <!-- csrf_token格納用 -->
    <input type="hidden" class="csrf_token" value="">
</head>

<body class="bg-light">

    <!-- メインコンテンツ -->
    <main>
        <div class="container py-3" style="max-width: 900px;">

            <!-- アカウント状態 -->
            <section class="card shadow-sm mb-3">
                <div class="card-body p-3">
                    <h1 class="h5 fw-bold mb-3">アカウント状態</h1>

                    <span id="statusBadge" class="badge bg-secondary fs-6">
                        確認中
                    </span>

                    <p id="statusMessage" class="text-secondary small mt-2 mb-0">
                        アカウント情報を取得しています。
                    </p>
                </div>
            </section>

            <!-- ユーザー情報 -->
            <section class="card shadow-sm mb-3">
                <div class="card-body p-3">
                    <h2 class="h5 fw-bold mb-3">ユーザー情報</h2>

                    <label for="token" class="form-label fw-bold">
                        トークン
                    </label>

                    <div class="input-group mb-2">
                        <input type="text" id="token" class="form-control"
                            value="取得中..." readonly>

                        <button type="button" id="copyTokenButton"
                            class="btn btn-outline-secondary"
                            onclick="copyText('token')" disabled>
                            コピー
                        </button>
                    </div>

                    <p class="form-text mb-3">
                        QRコードには固定トークンを格納します。
                    </p>

                    <div id="pageMessage" role="status"></div>
                </div>
            </section>

            <!-- QRコード -->
            <section class="card shadow-sm mb-3">
                <div class="card-body p-3 text-center">
                    <h2 class="h5 fw-bold mb-3">ユーザーQRコード</h2>

                    <div class="d-flex justify-content-center">
                        <div id="qrcode" class="bg-white border rounded p-2">
                            <div class="text-secondary small p-4">
                                読み込み中...
                            </div>
                        </div>
                    </div>

                    <p id="qrDescription" class="text-secondary small mt-3 mb-1">
                        ユーザー情報を確認しています。
                    </p>

                    <div id="qrMessage" class="small" role="status"></div>
                </div>
            </section>

            <!-- ポイント合計 -->
            <section class="card border-primary shadow-sm mb-3">
                <div class="card-body p-3 text-center">
                    <h2 class="h5 fw-bold mb-2">獲得ポイント合計</h2>

                    <div class="display-4 fw-bold text-primary">
                        <span id="totalScore">0</span>
                        <span class="fs-5">pt</span>
                    </div>

                    <p class="text-secondary small mb-0">
                        これまでに獲得したポイント
                    </p>
                </div>
            </section>

            <!-- ポイント履歴 -->
            <section class="card shadow-sm mb-3">
                <div class="card-body p-3">
                    <h2 class="h5 fw-bold mb-3">ポイント獲得履歴</h2>

                    <div class="table-responsive">
                        <table class="table table-striped table-sm align-middle mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th scope="col">日時</th>
                                    <th scope="col">ブース</th>
                                    <th scope="col" class="text-end">ポイント</th>
                                </tr>
                            </thead>

                            <tbody id="scoreHistory">
                                <tr>
                                    <td colspan="3" class="text-center text-secondary py-3">
                                        読み込み中...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- 再ログイン -->
            <section class="card shadow-sm mb-3">
                <div class="card-body p-3">
                    <h2 class="h5 fw-bold mb-2">再ログイン</h2>

                    <p class="text-secondary small">
                        セッション切れや端末変更時はこちら。
                    </p>

                    <div class="d-grid">
                        <button type="button" class="btn btn-outline-primary"
                            onclick="showLoginMock()">
                            再ログイン画面へ
                        </button>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <!-- フッター読み込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

    <!-- CSRFトークンの自動埋め込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>

    <script>
        'use strict';

        /**
         * APIのベースURL
         */
        const API_BASE_URL = <?= json_encode(
                                    rtrim((string) (getenv('API_URL') ?: ''), '/'),
                                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                                ) ?>;

        /**
         * APIエンドポイント
         */
        const API_ENDPOINTS = {
            info: '/user/user_info.php',
            generate: '/user/user_generate.php',
            heartbeat: '/user/user_heartbeat.php'
        };

        // ハートビートの送信間隔：1分
        const HEARTBEAT_INTERVAL = 60 * 1000;

        // ユーザー情報
        let user = null;

        // アカウント終了状態
        let isFinished = false;

        // 初期化の重複実行防止
        let isInitializing = false;

        // ハートビートのタイマー
        let heartbeatTimer = null;

        /**
         * APIのURLを生成する
         * @param {string} endpoint
         * @returns {string}
         */
        function getApiUrl(endpoint) {
            return API_BASE_URL + endpoint;
        }

        /**
         * CSRFトークンを取得する
         * fetch_csrf_token.phpによる埋め込みを利用する
         * @returns {string}
         */
        function getCsrfToken() {
            const csrfInput =
                document.querySelector('input[name="csrf_token"]') ||
                document.querySelector('input.csrf_token');

            return csrfInput && typeof csrfInput.value === 'string' ?
                csrfInput.value.trim() :
                '';
        }

        /**
         * CSRFトークンがDOMに挿入され、値が設定されるまで待機する。
         * valueプロパティだけが更新される実装にも対応するため、定期確認します。
         * @param {number} timeoutMs
         * @returns {Promise<string>}
         */
        function waitForCsrfToken(timeoutMs = 15000) {
            return new Promise((resolve, reject) => {
                const startedAt = Date.now();
                const timer = setInterval(() => {
                    const csrfToken = getCsrfToken();

                    if (csrfToken !== '') {
                        clearInterval(timer);
                        resolve(csrfToken);
                        return;
                    }

                    if (Date.now() - startedAt >= timeoutMs) {
                        clearInterval(timer);
                        reject(new Error(
                            'CSRFトークンを取得できませんでした。ページを再読み込みしてください。'
                        ));
                    }
                }, 50);
            });
        }

        /**
         * APIリクエストを送信する。CSRFトークンが取得できるまで送信を開始しません。
         * @param {string} endpoint
         * @param {Object|null} body
         * @returns {Promise<{response: Response, data: Object}>}
         */
        async function requestApi(endpoint, body = {}) {
            const csrfToken = await waitForCsrfToken();
            const response = await fetch(getApiUrl(endpoint), {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ...(body || {}),
                    csrf_token: csrfToken
                })
            });

            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                const error = new Error(
                    'APIのレスポンスがJSONではありません。'
                );
                error.status = response.status;
                throw error;
            }

            const data = await response.json();
            if (!response.ok || data.success !== true) {
                const error = new Error(
                    data.message ||
                    'API通信に失敗しました。HTTP ' + response.status
                );
                error.status = response.status;
                error.data = data;
                throw error;
            }

            return {
                response,
                data
            };
        }

        /**
         * ページを初期化する
         * 生成APIはセッション内の既存ユーザーを返すため、先に呼び出しても重複生成されません。
         */
        async function initializePage() {
            if (isInitializing) {
                return;
            }

            isInitializing = true;

            try {
                // 既存セッションなら既存ユーザーを返し、未生成の場合だけ新規作成します。
                const generateResult = await requestApi(
                    API_ENDPOINTS.generate, {}
                );
                const generatedUser = generateResult.data.data;

                if (
                    !generatedUser ||
                    typeof generatedUser.token !== 'string' ||
                    generatedUser.token.length !== 10
                ) {
                    throw new Error('ユーザー生成APIから有効な10文字のトークンを取得できませんでした。');
                }

                // ポイント履歴・合計・終了日時などを取得します。
                const infoResult = await requestApi(
                    API_ENDPOINTS.info, {}
                );
                const info = infoResult.data.data;

                if (!info || typeof info.token !== 'string') {
                    throw new Error('ユーザー情報APIのレスポンスが不正です。');
                }

                // ユーザーの特定にはtokenを使用し、DB内部IDはフロントエンドに公開しません。
                user = {
                    ...generatedUser,
                    ...info
                };

                renderUserInfo();
                renderScores();
                renderStatus();
                startHeartbeat();

            } catch (error) {
                console.error('初期化エラー:', error);

                showMessage(
                    'pageMessage',
                    error.message || 'ユーザー情報の取得に失敗しました。',
                    'danger'
                );
                renderHistoryError();

                document.getElementById('statusBadge').className =
                    'badge bg-danger fs-6';
                document.getElementById('statusBadge').textContent = '取得失敗';
                document.getElementById('statusMessage').textContent =
                    'ユーザー情報を取得できませんでした。時間をおいて再読み込みしてください。';

            } finally {
                isInitializing = false;
            }
        }

        /**
         * ユーザー情報を表示する
         */
        function renderUserInfo() {
            if (typeof user.token === 'string' && user.token.length > 0) {
                document.getElementById('token').value = user.token;
                document.getElementById('copyTokenButton').disabled = false;

                renderQRCode();
            } else {
                document.getElementById('token').value = '取得できません';
                document.getElementById('copyTokenButton').disabled = true;

                const qr = document.getElementById('qrcode');
                qr.replaceChildren();
                qr.textContent = 'QRコードを表示できません。';

                document.getElementById('qrDescription').textContent =
                    'APIからトークンを取得できませんでした。';

                document.getElementById('qrMessage').className =
                    'text-danger small';

                document.getElementById('qrMessage').textContent =
                    'ユーザー情報APIからトークンを取得できませんでした。';
            }
        }

        /**
         * トークンからQRコードを生成する
         */
        function renderQRCode() {
            const qr = document.getElementById('qrcode');
            const qrMessage = document.getElementById('qrMessage');

            qr.replaceChildren();
            qrMessage.textContent = '';

            if (!user || !user.token) {
                qr.textContent = 'トークンがありません。';
                return;
            }

            if (typeof QRCode === 'undefined') {
                qr.textContent = 'QRコードを生成できません。';
                qrMessage.textContent =
                    'QRコード生成ライブラリを確認してください。';
                return;
            }

            new QRCode(qr, {
                text: user.token,
                width: 200,
                height: 200,
                correctLevel: QRCode.CorrectLevel.M
            });

            document.getElementById('qrDescription').textContent =
                'スタッフにこのQRコードを提示してください。';

            qrMessage.className = 'text-success small';
            qrMessage.textContent = 'QRコード生成済み';
        }

        /**
         * アカウント状態を表示する
         */
        function renderStatus() {
            const badge = document.getElementById('statusBadge');
            const message = document.getElementById('statusMessage');

            const finishedAt = user ? user.finishedAt : null;
            const isActive = user ? user.isActive : false;

            isFinished = Boolean(finishedAt);

            if (isFinished) {
                badge.className = 'badge bg-danger fs-6';
                badge.textContent = '終了';

                message.textContent =
                    'アカウントは終了しています。QRコードを利用した得点付与はできません。終了日時：' +
                    formatDate(finishedAt);

                document.getElementById('qrDescription').textContent =
                    '終了済みアカウントのため、QRコードを利用した得点付与はできません。';

                document.getElementById('qrMessage').className =
                    'text-danger small';

                document.getElementById('qrMessage').textContent =
                    'アカウント終了済み';

            } else if (isActive) {
                badge.className = 'badge bg-success fs-6';
                badge.textContent = '有効';

                message.textContent = 'アカウントは有効です。';

            } else {
                badge.className = 'badge bg-warning text-dark fs-6';
                badge.textContent = '未アクティベート';

                message.textContent =
                    'アカウントはまだ有効化されていません。';
            }
        }

        /**
         * ポイント履歴と合計を表示する
         *
         * scores:
         * [{scoreId, score, storeId, storeName, createdAt}]
         */
        function renderScores() {
            const tbody = document.getElementById('scoreHistory');

            const scores = user && Array.isArray(user.scores) ?
                user.scores : [];

            const totalScore =
                user && Number.isSafeInteger(user.totalScore) ?
                user.totalScore :
                scores.reduce(
                    (sum, item) => sum + (Number(item.score) || 0),
                    0
                );

            document.getElementById('totalScore').textContent =
                totalScore.toLocaleString('ja-JP');

            tbody.replaceChildren();

            if (scores.length === 0) {
                const tr = document.createElement('tr');
                const td = document.createElement('td');

                td.colSpan = 3;
                td.className = 'text-center text-secondary py-3';
                td.textContent = 'ポイント獲得履歴はありません。';

                tr.appendChild(td);
                tbody.appendChild(tr);
                return;
            }

            scores.forEach(item => {
                const tr = document.createElement('tr');

                const dateCell = document.createElement('td');
                dateCell.className = 'small text-nowrap';
                dateCell.textContent = formatDate(item.createdAt);

                const boothCell = document.createElement('td');
                boothCell.className = 'text-nowrap';
                boothCell.textContent =
                    item.storeName || ('ブース' + item.storeId);

                const pointsCell = document.createElement('td');
                pointsCell.className =
                    'text-end fw-bold text-primary text-nowrap';

                const score = Number(item.score) || 0;

                pointsCell.textContent =
                    score.toLocaleString('ja-JP') + ' pt';

                tr.append(dateCell, boothCell, pointsCell);
                tbody.appendChild(tr);
            });
        }

        /**
         * 履歴取得に失敗した場合の表示
         */
        function renderHistoryError() {
            const tbody = document.getElementById('scoreHistory');
            tbody.replaceChildren();

            const tr = document.createElement('tr');
            const td = document.createElement('td');

            td.colSpan = 3;
            td.className = 'text-center text-danger py-3';
            td.textContent = 'ポイント履歴を取得できませんでした。';

            tr.appendChild(td);
            tbody.appendChild(tr);
        }

        /**
         * ハートビートを送信する
         * 1分に1回、セッションの生存を報告する
         */
        async function sendHeartbeat() {
            if (!user || isFinished) {
                return;
            }

            try {
                const result = await requestApi(
                    API_ENDPOINTS.heartbeat, {}
                );

                if (!result.data.success) {
                    console.warn(
                        'ハートビートに失敗しました。',
                        result.data.message || ''
                    );
                }

            } catch (error) {
                console.error('ハートビート送信エラー:', error);
            }
        }

        /**
         * ハートビートを開始する
         */
        function startHeartbeat() {
            if (heartbeatTimer !== null) {
                clearInterval(heartbeatTimer);
                heartbeatTimer = null;
            }

            if (!user || isFinished) {
                return;
            }

            // ページ表示直後に送信
            sendHeartbeat();

            // 以降1分ごとに送信
            heartbeatTimer = setInterval(
                sendHeartbeat,
                HEARTBEAT_INTERVAL
            );
        }

        /**
         * 日時を表示する
         * @param {string|Date|null} date
         * @returns {string}
         */
        function formatDate(date) {
            if (!date) {
                return '-';
            }

            const parsedDate = date instanceof Date ?
                date :
                new Date(date);

            if (Number.isNaN(parsedDate.getTime())) {
                return String(date);
            }

            return new Intl.DateTimeFormat('ja-JP', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            }).format(parsedDate);
        }

        /**
         * トークンをコピーする
         * @param {string} elementId
         */
        async function copyText(elementId) {
            const input = document.getElementById(elementId);

            if (
                !input.value ||
                input.value === '取得できません' ||
                input.value === '取得中...'
            ) {
                showMessage(
                    'pageMessage',
                    'コピーする情報がありません。',
                    'warning'
                );
                return;
            }

            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(input.value);
                } else {
                    input.focus();
                    input.select();

                    if (!document.execCommand('copy')) {
                        throw new Error('コピーに失敗しました。');
                    }
                }

                showMessage(
                    'pageMessage',
                    'コピーしました。',
                    'success'
                );

            } catch (error) {
                showMessage(
                    'pageMessage',
                    'コピーできません。手動でコピーしてください。',
                    'warning'
                );
            }
        }

        /**
         * 再ログイン画面への導線（仮）
         */
        function showLoginMock() {
            alert(
                '再ログイン画面への遷移先は未設定です。\n' +
                'UserLoginPageのURLが決まったら接続してください。'
            );
        }

        /**
         * メッセージを表示する
         * @param {string} elementId
         * @param {string} message
         * @param {string} type
         */
        function showMessage(elementId, message, type) {
            const element = document.getElementById(elementId);
            element.replaceChildren();

            const alertElement = document.createElement('div');
            alertElement.className =
                'alert alert-' + type + ' py-2 mb-0';
            alertElement.setAttribute('role', 'alert');
            alertElement.textContent = message;

            element.appendChild(alertElement);
        }

        // ページ表示時に初期化する
        document.addEventListener('DOMContentLoaded', initializePage);

        // ページを離れるときにタイマーを停止する
        window.addEventListener('pagehide', function() {
            if (heartbeatTimer !== null) {
                clearInterval(heartbeatTimer);
                heartbeatTimer = null;
            }
        });
    </script>

</body>

</html>