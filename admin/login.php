<?php

require_once dirname(__DIR__) . '/includes/functions.php';

if (admin_is_logged_in()) {
    redirect_to(app_path('admin/index.php'));
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';

    if (admin_attempt_login($username, $password)) {
        redirect_to(app_path('admin/index.php'));
    }

    $errorMessage = 'ログイン情報が正しくありません。';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>moreru live admin login</title>
    <style>
        body {
            margin: 0;
            padding: 32px 16px;
            background: #111;
            color: #f5f5f5;
            font-family: Arial, sans-serif;
        }

        .login-box {
            max-width: 420px;
            margin: 40px auto;
            padding: 24px;
            border: 1px solid #333;
            background: #1b1b1b;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 16px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #fff;
            border: none;
            cursor: pointer;
        }

        .error {
            margin-bottom: 16px;
            color: #ff7a7a;
        }

        .note {
            margin-top: 16px;
            color: #bdbdbd;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>moreru live admin</h1>
        <?php if ($errorMessage !== ''): ?>
            <div class="error"><?php echo h($errorMessage); ?></div>
        <?php endif; ?>
        <form method="post">
            <label for="username">Username</label>
            <input id="username" type="text" name="username" value="">

            <label for="password">Password</label>
            <input id="password" type="password" name="password" value="">

            <button type="submit">Login</button>
        </form>
        <div class="note">
            初期ユーザー名は admin、初期パスワードは moreru_local_admin です。運用前に config.php 側で変更してください。
        </div>
    </div>
</body>
</html>