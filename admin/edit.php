<?php

require_once dirname(__DIR__) . '/includes/functions.php';

admin_require_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = $id > 0 ? find_live_entry($id) : null;

if ($id > 0 && (!$item || $item['deleted_at'] !== null)) {
    redirect_to(app_path('admin/index.php'));
}

$errors = array();
$formData = array(
    'live_pict' => $item ? $item['live_pict'] : '',
    'body_html' => $item ? $item['body_html'] : default_live_body_html()
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $livePict = isset($_POST['live_pict']) ? trim($_POST['live_pict']) : '';
    $bodyHtml = isset($_POST['body_html']) ? trim($_POST['body_html']) : '';
    $csrfToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    $uploadResult = save_uploaded_live_image(isset($_FILES['live_pict_file']) ? $_FILES['live_pict_file'] : array());

    if ($uploadResult['success'] && $uploadResult['file_name'] !== '') {
        $livePict = $uploadResult['file_name'];
    }

    $formData['live_pict'] = $livePict;
    $formData['body_html'] = $bodyHtml;

    if (!verify_csrf_token($csrfToken)) {
        $errors[] = '不正なリクエストです。';
    }

    if (!$uploadResult['success']) {
        $errors[] = $uploadResult['error'];
    }

    if ($bodyHtml === '') {
        $errors[] = 'body_html は必須です。';
    }

    if (empty($errors)) {
        if ($postedId > 0) {
            update_live_entry($postedId, $livePict, $bodyHtml);
        } else {
            insert_live_entry($livePict, $bodyHtml);
        }

        redirect_to(app_path('admin/index.php'));
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>moreru live editor</title>
    <style>
        body {
            margin: 0;
            padding: 24px;
            background: #f7f7f7;
            color: #111;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="text"],
        textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #bbb;
            background: #fff;
        }

        textarea {
            min-height: 420px;
            font-family: Consolas, monospace;
            line-height: 1.5;
        }

        .help {
            margin-bottom: 16px;
            padding: 16px;
            background: #fff;
            border-left: 4px solid #111;
            white-space: pre-wrap;
            font-family: Consolas, monospace;
            font-size: 13px;
        }

        .upload-dropzone {
            margin-bottom: 12px;
            padding: 24px 16px;
            border: 2px dashed #777;
            background: #fff;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease;
        }

        .upload-dropzone.is-dragover {
            border-color: #111;
            background: #ececec;
        }

        .upload-dropzone strong {
            display: block;
            margin-bottom: 8px;
        }

        .upload-dropzone span {
            display: block;
            font-size: 13px;
            color: #555;
        }

        .upload-dropzone.has-file {
            border-style: solid;
        }

        .upload-dropzone.has-error {
            border-color: #c62828;
            background: #fff1f1;
        }

        .upload-error {
            margin-bottom: 16px;
            color: #c62828;
            font-size: 13px;
        }

        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }

        .actions {
            display: flex;
            gap: 12px;
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

        .error {
            margin-bottom: 12px;
            color: #c62828;
        }

        .preview {
            margin-top: 24px;
            padding: 16px;
            background: #fff;
            border: 1px solid #ddd;
        }

        .preview img {
            max-width: 300px;
            height: auto;
            display: block;
            margin-bottom: 16px;
        }

        .preview video {
            max-width: 300px;
            height: auto;
            display: block;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $id > 0 ? 'Live Schedule Edit' : 'Live Schedule Create'; ?></h1>

        <?php foreach ($errors as $error): ?>
            <div class="error"><?php echo h($error); ?></div>
        <?php endforeach; ?>

        <div class="help"><?php echo h(default_live_body_html()); ?></div>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo (int) $id; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">

            <!--<label for="live_pict">live_pict</label>
            <input id="live_pict" type="text" name="live_pict" value="<?php //echo h($formData['live_pict']); ?>" placeholder="例：224222.jpg">
            <div class="help">画像はファイル名だけを入力してください。image フォルダは自動で補われます。例: 223708.jpg</div>-->
            <label for="live_pict_file">画像・動画アップロード</label>
            <label class="upload-dropzone" for="live_pict_file" id="uploadDropzone">
                <strong id="uploadDropzoneTitle">ここに画像または mp4 ファイルをドラッグ</strong>
                <span id="uploadDropzoneText">またはクリックしてファイルを選択してください</span>
            </label>
            <input class="visually-hidden" id="live_pict_file" type="file" name="live_pict_file" accept=".jpg,.jpeg,.png,.gif,.webp,.mp4">
            <div class="upload-error" id="uploadError"></div>
            <div class="help">画像または mp4 をアップロードした場合は、そのファイル名が live_pict に自動反映されます。同名ファイルが既にある場合は 223708_1.jpg のように自動でリネームして保存します。</div>

            <label for="body_html">body_html</label>
            <div class="help"><?php echo h("Tickets の書き方サンプル\n\n通常の URL を開く場合:\nTickets:\n    <a href=\"https://eplus.jp/thebercedesmenz/\" target=\"_blank\">https://eplus.jp/thebercedesmenz/</a>\n\nメールアドレスをクリックしてメーラーを起動する場合:\nTickets:\n    <a href=\"mailto:termination616@gmail.com\">termination616@gmail.com</a>\n\nメールアドレスをそのまま href に入れるのではなく、必ず先頭に mailto: を付けてください。"); ?></div>
            <textarea id="body_html" name="body_html"><?php echo h($formData['body_html']); ?></textarea>

            <div class="actions">
                <button type="submit">保存</button>
                <a class="button button-secondary" href="<?php echo h(app_path('admin/index.php')); ?>">戻る</a>
            </div>
        </form>

        <div class="preview">
            <h2>Preview</h2>
            <?php if ($formData['live_pict'] !== ''): ?>
                <?php echo render_live_media($formData['live_pict'], '', 'preview media'); ?>
            <?php endif; ?>
            <?php echo $formData['body_html']; ?>
        </div>
    </div>
    <script>
        (function () {
            var fileInput = document.getElementById('live_pict_file');
            var dropzone = document.getElementById('uploadDropzone');
            var dropzoneTitle = document.getElementById('uploadDropzoneTitle');
            var dropzoneText = document.getElementById('uploadDropzoneText');
            var uploadError = document.getElementById('uploadError');

            if (!fileInput || !dropzone || !dropzoneTitle || !dropzoneText || !uploadError) {
                return;
            }

            function isAllowedImageFile(file) {
                var allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4'];
                var fileName = file && file.name ? file.name.toLowerCase() : '';
                var extension = fileName.indexOf('.') !== -1 ? fileName.split('.').pop() : '';
                var mimeType = file && file.type ? file.type.toLowerCase() : '';

                if (mimeType.indexOf('image/') === 0 || mimeType === 'video/mp4') {
                    return true;
                }

                return allowedExtensions.indexOf(extension) !== -1;
            }

            function setUploadError(message) {
                uploadError.textContent = message;

                if (message) {
                    dropzone.classList.add('has-error');
                    return;
                }

                dropzone.classList.remove('has-error');
            }

            function resetFileInput() {
                fileInput.value = '';
                dropzone.classList.remove('has-file');
                dropzoneTitle.textContent = 'ここに画像または mp4 ファイルをドラッグ';
                dropzoneText.textContent = 'またはクリックしてファイルを選択してください';
            }

            function updateDropzoneText() {
                if (fileInput.files && fileInput.files.length > 0) {
                    if (!isAllowedImageFile(fileInput.files[0])) {
                        setUploadError('jpg, jpeg, png, gif, webp, mp4 以外はアップロードできません。');
                        resetFileInput();
                        return;
                    }

                    setUploadError('');
                    dropzone.classList.add('has-file');
                    dropzoneTitle.textContent = '選択中: ' + fileInput.files[0].name;
                    dropzoneText.textContent = 'このまま保存するとアップロードされます';
                    return;
                }

                setUploadError('');
                dropzone.classList.remove('has-file');
                dropzoneTitle.textContent = 'ここに画像または mp4 ファイルをドラッグ';
                dropzoneText.textContent = 'またはクリックしてファイルを選択してください';
            }

            fileInput.addEventListener('change', updateDropzoneText);

            dropzone.addEventListener('dragenter', function (event) {
                event.preventDefault();
                dropzone.classList.add('is-dragover');
            });

            dropzone.addEventListener('dragover', function (event) {
                event.preventDefault();
                dropzone.classList.add('is-dragover');
            });

            dropzone.addEventListener('dragleave', function (event) {
                if (event.target === dropzone) {
                    dropzone.classList.remove('is-dragover');
                }
            });

            dropzone.addEventListener('drop', function (event) {
                event.preventDefault();
                dropzone.classList.remove('is-dragover');

                if (event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files.length > 0) {
                    if (!isAllowedImageFile(event.dataTransfer.files[0])) {
                        setUploadError('jpg, jpeg, png, gif, webp, mp4 以外はアップロードできません。');
                        resetFileInput();
                        return;
                    }

                    setUploadError('');
                    fileInput.files = event.dataTransfer.files;
                    updateDropzoneText();
                }
            });
        })();
    </script>
</body>
</html>