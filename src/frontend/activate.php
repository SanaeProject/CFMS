<?php

/**
 * ActivatePage
 * 受付スタッフまたは管理者がユーザーをアクティベートするページ。
 * ユーザーの識別には固定10文字の token を使用し、内部DB IDは表示しない。
 */
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="乙成隼士,松本浩成,永島愛貴,塩川海響,星川誠弥,中島悠翔">
    <link rel="icon" href="/images/CFMS.ico" sizes="any">
    <link rel="icon" href="/images/CFMS.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/images/CFMS.png">
    <meta name="keywords" content="nkc,名古屋工学院専門学校,文化祭">
    <meta property="og:title" content="受付アカウント有効化">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nkc.sanae.dev">
    <meta property="og:image" content="/images/CFMS.png">
    <meta property="og:description" content="受付スタッフがユーザーを有効化するページ">
    <title>受付アカウント有効化 | CFMS</title>
    <meta name="description" content="QRコードを読み取り、ユーザーのアカウントを有効化します。">

    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js" defer></script>
    <style>
        #qr-reader {
            width: 100%;
            max-width: 480px;
            margin-inline: auto;
        }

        #token {
            text-transform: uppercase;
            letter-spacing: .12em;
            font-family: monospace;
        }

        #result {
            white-space: pre-wrap;
        }
    </style>
</head>

