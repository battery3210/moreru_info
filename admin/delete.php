<?php

require_once dirname(__DIR__) . '/includes/functions.php';

admin_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_path('admin/index.php'));
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$csrfToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

if ($id > 0 && verify_csrf_token($csrfToken)) {
    soft_delete_live_entry($id);
}

redirect_to(app_path('admin/index.php'));