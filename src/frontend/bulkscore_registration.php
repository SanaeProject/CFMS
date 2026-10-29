
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

    <!-- ページタイトル -->
    <div class="card-header bg-primary text-white p-3">
        <h1 class="h4 mb-1">一括得点登録・ユーザー作成</h1>
        <p class="small mb-0">管理者用｜得点登録・ユーザー作成</p>
    </div>

    <!-- 結果表示 -->
    <div id="resultMessage"
         class="alert d-none mt-3"
         role="alert"
         aria-live="polite"></div>

    <!-- ユーザー作成 -->
    <section class="card shadow-sm mb-4 mt-3">

        <div class="card-header bg-white">
            <h2 class="h5 mb-0">ユーザー作成</h2>
        </div>

        <div class="card-body">

            <p class="text-secondary">
                紙媒体での運用などに対応するため、個別のユーザーを作成します。
            </p>

            <div class="mb-3">
                <label for="newUserName" class="form-label">
                    ユーザー名
                </label>

                <input type="text"
                       class="form-control"
                       id="newUserName"
                       maxlength="255"
                       placeholder="ユーザー名を入力してください">
            </div>

            <button type="button"
                    class="btn btn-outline-primary"
                    id="createUserButton">
                ユーザー作成
            </button>

            <p class="form-text mt-2 mb-0">
                ※ ユーザー作成APIが未定義のため、現在は未接続です。
            </p>

        </div>
    </section>

    <!-- 一括得点登録 -->
    <section class="card shadow-sm mb-4">

        <div class="card-header bg-white">
            <h2 class="h5 mb-0">一括得点登録</h2>
        </div>

        <div class="card-body">

            <p class="text-secondary">
                対象ユーザーと店舗を指定して得点を登録します。
            </p>

            <form id="bulkScoreForm">

                <!-- CSRFトークン：template.phpと同じ形式 -->
                <input type="hidden"
                       class="csrf_token"
                       name="csrf_token"
                       value="">

                <!-- ユーザー識別方法 -->
                <fieldset class="mb-3">

                    <legend class="form-label fs-6">
                        対象ユーザーの指定方法
                        <span class="text-danger">*</span>
                    </legend>

                    <div class="form-check">
                        <input class="form-check-input"
                               type="radio"
                               name="identifierType"
                               id="identifierToken"
                               value="token"
                               checked>

                        <label class="form-check-label"
                               for="identifierToken">
                            トークンで指定
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input"
                               type="radio"
                               name="identifierType"
                               id="identifierUserId"
                               value="userId">

                        <label class="form-check-label"
                               for="identifierUserId">
                            ユーザーIDで指定
                        </label>
                    </div>

                </fieldset>

                <!-- トークン入力 -->
                <div class="mb-3" id="tokenGroup">

                    <label for="token" class="form-label">
                        トークン
                        <span class="text-danger">*</span>
                    </label>

                    <input type="text"
                           class="form-control"
                           id="token"
                           name="token"
                           maxlength="255"
                           placeholder="対象ユーザーのトークンを入力"
                           autocomplete="off">

                </div>

                <!-- ユーザーID入力 -->
                <div class="mb-3 d-none" id="userIdGroup">

                    <label for="userId" class="form-label">
                        ユーザーID
                        <span class="text-danger">*</span>
                    </label>

                    <input type="text"
                           class="form-control"
                           id="userId"
                           name="userId"
                           maxlength="255"
                           placeholder="対象ユーザーIDを入力"
                           autocomplete="off">

                </div>

                <!-- 店舗選択 -->
                <div class="mb-3">

                    <label for="storeId" class="form-label">
                        対象店舗
                        <span class="text-danger">*</span>
                    </label>

                    <select class="form-select"
                            id="storeId"
                            name="storeId"
                            required>

                        <option value="">
                            店舗一覧を読み込んでいます...
                        </option>

                    </select>

                    <div class="form-text">
                        登録済み店舗から選択してください。
                    </div>

                </div>

                <!-- ポイント入力 -->
                <div class="mb-3">

                    <label for="score" class="form-label">
                        登録ポイント
                        <span class="text-danger">*</span>
                    </label>

                    <input type="number"
                           class="form-control"
                           id="score"
                           name="score"
                           min="1"
                           step="1"
                           placeholder="例：10"
                           required>

                    <div class="form-text">
                        ポイントの範囲はサーバー側でも検証してください。
                    </div>

                </div>

                <!-- 注意事項 -->
                <div class="alert alert-warning" role="alert">
                    <strong>注意：</strong>
                    ブース利用回数への算入については、
                    実運用ルールに従ってサーバー側で処理してください。
                </div>

                <!-- ボタン -->
                <div class="d-grid d-sm-flex gap-2">

                    <button type="submit"
                            class="btn btn-primary"
                            id="submitScoreButton">
                        得点を登録
                    </button>

                    <button type="reset"
                            class="btn btn-outline-secondary"
                            id="resetButton">
                        入力内容をクリア
                    </button>

                </div>

            </form>

        </div>
    </section>

    <!-- 店舗一覧の再読み込み -->
    <div class="text-end">

        <button type="button"
                class="btn btn-outline-secondary btn-sm"
                id="reloadStoresButton">
            店舗一覧を再読み込み
        </button>

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
    "use strict";

    /**
     * APIのベースURL
     *
     * template.phpと同じくAPI_URL環境変数を使用する。
     * API_URL未設定時は現在のホストを使用する。
     *
     * API_URLには通常、https://example.com のような
     * オリジンを設定する（末尾の /api は付けない）。
     */
    const API_BASE_URL = <?= json_encode(
        rtrim(getenv('API_URL') ?: '', '/'),
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) ?> || window.location.origin;

    /**
     * APIのURL
     */
    const STORE_LIST_API =
        API_BASE_URL + "/api/admin/stores";

    const BULK_SCORE_API =
        API_BASE_URL + "/api/admin/score/bulkAdd";

    /**
     * 画面要素
     */
    const form = document.getElementById("bulkScoreForm");
    const resultMessage = document.getElementById("resultMessage");

    const tokenRadio = document.getElementById("identifierToken");
    const userIdRadio = document.getElementById("identifierUserId");

    const tokenGroup = document.getElementById("tokenGroup");
    const userIdGroup = document.getElementById("userIdGroup");

    const tokenInput = document.getElementById("token");
    const userIdInput = document.getElementById("userId");

    const storeSelect = document.getElementById("storeId");
    const scoreInput = document.getElementById("score");

    const submitScoreButton =
        document.getElementById("submitScoreButton");

    const reloadStoresButton =
        document.getElementById("reloadStoresButton");

    let isSubmitting = false;
    let isLoadingStores = false;

    /**
     * 結果表示
     *
     * template.phpと同じくsuccess/messageを利用する。
     */
    function renderResult(data) {
        resultMessage.replaceChildren();

        const alertDiv = document.createElement("div");

        alertDiv.className = data.success
            ? "alert alert-success"
            : "alert alert-danger";

        alertDiv.setAttribute("role", "alert");
        alertDiv.textContent = data.message || (
            data.success
                ? "処理が完了しました。"
                : "処理に失敗しました。"
        );

        resultMessage.className = "mt-3";
        resultMessage.appendChild(alertDiv);
    }

    /**
     * メッセージ表示
     */
    function showMessage(message, type) {
        resultMessage.replaceChildren();

        const alertDiv = document.createElement("div");

        alertDiv.className = "alert alert-" + type;
        alertDiv.setAttribute("role", "alert");
        alertDiv.textContent = message;

        resultMessage.className = "mt-3";
        resultMessage.appendChild(alertDiv);
    }

    /**
     * メッセージ消去
     */
    function clearMessage() {
        resultMessage.replaceChildren();
        resultMessage.className = "alert d-none mt-3";
    }

    /**
     * CSRFトークン取得
     *
     * template.phpと同じhiddenフィールドを利用する。
     */
    function getCsrfToken() {
        const csrfInput = document.querySelector(".csrf_token");

        return csrfInput ? csrfInput.value.trim() : "";
    }

    /**
     * JSON形式のAPIレスポンスを取得する
     */
    async function requestJson(url, options = {}) {
        const response = await fetch(url, {
            ...options,

            credentials: "include",

            headers: {
                "Accept": "application/json",
                ...(options.body ? {
                    "Content-Type": "application/json"
                } : {}),
                ...(options.headers || {})
            }
        });

        let data;

        try {
            data = await response.json();
        } catch (error) {
            throw new Error(
                "サーバーから正しいJSONレスポンスが返されませんでした。"
            );
        }

        if (!response.ok || data.success !== true) {
            throw new Error(
                data.message || "サーバーでエラーが発生しました。"
            );
        }

        return data;
    }

    /**
     * ユーザー識別方法の切り替え
     */
    function updateIdentifierFields() {
        const useToken = tokenRadio.checked;

        tokenGroup.classList.toggle("d-none", !useToken);
        userIdGroup.classList.toggle("d-none", useToken);

        tokenInput.required = useToken;
        userIdInput.required = !useToken;

        if (useToken) {
            userIdInput.value = "";
        } else {
            tokenInput.value = "";
        }

        clearMessage();
    }

    tokenRadio.addEventListener(
        "change",
        updateIdentifierFields
    );

    userIdRadio.addEventListener(
        "change",
        updateIdentifierFields
    );

    /**
     * 店舗一覧を取得する
     *
     * GET /api/admin/stores
     *
     * 想定レスポンス:
     * {
     *   "success": true,
     *   "message": "店舗一覧を取得しました。",
     *   "stores": [
     *     {"id": "1", "name": "店舗A"}
     *   ]
     * }
     */
    async function loadStores() {
        if (isLoadingStores || isSubmitting) {
            return;
        }

        isLoadingStores = true;
        reloadStoresButton.disabled = true;

        storeSelect.disabled = true;
        storeSelect.replaceChildren();

        const loadingOption = document.createElement("option");

        loadingOption.value = "";
        loadingOption.textContent = "店舗一覧を読み込んでいます...";

        storeSelect.appendChild(loadingOption);

        try {
            const data = await requestJson(STORE_LIST_API, {
                method: "GET"
            });

            if (!Array.isArray(data.stores)) {
                throw new Error(
                    "店舗一覧の形式が正しくありません。"
                );
            }

            storeSelect.replaceChildren();

            const defaultOption = document.createElement("option");

            defaultOption.value = "";
            defaultOption.textContent = data.stores.length > 0
                ? "店舗を選択してください"
                : "登録済み店舗がありません";

            storeSelect.appendChild(defaultOption);

            data.stores.forEach(function(store) {
                const option = document.createElement("option");

                option.value =
                    store.id ??
                    store.storeId ??
                    store.store_id ??
                    "";

                option.textContent = store.name ?? "";

                if (option.value === "" || option.textContent === "") {
                    return;
                }

                storeSelect.appendChild(option);
            });

            clearMessage();

        } catch (error) {
            storeSelect.replaceChildren();

            const errorOption = document.createElement("option");

            errorOption.value = "";
            errorOption.textContent = "店舗一覧を取得できませんでした";

            storeSelect.appendChild(errorOption);

            showMessage(
                "店舗一覧の取得に失敗しました：" + error.message,
                "danger"
            );

        } finally {
            isLoadingStores = false;
            reloadStoresButton.disabled = false;
            storeSelect.disabled = false;
        }
    }

    /**
     * 一括得点登録
     *
     * POST /api/admin/score/bulkAdd
     *
     * 入力:
     * token または userId
     * storeId
     * score
     * csrf_token
     *
     * 出力:
     * success
     * message
     */
    form.addEventListener("submit", async function(event) {
        event.preventDefault();

        if (isSubmitting || isLoadingStores) {
            return;
        }

        clearMessage();

        const csrfToken = getCsrfToken();
        const storeId = storeSelect.value;
        const scoreValue = scoreInput.value.trim();
        const score = Number(scoreValue);

        if (!csrfToken) {
            showMessage(
                "CSRFトークンを取得できません。ページを再読み込みしてください。",
                "danger"
            );
            return;
        }

        if (!storeId) {
            showMessage(
                "対象店舗を選択してください。",
                "warning"
            );

            storeSelect.focus();
            return;
        }

        if (
            scoreValue === "" ||
            !Number.isSafeInteger(score) ||
            score < 1
        ) {
            showMessage(
                "ポイントは1以上の整数で入力してください。",
                "warning"
            );

            scoreInput.focus();
            return;
        }

        /**
         * template.phpと同じJSON POST形式
         */
        const payload = {
            storeId: storeId,
            score: score,
            csrf_token: csrfToken
        };

        if (tokenRadio.checked) {
            const token = tokenInput.value.trim();

            if (!token) {
                showMessage(
                    "対象ユーザーのトークンを入力してください。",
                    "warning"
                );

                tokenInput.focus();
                return;
            }

            payload.token = token;

        } else {
            const userId = userIdInput.value.trim();

            if (!userId) {
                showMessage(
                    "対象ユーザーIDを入力してください。",
                    "warning"
                );

                userIdInput.focus();
                return;
            }

            payload.userId = userId;
        }

        isSubmitting = true;

        submitScoreButton.disabled = true;
        submitScoreButton.textContent = "登録中...";

        try {
            const data = await requestJson(BULK_SCORE_API, {
                method: "POST",

                body: JSON.stringify(payload)
            });

            renderResult({
                success: true,
                message: data.message || "得点を登録しました。"
            });

            form.reset();
            updateIdentifierFields();

        } catch (error) {
            renderResult({
                success: false,
                message: "得点登録に失敗しました：" + error.message
            });

        } finally {
            isSubmitting = false;

            submitScoreButton.disabled = false;
            submitScoreButton.textContent = "得点を登録";
        }
    });

    /**
     * 入力内容をクリアする
     */
    form.addEventListener("reset", function() {
        window.setTimeout(function() {
            updateIdentifierFields();
            clearMessage();
        }, 0);
    });

    /**
     * 店舗一覧を再読み込みする
     */
    reloadStoresButton.addEventListener("click", function() {
        clearMessage();
        loadStores();
    });

    /**
     * ユーザー作成
     *
     * API仕様が未定義のため、実際の登録処理は行わない。
     */
    document.getElementById("createUserButton")
        .addEventListener("click", function() {

            const userName = document.getElementById("newUserName")
                .value.trim();

            if (!userName) {
                showMessage(
                    "ユーザー名を入力してください。",
                    "warning"
                );

                document.getElementById("newUserName").focus();
                return;
            }

            showMessage(
                "ユーザー作成APIが未定義のため、登録処理はまだ実行できません。",
                "warning"
            );
        });

    /**
     * 初期表示
     */
    updateIdentifierFields();
    loadStores();
</script>

</body>
</html>