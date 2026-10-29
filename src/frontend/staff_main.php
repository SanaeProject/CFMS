
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
    <meta property="og:title" content="CFMS 管理者用メインページ">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nkc.sanae.dev">
    <meta property="og:image" content="/images/CFMS.png">
    <meta property="og:image:alt" content="CFMSロゴ">
    <meta property="og:description" content="CFMSの管理者向け管理ページです。">

    <!-- タイトル・説明文 -->
    <title>管理者用メインページ | CFMS</title>
    <meta name="description" content="CFMSの管理者向けメインページです。">

    <!-- Bootstrap読み込み -->
    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light">

    <!-- メインコンテンツ -->
    <main>
        <div class="container py-4">

            <!-- ページタイトル・ログアウト操作 -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <p class="text-primary fw-bold mb-1">CFMS / ADMINISTRATOR</p>
                    <h1 class="h2 fw-bold mb-2">管理者用メインページ</h1>
                    <p class="text-secondary mb-0">
                        管理者向けの各種管理機能を利用できます。
                    </p>
                </div>

                <div>
                    <button type="button"
                            id="logoutButton"
                            class="btn btn-outline-danger">
                        ログアウト
                    </button>
                </div>
            </div>

            <!-- 管理者情報 -->
            <section class="card border-0 shadow-sm mb-4"
                     aria-labelledby="adminInfoTitle">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                        <div>
                            <h2 id="adminInfoTitle" class="h5 fw-bold mb-3">
                                ログイン中の管理者情報
                            </h2>

                                                       <p class="mb-1">
                                <span class="text-secondary">管理者名：</span>
                                <strong><?= htmlspecialchars($adminName ?? '未設定', ENT_QUOTES, 'UTF-8') ?></strong>
                            </p>

                            <p class="mb-0">
                                <span class="text-secondary">管理者ID：</span>
                                <strong><?= htmlspecialchars($adminId ?? '未設定', ENT_QUOTES, 'UTF-8') ?></strong>
                            </p>
                        </div>

                        <div>
                            <span class="badge text-bg-primary fs-6">
                                管理者
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 管理メニュー -->
            <section aria-labelledby="managementTitle">
                <div class="mb-3">
                    <h2 id="managementTitle" class="h4 fw-bold mb-1">
                        管理メニュー
                    </h2>
                    <p class="text-secondary mb-0">
                        利用する機能を選択してください。
                    </p>
                </div>

                <div class="row g-3">

                    <!-- スタッフ管理 -->
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="mb-3">
                                    <span class="badge text-bg-primary">STAFF</span>
                                </div>

                                <h3 class="h5 fw-bold">スタッフ管理</h3>
                                <p class="text-secondary flex-grow-1">
                                    登録されているスタッフの情報や権限を管理します。
                                </p>

                                <a href="/staff/staff_management.php"
                                   class="btn btn-outline-primary w-100">
                                    スタッフ管理を開く
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 店舗管理 -->
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="mb-3">
                                    <span class="badge text-bg-success">STORE</span>
                                </div>

                                <h3 class="h5 fw-bold">店舗管理</h3>
                                <p class="text-secondary flex-grow-1">
                                    文化祭に参加する店舗の情報を管理します。
                                </p>

                                <a href="/staff/store_management.php"
                                   class="btn btn-outline-success w-100">
                                    店舗管理を開く
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- スタッフアカウント作成 -->
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="mb-3">
                                    <span class="badge text-bg-info">ACCOUNT</span>
                                </div>

                                <h3 class="h5 fw-bold">スタッフアカウント作成</h3>
                                <p class="text-secondary flex-grow-1">
                                    新しいスタッフのアカウントを作成し、
                                    管理者・スタッフの権限を設定します。
                                </p>

                                <a href="/staff/staff_account_create.php"
                                   class="btn btn-outline-info w-100">
                                    アカウント作成を開く
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- ユーザー作成・得点付与 -->
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="mb-3">
                                    <span class="badge text-bg-warning">USER</span>
                                </div>

                                <h3 class="h5 fw-bold">ユーザー作成・得点付与</h3>
                                <p class="text-secondary flex-grow-1">
                                    新しい利用者を登録し、必要に応じて初期得点を記録します。
                                </p>

                                <a href="/staff/user_account_create.php"
                                   class="btn btn-outline-warning w-100">
                                    ユーザー作成を開く
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 得点付与 -->
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="mb-3">
                                    <span class="badge text-bg-danger">SCORE</span>
                                </div>

                                <h3 class="h5 fw-bold">得点付与</h3>
                                <p class="text-secondary flex-grow-1">
                                    利用者への得点付与を行い、得点履歴を記録します。
                                </p>

                                <a href="/staff/point_award.php"
                                   class="btn btn-outline-danger w-100">
                                    得点付与を開く
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- ユーザー終了処理 -->
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="mb-3">
                                    <span class="badge text-bg-secondary">USER STATUS</span>
                                </div>

                                <h3 class="h5 fw-bold">ユーザー終了処理</h3>
                                <p class="text-secondary flex-grow-1">
                                    利用終了時に、利用者の状態と終了日時を管理します。
                                </p>

                                <a href="/staff/user_end_processing.php"
                                   class="btn btn-outline-secondary w-100">
                                    終了処理を開く
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 受付画面の管理メニュー -->
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="mb-3">
                                    <span class="badge text-bg-dark">RECEPTION</span>
                                </div>

                                <h3 class="h5 fw-bold">受付</h3>
                                <p class="text-secondary flex-grow-1">
                                    受付業務に関する管理機能を利用します。
                                </p>

                                <a href="/staff/reception_management.php"
                                   class="btn btn-outline-dark w-100">
                                    受付画面の管理メニューを開く
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- 注意事項 -->
            <div class="alert alert-light border mt-4 mb-0" role="note">
                <h2 class="h6 fw-bold">管理者向け注意事項</h2>
                <p class="small text-secondary mb-0">
                    各操作の権限確認はサーバー側でも実施してください。
                    画面上で管理機能を非表示にするだけでは、
                    不正なアクセスを防ぐことはできません。
                </p>
            </div>

        </div>
    </main>

    <!-- ログアウト処理 -->
    <script>
        // TODO: ログアウトAPIのURLと処理を実装後に接続する
        document.getElementById('logoutButton').addEventListener('click', function () {
            const shouldLogout = confirm('ログアウトしますか？');

            if (!shouldLogout) {
                return;
            }
            alert('ログアウトAPIはまだ接続されていません。');
        });
    </script>

    <!-- フッター読み込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

    <!-- CSRFトークンの自動埋め込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>

</body>
</html>
