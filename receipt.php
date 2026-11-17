<?php

require_once __DIR__ . '/app/helpers.php';
$user = require_role(['Tenant', 'Admin']);
$paymentId = (string) ($_GET['id'] ?? '');
$statement = db()->prepare('SELECT p.paymentId, p.paymentDate, p.amount, p.status, c.contractId, c.userId, c.startDate, c.endDate, u.name, u.phone, r.roomNumber, i.billMonth, f.description AS fineDescription FROM Payment p JOIN Contract c ON c.contractId = p.contractId JOIN `User` u ON u.userId = c.userId JOIN Room r ON r.roomId = c.roomId LEFT JOIN Invoice i ON i.invoiceId = p.invoiceId LEFT JOIN Fine f ON f.fineId = p.fineId WHERE p.paymentId = ?');
$statement->execute([$paymentId]);
$receipt = $statement->fetch();
if (!$receipt || ($user['role'] === 'Tenant' && $receipt['userId'] !== $user['userId']) || $receipt['status'] !== 'อนุมัติ') {
    http_response_code(404);
    exit('ไม่พบใบเสร็จ');
}
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ใบเสร็จ <?= e($receipt['paymentId']) ?> | <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="receipt-page">
  <main class="receipt-sheet">
    <header class="receipt-header"><span class="brand-mark small"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="M5 15 16 6l11 9M9 14v11h14V14M7 28c3-2 5-2 9 0s6 2 9 0" /></svg></span><div><p class="eyebrow">FAH SAI PLACE</p><h1>ใบเสร็จรับเงิน</h1><p class="muted">หอพักฟ้าใสเพลส · อำเภอสันทราย จังหวัดเชียงใหม่</p></div><span class="receipt-stamp">ชำระแล้ว</span></header>
    <div class="receipt-meta"><div><small>เลขที่ใบเสร็จ</small><strong class="mono"><?= e($receipt['paymentId']) ?></strong></div><div><small>วันที่ชำระเงิน</small><strong><?= e(date('d/m/Y', strtotime($receipt['paymentDate']))) ?></strong></div></div>
    <section class="receipt-section"><h2>ได้รับเงินจาก</h2><div class="receipt-details"><div><small>ชื่อผู้เช่า</small><strong><?= e($receipt['name']) ?></strong></div><div><small>ห้องพัก</small><strong><?= e($receipt['roomNumber']) ?></strong></div><div><small>สัญญาเช่า</small><strong class="mono"><?= e($receipt['contractId']) ?></strong></div><div><small>โทรศัพท์</small><strong><?= e($receipt['phone']) ?></strong></div></div></section>
    <section class="receipt-section"><h2>รายการชำระ</h2><div class="receipt-line"><span><?php if ($receipt['billMonth']): ?>ใบแจ้งหนี้ประจำเดือน <?= e($receipt['billMonth']) ?><?php elseif ($receipt['fineDescription']): ?>ค่าปรับ: <?= e($receipt['fineDescription']) ?><?php else: ?>เงินชำระตามหลักฐานที่ได้รับอนุมัติ<?php endif; ?></span><strong><?= money($receipt['amount']) ?> บาท</strong></div><div class="receipt-total"><span>รวมทั้งสิ้น</span><strong><?= money($receipt['amount']) ?> บาท</strong></div><p class="receipt-words">สถานะการชำระ: <?= e($receipt['status']) ?></p></section>
    <p class="receipt-note">เอกสารนี้จัดทำจากข้อมูลการชำระเงินในระบบ ใช้เป็นหลักฐานรับเงินของรายการที่ผู้ดูแลอนุมัติแล้ว</p>
    <footer class="receipt-actions"><a class="button secondary" href="index.php?page=payments">กลับหน้าการชำระเงิน</a><button class="button primary" type="button" onclick="window.print()">พิมพ์ใบเสร็จ</button></footer>
  </main>
</body>
</html>
