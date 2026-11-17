<?php

require_once __DIR__ . '/app/helpers.php';

$page = (string) ($_GET['page'] ?? 'dashboard');
$publicPages = ['login', 'setup'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/app/actions.php';
}

if (!in_array($page, $publicPages, true)) {
    require_login();
}

$flashMessage = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($page === 'login') {
    require __DIR__ . '/views/login.php';
    exit;
}

if ($page === 'setup') {
    require __DIR__ . '/views/setup.php';
    exit;
}

require __DIR__ . '/views/layout.php';
