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
                                name="csrf_token"
                                value="">

                            <!-- ユーザーID：文字列として扱う -->
                            <div class="mb-3">
                                <label for="userId" class="form-label">
                                    ユーザーID
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="userId"
                                    name="userId"
                                    placeholder="ユーザーIDを入力"
                                    autocomplete="username"
                                    maxlength="100"
                                    required>

                                <div class="invalid-feedback">
                                    ユーザーIDを入力してください。
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
        // =====================================
        // DOM要素の取得
        // =====================================

        const loginForm = document.getElementById("loginForm");
        const userIdInput = document.getElementById("userId");
        const passcodeInput = document.getElementById("passcode");
        const csrfTokenInput = document.getElementById("csrfToken");
        const statusArea = document.getElementById("statusArea");
        const loginButton = document.getElementById("loginButton");

        // =====================================
        // モック用の定数
        // 実際の認証情報はサーバー側で管理する
        // =====================================

        const MOCK_USER = {
            userId: "user001",
            passcode: "demo1234",
            passcodeIssued: true,
            accountEnded: false
        };

        // =====================================
        // メッセージ表示関数
        // =====================================

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

        // =====================================
        // ログインAPIのモック
        // API仕様：POST /api/user/login
        // =====================================

        /**
         * ログイン処理を模擬する
         * @param {Object} payload 送信するログイン情報
         * @returns {Promise<Object>} ログイン結果
         */
        async function mockLoginApi(payload) {
            // 通信時間を模擬する
            await new Promise(resolve => setTimeout(resolve, 300));

            // CSRFトークンの存在を確認する
            // 本物のCSRF検証はサーバー側で行う
            if (!payload.csrfToken) {
                return {
                    success: false,
                    message: "CSRFトークンを取得できません。"
                };
            }

            // ユーザーIDを文字列として比較する
            if (payload.userId !== MOCK_USER.userId) {
                return {
                    success: false,
                    message: "ユーザーIDまたはパスコードが正しくありません。"
                };
            }

            // パスコードの発行状態を確認する
            if (!MOCK_USER.passcodeIssued) {
                return {
                    success: false,
                    message: "パスコードが発行されていません。"
                };
            }

            // アカウントの終了状態を確認する
            if (MOCK_USER.accountEnded) {
                return {
                    success: false,
                    message: "このアカウントは終了しています。"
                };
            }

            // パスコードを確認する
            if (payload.passcode !== MOCK_USER.passcode) {
                return {
                    success: false,
                    message: "ユーザーIDまたはパスコードが正しくありません。"
                };
            }

            // 認証成功
            return {
                success: true,
                message: "ログインに成功しました。"
            };
        }

        // =====================================
        // フォーム送信処理
        // デフォルト送信を禁止して非同期で実行する
        // =====================================

        loginForm.addEventListener("submit", async function(event) {
            event.preventDefault();
            clearMessage();

            // 入力値を取得する
            const userId = userIdInput.value.trim();
            const passcode = passcodeInput.value;
            const csrfToken = csrfTokenInput.value;

            // 必須入力チェック
            userIdInput.classList.toggle("is-invalid", !userId);
            passcodeInput.classList.toggle("is-invalid", !passcode);

            if (!userId || !passcode) {
                showMessage(
                    "ユーザーIDとパスコードを入力してください。"
                );
                return;
            }

            // 二重送信を防止する
            loginButton.disabled = true;
            loginButton.textContent = "認証中...";

            try {
                // モックAPIにログイン情報を渡す
                const result = await mockLoginApi({
                    userId: userId,
                    passcode: passcode,
                    csrfToken: csrfToken
                });

                if (result.success) {
                    // モック用のログイン状態を保存する
                    sessionStorage.setItem(
                        "cfmsMockUser",
                        JSON.stringify({
                            userId: userId,
                            isLoggedIn: true
                        })
                    );

                    showMessage(
                        "ログインに成功しました。ユーザーメインページへ進みます。",
                        "success"
                    );

                    // 実際の画面を作成したら遷移先を設定する
                    // window.location.href = "/user/home";

                } else {
                    showMessage(result.message);
                }

            } catch (error) {
                // エラー情報は開発者向けに記録する
                console.error("Login error:", error);

                showMessage(
                    "処理に失敗しました。もう一度お試しください。"
                );

            } finally {
                // ボタンを元の状態に戻す
                loginButton.disabled = false;
                loginButton.textContent = "ログイン";
            }
        });

        // =====================================
        // ログイン状態確認APIのモック
        // API仕様：POST /api/user/isLoggedIn
        // =====================================

        /**
         * ログイン状態を確認する
         * @returns {Promise<Object>} ログイン状態
         */
        async function mockIsLoggedInApi() {
            const storedUser = sessionStorage.getItem("cfmsMockUser");

            if (!storedUser) {
                return {
                    success: true,
                    isLoggedIn: false
                };
            }

            try {
                const user = JSON.parse(storedUser);

                return {
                    success: true,
                    isLoggedIn: user.isLoggedIn === true
                };

            } catch (error) {
                // 保存データが不正な場合は削除する
                sessionStorage.removeItem("cfmsMockUser");

                return {
                    success: true,
                    isLoggedIn: false
                };
            }
        }

        // =====================================
        // 画面読み込み時のログイン状態確認
        // =====================================

        document.addEventListener("DOMContentLoaded", async function() {
            try {
                const result = await mockIsLoggedInApi();

                if (result.isLoggedIn) {
                    showMessage(
                        "ログイン済みです。ユーザーメインページへ進めます。",
                        "info"
                    );
                }

            } catch (error) {
                console.error("Session check error:", error);
            }
        });

        // =====================================
        // 入力時に必須エラーを解除する
        // =====================================

        userIdInput.addEventListener("input", function() {
            if (this.value.trim()) {
                this.classList.remove("is-invalid");
            }
        });

        passcodeInput.addEventListener("input", function() {
            if (this.value) {
                this.classList.remove("is-invalid");
            }
        });
    </script>

    <!-- フッター読み込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

    <!-- CSRFトークンの自動埋め込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>
</body>

</html>