<!DOCTYPE html>
<html lang="ja">

<head>
    <!-- 基本設定 -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="乙成隼士,松本浩成,永島愛貴,塩川海響,星川誠弥,中島悠翔">

    <!-- サイトアイコン -->
    <link rel="icon" href="/images/CFMS.ico" sizes="any">
    <link rel="icon" href="/images/CFMS.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/images/CFMS.png">

    <meta name="keywords" content="nkc,名古屋工学院専門学校,文化祭">

    <!-- OGP設定 -->
    <meta property="og:title" content="パスコード発行 | CFMS">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nkc.sanae.dev">
    <meta property="og:image" content="/images/CFMS.png">
    <meta property="og:image:alt" content="CFMS">
    <meta property="og:description" content="ユーザーの再ログイン用パスコードを発行するページ">

    <!-- ページ情報 -->
    <title>パスコード発行 | CFMS</title>
    <meta name="description" content="ユーザーの再ログイン用パスコードを発行するページです。">

    <!-- Bootstrap読み込み -->
    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>

    <!-- メインコンテンツ -->
    <main class="py-4">
        <div class="container">

            <!-- ページタイトル -->
            <div class="mb-4">
                <h1 class="h3 fw-bold">
                    パスコード発行
                </h1>

                <p class="text-secondary">
                    ユーザーIDを入力して、再ログイン用のパスコードを発行します。
                </p>
            </div>

            <!-- パスコード発行フォーム -->
            <section class="card shadow-sm mb-4">

                <div class="card-body">

                    <h2 class="h5 mb-3">
                        パスコード発行
                    </h2>

                    <form id="passcodeForm" novalidate>

                        <!-- CSRFトークン -->
                        <input
                            type="hidden"
                            class="csrf_token"
                            name="csrfToken"
                            value=""
                        >

                        <!-- ユーザーID -->
                        <div class="mb-3">

                            <label for="userId" class="form-label">
                                ユーザーID
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                id="userId"
                                name="userId"
                                min="1"
                                step="1"
                                placeholder="ユーザーIDを入力"
                                autocomplete="off"
                                required
                            >

                            <div class="form-text">
                                パスコードを発行する対象ユーザーのIDを入力してください。
                            </div>

                            <div class="invalid-feedback">
                                1以上の整数を入力してください。
                            </div>

                        </div>

                        <!-- 発行ボタン -->
                        <button
                            type="submit"
                            id="issueButton"
                            class="btn btn-primary w-100"
                            disabled
                        >
                            CSRFトークン取得中...
                        </button>

                    </form>

                    <!-- 発行結果 -->
                    <div id="issuanceResult" class="mt-3" aria-live="polite">

                        <!-- 成功時 -->
                        <div id="passcodeArea" class="d-none">

                            <div class="alert alert-success">
                                パスコードを発行しました。
                                対象ユーザーに伝えてください。
                            </div>

                            <div class="mb-2 fw-bold">
                                発行されたパスコード
                            </div>

                            <div class="alert alert-primary text-center fs-4 fw-bold">
                                <span id="passcodeValue"></span>
                            </div>

                            <p class="small text-secondary">
                                このパスコードはユーザーの再ログイン時に使用します。
                            </p>

                        </div>

                        <!-- エラーメッセージ -->
                        <div id="issuanceMessage"></div>

                    </div>

                </div>

            </section>

        </div>
    </main>

    <script>
        //SECTION: API設定

        const API_URL = <?= json_encode(
            rtrim((string) (getenv('API_URL') ?: ''), '/'),
            JSON_UNESCAPED_SLASHES |
            JSON_HEX_TAG |
            JSON_HEX_APOS |
            JSON_HEX_QUOT |
            JSON_HEX_AMP
        ) ?>;

        const ISSUE_PASSCODE_API = `${API_URL}/user/issuePasscode`;

        //!SECTION


        //SECTION: DOM要素の取得

        const passcodeForm = document.getElementById('passcodeForm');
        const userIdInput = document.getElementById('userId');
        const issueButton = document.getElementById('issueButton');

        const passcodeArea = document.getElementById('passcodeArea');
        const passcodeValue = document.getElementById('passcodeValue');
        const issuanceMessage = document.getElementById('issuanceMessage');

        //!SECTION


        /**
         * CSRFトークンを取得する
         *
         * @returns {string} CSRFトークン
         */
        function getCsrfToken() {

            const csrfInput = document.querySelector('.csrf_token');

            return csrfInput?.value || '';
        }


        /**
         * CSRFトークンの取得状態に応じてボタンを制御する
         */
        function updateIssueButton() {

            const csrfToken = getCsrfToken();

            if (csrfToken) {

                issueButton.disabled = false;
                issueButton.textContent = 'パスコードを発行';

            } else {

                issueButton.disabled = true;
                issueButton.textContent = 'CSRFトークン取得中...';

            }
        }


        /**
         * APIレスポンスをJSONとして取得する
         *
         * @param {Response} response - fetchのレスポンス
         * @returns {Promise<Object>} JSONレスポンス
         */
        async function parseApiResponse(response) {

            const contentType = response.headers.get('content-type') || '';

            if (!contentType.includes('application/json')) {

                throw new Error(
                    'サーバーから正しい形式のレスポンスが返されませんでした。'
                );
            }

            const responseData = await response.json();

            if (!response.ok) {

                throw new Error(
                    responseData.message ||
                    `通信に失敗しました。（HTTP ${response.status}）`
                );
            }

            return responseData;
        }


        /**
         * メッセージを表示する
         *
         * @param {HTMLElement} target - 表示先
         * @param {string} message - メッセージ
         * @param {string} type - Bootstrapのalert種別
         */
        function showMessage(target, message, type) {

            target.replaceChildren();

            const alert = document.createElement('div');

            alert.className = `alert alert-${type}`;
            alert.textContent = message;

            target.appendChild(alert);
        }


        /**
         * パスコード発行結果を表示する
         *
         * @param {Object} responseData - APIレスポンス
         */
        function renderPasscodeResult(responseData) {

            passcodeArea.classList.add('d-none');
            issuanceMessage.replaceChildren();

            if (responseData.success !== true) {

                showMessage(
                    issuanceMessage,
                    responseData.message || 'パスコードを発行できませんでした。',
                    'danger'
                );

                return;
            }

            const passcode = responseData.passcode;

            if (
                typeof passcode !== 'string' ||
                passcode.length === 0
            ) {

                showMessage(
                    issuanceMessage,
                    'パスコードを取得できませんでした。',
                    'warning'
                );

                return;
            }

            // パスコードを表示
            passcodeValue.textContent = passcode;

            passcodeArea.classList.remove('d-none');
        }


        //SECTION: CSRFトークン取得イベント

        /**
         * CSRFトークン取得完了イベント
         *
         * fetch_csrf_token.php側で取得完了時に発火する
         */
        document.addEventListener('csrf-token-ready', function() {

            updateIssueButton();

            if (getCsrfToken()) {

                issuanceMessage.replaceChildren();

            } else {

                issueButton.disabled = true;

                showMessage(
                    issuanceMessage,
                    'CSRFトークンを取得できませんでした。ページを再読み込みしてください。',
                    'danger'
                );

            }

        });


        /**
         * CSRFトークン取得失敗イベント
         *
         * fetch_csrf_token.php側で取得失敗時に発火する
         */
        document.addEventListener('csrf-token-error', function() {

            issueButton.disabled = true;
            issueButton.textContent = 'パスコードを発行';

            showMessage(
                issuanceMessage,
                'CSRFトークンの取得に失敗しました。ページを再読み込みしてください。',
                'danger'
            );

        });

        //!SECTION


        //SECTION: パスコード発行処理

        /**
         * パスコード発行フォームの送信処理
         */
        passcodeForm.addEventListener('submit', async function(event) {

            event.preventDefault();

            //SECTION: 入力値の取得

            const userIdValue = userIdInput.value.trim();
            const csrfToken = getCsrfToken();

            passcodeArea.classList.add('d-none');
            issuanceMessage.replaceChildren();

            userIdInput.classList.remove('is-invalid');

            //!SECTION


            //SECTION: CSRFトークン確認

            if (!csrfToken) {

                issueButton.disabled = true;

                showMessage(
                    issuanceMessage,
                    'CSRFトークンを取得できませんでした。ページを再読み込みしてください。',
                    'danger'
                );

                return;
            }

            //!SECTION


            //SECTION: 入力チェック

            if (
                userIdValue === '' ||
                !/^[1-9]\d*$/.test(userIdValue) ||
                !Number.isSafeInteger(Number(userIdValue))
            ) {

                userIdInput.classList.add('is-invalid');
                userIdInput.focus();

                return;
            }

            //!SECTION


            //SECTION: API通信

            issueButton.disabled = true;
            issueButton.textContent = '発行中...';

            try {

                const response = await fetch(ISSUE_PASSCODE_API, {

                    method: 'POST',

                    credentials: 'include',

                    headers: {
                        'Content-Type': 'application/json'
                    },

                    body: JSON.stringify({

                        userId: Number(userIdValue),

                        csrfToken: csrfToken

                    })

                });

                const responseData = await parseApiResponse(response);

                renderPasscodeResult(responseData);

            } catch (error) {

                console.error(
                    'パスコード発行エラー:',
                    error
                );

                showMessage(
                    issuanceMessage,
                    error.message ||
                    'パスコード発行中にエラーが発生しました。',
                    'danger'
                );

            } finally {

                // CSRFトークンが取得済みの場合のみ再有効化
                updateIssueButton();

            }

            //!SECTION

        });

        //!SECTION


        //SECTION: 入力イベント

        /**
         * 入力時のエラー表示を解除する
         */
        userIdInput.addEventListener('input', function() {

            this.classList.remove('is-invalid');

        });

        //!SECTION


        //SECTION: 初期処理

        document.addEventListener('DOMContentLoaded', function() {

            // 初期状態のCSRFトークンを確認
            updateIssueButton();

            userIdInput.focus();

        });

        //!SECTION

    </script>

    <!-- フッター読み込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

    <!-- CSRFトークン取得 -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>

</body>

</html>