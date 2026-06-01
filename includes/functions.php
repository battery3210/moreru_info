<?php

require_once dirname(__FILE__) . '/bootstrap.php';

function moreru_config($section, $key = null)
{
    $config = isset($GLOBALS['moreru_config']) ? $GLOBALS['moreru_config'] : array();

    if (!isset($config[$section])) {
        return null;
    }

    if ($key === null) {
        return $config[$section];
    }

    return isset($config[$section][$key]) ? $config[$section][$key] : null;
}

function db_connection()
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $db = moreru_config('db');

    $connection = @new mysqli(
        $db['host'],
        $db['username'],
        $db['password'],
        $db['database'],
        (int) $db['port']
    );

    if ($connection->connect_errno) {
        die('Database connection failed: ' . $connection->connect_error);
    }

    if (!empty($db['charset'])) {
        $connection->set_charset($db['charset']);
    }

    return $connection;
}

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect_to($path)
{
    header('Location: ' . $path);
    exit;
}

function app_path($path)
{
    $basePath = trim((string) moreru_config('app', 'base_path'));
    $path = ltrim((string) $path, '/');

    if ($basePath === '') {
        return '/' . $path;
    }

    return '/' . trim($basePath, '/') . '/' . $path;
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(function_exists('random_bytes') ? random_bytes(16) : openssl_random_pseudo_bytes(16));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

function admin_is_logged_in()
{
    return !empty($_SESSION['admin_logged_in']);
}

function admin_require_login()
{
    if (!admin_is_logged_in()) {
        redirect_to(app_path('admin/login.php'));
    }
}

function admin_attempt_login($username, $password)
{
    $admin = moreru_config('admin');

    if ($username !== $admin['username']) {
        return false;
    }

    if (!password_verify($password, $admin['password_hash'])) {
        return false;
    }

    $_SESSION['admin_logged_in'] = true;

    return true;
}

function admin_logout()
{
    unset($_SESSION['admin_logged_in']);
}

function default_live_body_html()
{
    return '<div class="live-overview">' . "\n"
        . '    2026/7/26 (Sun)<br>' . "\n"
        . '    @ VENUE NAME<br><br>' . "\n"
        . '' . "\n"
        . '    "EVENT TITLE"<br>' . "\n"
        . '</div>' . "\n"
        . '<div class="live-detail">' . "\n"
        . '    <div class="live-detail__label">act -</div>' . "\n"
        . '    <div class="live-detail__acts">' . "\n"
        . '        moreru<br>' . "\n"
        . '        GUEST 1<br>' . "\n"
        . '        GUEST 2<br><br>' . "\n"
        . '' . "\n"
        . '        OPEN - 17:00 / START - 17:30<br>' . "\n"
        . '        ADV - 4000yen+1D<br><br>' . "\n"
        . '    </div>' . "\n"
        . '    Tickets:<br>' . "\n"
        . '    <a href="https://example.com" target="_blank">https://example.com</a><br>' . "\n"
        . '    <!-- メール受付にしたい場合 -->' . "\n"
        . '    <!-- <a href="mailto:info@example.com">info@example.com</a> -->' . "\n"
        . '</div>';
}

function fetch_live_entries($includeDeleted, $limit = null, $offset = 0)
{
    $sql = 'SELECT id, sort_order, live_pict, body_html, created_at, updated_at, deleted_at FROM live_schedules';

    if (!$includeDeleted) {
        $sql .= ' WHERE deleted_at IS NULL';
    }

    $sql .= ' ORDER BY sort_order DESC, id DESC';

    if ($limit !== null) {
        $limit = max(1, (int) $limit);
        $offset = max(0, (int) $offset);
        $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
    }

    $result = db_connection()->query($sql);
    $items = array();

    if ($result instanceof mysqli_result) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        $result->free();
    }

    return $items;
}

function count_live_entries($includeDeleted)
{
    $sql = 'SELECT COUNT(*) AS total_count FROM live_schedules';

    if (!$includeDeleted) {
        $sql .= ' WHERE deleted_at IS NULL';
    }

    $result = db_connection()->query($sql);

    if ($result instanceof mysqli_result) {
        $row = $result->fetch_assoc();
        $result->free();

        return isset($row['total_count']) ? (int) $row['total_count'] : 0;
    }

    return 0;
}

