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
    <meta name="description" content="CFMSのユーザー数、利用履歴、店舗別利用状況を集計・分析します。">

    <!-- CommentAnchor: Bootstrapの読み込み -->
    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

    <!-- CommentAnchor: Excel出力ライブラリ -->
    <script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>

    <!-- CommentAnchor: グラフ出力ライブラリ -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

                <!-- 再読み込み・Excelダウンロード -->
                <div class="d-flex justify-content-end gap-2 mb-4 flex-wrap">

                    <button
                        type="button"
                        id="downloadExcelButton"
                        class="btn btn-primary">
                        Excelダウンロード
                    </button>

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

                <!-- 利用者ランキング -->
                <section class="mb-5">

                    <h2 class="h5 mb-3">
                        利用者得点ランキング
                    </h2>

                    <div class="table-responsive">

                        <table class="table table-striped table-bordered align-middle">

                            <thead class="table-light">
                                <tr>
                                    <th>順位</th>
                                    <th>ユーザートークン</th>
                                    <th>得点</th>
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
            document.getElementById('result');

        const reloadButton =
            document.getElementById('reloadButton');

        const downloadExcelButton =
            document.getElementById('downloadExcelButton');

        const totalUsersElement =
            document.getElementById('totalUsers');

        const activeUsersElement =
            document.getElementById('activeUsers');

        const finishedUsersElement =
            document.getElementById('finishedUsers');

        const recentUserRankingBody =
            document.getElementById('recentUserRanking');

        const storeUserRankingBody =
            document.getElementById('storeUserRanking');

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

        // 最新の集計データ
        let statisticsData = null;

        // =====================================
        // メッセージ表示
        // =====================================

        function showResult(
            message,
            type = 'info'
        ) {

            resultDiv.replaceChildren();

            const alert =
                document.createElement('div');

            alert.className =
                `alert alert-${type}`;

            alert.textContent =
                message;

            resultDiv.appendChild(alert);
        }

        function clearResult() {

            resultDiv.replaceChildren();
        }

        // =====================================
        // CSRF
        // =====================================

        function getCsrfToken() {

            const csrfInput =
                document.querySelector('.csrf_token');

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
                Date.now() - start < timeoutMs
            ) {

                const token =
                    getCsrfToken();

                if (token) {
                    return token;
                }

                await new Promise(
                    resolve => setTimeout(resolve, 100)
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

            if (data.success !== true) {

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

            reloadButton.disabled = true;

            try {

                const data =
                    await callApi(STATISTICS_API);

                // ダウンロード用に集計データを保存
                statisticsData = data;

                totalUsersElement.textContent =
                    Number(
                        data.totalUsers ?? 0
                    ).toLocaleString('ja-JP');

                activeUsersElement.textContent =
                    Number(
                        data.activeUsers ?? 0
                    ).toLocaleString('ja-JP');

                finishedUsersElement.textContent =
                    Number(
                        data.finishedUsers ?? 0
                    ).toLocaleString('ja-JP');

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

                reloadButton.disabled = false;
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
                    document.createElement('tr');

                const cell =
                    document.createElement('td');

                cell.colSpan = 3;

                cell.className =
                    'text-center text-secondary';

                cell.textContent =
                    'データがありません';

                row.appendChild(cell);

                recentUserRankingBody.appendChild(row);

                return;
            }

            ranking.forEach(
                (user, index) => {

                    const row =
                        document.createElement('tr');

                    const rankCell =
                        document.createElement('td');

                    rankCell.textContent =
                        index + 1;

                    const userCell =
                        document.createElement('td');

                    userCell.textContent =
                        user.userId ?? '-';

                    const countCell =
                        document.createElement('td');

                    countCell.textContent =
                        Number(
                            user.useCount ?? 0
                        ).toLocaleString('ja-JP');

                    row.append(
                        rankCell,
                        userCell,
                        countCell
                    );

                    recentUserRankingBody.appendChild(row);
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
                    document.createElement('tr');

                const cell =
                    document.createElement('td');

                cell.colSpan = 4;

                cell.className =
                    'text-center text-secondary';

                cell.textContent =
                    'データがありません';

                row.appendChild(cell);

                storeUserRankingBody.appendChild(row);

                return;
            }

            ranking.forEach(
                (store, index) => {

                    const row =
                        document.createElement('tr');

                    const rankCell =
                        document.createElement('td');

                    rankCell.textContent =
                        index + 1;

                    const storeIdCell =
                        document.createElement('td');

                    storeIdCell.textContent =
                        store.storeId ?? '-';

                    const storeNameCell =
                        document.createElement('td');

                    storeNameCell.textContent =
                        store.storeName ?? '-';

                    const userCountCell =
                        document.createElement('td');

                    userCountCell.textContent =
                        Number(
                            store.userCount ?? 0
                        ).toLocaleString('ja-JP');

                    row.append(
                        rankCell,
                        storeIdCell,
                        storeNameCell,
                        userCountCell
                    );

                    storeUserRankingBody.appendChild(row);
                }
            );
        }

        // =====================================
        // 日付を取得
        // =====================================

        function getDateString() {

            const now =
                new Date();

            const year =
                now.getFullYear();

            const month =
                String(
                    now.getMonth() + 1
                ).padStart(2, '0');

            const day =
                String(
                    now.getDate()
                ).padStart(2, '0');

            return `${year}${month}${day}`;
        }

        // =====================================
        // 日時を表示用に変換
        // =====================================

        function formatDateTime(value) {

            if (!value) {
                return '-';
            }

            const date =
                new Date(
                    String(value).replace(' ', 'T')
                );

            if (Number.isNaN(date.getTime())) {
                return String(value);
            }

            const year =
                date.getFullYear();

            const month =
                String(date.getMonth() + 1).padStart(2, '0');

            const day =
                String(date.getDate()).padStart(2, '0');

            const hours =
                String(date.getHours()).padStart(2, '0');

            const minutes =
                String(date.getMinutes()).padStart(2, '0');

            const seconds =
                String(date.getSeconds()).padStart(2, '0');

            return `${year}/${month}/${day} ${hours}:${minutes}:${seconds}`;
        }

        // =====================================
        // 滞在時間を時:分:秒に変換
        // 例：30分 → 0:30:00
        // =====================================

        function formatStayTime(seconds) {

            if (
                seconds === null ||
                seconds === undefined ||
                seconds === ''
            ) {
                return '利用中';
            }

            const totalSeconds =
                Math.max(
                    0,
                    Math.floor(Number(seconds) || 0)
                );

            const hours =
                Math.floor(totalSeconds / 3600);

            const minutes =
                Math.floor(
                    (totalSeconds % 3600) / 60
                );

            const remainingSeconds =
                totalSeconds % 60;

            return [
                String(hours),
                String(minutes).padStart(2, '0'),
                String(remainingSeconds).padStart(2, '0')
            ].join(':');
        }

        // =====================================
        // 利用履歴の日付を取得
        // =====================================

        function getUsageDate(history) {

            const dateValue =
                history.scoreCreatedAt ??
                history.createdAt ??
                history.created_at ??
                null;

            if (!dateValue) {
                return null;
            }

            const date =
                new Date(
                    String(dateValue).replace(' ', 'T')
                );

            if (Number.isNaN(date.getTime())) {
                return null;
            }

            const year =
                date.getFullYear();

            const month =
                String(date.getMonth() + 1).padStart(2, '0');

            const day =
                String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        }

        // =====================================
        // 利用履歴を日付ごとに分類
        // =====================================

        function classifyUsageHistory(history) {

            if (!Array.isArray(history)) {
                return {
                    firstDay: [],
                    secondDay: [],
                    dates: []
                };
            }

            const dates =
                [...new Set(
                    history
                        .map(item => getUsageDate(item))
                        .filter(Boolean)
                )].sort();

            const firstDate =
                dates[0] ?? null;

            const secondDate =
                dates[1] ?? null;

            const firstDay =
                history.filter(
                    item => getUsageDate(item) === firstDate
                );

            const secondDay =
                history.filter(
                    item => getUsageDate(item) === secondDate
                );

            return {
                firstDay,
                secondDay,
                dates
            };
        }

        // =====================================
        // 利用履歴のExcel用データ
        // =====================================

        function createUsageHistoryRows(history) {

            const rows = [
                [
                    '利用日時',
                    'ユーザーID',
                    'ユーザートークン',
                    '店舗ID',
                    '店舗名',
                    '得点',
                    'スタッフ名'
                ]
            ];

            if (!Array.isArray(history)) {
                return rows;
            }

            history.forEach(item => {

                rows.push([
                    formatDateTime(
                        item.scoreCreatedAt ??
                        item.createdAt ??
                        item.created_at
                    ),
                    item.userId ?? '-',
                    item.userToken ?? item.userId ?? '-',
                    item.storeId ?? '-',
                    item.storeName ?? '-',
                    Number(item.score ?? 0),
                    item.staffName ?? '-'
                ]);
            });

            return rows;
        }

        // =====================================
        // 滞在時間のExcel用データ
        // =====================================

        function createStayTimeRows(stayTimes) {

            const rows = [
                [
                    'ユーザーID',
                    'ユーザートークン',
                    '利用開始日時',
                    '利用終了日時',
                    '滞在時間'
                ]
            ];

            if (!Array.isArray(stayTimes)) {
                return rows;
            }

            stayTimes.forEach(item => {

                rows.push([
                    item.userId ?? '-',
                    item.userToken ?? '-',
                    formatDateTime(
                        item.createdAt ??
                        item.userCreatedAt
                    ),
                    item.finishedAt ?
                        formatDateTime(item.finishedAt) :
                        '利用中',
                    formatStayTime(item.staySeconds)
                ]);
            });

            return rows;
        }

        // =====================================
        // 店舗別ランキングのExcel用データ
        // =====================================

        function createStoreRankingRows(ranking) {

            const rows = [
                [
                    '順位',
                    '店舗ID',
                    '店舗名',
                    '利用者数'
                ]
            ];

            if (!Array.isArray(ranking)) {
                return rows;
            }

            ranking.forEach((store, index) => {

                rows.push([
                    index + 1,
                    store.storeId ?? '-',
                    store.storeName ?? '-',
                    Number(store.userCount ?? 0)
                ]);
            });

            return rows;
        }

        // =====================================
        // 利用者ランキングのExcel用データ
        // =====================================

        function createUserRankingRows(ranking) {

            const rows = [
                [
                    '順位',
                    'ユーザートークン',
                    '得点'
                ]
            ];

            if (!Array.isArray(ranking)) {
                return rows;
            }

            ranking.forEach((user, index) => {

                rows.push([
                    index + 1,
                    user.userId ?? '-',
                    Number(user.useCount ?? 0)
                ]);
            });

            return rows;
        }

        // =====================================
        // ユーザー集計のExcel用データ
        // =====================================

        function createUserSummaryRows(data) {

            return [
                ['項目', '人数'],
                [
                    '総ユーザー数',
                    Number(data.totalUsers ?? 0)
                ],
                [
                    'アクティブユーザー',
                    Number(data.activeUsers ?? 0)
                ],
                [
                    '終了済みユーザー',
                    Number(data.finishedUsers ?? 0)
                ]
            ];
        }

        // =====================================
        // Excelワークシートの列幅調整
        // =====================================

        function setWorksheetColumnWidths(
            worksheet,
            rows
        ) {

            if (!Array.isArray(rows) || rows.length === 0) {
                return;
            }

            const columnCount =
                Math.max(
                    ...rows.map(row => row.length)
                );

            for (
                let columnIndex = 0;
                columnIndex < columnCount;
                columnIndex++
            ) {

                const maxLength =
                    Math.max(
                        ...rows.map(
                            row => String(
                                row[columnIndex] ?? ''
                            ).length
                        ),
                        10
                    );

                worksheet.getColumn(
                    columnIndex + 1
                ).width =
                    Math.min(
                        Math.max(maxLength + 4, 12),
                        40
                    );
            }
        }

        // =====================================
        // Excelワークシート作成
        // =====================================

        function addWorksheet(
            workbook,
            sheetName,
            rows
        ) {

            const worksheet =
                workbook.addWorksheet(sheetName);

            worksheet.addRows(rows);

            // ヘッダー行の書式
            const headerRow =
                worksheet.getRow(1);

            headerRow.font = {
                bold: true
            };

            headerRow.alignment = {
                vertical: 'middle',
                horizontal: 'center'
            };

            headerRow.eachCell(cell => {

                cell.fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: {
                        argb: 'FFD9EAF7'
                    }
                };
            });

            // 列幅調整
            setWorksheetColumnWidths(
                worksheet,
                rows
            );

            // フィルター
            if (rows.length > 1) {

                worksheet.autoFilter = {
                    from: {
                        row: 1,
                        column: 1
                    },
                    to: {
                        row: rows.length,
                        column: rows[0].length
                    }
                };
            }

            return worksheet;
        }

        // =====================================
        // 人気グラフを画像として作成
        // =====================================

        async function createPopularityChartImage(
            ranking
        ) {

            if (
                typeof Chart === 'undefined'
            ) {
                throw new Error(
                    'グラフ出力ライブラリを読み込めませんでした。'
                );
            }

            const chartData =
                Array.isArray(ranking) ?
                    ranking :
                    [];

            const canvas =
                document.createElement('canvas');

            canvas.width = 1200;
            canvas.height = 650;

            const labels =
                chartData.map(
                    item => item.storeName ?? '店舗名なし'
                );

            const values =
                chartData.map(
                    item => Number(item.userCount ?? 0)
                );

            const chart =
                new Chart(
                    canvas.getContext('2d'),
                    {
                        type: 'bar',

                        data: {
                            labels,

                            datasets: [
                                {
                                    label: '利用者数',

                                    data: values,

                                    borderWidth: 1
                                }
                            ]
                        },

                        options: {
                            responsive: false,

                            animation: false,

                            plugins: {
                                title: {
                                    display: true,
                                    text: '店舗別利用者数'
                                },

                                legend: {
                                    display: true
                                }
                            },

                            scales: {
                                y: {
                                    beginAtZero: true,

                                    ticks: {
                                        precision: 0
                                    },

                                    title: {
                                        display: true,
                                        text: '利用者数'
                                    }
                                },

                                x: {
                                    title: {
                                        display: true,
                                        text: '店舗名'
                                    }
                                }
                            }
                        }
                    }
                );

            // グラフを描画
            chart.update();

            // Excelに挿入する画像データ
            const imageData =
                canvas.toDataURL('image/png');

            chart.destroy();

            return imageData;
        }

        // =====================================
        // Excelダウンロード
        // =====================================

        async function downloadExcel() {

            if (!statisticsData) {

                showResult(
                    'ダウンロードするデータがありません。',
                    'warning'
                );

                return;
            }

            if (
                typeof ExcelJS === 'undefined'
            ) {

                showResult(
                    'Excel出力ライブラリを読み込めませんでした。',
                    'danger'
                );

                return;
            }

            downloadExcelButton.disabled = true;

            try {

                const data =
                    statisticsData;

                const workbook =
                    new ExcelJS.Workbook();

                workbook.creator = 'CFMS';

                workbook.created = new Date();

                // ---------------------------------
                // 利用履歴データ
                // ---------------------------------

                const usageHistory =
                    Array.isArray(data.usageHistory) ?
                        data.usageHistory :
                        [];

                // ---------------------------------
                // 1日目・2日目を自動判定
                // ---------------------------------

                const classified =
                    classifyUsageHistory(usageHistory);

                // ---------------------------------
                // 1. ユーザー集計
                // ---------------------------------

                addWorksheet(
                    workbook,
                    'ユーザー集計',
                    createUserSummaryRows(data)
                );

                // ---------------------------------
                // 2. 利用者ランキング
                // ---------------------------------

                addWorksheet(
                    workbook,
                    '利用者ランキング',
                    createUserRankingRows(
                        data.recentUserRanking
                    )
                );

                // ---------------------------------
                // 3. 店舗別ランキング
                // ---------------------------------

                addWorksheet(
                    workbook,
                    '店舗別ランキング',
                    createStoreRankingRows(
                        data.storeUserRanking
                    )
                );

                // ---------------------------------
                // 4. 利用履歴
                // ---------------------------------

                addWorksheet(
                    workbook,
                    '利用履歴',
                    createUsageHistoryRows(
                        usageHistory
                    )
                );

                // ---------------------------------
                // 5. 1日目
                // ---------------------------------

                const firstDayRows =
                    createUsageHistoryRows(
                        classified.firstDay
                    );

                const firstDaySheet =
                    addWorksheet(
                        workbook,
                        '1日目',
                        firstDayRows
                    );

                if (classified.dates[0]) {

                    firstDaySheet.getCell('A1').note =
                        `対象日：${classified.dates[0]}`;
                }

                // ---------------------------------
                // 6. 2日目
                // ---------------------------------

                const secondDayRows =
                    createUsageHistoryRows(
                        classified.secondDay
                    );

                const secondDaySheet =
                    addWorksheet(
                        workbook,
                        '2日目',
                        secondDayRows
                    );

                if (classified.dates[1]) {

                    secondDaySheet.getCell('A1').note =
                        `対象日：${classified.dates[1]}`;
                }

                // ---------------------------------
                // 7. 人気
                // ---------------------------------

                const popularitySheet =
                    addWorksheet(
                        workbook,
                        '人気',
                        createStoreRankingRows(
                            data.storeUserRanking
                        )
                    );

                // グラフ画像を作成
                const chartImage =
                    await createPopularityChartImage(
                        data.storeUserRanking
                    );

                // Excelに画像を追加
                const imageId =
                    workbook.addImage({
                        base64: chartImage,
                        extension: 'png'
                    });

                popularitySheet.addImage(
                    imageId,
                    'F2:N22'
                );

                // ---------------------------------
                // 8. 滞在時間
                // ---------------------------------

                addWorksheet(
                    workbook,
                    '滞在時間',
                    createStayTimeRows(
                        data.stayTimes
                    )
                );

                // ---------------------------------
                // Excelファイル出力
                // ---------------------------------

                const buffer =
                    await workbook.xlsx.writeBuffer();

                const blob =
                    new Blob(
                        [buffer],
                        {
                            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        }
                    );

                const url =
                    URL.createObjectURL(blob);

                const link =
                    document.createElement('a');

                link.href = url;

                link.download =
                    `CFMS集計データ_${getDateString()}.xlsx`;

                document.body.appendChild(link);

                link.click();

                link.remove();

                URL.revokeObjectURL(url);

                showResult(
                    'Excelファイルをダウンロードしました。',
                    'success'
                );

            } catch (error) {

                console.error(
                    'Excelダウンロードエラー:',
                    error
                );

                showResult(
                    error.message ||
                    'Excelファイルの作成に失敗しました。',
                    'danger'
                );

            } finally {

                downloadExcelButton.disabled = false;
            }
        }

        // =====================================
        // Excelダウンロードボタン
        // =====================================

        downloadExcelButton.addEventListener(
            'click',
            downloadExcel
        );

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