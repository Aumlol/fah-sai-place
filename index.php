<?php

require_once __DIR__ . '/app/helpers.php';

$page = (string) ($_GET['page'] ?? 'dashboard');
$publicPages = ['login', 'setup'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/app/actions.php';
}

if (!in_array($page, $publicPages, true) && !in_array($page, ['invoice_meter', 'invoice_meter_all'], true)) {
    require_login();
}

if (in_array($page, ['invoice_meter', 'invoice_meter_all'], true)) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
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
    if ($page === 'invoice_meter' && $contractId === '') {
        http_response_code(400);
        echo json_encode(['error' => 'กรุณาเลือกห้องก่อนโหลดข้อมูลมิเตอร์'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($billMonth === null) {
        http_response_code(400);
        echo json_encode(['error' => 'กรุณาเลือกห้องและระบุรอบบิลรูปแบบ MM/YYYY ให้ถูกต้อง'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $billMonthVariants = billing_month_variants($billMonth);

    if ($page === 'invoice_meter_all') {
        try {
            $statement = db()->prepare(
                "SELECT c.contractId, CONCAT_WS(' / ', u.name, coTenant.name) AS name,
                        r.roomNumber, r.rentalPrice,
                        m.waterUnit, m.electricUnit,
                        EXISTS(SELECT 1 FROM Invoice i WHERE i.contractId = c.contractId AND i.billMonth IN (?, ?)) AS invoiceExists
                 FROM Room r
                 LEFT JOIN Contract c ON c.contractId = (
                     SELECT activeContract.contractId
                     FROM Contract activeContract
                     WHERE activeContract.roomId = r.roomId AND activeContract.status = 'ใช้งานอยู่'
                     ORDER BY activeContract.startDate DESC, activeContract.contractId DESC
                     LIMIT 1
                 )
                 LEFT JOIN `User` u ON u.userId = c.userId
                 LEFT JOIN `User` coTenant ON coTenant.userId = c.coTenantUserId
                 LEFT JOIN MeterRecord m ON m.roomId = r.roomId AND m.month IN (?, ?)
                 ORDER BY r.roomNumber"
            );
            $statement->execute([
                $billMonthVariants[0], $billMonthVariants[1],
                $billMonthVariants[0], $billMonthVariants[1],
            ]);
            $rows = $statement->fetchAll();
            foreach ($rows as &$row) {
                $row['meterAvailable'] = $row['waterUnit'] !== null && $row['electricUnit'] !== null;
                $row['invoiceExists'] = (bool) $row['invoiceExists'];
                $row['waterRate'] = WATER_RATE;
                $row['electricRate'] = ELECTRIC_RATE;
            }
            unset($row);
        } catch (PDOException $exception) {
            $reference = log_database_exception($exception);
            http_response_code(500);
            echo json_encode(['error' => "โหลดข้อมูลมิเตอร์ไม่สำเร็จ (รหัสตรวจสอบ: {$reference})"], JSON_UNESCAPED_UNICODE);
            exit;
        }
        echo json_encode(['rooms' => $rows], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }

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

    $invoice['meterFound'] = $invoice['waterUnit'] !== null && $invoice['electricUnit'] !== null;
    $invoice['meterAvailable'] = $invoice['meterFound'];
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
