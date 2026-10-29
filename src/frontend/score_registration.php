
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="乙成隼士,松本浩成,永島愛貴,塩川海響,星川誠弥,中島悠翔">
    <meta name="description" content="スタッフ用の得点登録ページ">
    <meta name="keywords" content="nkc,名古屋工学院専門学校,文化祭">

    <link rel="icon" href="/images/CFMS.ico" sizes="any">
    <link rel="icon" href="/images/CFMS.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/images/CFMS.png">

    <meta property="og:title" content="得点登録ページ">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nkc.sanae.dev">
    <meta property="og:image" content="/images/CFMS.png">
    <meta property="og:description" content="QRコードからユーザーを確認して得点を登録します">

    <title>得点登録ページ</title>

    <!-- Bootstrap -->
    <link rel="stylesheet"
          href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">

    <!-- QRコード読み取りライブラリ -->
    <script src="https://unpkg.com/html5-qrcode"></script>

    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light">

<main class="container py-4" style="max-width: 650px;">

    <div class="card shadow-sm">

        <!-- ヘッダー -->
        <div class="card-header bg-primary text-white p-3">
            <h1 class="h4 mb-1">得点登録</h1>
            <p class="mb-0 small">スタッフ用｜ゲームポイント管理</p>
        </div>

        <div class="card-body">

            <!-- メッセージ -->
            <div id="messageArea"
                 class="alert d-none"
                 role="alert"
                 aria-live="polite"></div>

            <!-- 店舗選択 -->
            <section class="mb-4">
                <label for="storeId" class="form-label fw-bold">
                    担当店舗 *
                </label>

                <select id="storeId" class="form-select" required>
                    <option value="">店舗を選択してください</option>
                </select>

                <div class="form-text">
                    担当する店舗を選択してください。
                </div>
            </section>

            <hr>

            <!-- QRコード読み取り -->
            <section class="mb-4">
                <h2 class="h5 mb-3">1. QRコード読み取り</h2>

                <div class="border rounded p-3 bg-white">

                    <div id="qrReader" class="mb-3 d-none"></div>

                    <div id="cameraStatus"
                         class="alert alert-secondary py-2"
                         role="status"
                         aria-live="polite">
                        カメラを起動してQRコードを読み取ってください。
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="button"
                                id="startCameraButton"
                                class="btn btn-primary">
                            カメラを起動する
                        </button>

                        <button type="button"
                                id="stopCameraButton"
                                class="btn btn-outline-secondary d-none">
                            カメラを停止する
                        </button>
                    </div>

                    <div class="text-center text-secondary small mb-3">
                        または token を手入力
                    </div>

                    <!-- ユーザー情報取得フォーム -->
                    <form id="getUserForm">
                        <div class="mb-3">
                            <label for="token" class="form-label">
                                QRコードのtoken *
                            </label>

                            <input type="text"
                                   id="token"
                                   name="token"
                                   class="form-control"
                                   placeholder="tokenを入力してください"
                                   autocomplete="off"
                                   required>
                        </div>

                        <button type="submit"
                                id="getUserButton"
                                class="btn btn-primary w-100">
                            ユーザー情報を取得
                        </button>
                    </form>

                </div>
            </section>

            <!-- ユーザー情報 -->
            <section id="userSection" class="mb-4 d-none">

                <h2 class="h5 mb-3">2. ユーザー情報</h2>

                <div class="card bg-light">
                    <div class="card-body">
                        <dl class="row mb-0">

                            <dt class="col-6">ユーザーID</dt>
                            <dd class="col-6" id="userId">-</dd>

                            <dt class="col-6">アカウント状態</dt>
                            <dd class="col-6" id="accountStatus">-</dd>

                            <dt class="col-6">獲得ポイント合計</dt>
                            <dd class="col-6">
                                <strong id="totalScore">0</strong> pt
                            </dd>

                            <dt class="col-6">店舗利用回数</dt>
                            <dd class="col-6">
                                <span id="usageCount">0</span>
                                /
                                <span id="usageLimit">3</span> 回
                            </dd>

                        </dl>
                    </div>
                </div>

                <!-- 得点履歴 -->
                <div class="mt-3">
                    <h3 class="h6">得点履歴</h3>

                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>登録日時</th>
                                    <th>店舗</th>
                                    <th class="text-end">ポイント</th>
                                </tr>
                            </thead>

                            <tbody id="scoreHistory">
                                <tr>
                                    <td colspan="3"
                                        class="text-center text-secondary">
                                        履歴はありません
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </section>

            <!-- 得点登録 -->
            <section id="scoreSection" class="d-none">

                <h2 class="h5 mb-3">3. 1プレー分の得点登録</h2>

                <div class="border rounded p-3 bg-white">
                    <form id="scoreForm">

                        <div class="mb-3">
                            <label for="score" class="form-label">
                                獲得ポイント *
                            </label>

                            <input type="number"
                                   id="score"
                                   name="score"
                                   class="form-control"
                                   min="0"
                                   step="1"
                                   placeholder="ポイントを入力してください"
                                   required>

                            <div class="form-text">
                                1プレーにつき1回登録します。
                                ポイント数自体に上限はありません。
                            </div>
                        </div>

                        <button type="submit"
                                id="registerButton"
                                class="btn btn-success w-100"
                                disabled>
                            得点を登録
                        </button>
                    </form>
                </div>
            </section>

        </div>

        <div class="card-footer text-secondary small">
            得点登録ページ（モック）
        </div>

    </div>
