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
    <meta property="og:title" content="スタッフ管理ページ">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nkc.sanae.dev">
    <meta property="og:image" content="/images/CFMS.png">
    <meta property="og:image:alt" content="CFMS">
    <meta property="og:description" content="スタッフ情報を管理するページ">

    <title>スタッフ管理ページ</title>
    <meta name="description" content="スタッフ情報の登録・編集・削除を行います">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light">

    <main class="container py-4">

        <div class="card-header bg-primary text-white p-3">
            <h1 class="h4 mb-1">スタッフ管理</h1>
            <p class="small mb-0">管理者用｜スタッフ情報の登録・編集・削除</p>
        </div>

        <!-- メッセージ表示 -->
        <div id="result" class="mb-3" aria-live="polite"></div>

        <!-- スタッフ一覧 -->
        <section class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">スタッフ一覧</h2>
                <button type="button" class="btn btn-primary" id="newStaffBtn">
                    新規登録
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>スタッフID</th>
                                <th>名前</th>
                                <th>役割</th>
                                <th>担当店舗</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="staffList">
                            <tr>
                                <td colspan="5" class="text-center">
                                    スタッフ情報を読み込んでいます。
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- スタッフ登録・編集フォーム -->
        <section class="card shadow-sm mb-4" id="staffFormSection">
            <div class="card-header">
                <h2 class="h5 mb-0" id="formTitle">スタッフ登録</h2>
            </div>

            <div class="card-body">
                <form id="staffForm">

                    <!-- CSRFトークン -->
                    <input type="hidden" class="csrf_token" name="csrf_token" value="">

                    <!-- 編集対象のスタッフID -->
                    <input type="hidden" id="originalStaffId">

                    <div class="mb-3">
                        <label for="staffId" class="form-label">
                            スタッフID <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="staffId"
                            name="staffId"
                            maxlength="100"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="staffName" class="form-label">
                            名前 <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="staffName"
                            name="name"
                            maxlength="100"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="staffPassword" class="form-label">
                            パスワード
                            <span class="text-danger" id="passwordRequiredMark">*</span>
                        </label>
                        <input
                            type="password"
                            class="form-control"
                            id="staffPassword"
                            name="password"
                            autocomplete="new-password"
                            required>
                        <div class="form-text" id="passwordHelp">
                            新規登録時はパスワードが必須です。
                        </div>
                    </div>

                    <fieldset class="mb-3">
                        <legend class="form-label">
                            役割 <span class="text-danger">*</span>
                        </legend>

                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="role"
                                id="roleReception"
                                value="reception"
                                required>
                            <label class="form-check-label" for="roleReception">
                                受付
                            </label>
                        </div>

                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="role"
                                id="roleStore"
                                value="store">
                            <label class="form-check-label" for="roleStore">
                                店舗スタッフ
                            </label>
                        </div>

                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="role"
                                id="rolePrize"
                                value="prize">
                            <label class="form-check-label" for="rolePrize">
                                景品担当
                            </label>
                        </div>

                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="role"
                                id="roleAdmin"
                                value="admin">
                            <label class="form-check-label" for="roleAdmin">
                                管理者
                            </label>
                        </div>
                    </fieldset>

                    <fieldset class="mb-4">
                        <legend class="form-label">担当店舗</legend>
                        <div id="storeCheckboxes">
                            <p class="text-muted mb-0">
                                店舗情報を読み込んでいます。
                            </p>
                        </div>
                    </fieldset>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary" id="saveStaffBtn">
                            登録する
                        </button>
                        <button type="button" class="btn btn-secondary" id="cancelBtn">
                            キャンセル
                        </button>
                    </div>

                </form>
            </div>
        </section>

        <!-- スタッフ削除フォーム -->
        <section class="card shadow-sm mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">スタッフ削除</h2>
            </div>

            <div class="card-body">
                <form id="deleteForm">
                    <input type="hidden" class="csrf_token" name="csrf_token" value="">

                    <div class="mb-3">
                        <label for="deleteStaffId" class="form-label">
                            削除するスタッフID
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="deleteStaffId"
                            name="staffId"
                            required>
                    </div>

                    <button type="submit" class="btn btn-danger" id="deleteStaffBtn">
                        スタッフを削除
                    </button>
                </form>
            </div>
        </section>

    </main>

    <script>
        // APIのURL
        const API_URL = '<?= getenv('API_URL') ?>/template/template.php';

        // モック用スタッフデータ
        let staffs = [
            {
                staffId: 'staff001',
                name: '山田太郎',
                role: 'reception',
                storeIds: ['1']
            },
            {
                staffId: 'staff002',
                name: '佐藤花子',
                role: 'store',
                storeIds: ['1', '2']
            }
        ];

        // モック用店舗データ
        let stores = [
            { id: '1', name: '1組店舗' },
            { id: '2', name: '2組店舗' },
            { id: '3', name: '3組店舗' }
        ];

        const roleNames = {
            reception: '受付',
            store: '店舗スタッフ',
            prize: '景品担当',
            admin: '管理者'
        };

        // 結果メッセージを表示
        function renderResult(data) {
            const resultDiv = document.getElementById('result');
            resultDiv.replaceChildren();

            const alertDiv = document.createElement('div');
            alertDiv.className = data.success === true
                ? 'alert alert-success'
                : 'alert alert-danger';

            alertDiv.textContent = data.message || (
                data.success === true
                    ? '処理が完了しました。'
                    : '処理に失敗しました。'
            );

            resultDiv.appendChild(alertDiv);

            if (data.data) {
                console.log('サーバーからのデータ:', data.data);
            }
        }

        // CSRFトークンを取得
        function getCsrfToken() {
            return document.querySelector('.csrf_token').value;
        }

        // API通信
        function callApi(requestData) {
            const csrf_token = getCsrfToken();

            return fetch(API_URL, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ...requestData,
                    csrf_token
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('通信に失敗しました。');
                }

                return response.json();
            });
        }

        // スタッフ一覧を表示
        function renderStaffs() {
            const staffList = document.getElementById('staffList');
            staffList.replaceChildren();

            if (staffs.length === 0) {
                const tr = document.createElement('tr');
                const td = document.createElement('td');

                td.colSpan = 5;
                td.className = 'text-center text-muted';
                td.textContent = 'スタッフが登録されていません。';

                tr.appendChild(td);
                staffList.appendChild(tr);
                return;
            }

            staffs.forEach(staff => {
                const tr = document.createElement('tr');

                const idTd = document.createElement('td');
                idTd.textContent = staff.staffId;

                const nameTd = document.createElement('td');
                nameTd.textContent = staff.name;

                const roleTd = document.createElement('td');
                roleTd.textContent = roleNames[staff.role] || staff.role;

                const storeTd = document.createElement('td');

                const staffStores = stores
                    .filter(store =>
                        (staff.storeIds || []).includes(String(store.id))
                    )
                    .map(store => store.name);

                storeTd.textContent = staffStores.length > 0
                    ? staffStores.join('、')
                    : '未設定';

                const actionTd = document.createElement('td');
                const buttonGroup = document.createElement('div');
                buttonGroup.className = 'd-flex flex-wrap gap-2';

                const editButton = document.createElement('button');
                editButton.type = 'button';
                editButton.className = 'btn btn-sm btn-warning';
                editButton.textContent = '編集';
                editButton.addEventListener('click', () => {
                    editStaff(staff.staffId);
                });

                const deleteButton = document.createElement('button');
                deleteButton.type = 'button';
                deleteButton.className = 'btn btn-sm btn-danger';
                deleteButton.textContent = '削除';
                deleteButton.addEventListener('click', () => {
                    document.getElementById('deleteStaffId').value =
                        staff.staffId;

                    document.getElementById('deleteStaffId').focus();
                });

                buttonGroup.append(editButton, deleteButton);
                actionTd.appendChild(buttonGroup);

                tr.append(idTd, nameTd, roleTd, storeTd, actionTd);
                staffList.appendChild(tr);
            });
        }

        // 店舗チェックボックスを表示
        function renderStoreCheckboxes(selectedStoreIds = []) {
            const container = document.getElementById('storeCheckboxes');
            container.replaceChildren();

            const normalizedSelectedIds = selectedStoreIds.map(String);

            if (stores.length === 0) {
                const p = document.createElement('p');
                p.className = 'text-muted';
                p.textContent = '店舗が登録されていません。';
                container.appendChild(p);
                return;
            }

            stores.forEach(store => {
                const wrapper = document.createElement('div');
                wrapper.className = 'form-check';

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.className = 'form-check-input store-checkbox';
                checkbox.id = `store-${store.id}`;
                checkbox.value = String(store.id);
                checkbox.checked =
                    normalizedSelectedIds.includes(String(store.id));

                const label = document.createElement('label');
                label.className = 'form-check-label';
                label.htmlFor = checkbox.id;
                label.textContent = store.name;

                wrapper.append(checkbox, label);
                container.appendChild(wrapper);
            });
        }

        // 選択された店舗IDを取得
        function getSelectedStoreIds() {
            return Array.from(
                document.querySelectorAll('.store-checkbox:checked')
            ).map(checkbox => checkbox.value);
        }

        // パスワード必須状態を切り替える
        function setPasswordRequirement(isEditing) {
            const passwordInput = document.getElementById('staffPassword');
            const requiredMark = document.getElementById('passwordRequiredMark');
            const passwordHelp = document.getElementById('passwordHelp');

            passwordInput.required = !isEditing;
            requiredMark.classList.toggle('d-none', isEditing);

            passwordHelp.textContent = isEditing
                ? 'パスワードを変更しない場合は空欄にしてください。'
                : '新規登録時はパスワードが必須です。';
        }

        // フォームを初期状態に戻す
        function resetForm() {
            document.getElementById('staffForm').reset();

            document.getElementById('originalStaffId').value = '';
            document.getElementById('staffId').disabled = false;

            document.getElementById('formTitle').textContent = 'スタッフ登録';
            document.getElementById('saveStaffBtn').textContent = '登録する';

            setPasswordRequirement(false);
            renderStoreCheckboxes();
        }

        // 新規登録フォームを表示
        document.getElementById('newStaffBtn').addEventListener('click', () => {
            resetForm();

            document.getElementById('staffFormSection').scrollIntoView({
                behavior: 'smooth'
            });
        });

        // スタッフ編集
        function editStaff(staffId) {
            const staff = staffs.find(item => item.staffId === staffId);

            if (!staff) {
                renderResult({
                    success: false,
                    message: 'スタッフが見つかりません。'
                });
                return;
            }

            document.getElementById('originalStaffId').value = staff.staffId;
            document.getElementById('staffId').value = staff.staffId;
            document.getElementById('staffId').disabled = true;
            document.getElementById('staffName').value = staff.name;
            document.getElementById('staffPassword').value = '';

            const roleRadio = document.querySelector(
                `input[name="role"][value="${staff.role}"]`
            );

            if (roleRadio) {
                roleRadio.checked = true;
            }

            renderStoreCheckboxes(staff.storeIds || []);

            document.getElementById('formTitle').textContent = 'スタッフ編集';
            document.getElementById('saveStaffBtn').textContent = '更新する';

            // 編集時はパスワードを必須にしない
            setPasswordRequirement(true);

            document.getElementById('staffFormSection').scrollIntoView({
                behavior: 'smooth'
            });
        }

        // 登録・更新
        document.getElementById('staffForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const originalStaffId =
                document.getElementById('originalStaffId').value;

            const staffId =
                document.getElementById('staffId').value.trim();

            const name =
                document.getElementById('staffName').value.trim();

            const password =
                document.getElementById('staffPassword').value;

            const role =
                document.querySelector('input[name="role"]:checked')?.value;

            const storeIds = getSelectedStoreIds();

            const isEditing = originalStaffId !== '';

            // 必須項目チェック
            if (!staffId || !name || !role) {
                renderResult({
                    success: false,
                    message: '必須項目を入力してください。'
                });
                return;
            }

            // 新規登録時はパスワード必須
            if (!isEditing && password.trim() === '') {
                renderResult({
                    success: false,
                    message: '新規登録時はパスワードを入力してください。'
                });

                document.getElementById('staffPassword').focus();
                return;
            }

            // 新規登録時のスタッフID重複チェック
            if (!isEditing) {
                const exists = staffs.some(
                    staff => staff.staffId === staffId
                );

                if (exists) {
                    renderResult({
                        success: false,
                        message: 'このスタッフIDはすでに登録されています。'
                    });
                    return;
                }
            }

            const requestData = {
                action: isEditing ? 'update_staff' : 'create_staff',
                originalStaffId,
                staffId,
                name,
                role,
                storeIds
            };

            // パスワードが入力されている場合だけ送信
            if (password !== '') {
                requestData.password = password;
            }

            const saveButton = document.getElementById('saveStaffBtn');
            saveButton.disabled = true;

            callApi(requestData)
                .then(data => {
                    // APIが成功を明示していない場合は画面を更新しない
                    if (!data || data.success !== true) {
                        renderResult({
                            success: false,
                            message: data?.message ||
                                '登録・更新に失敗しました。画面の情報は変更していません。'
                        });
                        return;
                    }

                    // API成功後にだけローカル状態を更新
                    if (isEditing) {
                        staffs = staffs.map(staff =>
                            staff.staffId === originalStaffId
                                ? {
                                    staffId,
                                    name,
                                    role,
                                    storeIds
                                }
                                : staff
                        );
                    } else {
                        staffs.push({
                            staffId,
                            name,
                            role,
                            storeIds
                        });
                    }

                    renderStaffs();
                    resetForm();

                    renderResult({
                        success: true,
                        message: data.message || (
                            isEditing
                                ? 'スタッフ情報を更新しました。'
                                : 'スタッフを登録しました。'
                        ),
                        data: data.data
                    });
                })
                .catch(error => {
                    console.error('Error:', error);

                    renderResult({
                        success: false,
                        message: error.message ||
                            '通信エラーが発生しました。画面の情報は変更していません。'
                    });
                })
                .finally(() => {
                    saveButton.disabled = false;
                });
        });

        // キャンセル
        document.getElementById('cancelBtn').addEventListener('click', resetForm);

        // スタッフ削除
        document.getElementById('deleteForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const staffId =
                document.getElementById('deleteStaffId').value.trim();

            if (!staffId) {
                renderResult({
                    success: false,
                    message: '削除するスタッフIDを入力してください。'
                });
                return;
            }

            const staff = staffs.find(item => item.staffId === staffId);

            if (!staff) {
                renderResult({
                    success: false,
                    message: '指定されたスタッフが見つかりません。'
                });
                return;
            }

            if (!confirm(`スタッフ「${staff.name}」を削除しますか？`)) {
                return;
            }

            const deleteButton = document.getElementById('deleteStaffBtn');
            deleteButton.disabled = true;

            callApi({
                action: 'delete_staff',
                staffId
            })
            .then(data => {
                // APIが成功を明示していない場合は削除しない
                if (!data || data.success !== true) {
                    renderResult({
                        success: false,
                        message: data?.message ||
                            'スタッフの削除に失敗しました。画面の情報は変更していません。'
                    });
                    return;
                }

                // API成功後にだけローカル状態から削除
                staffs = staffs.filter(item => item.staffId !== staffId);

                renderStaffs();

                document.getElementById('deleteForm').reset();

                // 削除したスタッフを編集中だった場合はフォームをリセット
                if (
                    document.getElementById('originalStaffId').value === staffId
                ) {
                    resetForm();
                }

                renderResult({
                    success: true,
                    message: data.message || 'スタッフを削除しました。',
                    data: data.data
                });
            })
            .catch(error => {
                console.error('Error:', error);

                renderResult({
                    success: false,
                    message: error.message ||
                        '通信エラーが発生しました。画面の情報は変更していません。'
                });
            })
            .finally(() => {
                deleteButton.disabled = false;
            });
        });

        // 初期表示
        setPasswordRequirement(false);
        renderStoreCheckboxes();
        renderStaffs();
    </script>

    <!-- フッター読み込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

    <!-- CSRFトークンの自動埋め込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>

</body>
</html>