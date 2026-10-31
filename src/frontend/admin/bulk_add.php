<!DOCTYPE html>
<html lang="ja">

<head>
    <!-- 基本設定 -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="乙成隼士,松本浩成,永島愛貴,塩川海響,星川誠弥,中島悠翔">

    <!-- ファビコン -->
    <link rel="icon" href="/images/CFMS.ico" sizes="any">
    <link rel="icon" href="/images/CFMS.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/images/CFMS.png">

    <!-- OGPタグ -->
    <meta property="og:title" content="一括得点登録・ユーザー作成ページ">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nkc.sanae.dev">
    <meta property="og:image" content="/images/CFMS.png">
    <meta property="og:image:alt" content="CFMS">
    <meta property="og:description" content="管理者がユーザー作成と特例の得点登録を行うページ">

    <!-- タイトル・説明 -->
    <title>一括得点登録・ユーザー作成ページ</title>
    <meta name="description" content="管理者用の一括得点登録・ユーザー作成ページ">
    <meta name="keywords" content="nkc,名古屋工学院専門学校,文化祭">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light">
    <main class="container py-4">

        <div class="card shadow-sm">

            <div class="card-header bg-primary text-white">
                <h1 class="h4 mb-1">
                    一括得点登録・ユーザー作成
                </h1>

                <p class="mb-0 small">
                    管理者専用｜紙媒体運用向け特例処理
                </p>
            </div>

            <div class="card-body">

                <!-- メッセージ -->
                <div
                    id="result"
                    class="mb-3"
                    role="status"
                    aria-live="polite">
                </div>

                <form id="bulkAddForm">

                    <!-- CSRF -->
                    <input
                        type="hidden"
                        class="csrf_token"
                        name="csrf_token"
                        value="">

                    <!-- 店舗・ポイント一覧 -->
                    <div class="mb-3">

                        <div class="d-flex justify-content-between align-items-center mb-2">

                            <label class="form-label mb-0">
                                店舗ごとの得点
                            </label>

                            <button
                                type="button"
                                id="addStoreButton"
                                class="btn btn-sm btn-outline-primary">
                                店舗を追加
                            </button>

                        </div>

                        <div id="scoreRows">
                            <!-- JavaScriptで追加 -->
                        </div>

                        <div class="form-text">
                            店舗ごとにポイントを入力してください。
                        </div>

                    </div>

                    <div class="d-grid">
                        <button
                            type="submit"
                            id="bulkAddButton"
                            class="btn btn-primary">
                            ユーザー作成＋得点登録
                        </button>
                    </div>

                </form>

                <hr>

                <!-- 作成結果 -->
                <section
                    id="createdUserSection"
                    class="d-none">

                    <h2 class="h5 mb-3">
                        作成結果
                    </h2>

                    <div class="card bg-light">

                        <div class="card-body">

                            <div class="mb-2 text-secondary">
                                発行されたユーザートークン
                            </div>

                            <div
                                id="createdToken"
                                class="fs-4 fw-bold text-break">
                                -
                            </div>

                            <div class="small text-secondary mt-2">
                                このトークンを対象ユーザーへ渡してください。
                            </div>

                        </div>

                    </div>

                </section>

            </div>

            <div class="card-footer text-secondary small">
                BulkScoreRegistrationPage
            </div>

        </div>

    </main>

    <!-- フッター -->
    <?php
    include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php';
    ?>

    <!-- CSRFトークンの自動埋め込み -->
    <?php
    include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php';
    ?>

    <!-- JavaScript -->
    <script>
        'use strict';

        // =====================================
        // DOM
        // =====================================

        const bulkAddForm =
            document.getElementById(
                'bulkAddForm'
            );

        const scoreRows =
            document.getElementById(
                'scoreRows'
            );

        const addStoreButton =
            document.getElementById(
                'addStoreButton'
            );

        const bulkAddButton =
            document.getElementById(
                'bulkAddButton'
            );

        const resultDiv =
            document.getElementById(
                'result'
            );

        const createdUserSection =
            document.getElementById(
                'createdUserSection'
            );

        const createdToken =
            document.getElementById(
                'createdToken'
            );

        // =====================================
        // API
        // =====================================

        const API_BASE_URL =
            '<?= getenv('API_URL') ?>';

        const STORES_API =
            `${API_BASE_URL}/admin/stores.php`;

        const BULK_ADD_API =
            `${API_BASE_URL}/admin/bulk_add.php`;

        // =====================================
        // 状態
        // =====================================

        let stores = [];
        let isSubmitting = false;

        // =====================================
        // メッセージ
        // =====================================

        function showResult(
            message,
            type = 'info'
        ) {

            resultDiv.replaceChildren();

            const alert =
                document.createElement(
                    'div'
                );

            alert.className =
                `alert alert-${type}`;

            alert.textContent =
                message;

            resultDiv.appendChild(
                alert
            );
        }

        function clearResult() {
            resultDiv.replaceChildren();
        }

        // =====================================
        // CSRF
        // =====================================

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
                'CSRFトークンを取得できませんでした。'
            );
        }

        // =====================================
        // API通信
        // =====================================

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

            let data;

            try {

                data =
                    await response.json();

            } catch {

                throw new Error(
                    'サーバーから正しいJSONが返却されませんでした。'
                );
            }

            if (!response.ok) {
                throw new Error(
                    data.message ||
                    '通信エラーが発生しました。'
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

        // =====================================
        // 店舗一覧取得
        // =====================================

        async function loadStores() {

            const data =
                await callApi(
                    STORES_API
                );

            stores =
                Array.isArray(
                    data.stores
                ) ?
                data.stores : [];
        }

        // =====================================
        // 店舗セレクト生成
        // =====================================

        function createStoreSelect() {

            const select =
                document.createElement(
                    'select'
                );

            select.className =
                'form-select store-select';

            const defaultOption =
                document.createElement(
                    'option'
                );

            defaultOption.value = '';

            defaultOption.textContent =
                '店舗を選択してください';

            select.appendChild(
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

                select.appendChild(
                    option
                );
            });

            return select;
        }

        // =====================================
        // 登録行追加
        // =====================================

        function addScoreRow() {

            const row =
                document.createElement(
                    'div'
                );

            row.className =
                'score-row row g-2 mb-2';

            const storeCol =
                document.createElement(
                    'div'
                );

            storeCol.className =
                'col-md-6';

            const scoreCol =
                document.createElement(
                    'div'
                );

            scoreCol.className =
                'col-md-4';

            const removeCol =
                document.createElement(
                    'div'
                );

            removeCol.className =
                'col-md-2';

            const select =
                createStoreSelect();

            const scoreInput =
                document.createElement(
                    'input'
                );

            scoreInput.type = 'number';

            scoreInput.className =
                'form-control score-input';

            scoreInput.min = '1';

            scoreInput.placeholder =
                'ポイント';

            const removeButton =
                document.createElement(
                    'button'
                );

            removeButton.type =
                'button';

            removeButton.className =
                'btn btn-outline-danger w-100';

            removeButton.textContent =
                '削除';

            removeButton.addEventListener(
                'click',
                () => {

                    row.remove();

                    if (
                        scoreRows.children.length === 0
                    ) {
                        addScoreRow();
                    }
                }
            );

            storeCol.appendChild(
                select
            );

            scoreCol.appendChild(
                scoreInput
            );

            removeCol.appendChild(
                removeButton
            );

            row.append(
                storeCol,
                scoreCol,
                removeCol
            );

            scoreRows.appendChild(
                row
            );
        }
        // =====================================
        // 店舗追加ボタン
        // =====================================

        addStoreButton.addEventListener(
            'click',
            function() {

                addScoreRow();
            }
        );

        // =====================================
        // 送信データ生成
        // =====================================

        function buildRequestStores() {

            const rows =
                document.querySelectorAll(
                    '.score-row'
                );

            const result = [];

            rows.forEach(row => {

                const storeId =
                    Number(
                        row.querySelector(
                            '.store-select'
                        ).value
                    );

                const score =
                    Number(
                        row.querySelector(
                            '.score-input'
                        ).value
                    );

                if (
                    Number.isInteger(storeId) &&
                    storeId > 0 &&
                    Number.isSafeInteger(score) &&
                    score > 0
                ) {

                    result.push({
                        storeId,
                        score
                    });
                }
            });

            return result;
        }

        // =====================================
        // 一括登録
        // =====================================

        bulkAddForm.addEventListener(
            'submit',
            async function(event) {

                event.preventDefault();

                clearResult();

                if (isSubmitting) {
                    return;
                }

                const requestStores =
                    buildRequestStores();

                if (
                    requestStores.length === 0
                ) {

                    showResult(
                        '店舗とポイントを入力してください。',
                        'warning'
                    );

                    return;
                }

                isSubmitting = true;

                bulkAddButton.disabled =
                    true;

                bulkAddButton.textContent =
                    '登録中...';

                createdUserSection.classList.add(
                    'd-none'
                );

                try {

                    const data =
                        await callApi(
                            BULK_ADD_API, {
                                stores: requestStores
                            }
                        );

                    createdToken.textContent =
                        data.token;

                    createdUserSection.classList.remove(
                        'd-none'
                    );

                    showResult(
                        data.message ||
                        'ユーザーを作成し得点を登録しました。',
                        'success'
                    );

                    scoreRows.replaceChildren();

                    addScoreRow();

                } catch (error) {

                    showResult(
                        error.message ||
                        '登録に失敗しました。',
                        'danger'
                    );

                } finally {

                    isSubmitting = false;

                    bulkAddButton.disabled =
                        false;

                    bulkAddButton.textContent =
                        'ユーザー作成＋得点登録';
                }
            }
        );

        // =====================================
        // 初期化
        // =====================================

        async function initialize() {

            try {

                showResult(
                    '初期化中...',
                    'info'
                );

                await waitForCsrfToken();

                await loadStores();

                addScoreRow();

                clearResult();

            } catch (error) {

                showResult(
                    error.message ||
                    '初期化に失敗しました。',
                    'danger'
                );
            }
        }

        // =====================================
        // 開始
        // =====================================

        initialize();
    </script>

</body>

</html>