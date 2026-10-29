
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
</head>

<body class="bg-light">

    <main>
        <div class="container py-4">

            <!-- CommentAnchor: ページタイトルと操作ボタン -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <p class="text-primary fw-bold mb-1">CFMS / ANALYSIS</p>
                    <h1 class="h2 fw-bold mb-2">集計分析ページ</h1>
                    <p class="text-secondary mb-0">
                        ユーザー数や店舗別の利用状況を確認できます。
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="button" id="refreshButton" class="btn btn-outline-primary">
                        集計データを更新
                    </button>

                    <a href="/staff/staff_main.php" class="btn btn-outline-secondary">
                        管理メニューへ戻る
                    </a>
                </div>
            </div>

            <!-- CommentAnchor: 通知メッセージ -->
            <div id="statusMessage"
                 class="alert d-none"
                 role="status"
                 aria-live="polite"></div>

            <!-- CommentAnchor: ユーザー集計 -->
            <section class="mb-4" aria-labelledby="userStatisticsTitle">
                <h2 id="userStatisticsTitle" class="h4 fw-bold mb-3">
                    ユーザー集計
                </h2>

                <div class="row g-3">

                    <div class="col-12 col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <p class="text-secondary mb-2">総ユーザー数</p>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span id="totalUsers" class="h2 fw-bold text-primary mb-0">データがありません</span>
                                    <span class="text-secondary">人</span>
                                </div>
                                <p class="small text-secondary mb-0 mt-2">
                                    登録されているユーザーの合計
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <p class="text-secondary mb-2">アクティベート済みユーザー数</p>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span id="activeUsers" class="h2 fw-bold text-success mb-0">データがありません</span>
                                    <span class="text-secondary">人</span>
                                </div>
                                <p class="small text-secondary mb-0 mt-2">
                                    利用可能なユーザー
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <p class="text-secondary mb-2">終了済みユーザー数</p>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span id="finishedUsers" class="h2 fw-bold text-secondary mb-0">データがありません</span>
                                    <span class="text-secondary">人</span>
                                </div>
                                <p class="small text-secondary mb-0 mt-2">
                                    利用終了処理済みのユーザー
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- CommentAnchor: 3日間の利用者ランキング -->
            <section class="card border-0 shadow-sm mb-4" aria-labelledby="recentUserRankingTitle">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                        <div>
                            <h2 id="recentUserRankingTitle" class="h4 fw-bold mb-1">
                                3日間の利用者数ランキング
                            </h2>
                            <p class="text-secondary small mb-0">
                                3日間に利用したユーザーを、利用回数の多い順に表示します。
                            </p>
                        </div>
                        <span class="badge text-bg-primary align-self-start">LAST 3 DAYS</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">順位</th>
                                    <th scope="col">利用者名</th>
                                    <th scope="col" class="text-end">利用回数</th>
                                </tr>
                            </thead>
                            <tbody id="recentUserRankingBody">
                                <tr>
                                    <td colspan="3" class="text-center text-secondary py-4">
                                        データがありません
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- CommentAnchor: 店舗別利用者数ランキング -->
            <section class="card border-0 shadow-sm mb-4" aria-labelledby="storeUserRankingTitle">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                        <div>
                            <h2 id="storeUserRankingTitle" class="h4 fw-bold mb-1">
                                店舗別利用者数ランキング
                            </h2>
                            <p class="text-secondary small mb-0">
                                店舗を利用したユーザー数が多い順に表示します。
                            </p>
                        </div>
                        <span class="badge text-bg-success align-self-start">STORE USERS</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">順位</th>
                                    <th scope="col">店舗名</th>
                                    <th scope="col" class="text-end">利用者数</th>
                                </tr>
                            </thead>
                            <tbody id="storeUserRankingBody">
                                <tr>
                                    <td colspan="3" class="text-center text-secondary py-4">
                                        データがありません
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- CommentAnchor: CSV出力 -->
            <section class="card border-0 shadow-sm mb-4" aria-labelledby="csvExportTitle">
                <div class="card-body p-4">
                    <h2 id="csvExportTitle" class="h4 fw-bold mb-2">CSV出力</h2>
                    <p class="text-secondary">
                        集計データをCSVファイルとしてダウンロードできます。
                    </p>

                    <form id="exportForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="exportType" class="form-label">出力するデータ</label>
                                <select id="exportType" name="type" class="form-select" required>
                                    <option value="user_history">ユーザー履歴</option>
                                    <option value="store_statistics">店舗別集計</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <button type="submit" id="exportButton" class="btn btn-success w-100">
                                    CSVをダウンロード
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

            <!-- CommentAnchor: 最終取得日時 -->
            <p class="small text-secondary text-end mb-0">
                最終取得日時：
                <span id="lastUpdated">未取得</span>
            </p>

        </div>
    </main>

    <script>
        // CommentAnchor: APIの接続先
        const STATISTICS_API_URL = '/api/admin/statistics';
        const EXPORT_API_URL = '/api/admin/export';

        // CommentAnchor: HTML要素を取得
        const statusMessage = document.getElementById('statusMessage');
        const refreshButton = document.getElementById('refreshButton');
        const exportForm = document.getElementById('exportForm');
        const exportButton = document.getElementById('exportButton');

        // CommentAnchor: 通知メッセージを表示
        function showStatus(message, type) {
            if (!statusMessage) {
                console.error('HTMLに statusMessage がありません。');
                return;
            }

            statusMessage.className = `alert alert-${type}`;
            statusMessage.textContent = message;
        }

        // CommentAnchor: 通知メッセージを非表示
        function hideStatus() {
            if (!statusMessage) {
                console.error('HTMLに statusMessage がありません。');
                return;
            }

            statusMessage.className = 'alert d-none';
            statusMessage.textContent = '';
        }

        // CommentAnchor: 数値を表示
        function displayCount(elementId, value) {
            const element = document.getElementById(elementId);

            if (!element) {
                console.error(`HTMLに ${elementId} がありません。`);
                return;
            }

            if (value === null || value === undefined || value === '') {
                element.textContent = 'データがありません';
                return;
            }

            const count = Number(value);

            if (!Number.isFinite(count)) {
                element.textContent = 'データがありません';
                return;
            }

            element.textContent = count.toLocaleString('ja-JP');
        }

        // CommentAnchor: ランキング表を表示
        function renderRanking(tbodyId, ranking, nameKey, valueKey, valueLabel) {
            const tbody = document.getElementById(tbodyId);

            if (!tbody) {
                console.error(`HTMLに ${tbodyId} がありません。`);
                return;
            }

            tbody.replaceChildren();

            if (!Array.isArray(ranking) || ranking.length === 0) {
                const row = document.createElement('tr');
                const cell = document.createElement('td');

                cell.colSpan = 3;
                cell.className = 'text-center text-secondary py-4';
                cell.textContent = 'データがありません';

                row.appendChild(cell);
                tbody.appendChild(row);
                return;
            }

            ranking.forEach((item, index) => {
                const row = document.createElement('tr');

                const rankCell = document.createElement('th');
                rankCell.scope = 'row';
                rankCell.textContent = `${index + 1}位`;

                const nameCell = document.createElement('td');
                const name = item[nameKey];

                nameCell.textContent =
                    name === null || name === undefined || name === ''
                        ? 'データがありません'
                        : String(name);

                const valueCell = document.createElement('td');
                valueCell.className = 'text-end fw-bold';

                const value = item[valueKey];
                const numericValue = Number(value);

                if (
                    value === null ||
                    value === undefined ||
                    value === '' ||
                    !Number.isFinite(numericValue)
                ) {
                    valueCell.textContent = 'データがありません';
                } else {
                    valueCell.textContent =
                        `${numericValue.toLocaleString('ja-JP')}${valueLabel}`;
                }

                row.append(rankCell, nameCell, valueCell);
                tbody.appendChild(row);
            });
        }

        // CommentAnchor: JSON形式のAPIレスポンスを取得
        async function fetchJson(url) {
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const responseText = await response.text();
            let data;

            try {
                data = JSON.parse(responseText);
            } catch (error) {
                console.error('JSON以外のレスポンス:', responseText);
                throw new Error('APIからJSONデータを受信できませんでした。');
            }

            if (!response.ok || data.success !== true) {
                throw new Error(data.message || 'データを取得できませんでした。');
            }

            return data;
        }

        // CommentAnchor: 集計データを取得
        async function loadStatistics() {
            hideStatus();

            if (refreshButton) {
                refreshButton.disabled = true;
                refreshButton.textContent = '読み込み中...';
            }

            // CommentAnchor: 取得開始時はデータなしで初期化
            displayCount('totalUsers', null);
            displayCount('activeUsers', null);
            displayCount('finishedUsers', null);

            renderRanking('recentUserRankingBody', [], 'userName', 'useCount', '回');
            renderRanking('storeUserRankingBody', [], 'storeName', 'userCount', '人');

            const lastUpdated = document.getElementById('lastUpdated');

            if (lastUpdated) {
                lastUpdated.textContent = '未取得';
            }

            try {
                const data = await fetchJson(STATISTICS_API_URL);

                displayCount('totalUsers', data.totalUsers);
                displayCount('activeUsers', data.activeUsers);
                displayCount('finishedUsers', data.finishedUsers);

                // CommentAnchor: 直近3日間の利用者ランキング
                const recentUserRanking = Array.isArray(data.recentUserRanking)
                    ? [...data.recentUserRanking]
                    : [];

                recentUserRanking.sort(
                    (a, b) => Number(b.useCount ?? 0) - Number(a.useCount ?? 0)
                );

                renderRanking(
                    'recentUserRankingBody',
                    recentUserRanking,
                    'userName',
                    'useCount',
                    '回'
                );

                // CommentAnchor: 店舗別利用者数ランキング
                const storeUserRanking = Array.isArray(data.storeUserRanking)
                    ? [...data.storeUserRanking]
                    : [];

                storeUserRanking.sort(
                    (a, b) => Number(b.userCount ?? 0) - Number(a.userCount ?? 0)
                );

                renderRanking(
                    'storeUserRankingBody',
                    storeUserRanking,
                    'storeName',
                    'userCount',
                    '人'
                );

                if (lastUpdated) {
                    lastUpdated.textContent = new Date().toLocaleString('ja-JP');
                }

                showStatus('集計データを取得しました。', 'success');

            } catch (error) {
                console.error('集計データ取得エラー:', error);

                displayCount('totalUsers', null);
                displayCount('activeUsers', null);
                displayCount('finishedUsers', null);

                renderRanking('recentUserRankingBody', [], 'userName', 'useCount', '回');
                renderRanking('storeUserRankingBody', [], 'storeName', 'userCount', '人');

                if (lastUpdated) {
                    lastUpdated.textContent = '未取得';
                }

                showStatus('データがありません', 'warning');

            } finally {
                if (refreshButton) {
                    refreshButton.disabled = false;
                    refreshButton.textContent = '集計データを更新';
                }
            }
        }

        // CommentAnchor: 集計データ更新ボタン
        if (refreshButton) {
            refreshButton.addEventListener('click', loadStatistics);
        }

        // CommentAnchor: CSVファイルのダウンロード
        if (exportForm) {
            exportForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                hideStatus();

                if (exportButton) {
                    exportButton.disabled = true;
                    exportButton.textContent = '出力中...';
                }

                let downloadUrl = null;

                try {
                    const exportType = document.getElementById('exportType').value;
                    const url = `${EXPORT_API_URL}?type=${encodeURIComponent(exportType)}`;

                    const response = await fetch(url, {
                        method: 'GET',
                        credentials: 'include',
                        headers: {
                            'Accept': 'text/csv'
                        }
                    });

                    if (!response.ok) {
                        let message = 'データがありません';

                        try {
                            const errorData = await response.json();
                            message = errorData.message || message;
                        } catch (error) {
                            // CommentAnchor: JSONでないエラー応答は既定のメッセージを使う
                        }

                        throw new Error(message);
                    }

                    const blob = await response.blob();

                    if (blob.size === 0) {
                        throw new Error('データがありません');
                    }

                    downloadUrl = URL.createObjectURL(blob);

                    const link = document.createElement('a');
                    link.href = downloadUrl;
                    link.download =
                        `${exportType}_${new Date().toISOString().slice(0, 10)}.csv`;

                    document.body.appendChild(link);
                    link.click();
                    link.remove();

                    showStatus('CSVファイルをダウンロードしました。', 'success');

                } catch (error) {
                    console.error('CSV出力エラー:', error);
                    showStatus('データがありません', 'warning');

                } finally {
                    if (downloadUrl !== null) {
                        URL.revokeObjectURL(downloadUrl);
                    }

                    if (exportButton) {
                        exportButton.disabled = false;
                        exportButton.textContent = 'CSVをダウンロード';
                    }
                }
            });
        }

        // CommentAnchor: ページ読み込み時に集計データを取得
        loadStatistics();
    </script>

    <!-- CommentAnchor: 共通フッター -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

    <!-- CommentAnchor: CSRFトークン読み込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>

</body>
</html>