function get_setting($settingKey, $defaultValue)
{
    $statement = db_connection()->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1');

    if (!$statement) {
        return $defaultValue;
    }

    $statement->bind_param('s', $settingKey);
    $statement->execute();
    $statement->bind_result($settingValue);

    $value = $defaultValue;

    if ($statement->fetch()) {
        $value = $settingValue;
    }

    $statement->close();

    return $value;
}

function set_setting($settingKey, $settingValue)
{
    $now = date('Y-m-d H:i:s');
    $statement = db_connection()->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, created_at, updated_at) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = VALUES(updated_at)'
    );

    if (!$statement) {
        return false;
    }

    $statement->bind_param('ssss', $settingKey, $settingValue, $now, $now);
    $success = $statement->execute();
    $statement->close();

    return $success;
}

function get_live_schedule_per_page()
{
    $value = (int) get_setting('live_schedule_per_page', '10');

    if ($value < 1) {
        return 10;
    }

    if ($value > 100) {
        return 100;
    }

    return $value;
}

function build_page_url($page)
{
    return 'live_schedule.php?page=' . max(1, (int) $page);
}

function get_live_media_extension($fileName)
{
    return strtolower(pathinfo((string) $fileName, PATHINFO_EXTENSION));
}

function get_next_live_sort_order()
{
    $result = db_connection()->query('SELECT MAX(sort_order) AS max_sort_order FROM live_schedules');

    if ($result instanceof mysqli_result) {
        $row = $result->fetch_assoc();
        $result->free();

        if (isset($row['max_sort_order'])) {
            return (int) $row['max_sort_order'] + 1;
        }
    }

    return 1;
}

function is_live_video_file($fileName)
{
    return get_live_media_extension($fileName) === 'mp4';
}

function is_allowed_live_media_extension($extension)
{
    return in_array(strtolower((string) $extension), array('jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4'), true);
}

function live_media_url($fileName)
{
    return app_path('image/' . ltrim((string) $fileName, '/\\'));
}

function render_live_media($fileName, $className, $posterAlt)
{
    $fileName = trim((string) $fileName);

    if ($fileName === '') {
        return '';
    }

    $classAttribute = $className !== '' ? ' class="' . h($className) . '"' : '';
    $mediaUrl = h(live_media_url($fileName));

    if (is_live_video_file($fileName)) {
        return '<video' . $classAttribute . ' autoplay muted loop playsinline preload="auto">'
            . '<source src="' . $mediaUrl . '" type="video/mp4">'
            . '</video>';
    }

    return '<img' . $classAttribute . ' src="' . $mediaUrl . '" alt="' . h($posterAlt) . '">';
}

function normalize_uploaded_image_name($fileName)
{
    $fileName = basename((string) $fileName);
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $baseName = pathinfo($fileName, PATHINFO_FILENAME);

    $baseName = preg_replace('/[^A-Za-z0-9_-]+/', '_', $baseName);
    $baseName = trim($baseName, '_');

    if ($baseName === '') {
        $baseName = 'image';
    }

    return $baseName . ($extension !== '' ? '.' . $extension : '');
}

function save_uploaded_live_image($file)
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return array(
            'success' => true,
            'file_name' => '',
            'error' => ''
        );
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return array(
            'success' => false,
            'file_name' => '',
            'error' => '画像アップロードに失敗しました。'
        );
    }

    $originalName = isset($file['name']) ? $file['name'] : '';
    $normalizedName = normalize_uploaded_image_name($originalName);
    $extension = get_live_media_extension($normalizedName);

    if (!is_allowed_live_media_extension($extension)) {
        return array(
            'success' => false,
            'file_name' => '',
            'error' => 'jpg, jpeg, png, gif, webp, mp4 のみアップロードできます。'
        );
    }

    if (!is_live_video_file($normalizedName)) {
        $imageInfo = @getimagesize($file['tmp_name']);

        if ($imageInfo === false) {
            return array(
                'success' => false,
                'file_name' => '',
                'error' => '画像ファイルまたは mp4 ファイルを選択してください。'
            );
        }
    }

    $imageDirectory = dirname(__DIR__) . '/image';

    if (!is_dir($imageDirectory)) {
        return array(
            'success' => false,
            'file_name' => '',
            'error' => '画像保存先フォルダが見つかりません。'
        );
    }

    $baseName = pathinfo($normalizedName, PATHINFO_FILENAME);
    $candidateName = $normalizedName;
    $counter = 1;

    while (file_exists($imageDirectory . '/' . $candidateName)) {
        $candidateName = $baseName . '_' . $counter . '.' . $extension;
        $counter++;
    }

    if (!move_uploaded_file($file['tmp_name'], $imageDirectory . '/' . $candidateName)) {
        return array(
            'success' => false,
            'file_name' => '',
            'error' => '画像ファイルを保存できませんでした。'
        );
    }

    return array(
        'success' => true,
        'file_name' => $candidateName,
        'error' => ''
    );
}

