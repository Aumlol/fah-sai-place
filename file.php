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
} elseif (isset($_GET['work'])) {
    $statement = db()->prepare('SELECT workImage AS image, userId, maintenanceUserId FROM Repair WHERE repairId = ?');
    $statement->execute([(string) $_GET['work']]);
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
} elseif (isset($_GET['contract'], $_GET['type'])) {
    $documentColumns = [
        'signed' => 'tenantSignature',
        'id_card' => 'idCardDoc',
        'co_id_card' => 'coIdCardDoc',
    ];
    $documentType = (string) $_GET['type'];
    if (!isset($documentColumns[$documentType])) {
        http_response_code(404);
        exit('ไม่พบไฟล์');
    }

    $column = $documentColumns[$documentType];
    $statement = db()->prepare("SELECT c.`{$column}` AS document, c.userId, c.coTenantUserId FROM Contract c WHERE c.contractId = ?");
    $statement->execute([(string) $_GET['contract']]);
    $file = $statement->fetch();
    $authorized = $file && (
        in_array($user['role'], ['Admin', 'Staff'], true)
        || $file['userId'] === $user['userId']
        || $file['coTenantUserId'] === $user['userId']
    );
    if (!$authorized) {
        http_response_code(404);
        exit('ไม่พบไฟล์');
    }
    $path = __DIR__ . '/uploads/' . ($documentType === 'signed' ? 'contracts/' : 'id_cards/') . basename((string) $file['document']);
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

if (!$file || empty($file['evidence'] ?? $file['image'] ?? $file['profileImage'] ?? $file['document'] ?? '') || !is_file($path)) {
    http_response_code(404);
    exit('ไม่พบไฟล์');
}
$mime = mime_content_type($path) ?: 'application/octet-stream';
if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'], true)) {
    http_response_code(415);
    exit('ชนิดไฟล์ไม่รองรับ');
}

// หากไม่ได้เรียกผ่านแท็ก <img> (raw=1) ให้แสดงหน้าเว็บที่มีปุ่มปิดหน้าต่าง
if (!isset($_GET['raw'])) {
    $queryParams = $_GET;
    $queryParams['raw'] = '1';
    $rawUrl = 'file.php?' . http_build_query($queryParams);
    ?>
    <!doctype html>
    <html lang="th">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>หลักฐานรูปภาพ | <?= e(APP_NAME) ?></title>
      <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
          background: #0f172a;
          color: #f8fafc;
          font-family: system-ui, -apple-system, sans-serif;
          min-height: 100vh;
          display: flex;
          flex-direction: column;
        }
        .viewer-bar {
          position: sticky;
          top: 0;
          z-index: 10;
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 12px 20px;
          background: rgba(15, 23, 42, 0.92);
          backdrop-filter: blur(8px);
          border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .viewer-title { font-size: 15px; font-weight: 600; }
        .viewer-actions { display: flex; gap: 10px; }
        .btn-close {
          background: #ef4444;
          color: #fff;
          border: none;
          border-radius: 8px;
          padding: 8px 16px;
          font-size: 14px;
          font-weight: 600;
          cursor: pointer;
          text-decoration: none;
          display: inline-flex;
          align-items: center;
          gap: 6px;
        }
        .btn-close:hover { background: #dc2626; }
        .viewer-body {
          flex: 1;
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 20px;
        }
        .viewer-body img, .viewer-body iframe {
          max-width: 100%;
          max-height: calc(100vh - 90px);
          border-radius: 10px;
          box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
          object-fit: contain;
        }
        .viewer-body iframe { width: 100%; height: calc(100vh - 90px); border: 0; background: #fff; }
      </style>
    </head>
    <body>
      <header class="viewer-bar">
        <span class="viewer-title">หลักฐานรูปภาพ</span>
        <div class="viewer-actions">
          <button type="button" class="btn-close" onclick="closeViewer()">✕ ปิดหน้านี้</button>
        </div>
      </header>
      <main class="viewer-body">
        <?php if ($mime === 'application/pdf'): ?>
          <iframe src="<?= e($rawUrl) ?>" title="เอกสารที่บันทึก"></iframe>
        <?php else: ?>
          <img src="<?= e($rawUrl) ?>" alt="หลักฐานรูปภาพ">
        <?php endif; ?>
      </main>
      <script>
        function closeViewer() {
          window.close();
          setTimeout(() => {
            if (window.history.length > 1) {
              window.history.back();
            } else {
              window.location.href = 'index.php?page=payments';
            }
          }, 150);
        }
      </script>
    </body>
    </html>
    <?php
    exit;
}

header('Content-Type: ' . $mime);
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: inline; filename="' . basename($path) . '"');
readfile($path);