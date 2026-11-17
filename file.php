<?php

require_once __DIR__ . '/app/helpers.php';
$user = require_login();

if (isset($_GET['id'])) {
    $statement = db()->prepare('SELECT p.evidence, c.userId FROM Payment p JOIN Contract c ON c.contractId = p.contractId WHERE p.paymentId = ?');
    $statement->execute([(string) $_GET['id']]);
    $file = $statement->fetch();
    if (!$file || ($user['role'] !== 'Admin' && $file['userId'] !== $user['userId'])) {
        http_response_code(404);
        exit('ไม่พบไฟล์');
    }
    $path = __DIR__ . '/uploads/payments/' . basename((string) $file['evidence']);
} elseif (isset($_GET['after'])) {
    $statement = db()->prepare('SELECT completionImage AS image, userId, maintenanceUserId FROM Repair WHERE repairId = ?');
    $statement->execute([(string) $_GET['after']]);
    $file = $statement->fetch();
    $authorized = $file && ($user['role'] === 'Admin' || $user['role'] === 'Staff' || $file['userId'] === $user['userId'] || ($user['role'] === 'Technician' && $file['maintenanceUserId'] === $user['userId']));
    if (!$authorized) {
        http_response_code(404);
        exit('ไม่พบไฟล์');
    }
    $path = __DIR__ . '/uploads/repairs/' . basename((string) $file['image']);
} elseif (isset($_GET['repair'])) {
    $statement = db()->prepare('SELECT image, userId, maintenanceUserId FROM Repair WHERE repairId = ?');
    $statement->execute([(string) $_GET['repair']]);
    $file = $statement->fetch();
    $authorized = $file && ($user['role'] === 'Admin' || $user['role'] === 'Staff' || $file['userId'] === $user['userId'] || ($user['role'] === 'Technician' && $file['maintenanceUserId'] === $user['userId']));
    if (!$authorized) {
        http_response_code(404);
        exit('ไม่พบไฟล์');
    }
    $path = __DIR__ . '/uploads/repairs/' . basename((string) $file['image']);
} elseif (isset($_GET['news'])) {
    $statement = db()->prepare('SELECT image FROM News WHERE newsId = ?');
    $statement->execute([(string) $_GET['news']]);
    $file = $statement->fetch();
    if (!$file) {
        http_response_code(404);
        exit('ไม่พบไฟล์');
    }
    $path = __DIR__ . '/uploads/news/' . basename((string) $file['image']);
} elseif (isset($_GET['profile'])) {
    $statement = db()->prepare('SELECT profileImage FROM `User` WHERE userId = ?');
    $statement->execute([(string) $_GET['profile']]);
    $file = $statement->fetch();
    if (!$file || ($user['role'] !== 'Admin' && (string) $_GET['profile'] !== $user['userId'])) {
        http_response_code(404);
        exit('ไม่พบไฟล์');
    }
    $path = __DIR__ . '/uploads/profiles/' . basename((string) $file['profileImage']);
} else {
    http_response_code(404);
    exit('ไม่พบไฟล์');
}

if (!$file || empty($file['evidence'] ?? $file['image'] ?? $file['profileImage'] ?? '') || !is_file($path)) {
    http_response_code(404);
    exit('ไม่พบไฟล์');
}
$mime = mime_content_type($path) ?: 'application/octet-stream';
if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
    http_response_code(415);
    exit('ชนิดไฟล์ไม่รองรับ');
}
header('Content-Type: ' . $mime);
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: inline; filename="' . basename($path) . '"');
readfile($path);