</main>

<!-- 共通フッター -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

<script>
(() => {
    'use strict';

    // ========================================
    // 1. モックデータ
    // ========================================

    const stores = [
        { id: '1', name: '1組店舗' },
        { id: '2', name: '2組店舗' },
        { id: '3', name: '3組店舗' }
    ];

    // ユーザー情報のサンプル
    const mockUsers = {
        'demo-token-001': {
            userId: 'USER-001',
            isActive: true,
            isEnded: false,
            totalScore: 120,
            storeUsageCount: 1,
            storeUsageLimit: 3,
            scores: [
                {
                    createdAt: '2026-10-20 10:00',
                    storeId: '1',
                    score: 50
                },
                {
                    createdAt: '2026-10-21 13:30',
                    storeId: '2',
                    score: 70
                }
            ]
        },

        'demo-token-002': {
            userId: 'USER-002',
            isActive: true,
            isEnded: false,
            totalScore: 80,
            storeUsageCount: 0,
            storeUsageLimit: 3,
            scores: []
        }
    };

    // 現在のユーザー情報
    let currentUser = null;
    let currentToken = '';
    let currentStoreId = '';

    let html5QrCode = null;
    let cameraRunning = false;
    let processingQr = false;
    let loadingUser = false;
    let registeringScore = false;

    // ========================================
    // 2. DOM要素
    // ========================================

    const getUserForm = document.getElementById('getUserForm');
    const scoreForm = document.getElementById('scoreForm');

    const storeSelect = document.getElementById('storeId');
    const tokenInput = document.getElementById('token');
    const scoreInput = document.getElementById('score');

    const messageArea = document.getElementById('messageArea');
    const userSection = document.getElementById('userSection');
    const scoreSection = document.getElementById('scoreSection');

    const qrReaderElement = document.getElementById('qrReader');
    const cameraStatus = document.getElementById('cameraStatus');

    const startCameraButton =
        document.getElementById('startCameraButton');

    const stopCameraButton =
        document.getElementById('stopCameraButton');

    const getUserButton =
        document.getElementById('getUserButton');

    const registerButton =
        document.getElementById('registerButton');

    const scoreHistory = document.getElementById('scoreHistory');

    // ========================================
    // 3. メッセージ表示
    // ========================================

    function showMessage(message, type = 'info') {
        messageArea.textContent = message;
        messageArea.className = `alert alert-${type}`;
    }

    function hideMessage() {
        messageArea.textContent = '';
        messageArea.className = 'alert d-none';
    }

    function setCameraStatus(message, type = 'secondary') {
        cameraStatus.textContent = message;
        cameraStatus.className = `alert alert-${type} py-2`;
    }

    // ========================================
    // 4. 店舗一覧表示
    // APIは使わずサンプルデータを使用
    // ========================================

    function loadStores() {
        storeSelect.replaceChildren();

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = '店舗を選択してください';
        storeSelect.appendChild(defaultOption);

        stores.forEach(store => {
            const option = document.createElement('option');
            option.value = store.id;
            option.textContent = store.name;
            storeSelect.appendChild(option);
        });
    }

    function getStoreName(storeId) {
        const store = stores.find(item => item.id === String(storeId));
        return store ? store.name : '不明な店舗';
    }

    // ========================================
    // 5. ユーザー情報をリセット
    // ========================================

    function hideUser() {
        currentUser = null;
        currentToken = '';
        currentStoreId = '';

        userSection.classList.add('d-none');
        scoreSection.classList.add('d-none');

        scoreHistory.replaceChildren();
        registerButton.disabled = true;
    }

    // ========================================
    // 6. 得点履歴表示
    // ========================================

    function displayHistory(scores) {
        scoreHistory.replaceChildren();

        if (!Array.isArray(scores) || scores.length === 0) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');

            td.colSpan = 3;
            td.className = 'text-center text-secondary';
            td.textContent = '履歴はありません';

            tr.appendChild(td);
            scoreHistory.appendChild(tr);
            return;
        }

        // 新しい履歴を先に表示
        [...scores].reverse().forEach(item => {
            const tr = document.createElement('tr');

            const dateCell = document.createElement('td');
            dateCell.textContent = item.createdAt || '-';

            const storeCell = document.createElement('td');
            storeCell.textContent = getStoreName(item.storeId);

            const scoreCell = document.createElement('td');
            scoreCell.className = 'text-end';
            scoreCell.textContent =
                Number(item.score).toLocaleString('ja-JP');

            tr.append(dateCell, storeCell, scoreCell);
            scoreHistory.appendChild(tr);
        });
    }

    // ========================================
    // 7. ユーザー情報表示
    // ========================================

    function displayUser(user) {
        currentUser = user;

        document.getElementById('userId').textContent = user.userId;

        const statusElement =
            document.getElementById('accountStatus');

        if (user.isEnded) {
            statusElement.textContent = '終了済み';
            statusElement.className = 'col-6 text-danger fw-bold';
        } else if (user.isActive) {
            statusElement.textContent = '有効';
            statusElement.className = 'col-6 text-success fw-bold';
        } else {
            statusElement.textContent = '未アクティベート';
            statusElement.className = 'col-6 text-danger fw-bold';
        }

        document.getElementById('totalScore').textContent =
            Number(user.totalScore).toLocaleString('ja-JP');

        document.getElementById('usageCount').textContent =
            user.storeUsageCount;

        document.getElementById('usageLimit').textContent =
            user.storeUsageLimit;

        displayHistory(user.scores);

        userSection.classList.remove('d-none');
        scoreSection.classList.remove('d-none');

        const canAddScore =
            user.isActive &&
            !user.isEnded &&
            user.storeUsageCount < user.storeUsageLimit;

        registerButton.disabled = !canAddScore || registeringScore;

        if (!canAddScore) {
            showMessage(
                'このユーザーは得点登録できません。アカウント状態と店舗利用回数を確認してください。',
                'warning'
            );
        }
    }

    // ========================================
    // 8. ユーザー情報取得（モック）
    // API通信の代わりにサンプルデータを参照
    // ========================================

    async function getUserInfo(token) {
        if (loadingUser) return;

        const storeId = storeSelect.value;
        const trimmedToken = token.trim();

        if (!storeId) {
            showMessage('担当店舗を選択してください。', 'warning');
            return;
        }

        if (!trimmedToken) {
            showMessage('QRコードのtokenを入力してください。', 'warning');
            return;
        }

        loadingUser = true;
        getUserButton.disabled = true;

        hideUser();

        try {
            // 実際のAPI送信は行わない
            // デモ用tokenなら対応するユーザーを表示する
            const user = mockUsers[trimmedToken];

            if (!user) {
                showMessage(
                    '該当するユーザーが見つかりません。デモ用tokenを入力してください（demo-token-001 / demo-token-002）。',
                    'danger'
                );
                return;
            }

            currentToken = trimmedToken;
            currentStoreId = storeId;

            displayUser(user);

            if (user.isActive && !user.isEnded &&
                user.storeUsageCount < user.storeUsageLimit) {
                showMessage('ユーザー情報を取得しました（モック）。', 'success');
            }

        } finally {
            loadingUser = false;
            getUserButton.disabled = false;
        }
    }

    // ========================================
    // 9. 手入力フォーム
    // ========================================

    getUserForm.addEventListener('submit', function(event) {
        event.preventDefault();
        getUserInfo(tokenInput.value);
    });

    // ========================================
    // 10. QRコードカメラ停止
    // ========================================

    async function stopQrCamera() {
        if (!html5QrCode || !cameraRunning) {
            cameraRunning = false;
            qrReaderElement.classList.add('d-none');
            startCameraButton.disabled = false;
            stopCameraButton.classList.add('d-none');
            return;
        }

        try {
            await html5QrCode.stop();
        } catch (error) {
            console.error('カメラ停止エラー:', error);
        } finally {
            cameraRunning = false;
            qrReaderElement.classList.add('d-none');
            startCameraButton.disabled = false;
            stopCameraButton.classList.add('d-none');
        }
    }

    // ========================================
    // 11. QRコードカメラ起動
    // ========================================

    startCameraButton.addEventListener('click', async function() {
        if (cameraRunning) return;

        if (!storeSelect.value) {
            showMessage('先に担当店舗を選択してください。', 'warning');
            return;
        }

        if (typeof Html5Qrcode === 'undefined') {
            setCameraStatus(
                'QRコード読み取りライブラリを読み込めません。',
                'danger'
            );
            return;
        }

        startCameraButton.disabled = true;
        setCameraStatus('カメラを起動しています...', 'info');

        try {
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode('qrReader');
            }

            qrReaderElement.classList.remove('d-none');

            await html5QrCode.start(
                { facingMode: 'environment' },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                async function(decodedText) {
                    if (processingQr || loadingUser) return;

                    processingQr = true;

                    try {
                        const scannedToken = decodedText.trim();

                        if (!scannedToken) {
                            setCameraStatus(
                                'QRコードからtokenを取得できませんでした。',
                                'warning'
                            );
                            return;
                        }

                        tokenInput.value = scannedToken;

                        setCameraStatus(
                            '読み取り成功。ユーザー情報を表示します。',
                            'success'
                        );

                        await stopQrCamera();
                        await getUserInfo(scannedToken);

                    } finally {
                        processingQr = false;
                    }
                },
                function() {
                    // QRコード未検出時は読み取りを続ける
                }
            );

            cameraRunning = true;
            startCameraButton.disabled = true;
            stopCameraButton.classList.remove('d-none');

            setCameraStatus(
                'カメラ起動中です。QRコードを枠内に合わせてください。',
                'success'
            );

        } catch (error) {
            console.error('カメラ起動エラー:', error);

            cameraRunning = false;
            qrReaderElement.classList.add('d-none');
            startCameraButton.disabled = false;
            stopCameraButton.classList.add('d-none');

            setCameraStatus(
                'カメラを起動できません。HTTPS接続やカメラの使用許可を確認してください。',
                'danger'
            );
        }
    });

    // ========================================
    // 12. カメラ停止ボタン
    // ========================================

    stopCameraButton.addEventListener('click', async function() {
        await stopQrCamera();
        setCameraStatus('カメラを停止しました。', 'secondary');
    });

    // ========================================
    // 13. 得点登録（モック）
    // API送信せず、画面内のデータだけ更新
    // ========================================

    scoreForm.addEventListener('submit', function(event) {
        event.preventDefault();

        if (registeringScore) return;

        if (!currentUser || !currentToken) {
            showMessage(
                '先にQRコードを読み取り、ユーザー情報を取得してください。',
                'warning'
            );
            return;
        }

        const canAddScore =
            currentUser.isActive &&
            !currentUser.isEnded &&
            currentUser.storeUsageCount < currentUser.storeUsageLimit;

        if (!canAddScore) {
            showMessage('このユーザーは得点登録できません。', 'warning');
            return;
        }

        const rawScore = scoreInput.value;
        const score = Number(rawScore);

        if (
            rawScore.trim() === '' ||
            !Number.isSafeInteger(score) ||
            score < 0
        ) {
            showMessage(
                'ポイントは0以上の整数で入力してください。',
                'warning'
            );
            return;
        }

        if (!storeSelect.value) {
            showMessage('担当店舗を選択してください。', 'warning');
            return;
        }

        registeringScore = true;
        registerButton.disabled = true;

        try {
            // API送信の代わりにモックデータを更新
            currentUser.totalScore += score;

            currentUser.scores.push({
                createdAt: new Date().toLocaleString('ja-JP', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                }),
                storeId: storeSelect.value,
                score: score
            });

            // 画面に反映
            document.getElementById('totalScore').textContent =
                currentUser.totalScore.toLocaleString('ja-JP');

            displayHistory(currentUser.scores);

            scoreInput.value = '';

            showMessage(
                `${score}ポイントを登録しました（モック）。`,
                'success'
            );

        } catch (error) {
            console.error(error);
            showMessage('得点登録に失敗しました。', 'danger');

        } finally {
            registeringScore = false;

            const canAddScore =
                currentUser &&
                currentUser.isActive &&
                !currentUser.isEnded &&
                currentUser.storeUsageCount < currentUser.storeUsageLimit;

            registerButton.disabled = !canAddScore;
        }
    });

    // ========================================
    // 14. 店舗変更時
    // ========================================

    storeSelect.addEventListener('change', async function() {
        await stopQrCamera();

        hideUser();

        tokenInput.value = '';
        scoreInput.value = '';

        hideMessage();

        setCameraStatus(
            'カメラを起動してQRコードを読み取ってください。',
            'secondary'
        );
    });

    // ========================================
    // 15. ページ離脱時にカメラを停止
    // ========================================

    window.addEventListener('pagehide', function() {
        if (html5QrCode && cameraRunning) {
            html5QrCode.stop().catch(function(error) {
                console.error('カメラ停止エラー:', error);
            });
        }
    });

    // ========================================
    // 16. 初期表示
    // ========================================

    loadStores();

})();
</script>

</body>
</html>
