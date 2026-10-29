
<!DOCTYPE html>
<html lang="ja">

<head>
    <!-- 基本設定 -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="乙成隼士,松本浩成,永島愛貴,塩川海響,星川誠弥,中島悠翔">

    <!-- サイト情報 -->
    <title>CFMS | スタッフログイン</title>
    <meta name="description" content="CFMSの管理者・スタッフ専用ログインページ">
    <meta name="keywords" content="nkc,名古屋工学院専門学校,文化祭">

    <!-- ファビコン -->
    <link rel="icon" href="/images/CFMS.ico" sizes="any">
    <link rel="icon" href="/images/CFMS.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/images/CFMS.png">

    <!-- OGP設定 -->
    <meta property="og:title" content="CFMS | スタッフログイン">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nkc.sanae.dev">
    <meta property="og:image" content="/images/CFMS.png">
    <meta property="og:image:alt" content="CFMS">
    <meta property="og:description" content="CFMSの管理者・スタッフ専用ログインページ">

    <!-- CSRFトークン用 -->
    <meta name="csrf-token" content="">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js" defer></script>
</head>

<body class="bg-light">

    <!-- 画面全体：スマートフォンでも中央に配置 -->
    <main class="container min-vh-100 d-flex justify-content-center align-items-center py-4">

        <!-- ログインカード -->
        <div class="row justify-content-center w-100">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">

                <div class="card shadow-sm">

                    <!-- ページタイトル -->
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h1 class="h3 mb-2">CFMS</h1>
                        <p class="mb-0">管理者・スタッフログイン</p>
                    </div>

                    <!-- ログインフォーム -->
                    <div class="card-body p-4">

                        <h2 class="h4 mb-3">ログイン</h2>

                        <p class="text-secondary mb-4">
                            スタッフIDとパスワードを入力してください。
                        </p>

                        <!-- 処理結果・エラーメッセージ -->
                        <div
                            id="statusArea"
                            class="mb-3"
                            role="status"
                            aria-live="polite"
                        ></div>

                        <!-- ログインフォーム -->
                        <form id="loginForm" novalidate>

                            <!-- CSRFトークン -->
                            <input
                                type="hidden"
                                id="csrfToken"
                                name="csrf_token"
                                value=""
                            >

                            <!-- スタッフID -->
                            <div class="mb-3">
                                <label for="staffId" class="form-label">
                                    スタッフID
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="staffId"
                                    name="staffId"
                                    placeholder="スタッフIDを入力"
                                    autocomplete="username"
                                    maxlength="100"
                                    required
                                >

                                <div class="invalid-feedback">
                                    スタッフIDを入力してください。
                                </div>
                            </div>

                            <!-- パスワード -->
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    パスワード
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="password"
                                    name="password"
                                    placeholder="パスワードを入力"
                                    autocomplete="current-password"
                                    required
                                >

                                <div class="invalid-feedback">
                                    パスワードを入力してください。
                                </div>
                            </div>

                            <!-- パスワード表示切り替え -->
                            <div class="form-check mb-4">
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    id="showPassword"
                                >

                                <label
                                    class="form-check-label"
                                    for="showPassword"
                                >
                                    パスワードを表示する
                                </label>
                            </div>

                            <!-- ログインボタン -->
                            <div class="d-grid">
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    id="loginButton"
                                >
                                    ログイン
                                </button>
                            </div>

                        </form>

                        <!-- ログイン状態確認 -->
                        <div class="text-center mt-3">
                            <button
                                type="button"
                                class="btn btn-link text-decoration-none"
                                id="checkSessionButton"
                            >
                                ログイン状態を確認
                            </button>
                        </div>

                    </div>

                    <!-- カードフッター -->
                    <div class="card-footer text-center text-secondary py-3">
                        <small>CFMS 管理者・スタッフ専用システム</small>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- CSRFトークンの自動埋め込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>

    <!-- 共通フッター -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

    <script>
    (() => {
        'use strict';

        // ==============================
        // DOM要素の取得
        // ==============================

        const loginForm = document.getElementById('loginForm');
        const staffIdInput = document.getElementById('staffId');
        const passwordInput = document.getElementById('password');
        const csrfTokenInput = document.getElementById('csrfToken');

        const statusArea = document.getElementById('statusArea');
        const loginButton = document.getElementById('loginButton');

        const showPasswordCheckbox = document.getElementById('showPassword');
        const checkSessionButton = document.getElementById('checkSessionButton');

        // ==============================
        // モック用の定数
        // 実際の認証ではサーバー側で認証する
        // ==============================

        const MOCK_STAFF_ID = 'staff001';
        const MOCK_PASSWORD = 'demo1234';
        const SESSION_KEY = 'cfmsMockStaff';

        let loggingIn = false;
        let checkingSession = false;

        // ==============================
        // メッセージ表示
        // ==============================

        function showMessage(message, type = 'danger') {
            statusArea.replaceChildren();

            const alertElement = document.createElement('div');
            alertElement.className = `alert alert-${type} mb-0`;
            alertElement.setAttribute('role', 'alert');
            alertElement.textContent = message;

            statusArea.appendChild(alertElement);
        }

        function clearMessage() {
            statusArea.replaceChildren();
        }

        // ==============================
        // CSRFトークン取得
        // ==============================

        function getCsrfToken() {
            if (csrfTokenInput && csrfTokenInput.value) {
                return csrfTokenInput.value;
            }

            const meta = document.querySelector(
                'meta[name="csrf-token"]'
            );

            if (meta && meta.content) {
                return meta.content;
            }

            return '';
        }

        // ==============================
        // モックAPI：ログイン処理
        // ==============================

        /**
         * スタッフのログインを模擬する
         * 注意：この関数はデモ専用で、実際の認証には使用しない
         */
        async function mockLoginApi(payload) {
            // 通信時間を模擬する
            await new Promise(resolve => setTimeout(resolve, 300));

            if (!payload.csrfToken) {
                return {
                    success: false,
                    message: 'CSRFトークンを取得できません。'
                };
            }

            // デモ用の固定ID・パスワードを照合する
            if (
                payload.staffId === MOCK_STAFF_ID &&
                payload.password === MOCK_PASSWORD
            ) {
                const staff = {
                    staffId: MOCK_STAFF_ID,
                    displayName: 'デモスタッフ',
                    role: 'staff'
                };

                sessionStorage.setItem(
                    SESSION_KEY,
                    JSON.stringify(staff)
                );

                return {
                    success: true,
                    message: 'ログインに成功しました。',
                    staff: staff
                };
            }

            return {
                success: false,
                message: 'スタッフIDまたはパスワードが正しくありません。'
            };
        }

        // ==============================
        // ログインフォーム送信処理
        // ==============================

        loginForm.addEventListener('submit', async function(event) {
            event.preventDefault();

            if (loggingIn) {
                return;
            }

            clearMessage();

            const staffId = staffIdInput.value.trim();
            const password = passwordInput.value;
            const csrfToken = getCsrfToken();

            // 必須入力チェック
            staffIdInput.classList.toggle('is-invalid', !staffId);
            passwordInput.classList.toggle('is-invalid', !password);

            if (!staffId || !password) {
                showMessage(
                    'スタッフIDとパスワードを入力してください。',
                    'warning'
                );
                return;
            }

            // CSRFトークン確認
            if (!csrfToken) {
                showMessage(
                    'CSRFトークンを取得できません。ページを再読み込みしてください。',
                    'danger'
                );
                return;
            }

            loggingIn = true;
            loginButton.disabled = true;
            loginButton.textContent = '認証中...';

            try {
                const result = await mockLoginApi({
                    staffId: staffId,
                    password: password,
                    csrfToken: csrfToken
                });

                if (result.success === true) {
                    showMessage(
                        'ログインに成功しました。担当店舗の選択へ進みます。',
                        'success'
                    );

                    // 店舗選択画面を実装したら遷移先を設定する
                    // window.location.href = '/staff/stores';
                } else {
                    showMessage(
                        result.message || 'ログインに失敗しました。',
                        'danger'
                    );
                }

            } catch (error) {
                console.error('Login error:', error);

                showMessage(
                    '処理に失敗しました。もう一度お試しください。',
                    'danger'
                );

            } finally {
                loggingIn = false;
                loginButton.disabled = false;
                loginButton.textContent = 'ログイン';
            }
        });

        // ==============================
        // パスワード表示切り替え
        // ==============================

        showPasswordCheckbox.addEventListener('change', function() {
            passwordInput.type = this.checked ? 'text' : 'password';
        });

        // ==============================
        // モックAPI：ログイン状態確認
        // ==============================

        /**
         * 保存されたログイン情報を確認する
         * @returns {Promise<Object>} ログイン状態
         */
        async function mockIsLoggedInApi() {
            const storedStaff = sessionStorage.getItem(SESSION_KEY);

            // 保存データがない場合
            if (!storedStaff) {
                return {
                    success: true,
                    isLoggedIn: false,
                    staff: null
                };
            }

            try {
                const staff = JSON.parse(storedStaff);

                // null、配列、必要項目のないデータは無効とする
                if (
                    staff === null ||
                    typeof staff !== 'object' ||
                    Array.isArray(staff) ||
                    typeof staff.staffId !== 'string' ||
                    typeof staff.displayName !== 'string' ||
                    typeof staff.role !== 'string'
                ) {
                    sessionStorage.removeItem(SESSION_KEY);

                    return {
                        success: true,
                        isLoggedIn: false,
                        staff: null
                    };
                }

                return {
                    success: true,
                    isLoggedIn: true,
                    staff: staff
                };

            } catch (error) {
                // JSONの形式が壊れている場合は削除する
                sessionStorage.removeItem(SESSION_KEY);

                return {
                    success: true,
                    isLoggedIn: false,
                    staff: null
                };
            }
        }

        // ==============================
        // ログイン状態確認ボタン
        // ==============================

        checkSessionButton.addEventListener('click', async function() {
            if (checkingSession) {
                return;
            }

            checkingSession = true;
            checkSessionButton.disabled = true;
            clearMessage();

            try {
                const result = await mockIsLoggedInApi();

                if (result.isLoggedIn && result.staff) {
                    showMessage(
                        'ログイン中です：' + result.staff.displayName,
                        'success'
                    );
                } else {
                    showMessage(
                        '現在ログインしていません。',
                        'info'
                    );
                }

            } catch (error) {
                console.error('Session check error:', error);

                showMessage(
                    'ログイン状態の確認に失敗しました。',
                    'danger'
                );

            } finally {
                checkingSession = false;
                checkSessionButton.disabled = false;
            }
        });

        // ==============================
        // 入力時に必須エラーを解除する
        // ==============================

        staffIdInput.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });

        passwordInput.addEventListener('input', function() {
            if (this.value) {
                this.classList.remove('is-invalid');
            }
        });

    })();
    </script>

</body>
</html>
