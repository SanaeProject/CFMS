
<?php
// PasswordIssuancePage - パスコード発行画面のモック
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="author" content="乙成隼士,松本浩成,永島愛貴,塩川海響,星川誠弥,中島悠翔">
    <meta name="keywords" content="nkc,名古屋工学院専門学校,文化祭">
    <meta name="description" content="ユーザーのパスコード発行と応答停止ユーザーの確認画面">

    <link rel="icon" href="/images/CFMS.ico">
    <link rel="icon" type="image/svg+xml" href="/images/CFMS.svg">
    <link rel="apple-touch-icon" href="/images/CFMS.png">

    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">

    <title>パスコード発行 | CFMS</title>

    <style>
        .page-container {
            max-width: 1100px;
            margin: 0 auto;
        }

        .section-card {
            border: 1px solid #dee2e6;
            border-radius: 12px;
            background-color: #fff;
        }

        .passcode-display {
            min-height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            border: 1px dashed #adb5bd;
            border-radius: 8px;
            background-color: #f8f9fa;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            overflow-wrap: anywhere;
        }

        .table-responsive {
            border-radius: 8px;
        }

        .status-badge {
            white-space: nowrap;
        }
    </style>
</head>

<body class="bg-light">
    <main class="container py-4 py-md-5">
        <div class="page-container">
            <header class="mb-4">
                <h1 class="h3 fw-bold mb-2">パスコード発行</h1>
                <p class="text-secondary mb-0">
                    ユーザーの再ログイン用パスコードを発行し、応答停止中のユーザーを確認します。
                </p>
                <div class="alert alert-warning mt-3 mb-0" role="status">
                    <strong>モック画面：</strong>
                    表示されるデータはすべてダミーです。実際のAPI・DBには接続しません。
                </div>
            </header>

            <!-- SECTION: パスコード発行 -->
            <section class="section-card p-3 p-md-4 mb-4"
                     aria-labelledby="issuance-heading">
                <h2 id="issuance-heading" class="h5 fw-bold mb-3">
                    パスコードの発行
                </h2>

                <form id="issuance-form">
                    <div class="mb-3">
                        <label for="user-id" class="form-label">
                            ユーザーID
                        </label>
                        <input
                            type="number"
                            class="form-control"
                            id="user-id"
                            name="user_id"
                            min="1"
                            step="1"
                            placeholder="例：1001"
                            required
                        >
                        <div class="form-text">
                            モック用の登録済みユーザーID：1001、1002、1003
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        パスコードを発行
                    </button>
                </form>

                <div id="issuance-message"
                     class="mt-3"
                     role="status"
                     aria-live="polite"
                     hidden></div>

                <div id="passcode-area" class="mt-4" hidden>
                    <label class="form-label fw-bold" for="passcode-value">
                        発行されたパスコード（ダミー）
                    </label>

                    <output id="passcode-value" class="passcode-display">
                    </output>

                    <button type="button"
                            id="copy-passcode-button"
                            class="btn btn-outline-secondary btn-sm mt-2">
                        パスコードをコピー
                    </button>

                    <span id="copy-message"
                          class="small text-secondary ms-2"
                          role="status"
                          aria-live="polite"></span>
                </div>
            </section>
            <!-- !SECTION -->

            <!-- SECTION: 応答停止ユーザー一覧 -->
            <section class="section-card p-3 p-md-4"
                     aria-labelledby="expired-heading">
                <div class="d-flex flex-wrap justify-content-between
                            align-items-center gap-2 mb-3">
                    <div>
                        <h2 id="expired-heading" class="h5 fw-bold mb-1">
                            応答停止ユーザー
                        </h2>
                        <p class="text-secondary small mb-0">
                            最終ハートビートから5分以上経過した想定のユーザーです。
                        </p>
                    </div>

                    <button type="button"
                            id="reload-users-button"
                            class="btn btn-outline-primary btn-sm">
                        一覧を再読み込み
                    </button>
                </div>

                <div id="list-message"
                     class="small text-secondary mb-2"
                     role="status"
                     aria-live="polite"></div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover
                                  align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ユーザーID</th>
                                <th scope="col">最終利用店舗</th>
                                <th scope="col">最終応答日時</th>
                                <th scope="col">状態</th>
                            </tr>
                        </thead>
                        <tbody id="expired-users-table">
                        </tbody>
                    </table>
                </div>

                <p class="text-secondary small mt-3 mb-0">
                    ※ 一覧は画面確認用の固定データです。
                </p>
            </section>
            <!-- !SECTION -->
        </div>
    </main>

    <?php include_once __DIR__ . '/components/footer.php'; ?>
    <?php include_once __DIR__ . '/components/fetch_csrf_token.php'; ?>

    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

    <script>
        'use strict';

        // ANCHOR - モック用データ
        const mockUsers = [
            {
                userId: 1001,
                isActive: true,
                isFinished: false
            },
            {
                userId: 1002,
                isActive: true,
                isFinished: false
            },
            {
                userId: 1003,
                isActive: true,
                isFinished: false
            },
            {
                userId: 1004,
                isActive: false,
                isFinished: true
            }
        ];

        const mockExpiredUsers = [
            {
                userId: 1001,
                lastStoreName: '本店',
                lastHeartbeatAt: '2026-10-23 14:20:00'
            },
            {
                userId: 1002,
                lastStoreName: '第2店舗',
                lastHeartbeatAt: '2026-10-23 14:15:00'
            },
            {
                userId: 1003,
                lastStoreName: '本店',
                lastHeartbeatAt: '2026-10-23 14:10:00'
            }
        ];

        // ANCHOR - HTML表示用の要素取得
        const issuanceForm = document.getElementById('issuance-form');
        const userIdInput = document.getElementById('user-id');
        const issuanceMessage = document.getElementById('issuance-message');
        const passcodeArea = document.getElementById('passcode-area');
        const passcodeValue = document.getElementById('passcode-value');
        const copyPasscodeButton = document.getElementById('copy-passcode-button');
        const copyMessage = document.getElementById('copy-message');
        const expiredUsersTable = document.getElementById('expired-users-table');
        const listMessage = document.getElementById('list-message');
        const reloadUsersButton = document.getElementById('reload-users-button');

        // ANCHOR - メッセージ表示
        function showMessage(element, message, type) {
            element.textContent = message;
            element.className = `alert alert-${type} mt-3`;
            element.hidden = false;
        }

        // ANCHOR - ダミーパスコード生成
        function generateMockPasscode() {
            const characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            let passcode = '';

            for (let i = 0; i < 8; i++) {
                const index = Math.floor(Math.random() * characters.length);
                passcode += characters[index];
            }

            return passcode;
        }

        // ANCHOR - パスコード発行のモック処理
        issuanceForm.addEventListener('submit', function (event) {
            event.preventDefault();

            passcodeArea.hidden = true;
            copyMessage.textContent = '';

            const userId = Number(userIdInput.value);

            if (
                userIdInput.value.trim() === '' ||
                !Number.isSafeInteger(userId) ||
                userId <= 0
            ) {
                showMessage(
                    issuanceMessage,
                    '正しいユーザーIDを入力してください。',
                    'danger'
                );
                return;
            }

            const user = mockUsers.find(
                (item) => item.userId === userId
            );

            if (!user) {
                showMessage(
                    issuanceMessage,
                    '指定されたユーザーは存在しません。',
                    'danger'
                );
                return;
            }

            if (user.isFinished || !user.isActive) {
                showMessage(
                    issuanceMessage,
                    'このユーザーは終了済みのため、発行できません。',
                    'danger'
                );
                return;
            }

            const passcode = generateMockPasscode();

            passcodeValue.textContent = passcode;
            passcodeArea.hidden = false;

            showMessage(
                issuanceMessage,
                `ユーザーID ${userId} のダミーパスコードを発行しました。`,
                'success'
            );
        });

        // ANCHOR - パスコードのコピー
        copyPasscodeButton.addEventListener('click', async function () {
            const passcode = passcodeValue.textContent;

            try {
                await navigator.clipboard.writeText(passcode);
                copyMessage.textContent = 'コピーしました。';
            } catch (error) {
                copyMessage.textContent =
                    'コピーできませんでした。パスコードを選択してコピーしてください。';
            }
        });

        // ANCHOR - 応答停止ユーザー一覧の表示
        function renderExpiredUsers() {
            expiredUsersTable.replaceChildren();

            if (mockExpiredUsers.length === 0) {
                const row = document.createElement('tr');
                const cell = document.createElement('td');

                cell.colSpan = 4;
                cell.className = 'text-center text-secondary py-4';
                cell.textContent = '応答停止ユーザーはいません。';

                row.appendChild(cell);
                expiredUsersTable.appendChild(row);
            } else {
                mockExpiredUsers.forEach(function (user) {
                    const row = document.createElement('tr');

                    const userIdCell = document.createElement('td');
                    userIdCell.textContent = user.userId;

                    const storeCell = document.createElement('td');
                    storeCell.textContent = user.lastStoreName;

                    const heartbeatCell = document.createElement('td');
                    heartbeatCell.textContent = user.lastHeartbeatAt;

                    const statusCell = document.createElement('td');
                    const badge = document.createElement('span');

                    badge.className = 'badge text-bg-danger status-badge';
                    badge.textContent = '応答停止';
                    statusCell.appendChild(badge);

                    row.append(
                        userIdCell,
                        storeCell,
                        heartbeatCell,
                        statusCell
                    );

                    expiredUsersTable.appendChild(row);
                });
            }

            listMessage.textContent =
                `${mockExpiredUsers.length}件のダミーデータを表示しています。`;
        }

        // ANCHOR - 一覧の再読み込み
        reloadUsersButton.addEventListener('click', function () {
            renderExpiredUsers();
            listMessage.textContent =
                '一覧を再表示しました（モックデータのため内容は変わりません）。';
        });

        // ANCHOR - 初期表示
        renderExpiredUsers();
    </script>
</body>
</html>