<body class="bg-light">
    <main class="container py-4" style="max-width: 720px;">
        <h1 class="h3 text-center mb-3">受付アカウント有効化</h1>
        <p class="text-center text-secondary mb-4">
            支払いを確認してから、ユーザーのアカウントを有効化してください。
        </p>

        <div id="result" class="mb-3" aria-live="polite"></div>

        <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <form id="activateForm">
                    <input type="hidden" class="csrf_token" name="csrf_token" value="">

                    <h2 class="h5 mb-3">QRコード読み取り</h2>
                    <p class="text-secondary small">
                        ユーザーのQRコードを読み取ってください。カメラが使えない場合は、トークンを手入力できます。
                    </p>

                    <div id="qr-reader" class="mb-3" aria-label="QRコード読み取り画面"></div>
                    <div class="d-grid d-sm-flex gap-2 mb-4">
                        <button type="button" id="startCameraButton" class="btn btn-outline-primary">
                            カメラを起動する
                        </button>
                        <button type="button" id="stopCameraButton" class="btn btn-outline-secondary" disabled>
                            カメラを停止する
                        </button>
                    </div>

                    <div class="mb-4">
                        <label for="token" class="form-label">ユーザートークン（10文字）</label>
                        <input type="text" class="form-control form-control-lg" id="token" name="token"
                            placeholder="例: A1B2C3D4E5" autocomplete="off"
                            autocapitalize="characters" maxlength="10" minlength="10"
                            pattern="[A-Za-z0-9]{10}" required>
                        <div class="form-text">英字と数字の10文字です。入力内容は送信前に大文字へ統一します。</div>
                    </div>

                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h2 class="h6 mb-2">アカウント状態</h2>
                            <p id="accountStatus" class="mb-0">未確認</p>
                            <p class="form-text mb-0 mt-2">
                                有効化可否はサーバー側で確認します。既に有効化済み、または終了済みのアカウントは処理されません。
                            </p>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" id="activateButton" class="btn btn-success btn-lg">
                            アクティベートする
                        </button>
                        <button type="reset" id="resetButton" class="btn btn-outline-secondary">
                            入力をリセット
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="alert alert-info">
            <h2 class="h6">受付時の注意事項</h2>
            <ul class="mb-0">
                <li>料金の支払いを確認してから操作してください。</li>
                <li>画面に表示されたトークンが対象ユーザーのものか確認してください。</li>
                <li>管理者または受付担当（reception）のみ操作できます。</li>
            </ul>
        </div>
    </main>

    <?php
    include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php';
    ?>

    <script>
        'use strict';

        const API_BASE_URL = <?= json_encode(
                                    rtrim((string)(getenv('API_URL') ?: ''), '/'),
                                    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                                ) ?>;
        const ACTIVATE_ENDPOINT = '/user/user_activate.php';

        const form = document.getElementById('activateForm');
        const tokenInput = document.getElementById('token');
        const activateButton = document.getElementById('activateButton');
        const startCameraButton = document.getElementById('startCameraButton');
        const stopCameraButton = document.getElementById('stopCameraButton');
        const accountStatus = document.getElementById('accountStatus');
        const result = document.getElementById('result');

        let scanner = null;
        let cameraRunning = false;
        let processing = false;

        function showResult(message, type = 'info') {
            const types = {
                success: 'alert alert-success',
                error: 'alert alert-danger',
                info: 'alert alert-info',
                warning: 'alert alert-warning'
            };
            result.replaceChildren();
            const element = document.createElement('div');
            element.className = types[type] || types.info;
            element.setAttribute('role', 'alert');
            element.textContent = message;
            result.appendChild(element);
        }

        function normalizeToken(value) {
            return String(value ?? '').trim().toUpperCase();
        }

        function getCsrfToken() {
            const input = form.querySelector('input[name="csrf_token"]') ||
                form.querySelector('.csrf_token');
            return input && typeof input.value === 'string' ? input.value.trim() : '';
        }

        async function stopCamera() {
            if (!scanner) {
                cameraRunning = false;
                stopCameraButton.disabled = true;
                startCameraButton.disabled = false;
                return;
            }

            try {
                if (cameraRunning) {
                    await scanner.stop();
                }
            } catch (error) {
                console.error('カメラ停止エラー:', error);
            } finally {
                try {
                    scanner.clear();
                } catch (error) {
                    // スキャナーが初期化されていない場合は無視する
                }
                scanner = null;
                cameraRunning = false;
                stopCameraButton.disabled = true;
                startCameraButton.disabled = false;
            }
        }

        async function onQrCodeScanned(decodedText) {
            const token = normalizeToken(decodedText);
            if (!/^[A-Z0-9]{10}$/.test(token)) {
                showResult('QRコードの内容が10文字のユーザートークンではありません。', 'error');
                return;
            }

            tokenInput.value = token;
            accountStatus.textContent = '未確認';
            showResult('QRコードを読み取りました。トークンを確認してから有効化してください。', 'success');
            await stopCamera();
        }

        async function startCamera() {
            if (cameraRunning || processing) return;

            if (typeof Html5Qrcode === 'undefined') {
                showResult('QRコード読み取りライブラリを読み込めませんでした。', 'error');
                return;
            }

            startCameraButton.disabled = true;
            try {
                scanner = new Html5Qrcode('qr-reader');
                await scanner.start({
                        facingMode: 'environment'
                    }, {
                        fps: 10,
                        qrbox: {
                            width: 250,
                            height: 250
                        },
                        aspectRatio: 1.0
                    },
                    onQrCodeScanned,
                    () => {}
                );
                cameraRunning = true;
                stopCameraButton.disabled = false;
                showResult('カメラを起動しました。QRコードをかざしてください。', 'info');
            } catch (error) {
                console.error('カメラ起動エラー:', error);
                await stopCamera();
                showResult('カメラを起動できません。カメラの使用許可とHTTPS接続を確認してください。', 'error');
            } finally {
                if (!cameraRunning) startCameraButton.disabled = false;
            }
        }

        startCameraButton.addEventListener('click', startCamera);
        stopCameraButton.addEventListener('click', stopCamera);

        tokenInput.addEventListener('input', () => {
            tokenInput.value = normalizeToken(tokenInput.value);
            accountStatus.textContent = '未確認';
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (processing) return;

            const token = normalizeToken(tokenInput.value);
            if (!/^[A-Z0-9]{10}$/.test(token)) {
                showResult('トークンは英字と数字の10文字で入力してください。', 'error');
                tokenInput.focus();
                return;
            }

            const csrfToken = getCsrfToken();
            if (!csrfToken) {
                showResult('CSRFトークンを取得できません。ページを再読み込みしてください。', 'error');
                return;
            }

            if (!API_BASE_URL) {
                showResult('API_URLが設定されていません。環境変数を確認してください。', 'error');
                return;
            }

            processing = true;
            activateButton.disabled = true;
            activateButton.textContent = '処理中...';

            try {
                const response = await fetch(API_BASE_URL + ACTIVATE_ENDPOINT, {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        token,
                        csrf_token: csrfToken
                    })
                });

                let payload;
                try {
                    payload = await response.json();
                } catch {
                    throw new Error('サーバーから正しいJSONレスポンスが返されませんでした。');
                }

                if (!response.ok || payload.success !== true) {
                    accountStatus.textContent = '有効化できませんでした';
                    throw new Error(payload.message || 'アクティベートに失敗しました。');
                }

                if (payload.data?.isActive !== true) {
                    accountStatus.textContent = '状態を確認できません';
                    throw new Error('サーバーの応答から有効化状態を確認できませんでした。');
                }

                accountStatus.textContent = '有効';
                showResult(payload.message || 'アカウントを有効化しました。', 'success');
            } catch (error) {
                showResult(error.message || '通信エラーが発生しました。', 'error');
            } finally {
                processing = false;
                activateButton.disabled = false;
                activateButton.textContent = 'アクティベートする';
            }
        });

        form.addEventListener('reset', () => {
            void stopCamera();
            accountStatus.textContent = '未確認';
            result.replaceChildren();
        });

        window.addEventListener('pagehide', () => {
            if (scanner && cameraRunning) {
                scanner.stop().catch((error) => console.error('カメラ停止エラー:', error));
            }
        });
    </script>
</body>

</html>