<!DOCTYPE html>

<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="author" content="乙成隼士,松本浩成,永島愛貴,塩川海響,星川誠弥,中島悠翔">

    <link rel="icon" type="image/x-icon" href="/images/CFMS.ico">
    <link rel="icon" type="image/svg+xml" href="/images/CFMS.svg">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/CFMS.png">

    <meta name="keywords" content="CFMS,文化祭,スタッフログイン">

    <!-- OGP -->
    <meta property="og:title" content="スタッフログイン | CFMS" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= getenv('FRONTEND_URL') ?>" />
    <meta property="og:image" content="/images/CFMS.png" />
    <meta property="og:description" content="CFMS スタッフログインページ" />

    <title>スタッフログイン | CFMS</title>
    <meta name="description" content="CFMSの管理者・スタッフログインページ">

    <!-- Bootstrap -->
    <link href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <main class="container py-5">

        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="card shadow">
                    <div class="card-body">

                        <h1 class="text-center mb-4">
                            CFMS
                        </h1>

                        <h2 class="h4 text-center mb-4">
                            スタッフログイン
                        </h2>

                        <form id="staffLoginForm">

                            <!-- CSRF -->
                            <input type="hidden"
                                   class="csrf_token"
                                   name="csrf_token"
                                   value="">

                            <div class="mb-3">
                                <label for="staffId" class="form-label">
                                    スタッフID
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="staffId"
                                       name="staffId"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    スタッフ名
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="name"
                                       name="name"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    パスワード
                                </label>
                                <input type="password"
                                       class="form-control"
                                       id="password"
                                       name="password"
                                       required>
                            </div>

                            <button type="submit"
                                    class="btn btn-primary w-100">
                                ログイン
                            </button>

                        </form>

                        <div id="result" class="mt-3"></div>

                    </div>
                </div>

            </div>
        </div>

    </main>

    <script>
        /**
         * サーバーからのレスポンスを表示
         * @param {Object} data
         */
        function renderResult(data) {

            const resultDiv = document.getElementById('result');

            if (data.success) {

                resultDiv.innerHTML =
                    `<div class="alert alert-success">${data.message}</div>`;

                // 担当店舗選択ページへ遷移
                window.location.href = '/staff/main/';

            } else {

                resultDiv.innerHTML =
                    `<div class="alert alert-danger">${data.message}</div>`;
            }
        }

        /**
         * ログイン送信
         */
        document.getElementById('staffLoginForm')
            .addEventListener('submit', function(event) {

                event.preventDefault();

                const staffId =
                    document.getElementById('staffId').value;

                const name =
                    document.getElementById('name').value;

                const password =
                    document.getElementById('password').value;

                const csrf_token =
                    document.querySelector('.csrf_token').value;

                fetch(
                    '<?= getenv("API_URL") ?>/staff/staff_login.php', {
                        method: 'POST',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            staffId,
                            name,
                            password,
                            csrf_token
                        })
                    }
                )
                .then(response => response.json())
                .then(data => renderResult(data))
                .catch(error => {

                    console.error('Error:', error);

                    document.getElementById('result').innerHTML =
                        '<div class="alert alert-danger">通信エラーが発生しました。</div>';
                });
            });
    </script>

    <!-- フッター -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

    <!-- CSRF取得 -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>

</body>

</html>