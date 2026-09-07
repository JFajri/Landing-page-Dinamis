<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    doLogout();
}

redirect('admin/login.php');
