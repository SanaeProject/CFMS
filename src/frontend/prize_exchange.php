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
    <meta property="og:title" content="景品交換・ユーザー終了">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nkc.sanae.dev">
    <meta property="og:image" content="/images/CFMS.png">
    <meta property="og:image:alt" content="CFMS">
    <meta property="og:description" content="ユーザーの景品交換とアカウント終了を行います。">

    <title>景品交換・ユーザー終了 | CFMS</title>
    <meta name="description" content="ユーザーのQRコードを読み取り、獲得ポイントを確認してアカウントを終了するページです。">

    <!-- bootstrap読み込み -->
    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

    <!-- QRコード読み取りライブラリ -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <style>
        /*SECTION: ページ固有のスタイル*/
        body {
            background-color: #f5f7fa;
        }

        .page-header {
            border-bottom: 1px solid #dee2e6;
        }

        .info-label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .score-value {
            font-size: 2rem;
            font-weight: 700;
        }

        #reader {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        .status-badge {
            min-width: 6rem;
        }

        /*SECTION: スマートフォン対応*/
        @media (max-width: 576px) {
            .score-value {
                font-size: 1.6rem;
            }
        }
        //!SECTION
    </style>
</head>

<body>
    <!--SECTION: メインコンテンツ-->
    <main class="container py-4 py-md-5">
        <header class="page-header pb-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="fs-1" aria-hidden="true">🎁</div>
                <div>
                    <h1 class="h3 mb-1">景品交換・ユーザー終了</h1>
                    <p class="text-secondary mb-0">PrizeExchangePage</p>
                </div>
            </div>
        </header>

        <!--SECTION: 処理結果・エラーメッセージ-->
        <div id="result" class="mb-3" role="status" aria-live="polite"></div>
        <!--!SECTION-->

        <div class="row g-4">
            <!--SECTION: ユーザー検索-->
            <section class="col-12 col-lg-5" aria-labelledby="searchHeading">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h2 id="searchHeading" class="h5 mb-0">1. ユーザー検索</h2>
                    </div>

                    <div class="card-body">
                        <p class="text-secondary small">
                            QRコードを読み取るか、トークンを手動で入力してください。
                        </p>

                        <button type="button" id="startScannerButton"
                            class="btn btn-primary w-100 mb-2">
                            QRコードを読み取る
                        </button>

                        <button type="button" id="stopScannerButton"
                            class="btn btn-outline-secondary w-100 mb-3" disabled>
                            カメラを停止
                        </button>

                        <div id="reader" class="mb-3"></div>

                        <form id="lookupForm">
                            <!-- CSRFトークン自動埋め込み対象 -->
                            <input type="hidden" class="csrf_token" name="csrf_token" value="">

                            <div class="mb-3">
                                <label for="token" class="form-label">
                                    ユーザートークン
                                </label>
                                <input type="text" class="form-control" id="token"
                                    name="token" autocomplete="off"
                                    placeholder="トークンを入力してください" required>
                            </div>

                            <button type="submit" id="lookupButton"
                                class="btn btn-outline-primary w-100">
                                ユーザー情報を検索
                            </button>
                        </form>
                    </div>
                </div>
            </section>
            <!--!SECTION-->

            <!--SECTION: ユーザー情報-->
            <section class="col-12 col-lg-7" aria-labelledby="userInfoHeading">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h2 id="userInfoHeading" class="h5 mb-0">2. ユーザー情報</h2>
                    </div>

                    <div class="card-body">
                        <div class="mb-4">
                            <div class="info-label mb-1">対象ユーザーID</div>
                            <div id="userId" class="fs-5 fw-bold">未検索</div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-sm-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="info-label mb-2">アクティベート状態</div>
                                    <span id="activeStatus"
                                        class="badge text-bg-secondary status-badge">未確認</span>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="info-label mb-2">アカウント終了状態</div>
                                    <span id="finishedStatus"
                                        class="badge text-bg-secondary status-badge">未確認</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="info-label mb-1">終了日時</div>
                            <div id="finishedAt">未確認</div>
                        </div>

                        <div class="rounded bg-light border p-4 mb-4">
                            <div class="info-label">獲得ポイント合計（確認用）</div>
                            <div>
                                <span id="totalScore" class="score-value">--</span>
                                <span class="text-secondary">pt</span>
                            </div>
                            <p class="text-secondary small mb-0 mt-2">
                                ※ 景品交換ページではポイントの消費・変更を行いません。
                            </p>
                        </div>

                        <!--SECTION: 景品交換後の終了処理-->
                        <div class="alert alert-warning small">
                            景品をユーザーに渡したことを確認してから、終了処理を実行してください。
                            終了後はユーザーへの得点付与が拒否されます。
                        </div>

                        <button type="button" id="finishButton"
                            class="btn btn-danger w-100" disabled>
                            アカウントを終了する
                        </button>
                        <!--!SECTION-->
                    </div>
                </div>
            </section>
            <!--!SECTION-->
        </div>
    </main>
    <!--!SECTION-->

    <!--SECTION: ページ処理-->
    <script>
        //SECTION: DOM要素
        const lookupForm = document.getElementById('lookupForm');
        const tokenInput = document.getElementById('token');
        const lookupButton = document.getElementById('lookupButton');
        const finishButton = document.getElementById('finishButton');
        const startScannerButton = document.getElementById('startScannerButton');
        const stopScannerButton = document.getElementById('stopScannerButton');
        const resultDiv = document.getElementById('result');

        const userIdDiv = document.getElementById('userId');
        const activeStatusSpan = document.getElementById('activeStatus');
        const finishedStatusSpan = document.getElementById('finishedStatus');
        const finishedAtDiv = document.getElementById('finishedAt');
        const totalScoreSpan = document.getElementById('totalScore');

        let currentUser = null;
        let html5QrCode = null;
        let scannerRunning = false;
        let requestVersion = 0;
        let isFinishing = false;
        //!SECTION

        //SECTION: 表示処理
        /**
         * メッセージを安全に表示する。
         * @param {string} message 表示メッセージ
         * @param {'success'|'danger'|'warning'|'info'} type 表示種別
         */
        function showResult(message, type = 'info') {
            resultDiv.replaceChildren();

            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} mb-0`;
            alertDiv.setAttribute('role', 'alert');
            alertDiv.textContent = message;

            resultDiv.appendChild(alertDiv);
        }

        /**
         * バッジの表示を更新する。
         * @param {HTMLElement} element 表示対象
         * @param {string} text 表示文字列
         * @param {string} color Bootstrapの背景色クラス
         */
        function updateBadge(element, text, color) {
            element.textContent = text;
            element.className = `badge text-bg-${color} status-badge`;
        }

        /**
         * 検索結果を画面に反映する。
         * @param {Object} user ユーザー情報
         */
        function renderUser(user) {
            userIdDiv.textContent = String(user.userId);
            updateBadge(
                activeStatusSpan,
                user.isActive ? '有効' : '未アクティブ',
                user.isActive ? 'success' : 'secondary'
            );

            const isFinished = user.finishedAt !== null && user.finishedAt !== undefined;

            updateBadge(
                finishedStatusSpan,
                isFinished ? '終了済み' : '未終了',
                isFinished ? 'danger' : 'success'
            );

            finishedAtDiv.textContent = isFinished
                ? String(user.finishedAt)
                : '未終了';

            const score = Number(user.totalScore);
            totalScoreSpan.textContent =
                Number.isFinite(score) ? score.toLocaleString('ja-JP') : '--';

            // 未アクティブ・終了済みユーザーは終了処理の対象外とする。
            finishButton.disabled =
                !user.isActive || isFinished || isFinishing;
        }

        /**
         * 検索結果を初期状態に戻す。
         */
        function resetUserInfo() {
            currentUser = null;
            userIdDiv.textContent = '未検索';
            updateBadge(activeStatusSpan, '未確認', 'secondary');
            updateBadge(finishedStatusSpan, '未確認', 'secondary');
            finishedAtDiv.textContent = '未確認';
            totalScoreSpan.textContent = '--';
            finishButton.disabled = true;
        }
        //!SECTION

        //SECTION: API通信
        /**
         * APIのJSONレスポンスを取得する。
         * CFMS共通レスポンスのdata内、および直接形式の両方に対応する。
         * @param {Response} response fetchのレスポンス
         * @returns {Promise<Object>} APIデータ
         */
        async function parseApiResponse(response) {
            const responseData = await response.json();

            if (!response.ok || responseData.success !== true) {
                throw new Error(
                    responseData.message || 'サーバーとの通信に失敗しました。'
                );
            }

            return responseData.data ?? responseData;
        }

        /**
         * トークンからユーザー情報を取得する。
         * @param {string} token ユーザートークン
         */
        async function lookupUser(token) {
            const version = ++requestVersion;

            resetUserInfo();
            lookupButton.disabled = true;
            showResult('ユーザー情報を取得しています...', 'info');

            try {
                const response = await fetch(
                    '<?= rtrim(getenv('API_URL') ?: '', '/') ?>/user/lookupByToken',
                    {
                        method: 'POST',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            token,
                            csrfToken: document.querySelector('.csrf_token').value
                        })
                    }
                );

                const user = await parseApiResponse(response);

                // 古い検索リクエストの結果で画面を上書きしない。
                if (version !== requestVersion) {
                    return;
                }

                if (
                    user.userId === undefined ||
                    typeof user.isActive !== 'boolean' ||
                    user.totalScore === undefined
                ) {
                    throw new Error('APIのレスポンス形式が正しくありません。');
                }

                currentUser = {
                    token,
                    userId: user.userId,
                    isActive: user.isActive,
                    finishedAt: user.finishedAt ?? null,
                    totalScore: user.totalScore
                };

                renderUser(currentUser);
                showResult('ユーザー情報を取得しました。', 'success');
            } catch (error) {
                if (version === requestVersion) {
                    resetUserInfo();
                    showResult(error.message || 'ユーザー検索に失敗しました。', 'danger');
                }
            } finally {
                if (version === requestVersion) {
                    lookupButton.disabled = false;
                }
            }
        }

        /**
         * アカウントを終了する。
         */
        async function finishUser() {
            if (!currentUser || isFinishing) {
                return;
            }

            if (!currentUser.isActive || currentUser.finishedAt !== null) {
                showResult('このユーザーは終了処理の対象外です。', 'warning');
                return;
            }

            const confirmed = window.confirm(
                `ユーザーID: ${currentUser.userId}\n\n` +
                '景品を渡したことを確認しましたか？\n' +
                'アカウントを終了すると、得点を付与できなくなります。'
            );

            if (!confirmed) {
                return;
            }

            const targetToken = currentUser.token;
            const targetUserId = currentUser.userId;
            const csrfToken = document.querySelector('.csrf_token').value;

            isFinishing = true;
            finishButton.disabled = true;
            lookupButton.disabled = true;
            showResult('アカウントを終了しています...', 'info');

            try {
                const response = await fetch(
                    '<?= rtrim(getenv('API_URL') ?: '', '/') ?>/user/finish',
                    {
                        method: 'POST',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            token: targetToken,
                            csrfToken
                        })
                    }
                );

                const data = await parseApiResponse(response);

                // 終了処理中に対象ユーザーが変わっていないことを確認する。
                if (
                    !currentUser ||
                    currentUser.token !== targetToken ||
                    String(currentUser.userId) !== String(targetUserId)
                ) {
                    showResult(
                        '終了処理の結果を確認できません。対象ユーザーを再検索してください。',
                        'warning'
                    );
                    return;
                }

                currentUser.finishedAt = data.finishedAt ?? new Date().toISOString();
                renderUser(currentUser);
                showResult(data.message || 'アカウントを終了しました。', 'success');
            } catch (error) {
                showResult(
                    error.message ||
                    '終了処理に失敗しました。ユーザー情報を再検索して状態を確認してください。',
                    'danger'
                );

                // 通信エラー時はサーバー側で終了済みの可能性もあるため、
                // ボタンを再有効化する前にユーザー情報を再取得する。
                await lookupUser(targetToken);
            } finally {
                isFinishing = false;
                lookupButton.disabled = false;

                if (currentUser) {
                    renderUser(currentUser);
                } else {
                    finishButton.disabled = true;
                }
            }
        }
        //!SECTION

        //SECTION: フォームイベント
        lookupForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const token = tokenInput.value.trim();

            if (!token) {
                showResult('トークンを入力してください。', 'warning');
                return;
            }

            if (isFinishing) {
                showResult('終了処理中は検索できません。', 'warning');
                return;
            }

            await lookupUser(token);
        });

        finishButton.addEventListener('click', finishUser);
        //!SECTION

        //SECTION: QRコードリーダー
        /**
         * QRコードリーダーを起動する。
         * カメラを利用するためHTTPS環境が必要。
         */
        async function startScanner() {
            if (scannerRunning) {
                return;
            }

            if (typeof Html5Qrcode === 'undefined') {
                showResult('QRコードライブラリを読み込めませんでした。', 'danger');
                return;
            }

            try {
                html5QrCode = new Html5Qrcode('reader');

                const cameras = await Html5Qrcode.getCameras();

                if (!cameras || cameras.length === 0) {
                    throw new Error('利用できるカメラが見つかりません。');
                }

                const camera = cameras.find(item =>
                    /back|rear|environment|背面/i.test(item.label)
                ) || cameras[0];

                await html5QrCode.start(
                    camera.id,
                    {
                        fps: 10,
                        qrbox: {
                            width: 250,
                            height: 250
                        }
                    },
                    async decodedText => {
                        // 読み取り成功後、カメラを停止してから検索する。
                        tokenInput.value = decodedText.trim();
                        await stopScanner();
                        await lookupUser(tokenInput.value);
                    },
                    () => {
                        // 読み取り途中の失敗は継続する。
                    }
                );

                scannerRunning = true;
                startScannerButton.disabled = true;
                stopScannerButton.disabled = false;
                showResult('QRコードをカメラにかざしてください。', 'info');
            } catch (error) {
                scannerRunning = false;
                startScannerButton.disabled = false;
                stopScannerButton.disabled = true;

                showResult(
                    error.message || 'カメラを起動できませんでした。',
                    'danger'
                );
            }
        }

        /**
         * QRコードリーダーを停止する。
         */
        async function stopScanner() {
            if (!html5QrCode || !scannerRunning) {
                return;
            }

            try {
                await html5QrCode.stop();
                html5QrCode.clear();
            } catch (error) {
                console.error('QRコードリーダー停止エラー:', error);
            } finally {
                scannerRunning = false;
                startScannerButton.disabled = false;
                stopScannerButton.disabled = true;
            }
        }

        startScannerButton.addEventListener('click', startScanner);
        stopScannerButton.addEventListener('click', stopScanner);
        //!SECTION
    </script>
    <!--!SECTION-->

    <!-- フッター読み込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

    <!-- CSRFトークンの自動埋め込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>
</body>

</html>
