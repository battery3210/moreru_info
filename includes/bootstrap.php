<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Tokyo');

$GLOBALS['moreru_config'] = require dirname(__DIR__) . '/config.php';