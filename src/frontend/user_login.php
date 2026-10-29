<!DOCTYPE html>
<html lang="ja">

<head>
    <!-- SECTION: 基本設定 -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="乙成隼士,松本浩成,永島愛貴,塩川海響,星川誠弥,中島悠翔">

    <title>CFMS | ユーザー再ログイン</title>
    <meta name="description" content="CFMSのユーザー再ログインページ">
    <meta name="keywords" content="nkc,名古屋工学院専門学校,文化祭">

    <link rel="icon" href="/images/CFMS.ico" sizes="any">
    <link rel="icon" href="/images/CFMS.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/images/CFMS.png">

    <meta property="og:title" content="CFMS | ユーザー再ログイン">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nkc.sanae.dev">
    <meta property="og:image" content="/images/CFMS.png">
    <meta property="og:image:alt" content="CFMS">
    <meta property="og:description" content="CFMSのユーザー再ログインページ">

    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <!-- !SECTION -->
</head>

<body class="bg-light">

    <main class="container min-vh-100 d-flex justify-content-center align-items-center py-4">

        <div class="row justify-content-center w-100">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">

                <div class="card shadow-sm">

                    <div class="card-header bg-primary text-white text-center py-4">
                        <h1 class="h3 mb-2">CFMS</h1>
                        <p class="mb-0">ユーザー再ログイン</p>
                    </div>

                    <div class="card-body p-4">

                        <h2 class="h4 mb-3">
                            再ログイン
                        </h2>

                        <p class="text-secondary mb-4">
                            10文字のトークンとパスコードを入力してください。
                        </p>

                        <!-- ANCHOR - メッセージ表示領域 -->
                        <div
                            id="statusArea"
                            class="mb-3"
                            role="status"
                            aria-live="polite"></div>

                        <!-- ANCHOR - ログインフォーム -->
                        <form id="loginForm" novalidate>

                            <div class="mb-3">
                                <label
                                    for="token"
                                    class="form-label">
                                    トークン
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="token"
                                    name="token"
                                    maxlength="10"
                                    minlength="10"
                                    placeholder="10文字のトークンを入力"
                                    required>

                                <div class="invalid-feedback">
                                    10文字のトークンを入力してください。
                                </div>
                            </div>

                            <div class="mb-3">
                                <label
                                    for="passcode"
                                    class="form-label">
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

                            <div class="d-grid">
                                <button
                                    type="submit"
                                    id="loginButton"
                                    class="btn btn-primary">
                                    ログイン
                                </button>
                            </div>

                        </form>

                    </div>

                    <div class="card-footer text-center text-secondary py-3">
                        <small>
                            CFMS ユーザー専用システム
                        </small>
                    </div>

                </div>

            </div>
        </div>

    </main>

    <script>
        //SECTION: DOM取得

        const loginForm = document.getElementById("loginForm");
        const tokenInput = document.getElementById("token");
        const passcodeInput = document.getElementById("passcode");
        const statusArea = document.getElementById("statusArea");
        const loginButton = document.getElementById("loginButton");

        //!SECTION

        //SECTION: メッセージ表示

        /**
         * メッセージ表示
         * @param {string} message
         * @param {string} type
         */
        function showMessage(message, type = "danger") {

            statusArea.replaceChildren();

            const alertElement = document.createElement("div");

            alertElement.className =
                `alert alert-${type} mb-0`;

            alertElement.textContent = message;

            statusArea.appendChild(alertElement);
        }

        /**
         * メッセージクリア
         */
        function clearMessage() {
            statusArea.replaceChildren();
        }

        //!SECTION

        //SECTION: ログイン状態確認

        //ANCHOR - ログイン済み確認
        document.addEventListener(
            "DOMContentLoaded",
            async function() {

                try {

                    const response = await fetch(
                        "<?= getenv('API_URL') ?>/user/is_logged_in.php",
                        {
                            method: "POST",
                            credentials: "include",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({})
                        }
                    );

                    const result = await response.json();

                    if (
                        result.success &&
                        result.data.isLoggedIn
                    ) {

                        window.location.href =
                            "/user/main_page.php";

                    }

                } catch (error) {

                    console.error(error);

                }

            }
        );

        //!SECTION

        //SECTION: ログイン処理

        //ANCHOR - フォーム送信
        loginForm.addEventListener(
            "submit",
            async function(event) {

                event.preventDefault();

                clearMessage();

                const token =
                    tokenInput.value.trim();

                const passcode =
                    passcodeInput.value;

                const csrfToken =
                    document.querySelector(
                        'input[name="csrf_token"]'
                    )?.value;

                tokenInput.classList.remove(
                    "is-invalid"
                );

                passcodeInput.classList.remove(
                    "is-invalid"
                );

                if (token.length !== 10) {

                    tokenInput.classList.add(
                        "is-invalid"
                    );

                    showMessage(
                        "トークンは10文字で入力してください。"
                    );

                    return;
                }

                if (!passcode) {

                    passcodeInput.classList.add(
                        "is-invalid"
                    );

                    showMessage(
                        "パスコードを入力してください。"
                    );

                    return;
                }

                loginButton.disabled = true;
                loginButton.textContent = "認証中...";

                try {

                    const response = await fetch(
                        "<?= getenv('API_URL') ?>/user/login.php",
                        {
                            method: "POST",
                            credentials: "include",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({
                                token: token,
                                passcode: passcode,
                                csrf_token: csrfToken
                            })
                        }
                    );
                    const result =
                        await response.json();

                    if (result.success) {

                        showMessage(
                            result.message,
                            "success"
                        );

                        setTimeout(() => {

                            window.location.href =
                                "/user/main_page.php";

                        }, 1000);

                    } else {

                        showMessage(
                            result.message
                        );

                    }

                } catch (error) {

                    console.error(error);

                    showMessage(
                        "通信エラーが発生しました。"
                    );

                } finally {

                    loginButton.disabled = false;
                    loginButton.textContent = "ログイン";

                }

            }
        );

        //!SECTION

        //SECTION: 入力補助

        tokenInput.addEventListener(
            "input",
            function() {

                if (
                    this.value.trim().length === 10
                ) {

                    this.classList.remove(
                        "is-invalid"
                    );

                }

            }
        );

        passcodeInput.addEventListener(
            "input",
            function() {

                if (this.value) {

                    this.classList.remove(
                        "is-invalid"
                    );

                }

            }
        );

        //!SECTION
    </script>

    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>

</body>

</html>