function find_live_entry($id)
{
    $statement = db_connection()->prepare(
        'SELECT id, sort_order, live_pict, body_html, created_at, updated_at, deleted_at FROM live_schedules WHERE id = ? LIMIT 1'
    );

    $statement->bind_param('i', $id);
    $statement->execute();
    $statement->bind_result($rowId, $sortOrder, $livePict, $bodyHtml, $createdAt, $updatedAt, $deletedAt);

    $row = null;

    if ($statement->fetch()) {
        $row = array(
            'id' => $rowId,
            'sort_order' => $sortOrder,
            'live_pict' => $livePict,
            'body_html' => $bodyHtml,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'deleted_at' => $deletedAt
        );
    }

    $statement->close();

    return $row;
}

function insert_live_entry($livePict, $bodyHtml)
{
    $now = date('Y-m-d H:i:s');
    $sortOrder = get_next_live_sort_order();
    $statement = db_connection()->prepare(
        'INSERT INTO live_schedules (sort_order, live_pict, body_html, created_at, updated_at, deleted_at) VALUES (?, ?, ?, ?, ?, NULL)'
    );
    $statement->bind_param('issss', $sortOrder, $livePict, $bodyHtml, $now, $now);
    $success = $statement->execute();
    $statement->close();

    return $success;
}

function update_live_entry($id, $livePict, $bodyHtml)
{
    $now = date('Y-m-d H:i:s');
    $statement = db_connection()->prepare(
        'UPDATE live_schedules SET live_pict = ?, body_html = ?, updated_at = ? WHERE id = ? AND deleted_at IS NULL'
    );
    $statement->bind_param('sssi', $livePict, $bodyHtml, $now, $id);
    $success = $statement->execute();
    $statement->close();

    return $success;
}

function soft_delete_live_entry($id)
{
    $now = date('Y-m-d H:i:s');
    $statement = db_connection()->prepare(
        'UPDATE live_schedules SET deleted_at = ?, updated_at = ? WHERE id = ? AND deleted_at IS NULL'
    );
    $statement->bind_param('ssi', $now, $now, $id);
    $success = $statement->execute();
    $statement->close();

    return $success;
}

function update_live_entry_sort_orders($orderedIds)
{
    if (!is_array($orderedIds) || empty($orderedIds)) {
        return false;
    }

    $normalizedIds = array();

    foreach ($orderedIds as $orderedId) {
        $orderedId = (int) $orderedId;

        if ($orderedId > 0 && !in_array($orderedId, $normalizedIds, true)) {
            $normalizedIds[] = $orderedId;
        }
    }

    if (empty($normalizedIds)) {
        return false;
    }

    $result = db_connection()->query('SELECT id FROM live_schedules WHERE deleted_at IS NULL');
    $existingIds = array();

    if ($result instanceof mysqli_result) {
        while ($row = $result->fetch_assoc()) {
            $existingIds[] = (int) $row['id'];
        }

        $result->free();
    }

    sort($normalizedIds);
    sort($existingIds);

    if ($normalizedIds !== $existingIds) {
        return false;
    }

    $connection = db_connection();
    $statement = $connection->prepare('UPDATE live_schedules SET sort_order = ?, updated_at = ? WHERE id = ? AND deleted_at IS NULL');

    if (!$statement) {
        return false;
    }

    $connection->autocommit(false);
    $now = date('Y-m-d H:i:s');
    $success = true;

    $maxSortOrder = count($orderedIds);

    foreach ($orderedIds as $index => $orderedId) {
        $sortOrder = $maxSortOrder - $index;
        $rowId = (int) $orderedId;
        $statement->bind_param('isi', $sortOrder, $now, $rowId);

        if (!$statement->execute()) {
            $success = false;
            break;
        }
    }

    $statement->close();

    if ($success) {
        $connection->commit();
    } else {
        $connection->rollback();
    }

    $connection->autocommit(true);

    return $success;
}