<?php

require_once dirname(__DIR__) . '/includes/functions.php';

admin_require_login();

$settingErrors = array();
$reorderMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['settings_form'])) {
    $csrfToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    $perPageInput = isset($_POST['live_schedule_per_page']) ? (int) $_POST['live_schedule_per_page'] : 0;

    if (!verify_csrf_token($csrfToken)) {
        $settingErrors[] = '不正なリクエストです。';
    }

    if ($perPageInput < 1 || $perPageInput > 100) {
        $settingErrors[] = '1ページあたりの件数は 1 から 100 の間で入力してください。';
    }

    if (empty($settingErrors)) {
        set_setting('live_schedule_per_page', (string) $perPageInput);
        redirect_to(app_path('admin/index.php'));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reorder_form'])) {
    $csrfToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    $orderedIds = isset($_POST['ordered_ids']) ? $_POST['ordered_ids'] : array();

    if (!verify_csrf_token($csrfToken)) {
        $reorderMessage = '並び順の保存に失敗しました。ページを再読み込みしてからやり直してください。';
    } elseif (!update_live_entry_sort_orders($orderedIds)) {
        $reorderMessage = '並び順を保存できませんでした。';
    } else {
        redirect_to(app_path('admin/index.php?reordered=1'));
    }
}

$items = fetch_live_entries(false);
$liveSchedulePerPage = get_live_schedule_per_page();

if (isset($_GET['reordered']) && $_GET['reordered'] === '1') {
    $reorderMessage = '並び順を保存しました。';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>moreru live admin</title>
    <style>
        body {
            margin: 0;
            padding: 24px;
            background: #f2f2f2;
            color: #222;
            font-family: Arial, sans-serif;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .button,
        button {
            display: inline-block;
            padding: 10px 14px;
            border: 1px solid #111;
            background: #111;
            color: #fff;
            text-decoration: none;
            cursor: pointer;
        }

        .button-secondary {
            background: #fff;
            color: #111;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
            text-align: left;
        }

        img {
            max-width: 180px;
            height: auto;
            display: block;
        }

        video {
            max-width: 180px;
            height: auto;
            display: block;
        }

        .preview {
            max-width: 420px;
            max-height: 130px;
            overflow: hidden;
            color: #666;
            font-size: 13px;
            line-height: 1.5;
        }

        .actions form {
            display: inline-block;
            margin: 0;
        }

        .settings-box {
            margin-bottom: 24px;
            padding: 16px;
            background: #fff;
            border: 1px solid #ddd;
        }

        .settings-box h2 {
            margin-top: 0;
            margin-bottom: 12px;
        }

        .settings-box label {
            display: inline-block;
            margin-right: 12px;
        }

        .settings-box input[type="number"] {
            width: 100px;
            padding: 8px;
            margin-right: 12px;
        }

        .error {
            margin-bottom: 12px;
            color: #c62828;
        }

        .message {
            margin-bottom: 12px;
            color: #1b5e20;
        }

        .sort-help {
            margin-bottom: 16px;
            color: #555;
            font-size: 13px;
        }

        .drag-handle {
            width: 36px;
            text-align: center;
            cursor: move;
            color: #555;
            font-size: 20px;
            user-select: none;
        }

        .sortable-row.is-dragging {
            opacity: 0.45;
        }

        .sortable-row td {
            background: #fff;
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <h1>Live Schedule Admin</h1>
        <div>
            <a class="button button-secondary" href="<?php echo h(app_path('live_schedule.php')); ?>" target="_blank">公開ページを見る</a>
            <a class="button" href="<?php echo h(app_path('admin/edit.php')); ?>">新規追加</a>
            <a class="button button-secondary" href="<?php echo h(app_path('admin/logout.php')); ?>">ログアウト</a>
        </div>
    </div>

    <div class="settings-box">
        <h2>公開ページ設定</h2>
        <?php if ($reorderMessage !== ''): ?>
            <div class="<?php echo strpos($reorderMessage, '失敗') !== false ? 'error' : 'message'; ?>"><?php echo h($reorderMessage); ?></div>
        <?php endif; ?>
        <?php foreach ($settingErrors as $error): ?>
            <div class="error"><?php echo h($error); ?></div>
        <?php endforeach; ?>
        <form method="post">
            <input type="hidden" name="settings_form" value="1">
            <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
            <label for="live_schedule_per_page">1ページあたりの表示件数</label>
            <input id="live_schedule_per_page" type="number" name="live_schedule_per_page" min="1" max="100" value="<?php echo (int) $liveSchedulePerPage; ?>">
            <button type="submit">設定を保存</button>
        </form>
    </div>

    <div class="sort-help">一覧の行をドラッグすると、その順番で自動保存されます。</div>

    <form method="post" id="reorderForm">
        <input type="hidden" name="reorder_form" value="1">
        <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
        <div id="reorderInputs"></div>
    </form>

    <table>
        <thead>
            <tr>
                <th>Sort</th>
                <th>ID</th>
                <th>Image</th>
                <th>Body Preview</th>
                <th>Updated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="sortableTableBody">
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="6">データがありません。</td>
                </tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <tr class="sortable-row" data-live-id="<?php echo (int) $item['id']; ?>" draggable="true">
                        <td class="drag-handle" title="ドラッグして並び替え">&#8645;</td>
                        <td><?php echo (int) $item['id']; ?></td>
                        <td>
                            <?php if ($item['live_pict'] !== ''): ?><?php echo h($item['live_pict']); ?>
                                <?php echo render_live_media($item['live_pict'], '', 'live media'); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="preview"><?php echo h(strip_tags($item['body_html'])); ?></div>
                        </td>
                        <td><?php echo h($item['updated_at']); ?></td>
                        <td class="actions">
                            <a class="button button-secondary" href="<?php echo h(app_path('admin/edit.php')); ?>?id=<?php echo (int) $item['id']; ?>">編集</a>
                            <form method="post" action="<?php echo h(app_path('admin/delete.php')); ?>" onsubmit="return confirm('削除しますか？');">
                                <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
                                <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
                                <button type="submit">削除</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        (function () {
            var tbody = document.getElementById('sortableTableBody');
            var reorderForm = document.getElementById('reorderForm');
            var reorderInputs = document.getElementById('reorderInputs');
            var draggedRow = null;
            var dragStartOrder = '';
            var isSubmitting = false;

            if (!tbody || !reorderForm || !reorderInputs) {
                return;
            }

            function getRows() {
                return tbody.querySelectorAll('tr.sortable-row');
            }

            function serializeOrder() {
                var rows = getRows();
                var ids = [];
                var index = 0;

                for (index = 0; index < rows.length; index++) {
                    ids.push(rows[index].getAttribute('data-live-id'));
                }

                return ids.join(',');
            }

            function rebuildInputs() {
                var rows = getRows();
                var html = '';
                var index = 0;

                for (index = 0; index < rows.length; index++) {
                    html += '<input type="hidden" name="ordered_ids[]" value="' + rows[index].getAttribute('data-live-id') + '">';
                }

                reorderInputs.innerHTML = html;
            }

            function getDragAfterElement(container, clientY) {
                var rows = container.querySelectorAll('tr.sortable-row:not(.is-dragging)');
                var closest = null;
                var closestOffset = Number.NEGATIVE_INFINITY;
                var index = 0;

                for (index = 0; index < rows.length; index++) {
                    var box = rows[index].getBoundingClientRect();
                    var offset = clientY - box.top - (box.height / 2);

                    if (offset < 0 && offset > closestOffset) {
                        closestOffset = offset;
                        closest = rows[index];
                    }
                }

                return closest;
            }

            function submitOrderIfChanged() {
                var currentOrder = serializeOrder();

                if (isSubmitting || currentOrder === '' || currentOrder === dragStartOrder) {
                    return;
                }

                isSubmitting = true;
                rebuildInputs();
                reorderForm.submit();
            }

            tbody.addEventListener('dragstart', function (event) {
                var row = event.target.closest('tr.sortable-row');

                if (!row) {
                    return;
                }

                draggedRow = row;
                dragStartOrder = serializeOrder();
                row.classList.add('is-dragging');
                event.dataTransfer.effectAllowed = 'move';
            });

            tbody.addEventListener('dragover', function (event) {
                var afterElement;

                if (!draggedRow) {
                    return;
                }

                event.preventDefault();
                afterElement = getDragAfterElement(tbody, event.clientY);

                if (afterElement === null) {
                    tbody.appendChild(draggedRow);
                } else if (afterElement !== draggedRow) {
                    tbody.insertBefore(draggedRow, afterElement);
                }
            });

            tbody.addEventListener('dragend', function () {
                if (!draggedRow) {
                    return;
                }

                draggedRow.classList.remove('is-dragging');
                draggedRow = null;
                submitOrderIfChanged();
            });

            rebuildInputs();
        })();
    </script>
</body>
</html>