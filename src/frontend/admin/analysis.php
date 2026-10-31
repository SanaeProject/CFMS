<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="author" content="乙成隼士,松本浩成,永島愛貴,塩川海響,星川誠弥,中島悠翔">
    <meta name="keywords" content="nkc,名古屋工学院専門学校,文化祭">

    <link rel="icon" href="/images/CFMS.ico" sizes="any">
    <link rel="icon" href="/images/CFMS.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/images/CFMS.png">

    <meta property="og:title" content="集計分析ページ | CFMS">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nkc.sanae.dev">
    <meta property="og:image" content="/images/CFMS.png">
    <meta property="og:image:alt" content="CFMSロゴ">
    <meta property="og:description" content="CFMSのユーザー数や店舗別利用状況を確認する集計分析ページです。">

    <title>集計分析ページ | CFMS</title>
    <meta name="description" content="CFMSのユーザー数、3日間の利用者数、店舗別利用者数を集計・分析します。">

    <!-- CommentAnchor: Bootstrapの読み込み -->
    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

    <input type="hidden" class="csrf_token" value="">
</head>

<body class="bg-light">

    <main class="container py-4">

        <div class="card shadow-sm">

            <div class="card-header bg-primary text-white">
                <h1 class="h4 mb-1">
                    利用状況集計
                </h1>

                <p class="mb-0 small">
                    管理者専用｜システム利用状況の確認
                </p>
            </div>

            <div class="card-body">

                <!-- 結果メッセージ -->
                <div
                    id="result"
                    class="mb-3"
                    role="status"
                    aria-live="polite">
                </div>

                <!-- 再読込 -->
                <div class="d-flex justify-content-end mb-4">
                    <button
                        type="button"
                        id="reloadButton"
                        class="btn btn-outline-primary">
                        再読み込み
                    </button>
                </div>

                <!-- 集計サマリー -->
                <section class="mb-5">

                    <h2 class="h5 mb-3">
                        ユーザー集計
                    </h2>

                    <div class="row g-3">

                        <div class="col-md-4">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <div class="text-secondary small">
                                        総ユーザー数
                                    </div>

                                    <div
                                        id="totalUsers"
                                        class="fs-2 fw-bold">
                                        -
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card text-center h-100 border-success">
                                <div class="card-body">
                                    <div class="text-secondary small">
                                        アクティブユーザー
                                    </div>

                                    <div
                                        id="activeUsers"
                                        class="fs-2 fw-bold text-success">
                                        -
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card text-center h-100 border-danger">
                                <div class="card-body">
                                    <div class="text-secondary small">
                                        終了済みユーザー
                                    </div>

                                    <div
                                        id="finishedUsers"
                                        class="fs-2 fw-bold text-danger">
                                        -
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </section>

                <!-- 利用ランキング -->
                <section class="mb-5">

                    <h2 class="h5 mb-3">
                        利用者ランキング
                    </h2>

                    <div class="table-responsive">

                        <table class="table table-striped table-bordered align-middle">

                            <thead class="table-light">
                                <tr>
                                    <th>順位</th>
                                    <th>ユーザーID</th>
                                    <th>利用回数</th>
                                </tr>
                            </thead>

                            <tbody id="recentUserRanking">
                                <tr>
                                    <td colspan="3" class="text-center text-secondary">
                                        読み込み中...
                                    </td>
                                </tr>
                            </tbody>

                        </table>

                    </div>

                </section>

                <!-- 店舗別利用者数ランキング -->
                <section>

                    <h2 class="h5 mb-3">
                        店舗別利用者数ランキング
                    </h2>

                    <div class="table-responsive">

                        <table class="table table-striped table-bordered align-middle">

                            <thead class="table-light">
                                <tr>
                                    <th>順位</th>
                                    <th>店舗ID</th>
                                    <th>店舗名</th>
                                    <th>利用者数</th>
                                </tr>
                            </thead>

                            <tbody id="storeUserRanking">
                                <tr>
                                    <td colspan="4" class="text-center text-secondary">
                                        読み込み中...
                                    </td>
                                </tr>
                            </tbody>

                        </table>

                    </div>

                </section>

            </div>

            <div class="card-footer text-secondary small">
                StatisticsPage
            </div>

        </div>

    </main>

    <script>
        'use strict';

        // =====================================
        // DOM
        // =====================================

        const resultDiv =
            document.getElementById(
                'result'
            );

        const reloadButton =
            document.getElementById(
                'reloadButton'
            );

        const totalUsersElement =
            document.getElementById(
                'totalUsers'
            );

        const activeUsersElement =
            document.getElementById(
                'activeUsers'
            );

        const finishedUsersElement =
            document.getElementById(
                'finishedUsers'
            );

        const recentUserRankingBody =
            document.getElementById(
                'recentUserRanking'
            );

        const storeUserRankingBody =
            document.getElementById(
                'storeUserRanking'
            );

        // =====================================
        // API
        // =====================================

        const API_BASE_URL =
            '<?= getenv('API_URL') ?>';

        const STATISTICS_API =
            `${API_BASE_URL}/admin/statistics.php`;

        // =====================================
        // 状態管理
        // =====================================

        let loading = false;

        // =====================================
        // メッセージ表示
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

        // =====================================
        // 集計取得
        // =====================================

        async function loadStatistics() {

            if (loading) {
                return;
            }

            loading = true;

            reloadButton.disabled =
                true;

            try {

                const data =
                    await callApi(
                        STATISTICS_API
                    );

                totalUsersElement.textContent =
                    Number(
                        data.totalUsers ?? 0
                    ).toLocaleString(
                        'ja-JP'
                    );

                activeUsersElement.textContent =
                    Number(
                        data.activeUsers ?? 0
                    ).toLocaleString(
                        'ja-JP'
                    );

                finishedUsersElement.textContent =
                    Number(
                        data.finishedUsers ?? 0
                    ).toLocaleString(
                        'ja-JP'
                    );

                renderRecentUserRanking(
                    data.recentUserRanking || []
                );

                renderStoreUserRanking(
                    data.storeUserRanking || []
                );

                clearResult();

            } catch (error) {

                showResult(
                    error.message ||
                    '集計取得に失敗しました。',
                    'danger'
                );

            } finally {

                loading = false;

                reloadButton.disabled =
                    false;
            }
        }
        // =====================================
        // 利用者ランキング描画
        // =====================================

        function renderRecentUserRanking(
            ranking
        ) {

            recentUserRankingBody.replaceChildren();

            if (
                !Array.isArray(ranking) ||
                ranking.length === 0
            ) {

                const row =
                    document.createElement(
                        'tr'
                    );

                const cell =
                    document.createElement(
                        'td'
                    );

                cell.colSpan = 3;

                cell.className =
                    'text-center text-secondary';

                cell.textContent =
                    'データがありません';

                row.appendChild(cell);

                recentUserRankingBody.appendChild(
                    row
                );

                return;
            }

            ranking.forEach(
                (
                    user,
                    index
                ) => {

                    const row =
                        document.createElement(
                            'tr'
                        );

                    const rankCell =
                        document.createElement(
                            'td'
                        );

                    rankCell.textContent =
                        index + 1;

                    const userCell =
                        document.createElement(
                            'td'
                        );

                    userCell.textContent =
                        user.userId ?? '-';

                    const countCell =
                        document.createElement(
                            'td'
                        );

                    countCell.textContent =
                        Number(
                            user.useCount ?? 0
                        ).toLocaleString(
                            'ja-JP'
                        );

                    row.append(
                        rankCell,
                        userCell,
                        countCell
                    );

                    recentUserRankingBody.appendChild(
                        row
                    );
                }
            );
        }

        // =====================================
        // 店舗別ランキング描画
        // =====================================

        function renderStoreUserRanking(
            ranking
        ) {

            storeUserRankingBody.replaceChildren();

            if (
                !Array.isArray(ranking) ||
                ranking.length === 0
            ) {

                const row =
                    document.createElement(
                        'tr'
                    );

                const cell =
                    document.createElement(
                        'td'
                    );

                cell.colSpan = 4;

                cell.className =
                    'text-center text-secondary';

                cell.textContent =
                    'データがありません';

                row.appendChild(cell);

                storeUserRankingBody.appendChild(
                    row
                );

                return;
            }

            ranking.forEach(
                (
                    store,
                    index
                ) => {

                    const row =
                        document.createElement(
                            'tr'
                        );

                    const rankCell =
                        document.createElement(
                            'td'
                        );

                    rankCell.textContent =
                        index + 1;

                    const storeIdCell =
                        document.createElement(
                            'td'
                        );

                    storeIdCell.textContent =
                        store.storeId ?? '-';

                    const storeNameCell =
                        document.createElement(
                            'td'
                        );

                    storeNameCell.textContent =
                        store.storeName ?? '-';

                    const userCountCell =
                        document.createElement(
                            'td'
                        );

                    userCountCell.textContent =
                        Number(
                            store.userCount ?? 0
                        ).toLocaleString(
                            'ja-JP'
                        );

                    row.append(
                        rankCell,
                        storeIdCell,
                        storeNameCell,
                        userCountCell
                    );

                    storeUserRankingBody.appendChild(
                        row
                    );
                }
            );
        }

        // =====================================
        // 再読み込み
        // =====================================

        reloadButton.addEventListener(
            'click',
            async function() {

                await loadStatistics();
            }
        );

        // =====================================
        // 初期化
        // =====================================

        async function initialize() {

            try {

                showResult(
                    '集計データを取得しています...',
                    'info'
                );

                await waitForCsrfToken();

                await loadStatistics();

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

    <!-- CommentAnchor: 共通フッター -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

    <!-- CommentAnchor: CSRFトークン読み込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>

</body>

</html>