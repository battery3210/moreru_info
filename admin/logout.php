<?php

require_once dirname(__DIR__) . '/includes/functions.php';

admin_logout();
redirect_to(app_path('admin/login.php'));