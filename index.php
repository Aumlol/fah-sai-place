<?php

require_once __DIR__ . '/app/helpers.php';

$page = (string) ($_GET['page'] ?? 'dashboard');
$publicPages = ['login', 'setup'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/app/actions.php';
}

if (!in_array($page, $publicPages, true) && $page !== 'invoice_meter') {
    require_login();
}

if ($page === 'invoice_meter') {
    header('Content-Type: application/json; charset=utf-8');
    $user = current_user();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'กรุณาเข้าสู่ระบบใหม่'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (!in_array($user['role'], ['Admin', 'Staff'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'ไม่มีสิทธิ์ดูข้อมูลมิเตอร์'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $contractId = trim((string) ($_GET['contractId'] ?? ''));
    $billMonth = normalize_billing_month((string) ($_GET['billMonth'] ?? ''));
    if ($contractId === '' || $billMonth === null) {
        http_response_code(400);
        echo json_encode(['error' => 'กรุณาเลือกห้องและระบุรอบบิลรูปแบบ MM/YYYY ให้ถูกต้อง'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $billMonthVariants = billing_month_variants($billMonth);

    try {
        $statement = db()->prepare(
            "SELECT c.contractId, u.name, r.roomNumber, r.rentalPrice,
                    m.waterUnit, m.electricUnit,
                    EXISTS(SELECT 1 FROM Invoice i WHERE i.contractId = c.contractId AND i.billMonth IN (?, ?)) AS invoiceExists
             FROM Contract c
             JOIN `User` u ON u.userId = c.userId
             JOIN Room r ON r.roomId = c.roomId
             LEFT JOIN MeterRecord m ON m.roomId = c.roomId AND m.month IN (?, ?)
             WHERE c.contractId = ? AND c.status = 'ใช้งานอยู่'
             LIMIT 1"
        );
        $statement->execute([
            $billMonthVariants[0],
            $billMonthVariants[1],
            $billMonthVariants[0],
            $billMonthVariants[1],
            $contractId,
        ]);
        $invoice = $statement->fetch();
    } catch (PDOException $exception) {
        $reference = log_database_exception($exception);
        http_response_code(500);
        echo json_encode(
            ['error' => "โหลดข้อมูลมิเตอร์ไม่สำเร็จ (รหัสตรวจสอบ: {$reference})"],
            JSON_UNESCAPED_UNICODE
        );
        exit;
    }
    if (!$invoice) {
        http_response_code(404);
        echo json_encode(['error' => 'ไม่พบสัญญาเช่าที่กำลังใช้งานของห้องนี้'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $invoice['meterAvailable'] = $invoice['waterUnit'] !== null && $invoice['electricUnit'] !== null;
    $invoice['invoiceExists'] = (bool) $invoice['invoiceExists'];
    $invoice['waterRate'] = WATER_RATE;
    $invoice['electricRate'] = ELECTRIC_RATE;
    echo json_encode($invoice, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
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
