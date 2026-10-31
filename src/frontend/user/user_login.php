<!DOCTYPE html>
<html lang="ja">

<head>
    <!-- 基本設定 -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="乙成隼士,松本浩成,永島愛貴,塩川海響,星川誠弥,中島悠翔">

    <!-- サイト情報 -->
    <title>CFMS | ユーザー再ログイン</title>
    <meta name="description" content="CFMSのユーザー再ログインページ">
    <meta name="keywords" content="nkc,名古屋工学院専門学校,文化祭">

    <!-- ファビコン -->
    <link rel="icon" href="/images/CFMS.ico" sizes="any">
    <link rel="icon" href="/images/CFMS.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/images/CFMS.png">

    <!-- OGP設定 -->
    <meta property="og:title" content="CFMS | ユーザー再ログイン">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nkc.sanae.dev">
    <meta property="og:image" content="/images/CFMS.png">
    <meta property="og:image:alt" content="CFMS">
    <meta property="og:description" content="CFMSのユーザー再ログインページ">

    <!-- bootstrap読み込み -->
    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light">

    <!-- 画面全体：Bootstrapの機能で中央に配置 -->
    <main class="container min-vh-100 d-flex justify-content-center align-items-center py-4">

        <!-- ログインカード -->
        <div class="row justify-content-center w-100">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">

                <div class="card shadow-sm">

                    <!-- ヘッダー -->
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h1 class="h3 mb-2">CFMS</h1>
                        <p class="mb-0">ユーザー再ログイン</p>
                    </div>

                    <!-- 入力フォーム -->
                    <div class="card-body p-4">

                        <h2 class="h4 mb-3">再ログイン</h2>

                        <p class="text-secondary mb-4">
                            ユーザーIDとパスコードを入力してください。
                        </p>

                        <!-- エラーメッセージ・処理結果表示領域 -->
                        <div
                            id="statusArea"
                            class="mb-3"
                            role="status"
                            aria-live="polite"></div>

                        <!-- JavaScriptで非同期送信するフォーム -->
                        <form id="loginForm" novalidate>

                            <!-- CSRFトークン：PHP側で自動設定 -->
                            <input
                                type="hidden"
                                id="csrfToken"
                                class="csrf_token"
                                name="csrf_token"
                                value="">

                            <!-- ユーザーID：文字列として扱う -->
                            <div class="mb-3">
                                <label for="userId" class="form-label">
                                    トークン
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="userId"
                                    name="token"
                                    placeholder="10文字のトークンを入力"
                                    autocomplete="username"
                                    minlength="10"
                                    maxlength="10"
                                    required>

                                <div class="invalid-feedback">
                                    ユーザートークンを入力してください。
                                </div>
                            </div>

                            <!-- パスコード -->
                            <div class="mb-3">
                                <label for="passcode" class="form-label">
                                    パスコード
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="passcode"
                                    name="passcode"
                                    placeholder="パスコードを入力"
                                    autocomplete="current-password"
                                    required>

                                <div class="invalid-feedback">
                                    パスコードを入力してください。
                                </div>
                            </div>

                            <!-- ログインボタン -->
                            <div class="d-grid">
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    id="loginButton">
                                    ログイン
                                </button>
                            </div>

                        </form>
                    </div>

                    <!-- フッター -->
                    <div class="card-footer text-center text-secondary py-3">
                        <small>CFMS ユーザー専用システム</small>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- JavaScript：ログイン処理 -->
    <script>
        const loginForm = document.getElementById("loginForm");
        const userIdInput = document.getElementById("userId");
        const passcodeInput = document.getElementById("passcode");
        const csrfTokenInput = document.getElementById("csrfToken");
        const statusArea = document.getElementById("statusArea");
        const loginButton = document.getElementById("loginButton");

        const API_BASE_URL =
            '<?= getenv('API_URL') ?>';

        const LOGIN_API =
            `${API_BASE_URL}/user/login.php`;

        const IS_LOGGED_IN_API =
            `${API_BASE_URL}/user/is_logged_in.php`;

        function getCsrfToken() {
            return csrfTokenInput.value;
        }
        /**
         * 処理結果やエラーメッセージを表示する
         * @param {string} message 表示するメッセージ
         * @param {string} type Bootstrapのアラート種類
         */
        function showMessage(message, type = "danger") {
            statusArea.replaceChildren();

            const alertElement = document.createElement("div");
            alertElement.className = `alert alert-${type} mb-0`;
            alertElement.textContent = message;

            statusArea.appendChild(alertElement);
        }
        /**
         * メッセージを消去する
         */
        function clearMessage() {
            statusArea.replaceChildren();
        }
        async function waitForCsrfToken(
            timeoutMs = 10000
        ) {

            const start = Date.now();

            while (
                Date.now() - start < timeoutMs
            ) {

                const token =
                    getCsrfToken();

                if (token) {
                    return token;
                }

                await new Promise(
                    resolve =>
                    setTimeout(resolve, 100)
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

            const data =
                await response.json();

            if (!response.ok) {
                throw new Error(
                    data.message ||
                    '通信に失敗しました。'
                );
            }

            return data;
        }
        loginForm.addEventListener(
            "submit",
            async function(event) {

                event.preventDefault();

                clearMessage();

                const token =
                    userIdInput.value.trim();

                const passcode =
                    passcodeInput.value;

                userIdInput.classList.toggle(
                    "is-invalid",
                    token.length !== 10
                );

                passcodeInput.classList.toggle(
                    "is-invalid",
                    !passcode
                );

                if (
                    token.length !== 10 ||
                    !passcode
                ) {

                    showMessage(
                        "トークンとパスコードを入力してください。"
                    );

                    return;
                }

                loginButton.disabled = true;
                loginButton.textContent = "認証中...";

                try {

                    const result =
                        await callApi(
                            LOGIN_API, {
                                token,
                                passcode
                            }
                        );

                    if (result.success) {

                        showMessage(
                            "ログインに成功しました。",
                            "success"
                        );
                        window.location.href =
                            "/user/user_mainpage.php";
                    } else {

                        showMessage(
                            result.message ||
                            "ログインに失敗しました。"
                        );
                    }

                } catch (error) {

                    showMessage(
                        error.message ||
                        "処理に失敗しました。"
                    );

                } finally {

                    loginButton.disabled = false;
                    loginButton.textContent = "ログイン";
                }
            }
        );
        document.addEventListener(
            "DOMContentLoaded",
            async function() {

                try {

                    await waitForCsrfToken();

                    const result =
                        await callApi(
                            IS_LOGGED_IN_API
                        );

                    if (
                        result.isLoggedIn === true
                    ) {

                        showMessage(
                            "ログイン済みです。",
                            "info"
                        );

                        /*
                        window.location.href =
                            "/user/index.php";
                        */
                    }

                } catch (error) {

                    console.error(
                        error
                    );
                }
            }
        );
    </script>

    <!-- フッター読み込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

    <!-- CSRFトークンの自動埋め込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>
</body>

</html>