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
    <input type="hidden" class="csrf_token" value="">
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

                                <dt class="col-6">トークン</dt>
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
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody id="scoreHistory">
                                    <tr>
                                        <td colspan="4"
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
        </div>
    </main>

    <!-- CSRF取得 -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>

    <!-- 共通フッター -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

    <script>
        (() => {
            'use strict';

            const API_BASE_URL =
                '<?= getenv('API_URL') ?>';

            const STAFF_STORES_API =
                `${API_BASE_URL}/staff/stores.php`;

            const SCORE_GET_API =
                `${API_BASE_URL}/score/get.php`;

            const SCORE_ADD_API =
                `${API_BASE_URL}/score/add.php`;

            const SCORE_DELETE_API =
                `${API_BASE_URL}/score/delete.php`;

            let stores = [];

            let currentUser = null;
            let currentToken = '';

            let html5QrCode = null;
            let cameraRunning = false;
            let processingQr = false;
            let loadingUser = false;
            let registeringScore = false;

            const getUserForm =
                document.getElementById(
                    'getUserForm'
                );

            const scoreForm =
                document.getElementById(
                    'scoreForm'
                );

            const storeSelect =
                document.getElementById(
                    'storeId'
                );

            const tokenInput =
                document.getElementById(
                    'token'
                );

            const scoreInput =
                document.getElementById(
                    'score'
                );

            const messageArea =
                document.getElementById(
                    'messageArea'
                );

            const userSection =
                document.getElementById(
                    'userSection'
                );

            const scoreSection =
                document.getElementById(
                    'scoreSection'
                );

            const qrReaderElement =
                document.getElementById(
                    'qrReader'
                );

            const cameraStatus =
                document.getElementById(
                    'cameraStatus'
                );

            const startCameraButton =
                document.getElementById(
                    'startCameraButton'
                );

            const stopCameraButton =
                document.getElementById(
                    'stopCameraButton'
                );

            const getUserButton =
                document.getElementById(
                    'getUserButton'
                );

            const registerButton =
                document.getElementById(
                    'registerButton'
                );

            const scoreHistory =
                document.getElementById(
                    'scoreHistory'
                );

            function showMessage(
                message,
                type = 'info'
            ) {

                messageArea.textContent =
                    message;

                messageArea.className =
                    `alert alert-${type}`;
            }

            function hideMessage() {

                messageArea.textContent = '';

                messageArea.className =
                    'alert d-none';
            }

            function setCameraStatus(
                message,
                type = 'secondary'
            ) {

                cameraStatus.textContent =
                    message;

                cameraStatus.className =
                    `alert alert-${type} py-2`;
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

                const csrf_token =
                    getCsrfToken();

                const response =
                    await fetch(url, {
                        method: 'POST',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ...requestData,
                            csrf_token
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
                        '通信に失敗しました。'
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

            async function loadStores() {

                const data =
                    await callApi(
                        STAFF_STORES_API
                    );

                stores =
                    Array.isArray(
                        data.stores
                    ) ?
                    data.stores : [];

                storeSelect.replaceChildren();

                const defaultOption =
                    document.createElement(
                        'option'
                    );

                defaultOption.value = '';

                defaultOption.textContent =
                    '店舗を選択してください';

                storeSelect.appendChild(
                    defaultOption
                );

                stores.forEach(store => {

                    const option =
                        document.createElement(
                            'option'
                        );

                    option.value =
                        String(store.id);

                    option.textContent =
                        store.name;

                    storeSelect.appendChild(
                        option
                    );
                });
            }

            function getStoreName(
                storeId
            ) {

                const store =
                    stores.find(
                        item =>
                        Number(item.id) ===
                        Number(storeId)
                    );

                return store ?
                    store.name :
                    '不明な店舗';
            }

            function hideUser() {

                currentUser = null;
                currentToken = '';

                userSection.classList.add(
                    'd-none'
                );

                scoreSection.classList.add(
                    'd-none'
                );

                scoreHistory.replaceChildren();

                registerButton.disabled =
                    true;
            }

            async function getUserInfo(
                token
            ) {

                if (loadingUser) {
                    return;
                }

                const storeId =
                    storeSelect.value;

                const trimmedToken =
                    token.trim();

                if (!storeId) {

                    showMessage(
                        '担当店舗を選択してください。',
                        'warning'
                    );

                    return;
                }

                if (!trimmedToken) {

                    showMessage(
                        'tokenを入力してください。',
                        'warning'
                    );

                    return;
                }

                loadingUser = true;

                getUserButton.disabled =
                    true;

                hideUser();

                try {

                    const data =
                        await callApi(
                            SCORE_GET_API, {
                                token: trimmedToken,
                                store_id: Number(
                                    storeId
                                )
                            }
                        );

                    currentToken =
                        trimmedToken;

                    displayUser(
                        data
                    );

                    showMessage(
                        'ユーザー情報を取得しました。',
                        'success'
                    );

                } catch (error) {

                    showMessage(
                        error.message,
                        'danger'
                    );

                } finally {

                    loadingUser =
                        false;

                    getUserButton.disabled =
                        false;
                }
            }

            function displayUser(data) {

                currentUser = data;

                document.getElementById(
                    'userId'
                ).textContent = data.token;

                const statusElement =
                    document.getElementById(
                        'accountStatus'
                    );

                if (data.user.isFinished) {

                    statusElement.textContent =
                        '終了済み';

                    statusElement.className =
                        'col-6 text-danger fw-bold';

                } else if (
                    data.user.isActive
                ) {

                    statusElement.textContent =
                        '有効';

                    statusElement.className =
                        'col-6 text-success fw-bold';

                } else {

                    statusElement.textContent =
                        '未アクティベート';

                    statusElement.className =
                        'col-6 text-danger fw-bold';
                }

                document.getElementById(
                        'totalScore'
                    ).textContent =
                    Number(
                        data.totalScore
                    ).toLocaleString('ja-JP');

                document.getElementById(
                        'usageCount'
                    ).textContent =
                    data.storeUsageCount;

                document.getElementById(
                        'usageLimit'
                    ).textContent =
                    data.storeUsageLimit;

                displayHistory(
                    data.scores || []
                );

                userSection.classList.remove(
                    'd-none'
                );

                scoreSection.classList.remove(
                    'd-none'
                );

                registerButton.disabled = !data.canAddScore;
            }

            function displayHistory(scores) {

                scoreHistory.replaceChildren();

                if (
                    !Array.isArray(scores) ||
                    scores.length === 0
                ) {

                    const tr =
                        document.createElement('tr');

                    const td =
                        document.createElement('td');

                    td.colSpan = 3;
                    td.className =
                        'text-center text-secondary';

                    td.textContent =
                        '履歴はありません';

                    tr.appendChild(td);

                    scoreHistory.appendChild(tr);

                    return;
                }

                scores.forEach(item => {

                    const tr =
                        document.createElement('tr');

                    const dateCell =
                        document.createElement('td');

                    dateCell.textContent =
                        item.createdAt ?? '-';

                    const storeCell =
                        document.createElement('td');

                    storeCell.textContent =
                        item.storeName ??
                        getStoreName(
                            item.storeId
                        );

                    const scoreCell =
                        document.createElement('td');

                    scoreCell.className =
                        'text-end';

                    scoreCell.textContent =
                        Number(item.score)
                        .toLocaleString('ja-JP');

                    const actionCell =
                        document.createElement('td');

                    const deleteButton =
                        document.createElement('button');

                    deleteButton.type = 'button';

                    deleteButton.className =
                        'btn btn-sm btn-outline-danger';

                    deleteButton.textContent =
                        '削除';

                    deleteButton.addEventListener(
                        'click',
                        async () => {

                            const confirmed =
                                confirm(
                                    'この得点を削除しますか？'
                                );

                            if (!confirmed) {
                                return;
                            }

                            try {

                                const result =
                                    await callApi(
                                        SCORE_DELETE_API, {
                                            score_id: item.scoreId
                                        }
                                    );

                                showMessage(
                                    result.message ||
                                    '得点を削除しました。',
                                    'success'
                                );

                                await getUserInfo(
                                    currentToken
                                );

                            } catch (error) {

                                showMessage(
                                    error.message ||
                                    '得点の削除に失敗しました。',
                                    'danger'
                                );
                            }
                        }
                    );

                    actionCell.appendChild(
                        deleteButton
                    );

                    tr.append(
                        dateCell,
                        storeCell,
                        scoreCell,
                        actionCell
                    );

                    scoreHistory.appendChild(tr);
                });
            }

            getUserForm.addEventListener(
                'submit',
                async event => {

                    event.preventDefault();

                    await getUserInfo(
                        tokenInput.value
                    );
                }
            );

            async function stopQrCamera() {

                if (
                    !html5QrCode ||
                    !cameraRunning
                ) {

                    cameraRunning = false;

                    qrReaderElement.classList.add(
                        'd-none'
                    );

                    startCameraButton.disabled =
                        false;

                    stopCameraButton.classList.add(
                        'd-none'
                    );

                    return;
                }

                try {

                    await html5QrCode.stop();

                } catch (error) {

                    console.error(
                        'カメラ停止エラー:',
                        error
                    );

                } finally {

                    cameraRunning = false;

                    qrReaderElement.classList.add(
                        'd-none'
                    );

                    startCameraButton.disabled =
                        false;

                    stopCameraButton.classList.add(
                        'd-none'
                    );
                }
            }

            startCameraButton.addEventListener(
                'click',
                async () => {

                    if (cameraRunning) {
                        return;
                    }

                    if (
                        !storeSelect.value
                    ) {

                        showMessage(
                            '先に担当店舗を選択してください。',
                            'warning'
                        );

                        return;
                    }

                    if (
                        typeof Html5Qrcode ===
                        'undefined'
                    ) {

                        setCameraStatus(
                            'QRコードライブラリを読み込めません。',
                            'danger'
                        );

                        return;
                    }

                    startCameraButton.disabled =
                        true;

                    try {

                        if (!html5QrCode) {

                            html5QrCode =
                                new Html5Qrcode(
                                    'qrReader'
                                );
                        }

                        qrReaderElement.classList.remove(
                            'd-none'
                        );

                        await html5QrCode.start({
                                facingMode: 'environment'
                            }, {
                                fps: 10,
                                qrbox: {
                                    width: 250,
                                    height: 250
                                }
                            },
                            async decodedText => {

                                if (
                                    processingQr
                                ) {
                                    return;
                                }

                                processingQr = true;

                                try {

                                    const token =
                                        decodedText.trim();

                                    tokenInput.value =
                                        token;

                                    await stopQrCamera();

                                    await getUserInfo(
                                        token
                                    );

                                } finally {

                                    processingQr = false;
                                }
                            }
                        );

                        cameraRunning = true;

                        stopCameraButton.classList.remove(
                            'd-none'
                        );

                        setCameraStatus(
                            'QRコードを読み取ってください。',
                            'success'
                        );

                    } catch (error) {

                        console.error(error);

                        startCameraButton.disabled =
                            false;

                        setCameraStatus(
                            'カメラを起動できませんでした。',
                            'danger'
                        );
                    }
                }
            );

            stopCameraButton.addEventListener(
                'click',
                async () => {

                    await stopQrCamera();

                    setCameraStatus(
                        'カメラを停止しました。',
                        'secondary'
                    );
                }
            );

            scoreForm.addEventListener(
                'submit',
                async event => {

                    event.preventDefault();

                    if (
                        registeringScore
                    ) {
                        return;
                    }

                    if (
                        !currentToken
                    ) {

                        showMessage(
                            '先にユーザー情報を取得してください。',
                            'warning'
                        );

                        return;
                    }

                    const score =
                        Number(
                            scoreInput.value
                        );

                    if (
                        !Number.isSafeInteger(
                            score
                        ) ||
                        score <= 0
                    ) {

                        showMessage(
                            'ポイントは1以上の整数で入力してください。',
                            'warning'
                        );

                        return;
                    }

                    registeringScore = true;

                    registerButton.disabled =
                        true;

                    try {

                        const result =
                            await callApi(
                                SCORE_ADD_API, {
                                    token: currentToken,
                                    store_id: Number(
                                        storeSelect.value
                                    ),
                                    score
                                }
                            );

                        scoreInput.value = '';

                        showMessage(
                            result.message ||
                            '得点を登録しました。',
                            'success'
                        );

                        await getUserInfo(
                            currentToken
                        );

                    } catch (error) {

                        showMessage(
                            error.message ||
                            '得点登録に失敗しました。',
                            'danger'
                        );

                    } finally {

                        registeringScore =
                            false;

                        if (
                            currentUser
                        ) {

                            registerButton.disabled = !currentUser.canAddScore;
                        }
                    }
                }
            );

            storeSelect.addEventListener(
                'change',
                async () => {

                    await stopQrCamera();

                    hideUser();

                    tokenInput.value = '';
                    scoreInput.value = '';

                    hideMessage();

                    setCameraStatus(
                        'カメラを起動してQRコードを読み取ってください。',
                        'secondary'
                    );
                }
            );

            window.addEventListener(
                'pagehide',
                () => {

                    if (
                        html5QrCode &&
                        cameraRunning
                    ) {

                        html5QrCode
                            .stop()
                            .catch(error => {
                                console.error(error);
                            });
                    }
                }
            );

            (async () => {

                try {

                    await waitForCsrfToken();

                    await loadStores();

                    setCameraStatus(
                        'カメラを起動してQRコードを読み取ってください。',
                        'secondary'
                    );

                } catch (error) {

                    showMessage(
                        error.message ||
                        '初期化に失敗しました。',
                        'danger'
                    );
                }

            })();

        })();
    </script>
</body>

</html>