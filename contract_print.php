<?php
foreach (['/app/bootstrap.php', '/app/init.php', '/app/helpers.php', '/config/database.php'] as $relPath) {
    if (is_file(__DIR__ . $relPath)) {
        require_once __DIR__ . $relPath;
    }
}
if (!function_exists('db') || !function_exists('require_login')) {
    if (is_file(__DIR__ . '/app/db.php')) {
        require_once __DIR__ . '/app/db.php';
    }
    if (is_file(__DIR__ . '/app/auth.php')) {
        require_once __DIR__ . '/app/auth.php';
    }
}
$user = require_login();
$canEditContract = in_array($user['role'], ['Admin', 'Staff'], true);

$contractId = trim((string) ($_GET['id'] ?? ''));
if ($contractId === '') {
    http_response_code(400);
    exit('ไม่พบรหัสสัญญาเช่า');
}

$stmt = db()->prepare("
    SELECT c.*,
           r.roomNumber, r.rentalPrice,
           u1.name AS mainName, u1.phone AS mainPhone, u1.email AS mainEmail,
           u1.birthDate AS mainBirthDate, u1.address AS mainAddress,
           u1.occupation AS mainOccupation, u1.workplace AS mainWorkplace,
           u1.emergencyContactName AS mainEmName, u1.emergencyContactRelation AS mainEmRelation, u1.emergencyContactPhone AS mainEmPhone,
           u2.name AS coName, u2.phone AS coPhone
    FROM Contract c
    JOIN Room r ON r.roomId = c.roomId
    JOIN `User` u1 ON u1.userId = c.userId
    LEFT JOIN `User` u2 ON u2.userId = c.coTenantUserId
    WHERE c.contractId = ?
    LIMIT 1
");
$stmt->execute([$contractId]);
$contract = $stmt->fetch();

if (!$contract) {
    http_response_code(404);
    exit('ไม่พบข้อมูลสัญญาเช่า');
}

if ($user['role'] === 'Tenant' && $contract['userId'] !== $user['userId'] && $contract['coTenantUserId'] !== $user['userId']) {
    http_response_code(403);
    exit('คุณไม่มีสิทธิ์เข้าถึงเอกสารสัญญานี้');
}

$ageText = '';
if (!empty($contract['mainBirthDate'])) {
    try {
        $dob = new DateTime($contract['mainBirthDate']);
        $ref = !empty($contract['startDate']) ? new DateTime($contract['startDate']) : new DateTime();
        $age = $dob->diff($ref)->y;
        $ageText = " (อายุ {$age} ปี)";
    } catch (Throwable $e) {
        $ageText = '';
    }
}

$hasCoTenant = !empty($contract['coTenantUserId']) && !empty($contract['coName']);
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>สัญญาเช่าห้องพัก <?= e($contract['roomNumber']) ?> (<?= e($contract['contractId']) ?>) | หอพักฟ้าใสเพลส</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700;800&display=swap');

    * {
      box-sizing: border-box;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    body {
      margin: 0;
      padding: 0;
      background: #e2e8f0;
      font-family: 'Sarabun', sans-serif;
      color: #0f172a;
      font-size: 13px;
      line-height: 1.48;
    }

    .print-toolbar {
      position: sticky;
      top: 0;
      z-index: 100;
      background: #0f172a;
      color: #ffffff;
      padding: 12px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
    }

    .print-toolbar-info {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 14px;
    }

    .print-toolbar-actions {
      display: flex;
      gap: 10px;
    }

    .contract-editor {
      width: min(1050px, calc(100% - 32px));
      margin: 16px auto;
      padding: 18px;
      background: #ffffff;
      border-radius: 10px;
      box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
    }

    .contract-editor h2 { margin: 0 0 4px; font-size: 16px; }
    .contract-editor p { margin: 0 0 12px; color: #64748b; font-size: 12px; }
    .contract-editor-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .contract-editor label { display: grid; gap: 4px; color: #475569; font-size: 11px; }
    .contract-editor input { min-width: 0; height: 34px; padding: 0 8px; border: 1px solid #cbd5e1; border-radius: 6px; color: #0f172a; font: inherit; }
    .contract-editor input:focus { outline: 2px solid #bfdbfe; border-color: #3b82f6; }

    @media screen and (max-width: 720px) {
      .print-toolbar { align-items: flex-start; flex-direction: column; gap: 10px; padding: 12px 16px; }
      .print-toolbar-info { align-items: flex-start; flex-direction: column; gap: 2px; }
      .contract-editor-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media screen and (max-width: 480px) {
      .contract-editor-grid { grid-template-columns: minmax(0, 1fr); }
    }

    .btn {
      border: none;
      border-radius: 8px;
      padding: 8px 16px;
      font-family: inherit;
      font-size: 13.5px;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .btn-primary {
      background: #2563eb;
      color: #ffffff;
    }

    .btn-primary:hover {
      background: #1d4ed8;
    }

    .btn-secondary {
      background: #334155;
      color: #f8fafc;
    }

    .btn-secondary:hover {
      background: #475569;
    }

    .a4-page {
      width: 210mm;
      height: 296mm;
      margin: 20px auto;
      background: #ffffff;
      padding: 11mm 14mm 12mm 14mm;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      overflow: hidden;
    }

    .doc-header {
      position: relative;
      text-align: center;
      border-bottom: 1.5px solid #0f172a;
      padding-bottom: 9px;
      margin-bottom: 10px;
    }

    .doc-meta-box {
      position: absolute;
      top: 0;
      right: 0;
      border: 1px solid #cbd5e1;
      background: #f8fafc;
      border-radius: 8px;
      padding: 4px 10px;
      text-align: right;
      font-size: 11px;
      line-height: 1.45;
    }

    .doc-meta-box strong {
      color: #0f172a;
    }

    .doc-logo-wrap {
      display: inline-flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .doc-logo-badge {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 4px;
    }

    .doc-logo-badge svg {
      width: 23px;
      height: 23px;
      stroke: #ffffff;
      fill: none;
      stroke-width: 2.1;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .doc-title {
      margin: 0;
      font-size: 17px;
      font-weight: 800;
      color: #0f172a;
    }

    .doc-subtitle {
      margin: 1px 0 0;
      font-size: 11px;
      color: #475569;
      font-weight: 500;
    }

    .intro-text {
      margin: 0 0 10px;
      text-indent: 2em;
      text-align: justify;
      font-size: 12.2px;
      line-height: 1.48;
    }

    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 12px;
      font-size: 11.8px;
    }

    .info-table td {
      border: 1px solid #cbd5e1;
      padding: 4.5px 8px;
      vertical-align: middle;
    }

    .info-table td.label-cell {
      background: #f1f5f9;
      font-weight: 700;
      color: #1e293b;
      width: 17%;
      white-space: nowrap;
    }

    .info-table td.val-cell {
      width: 33%;
      color: #0f172a;
    }

    .section-block {
      margin-bottom: 12px;
    }

    .section-bar {
      background: #f1f5f9;
      border-left: 4px solid #2563eb;
      padding: 4px 10px;
      font-weight: 800;
      font-size: 12.4px;
      color: #0f172a;
      margin: 0 0 7px;
    }

    .clause-item {
      margin: 0 0 5px;
      font-size: 11.9px;
      line-height: 1.48;
      text-align: justify;
    }

    .rules-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 11.4px;
    }

    .rules-table th {
      background: #f8fafc;
      color: #1e293b;
      font-weight: 700;
      border: 1px solid #cbd5e1;
      padding: 4.5px 8px;
      text-align: left;
    }

    .rules-table th.col-num,
    .rules-table td.col-num {
      width: 5%;
      text-align: center;
      font-weight: 700;
    }

    .rules-table th.col-rule,
    .rules-table td.col-rule {
      width: 68%;
    }

    .rules-table th.col-fine,
    .rules-table td.col-fine {
      width: 27%;
      text-align: right;
      white-space: nowrap;
    }

    .rules-table td {
      border: 1px solid #cbd5e1;
      padding: 4px 8px;
      vertical-align: middle;
      line-height: 1.38;
    }

    .rules-table tbody tr:nth-child(even) {
      background: #fcfdff;
    }

    .fine-text {
      color: #dc2626;
      font-weight: 700;
    }

    .signature-section {
      padding-top: 10px;
      border-top: 1px dashed #cbd5e1;
      page-break-inside: avoid;
      break-inside: avoid;
    }

    .ack-text {
      margin: 0 0 10px;
      font-size: 11.6px;
      color: #334155;
      text-align: center;
      line-height: 1.42;
    }

    .sig-grid {
      display: grid;
      grid-template-columns: <?= $hasCoTenant ? 'repeat(3, 1fr)' : 'repeat(2, 1fr)' ?>;
      gap: 14px;
    }

    .sig-box {
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      padding: 8px 10px;
      text-align: center;
      background: #ffffff;
    }

    /* เว้นพื้นที่ว่างสำหรับเซ็นปากกาจริง */
    .sig-write-space {
      height: 52px;
    }

    .sig-line {
      border-bottom: 1px dotted #475569;
      margin: 0 12px 5px;
    }

    .sig-role {
      font-weight: 700;
      font-size: 11.6px;
      color: #0f172a;
    }

    .sig-name {
      font-size: 11px;
      color: #475569;
    }

    @page {
      size: A4 portrait;
      margin: 0;
    }

    @media print {
      html, body {
        width: 210mm;
        height: 296mm;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
      }

      .print-toolbar {
        display: none !important;
      }

      .contract-editor {
        display: none !important;
      }

      .a4-page {
        width: 210mm;
        height: 296mm;
        margin: 0;
        padding: 10mm 13mm 11mm 13mm;
        box-shadow: none;
        overflow: hidden;
      }
    }
  </style>
</head>
<body>

  <div class="print-toolbar">
    <div class="print-toolbar-info">
      <strong>เอกสารสัญญาเช่าห้องพัก <span data-print-field="roomNumber"><?= e($contract['roomNumber']) ?></span></strong>
      <span style="opacity: 0.75;">(เลขที่สัญญา: <?= e($contract['contractId']) ?> — ตรวจสอบข้อมูลก่อนพิมพ์)</span>
    </div>
    <div class="print-toolbar-actions">
      <button type="button" class="btn btn-primary" onclick="window.print()">พิมพ์สัญญา</button>
      <button type="button" class="btn btn-secondary" onclick="window.close()">ปิดหน้าต่าง</button>
    </div>
  </div>

  <?php if ($canEditContract): ?>
  <section class="contract-editor" aria-label="แก้ไขข้อมูลสัญญาก่อนพิมพ์">
    <h2>แก้ไขข้อมูลก่อนพิมพ์</h2>
    <p>การแก้ไขมีผลเฉพาะเอกสารฉบับที่จะพิมพ์ครั้งนี้ และไม่เปลี่ยนข้อมูลในระบบ</p>
    <div class="contract-editor-grid">
      <label>ชื่อผู้เช่าหลัก<input data-edit-field="mainName" value="<?= e($contract['mainName']) ?>"></label>
      <label>วันเกิดผู้เช่าหลัก<input type="date" data-edit-field="mainBirthDate" value="<?= e($contract['mainBirthDate']) ?>"></label>
      <label>เบอร์โทรศัพท์<input data-edit-field="mainPhone" value="<?= e($contract['mainPhone']) ?>"></label>
      <label>ที่อยู่ตามทะเบียนบ้าน<input data-edit-field="mainAddress" value="<?= e($contract['mainAddress']) ?>"></label>
      <label>อาชีพ / สถานะ<input data-edit-field="mainOccupation" value="<?= e($contract['mainOccupation']) ?>"></label>
      <label>ที่ทำงาน / สถานศึกษา<input data-edit-field="mainWorkplace" value="<?= e($contract['mainWorkplace']) ?>"></label>
      <label>ผู้ติดต่อฉุกเฉิน<input data-edit-field="mainEmName" value="<?= e($contract['mainEmName']) ?>"></label>
      <label>ความสัมพันธ์<input data-edit-field="mainEmRelation" value="<?= e($contract['mainEmRelation']) ?>"></label>
      <label>เบอร์ฉุกเฉิน<input data-edit-field="mainEmPhone" value="<?= e($contract['mainEmPhone']) ?>"></label>
      <?php if ($hasCoTenant): ?>
        <label>ชื่อผู้ร่วมพัก<input data-edit-field="coName" value="<?= e($contract['coName']) ?>"></label>
        <label>เบอร์โทรผู้ร่วมพัก<input data-edit-field="coPhone" value="<?= e($contract['coPhone']) ?>"></label>
      <?php endif; ?>
      <label>เลขห้อง<input data-edit-field="roomNumber" value="<?= e($contract['roomNumber']) ?>"></label>
      <label>ค่าเช่าต่อเดือน<input type="number" min="0" step="0.01" data-edit-field="rentalPrice" value="<?= e((string) $contract['rentalPrice']) ?>"></label>
      <label>เงินประกัน<input type="number" min="0" step="0.01" data-edit-field="deposit" value="<?= e((string) $contract['deposit']) ?>"></label>
      <label>วันเริ่มสัญญา<input type="date" data-edit-field="startDate" value="<?= e($contract['startDate']) ?>"></label>
      <label>วันสิ้นสุดสัญญา<input type="date" data-edit-field="endDate" value="<?= e($contract['endDate']) ?>"></label>
    </div>
  </section>
  <?php endif; ?>

  <div class="a4-page">
    <div>
      <header class="doc-header">
        <div class="doc-meta-box">
          <div>เลขที่สัญญา: <strong><?= e($contract['contractId']) ?></strong></div>
          <div>วันที่ทำสัญญา: <strong data-print-field="startDate"><?= e(thai_date($contract['startDate'])) ?></strong></div>
        </div>

        <div class="doc-logo-wrap">
          <div class="doc-logo-badge" aria-hidden="true">
            <svg viewBox="0 0 32 32">
              <path d="M5 15 16 6l11 9M9 14v11h14V14M7 28c3-2 5-2 9 0s6 2 9 0" />
            </svg>
          </div>
          <h1 class="doc-title">หนังสือสัญญาเช่าห้องพักและกฎระเบียบหอพักฟ้าใสเพลส</h1>
          <p class="doc-subtitle">FAH SAI PLACE RESIDENTIAL · อำเภอเมืองเชียงใหม่ จังหวัดเชียงใหม่</p>
        </div>
      </header>

      <p class="intro-text">
        หนังสือสัญญาฉบับนี้ทำขึ้นระหว่าง <strong>หอพักฟ้าใสเพลส</strong> ซึ่งต่อไปในสัญญานี้เรียกว่า <strong>“ผู้ให้เช่า”</strong> ฝ่ายหนึ่ง กับผู้เช่าตามรายละเอียดด้านล่าง ซึ่งต่อไปในสัญญานี้เรียกว่า <strong>“ผู้เช่า”</strong> อีกฝ่ายหนึ่ง ทั้งสองฝ่ายได้ตกลงทำสัญญาเช่าห้องพักและยินยอมปฏิบัติตามเงื่อนไขดังต่อไปนี้
      </p>

      <table class="info-table">
        <tr>
          <td class="label-cell">ผู้เช่าหลัก</td>
          <td class="val-cell"><strong data-print-field="mainName"><?= e($contract['mainName']) ?></strong></td>
          <td class="label-cell">หลักฐานแสดงตน</td>
          <td class="val-cell">ตามสำเนาบัตรประชาชนแนบท้ายสัญญา</td>
        </tr>
        <tr>
          <td class="label-cell">วันเกิด / อายุ</td>
          <td class="val-cell"><span data-print-field="mainBirthDate"><?= !empty($contract['mainBirthDate']) ? e(thai_date($contract['mainBirthDate'])) : '—' ?></span><span data-print-field="mainAge"><?= e($ageText) ?></span></td>
          <td class="label-cell">เบอร์โทรศัพท์</td>
          <td class="val-cell" data-print-field="mainPhone"><?= e($contract['mainPhone']) ?></td>
        </tr>
        <tr>
          <td class="label-cell">ที่อยู่ทะเบียนบ้าน</td>
          <td class="val-cell" colspan="3" data-print-field="mainAddress"><?= e($contract['mainAddress'] ?: '—') ?></td>
        </tr>
        <tr>
          <td class="label-cell">อาชีพ / สถานะ</td>
          <td class="val-cell" data-print-field="mainOccupation"><?= e($contract['mainOccupation'] ?: '—') ?></td>
          <td class="label-cell">ที่ทำงาน / สถานศึกษา</td>
          <td class="val-cell" data-print-field="mainWorkplace"><?= e($contract['mainWorkplace'] ?: '—') ?></td>
        </tr>
        <tr>
          <td class="label-cell">บุคคลติดต่อฉุกเฉิน</td>
          <td class="val-cell">
            <span data-print-field="mainEmName"><?= e($contract['mainEmName'] ?: '—') ?></span>
            <span data-print-field="mainEmRelation"><?= !empty($contract['mainEmRelation']) ? '(' . e($contract['mainEmRelation']) . ')' : '' ?></span>
          </td>
          <td class="label-cell">เบอร์โทรฉุกเฉิน</td>
          <td class="val-cell" data-print-field="mainEmPhone"><?= e($contract['mainEmPhone'] ?: '—') ?></td>
        </tr>
        <?php if ($hasCoTenant): ?>
        <tr>
          <td class="label-cell">ผู้ร่วมพักคนที่ 2</td>
          <td class="val-cell"><strong data-print-field="coName"><?= e($contract['coName']) ?></strong></td>
          <td class="label-cell">เบอร์โทรผู้ร่วมพัก</td>
          <td class="val-cell" data-print-field="coPhone"><?= e($contract['coPhone'] ?: '—') ?></td>
        </tr>
        <?php endif; ?>
        <tr>
          <td class="label-cell">ห้องพักเลขที่</td>
          <td class="val-cell"><strong>ห้อง <span data-print-field="roomNumber"><?= e($contract['roomNumber']) ?></span></strong> (ค่าเช่า <span data-print-field="rentalPrice"><?= money($contract['rentalPrice']) ?></span> บาท/เดือน)</td>
          <td class="label-cell">เงินประกันสัญญา</td>
          <td class="val-cell"><strong><span data-print-field="deposit"><?= money($contract['deposit']) ?></span> บาท</strong></td>
        </tr>
        <tr>
          <td class="label-cell">ระยะเวลาเช่า</td>
          <td class="val-cell" colspan="3">
            ตั้งแต่วันที่ <strong data-print-field="startDate"><?= e(thai_date($contract['startDate'])) ?></strong>
            ถึงวันที่ <strong data-print-field="endDate"><?= !empty($contract['endDate']) ? e(thai_date($contract['endDate'])) : 'ไม่กำหนดวันสิ้นสุดสัญญา' ?></strong>
          </td>
        </tr>
      </table>

      <div class="section-block">
        <div class="section-bar">ส่วนที่ 1 : ข้อตกลงและเงื่อนไขการเช่าห้องพัก</div>
        <p class="clause-item">
          <strong>ข้อ 1. จำนวนผู้พักอาศัย:</strong> อนุญาตให้เข้าพักอาศัยได้ <strong>ไม่เกิน 2 ท่านต่อ 1 ห้องพัก</strong> ตามรายชื่อและสำเนาบัตรประชาชนที่แนบท้ายสัญญาฉบับนี้เท่านั้น
        </p>
        <p class="clause-item">
          <strong>ข้อ 2. การชำระค่าเช่าและสาธารณูปโภค:</strong> ผู้เช่าตกลงชำระค่าเช่า ค่าน้ำประปา และค่าไฟฟ้าประจำเดือนตามกำหนดในใบแจ้งหนี้ หากชำระล่าช้าต้องชำระค่าปรับตามอัตราที่หอพักกำหนด
        </p>
        <p class="clause-item">
          <strong>ข้อ 3. เงินประกันความเสียหาย:</strong> ผู้เช่าวางเงินประกันจำนวน <strong><span data-print-field="deposit"><?= money($contract['deposit']) ?></span> บาท</strong> โดยผู้ให้เช่าจะคืนเงินประกันเมื่อครบกำหนดสัญญาและผู้เช่าส่งมอบห้องคืนในสภาพเรียบร้อย ปราศจากความเสียหายและไม่มียอดค้างชำระ
        </p>
      </div>

      <div class="section-block">
        <div class="section-bar">ส่วนที่ 2 : กฎระเบียบหอพักและอัตราค่าปรับกรณีฝ่าฝืน</div>
        <table class="rules-table">
          <thead>
            <tr>
              <th class="col-num">ข้อ</th>
              <th class="col-rule">ข้อกำหนดและระเบียบการพักอาศัย</th>
              <th class="col-fine">บทลงโทษ / อัตราค่าปรับ</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="col-num">1</td>
              <td class="col-rule"><strong>ห้ามสูบบุหรี่ในห้องพักและอาคาร:</strong> ห้ามสูบบุหรี่ บุหรี่ไฟฟ้า หรือก่อควันรบกวนพื้นที่ส่วนกลาง</td>
              <td class="col-fine"><span class="fine-text">ปรับ 1,000 – 5,000 บาท</span></td>
            </tr>
            <tr>
              <td class="col-num">2</td>
              <td class="col-rule"><strong>ห้ามส่งเสียงดังรบกวนผู้อื่น:</strong> ห้ามส่งเสียงดังหรือเปิดเครื่องเสียงรบกวนห้องข้างเคียง</td>
              <td class="col-fine"><span class="fine-text">ปรับ 500 – 1,500 บาท</span></td>
            </tr>
            <tr>
              <td class="col-num">3</td>
              <td class="col-rule"><strong>ห้ามเล่นการพนันในห้องพัก:</strong> ห้ามลักลอบเล่นหรือจัดให้มีการเล่นการพนันทุกประเภทในหอพัก</td>
              <td class="col-fine"><span class="fine-text">ปรับ 2,000 – 5,000 บาท</span></td>
            </tr>
            <tr>
              <td class="col-num">4</td>
              <td class="col-rule"><strong>ห้ามยุ่งเกี่ยวกับสิ่งผิดกฎหมาย:</strong> ห้ามนำยาเสพติด อาวุธ หรือสิ่งผิดกฎหมายเข้ามาในหอพัก</td>
              <td class="col-fine"><span class="fine-text">บอกเลิกสัญญาและให้ออกทันที</span></td>
            </tr>
            <tr>
              <td class="col-num">5</td>
              <td class="col-rule"><strong>ห้ามเลี้ยงสัตว์ทุกชนิดในห้องพัก:</strong> ห้ามนำสัตว์เลี้ยงทุกชนิดเข้ามาเลี้ยงหรือพักอาศัยในอาคาร</td>
              <td class="col-fine"><span class="fine-text">ปรับ 1,000 – 5,000 บาท</span></td>
            </tr>
            <tr>
              <td class="col-num">6</td>
              <td class="col-rule"><strong>ห้ามเจาะผนังหรือดัดแปลงห้องพัก:</strong> ห้ามตอกตะปู ติดสติกเกอร์ หรือต่อเติมโดยไม่ได้รับอนุญาต</td>
              <td class="col-fine"><span class="fine-text">จุดละ 200–500 บ. (ทาสี 1,000–3,000 บ.)</span></td>
            </tr>
            <tr>
              <td class="col-num">7</td>
              <td class="col-rule"><strong>การรักษาความสะอาด:</strong> ดูแลความสะอาดห้องพักและทิ้งขยะในจุดที่หอพักจัดเตรียมไว้ให้เรียบร้อย</td>
              <td class="col-fine"><span class="fine-text">ปรับ 300 – 500 บาท</span></td>
            </tr>
            <tr>
              <td class="col-num">8</td>
              <td class="col-rule"><strong>ความรับผิดชอบต่อทรัพย์สินเสียหาย:</strong> กรณีทรัพย์สินภายในห้องชำรุดและไม่แจ้งก่อนย้ายออก</td>
              <td class="col-fine"><span class="fine-text">หักชดใช้ตามจริงจากเงินประกัน</span></td>
            </tr>
            <tr>
              <td class="col-num">9</td>
              <td class="col-rule"><strong>ห้ามประกอบอาหารที่มีควันและเปลวไฟ:</strong> ห้ามใช้เตาแก๊สหรือเตาถ่านประกอบอาหารในห้องพัก</td>
              <td class="col-fine"><span class="fine-text">ปรับ 1,000 – 3,000 บาท</span></td>
            </tr>
            <tr>
              <td class="col-num">10</td>
              <td class="col-rule"><strong>ห้ามนำบุคคลภายนอกเข้าพักค้างคืน:</strong> ห้ามบุคคลที่ไม่มีชื่อในสัญญาพักค้างคืนโดยไม่ได้รับอนุญาต</td>
              <td class="col-fine"><span class="fine-text">ปรับ 500 บาท</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <section class="signature-section">
      <p class="ack-text">
        คู่สัญญาทั้งสองฝ่ายได้อ่านและเข้าใจข้อความในสัญญาเช่าและกฎระเบียบหอพักฟ้าใสเพลสโดยตลอดแล้ว เห็นว่าถูกต้องตรงตามเจตนาทุกประการ จึงได้ลงลายมือชื่อไว้เป็นหลักฐานสำคัญต่อกัน
      </p>

      <div class="sig-grid">
        <div class="sig-box">
          <div class="sig-write-space"></div>
          <div class="sig-line"></div>
          <div class="sig-role">ลงชื่อ ผู้เช่าหลัก</div>
          <div class="sig-name">( <span data-print-field="mainName"><?= e($contract['mainName']) ?></span> )</div>
        </div>

        <?php if ($hasCoTenant): ?>
        <div class="sig-box">
          <div class="sig-write-space"></div>
          <div class="sig-line"></div>
          <div class="sig-role">ลงชื่อ ผู้ร่วมพัก (คนที่ 2)</div>
          <div class="sig-name">( <span data-print-field="coName"><?= e($contract['coName']) ?></span> )</div>
        </div>
        <?php endif; ?>

        <div class="sig-box">
          <div class="sig-write-space"></div>
          <div class="sig-line"></div>
          <div class="sig-role">ลงชื่อ ผู้ให้เช่า / ผู้ดูแลหอพัก</div>
          <div class="sig-name">( หอพักฟ้าใสเพลส )</div>
        </div>
      </div>
    </section>
  </div>

  <?php if ($canEditContract): ?>
  <script>
    const printFields = document.querySelectorAll('[data-print-field]');
    const editFields = document.querySelectorAll('[data-edit-field]');
    const formatPrintDate = (value, emptyText = '—') => {
      if (!value) return emptyText;
      const [year, month, day] = value.split('-').map(Number);
      return `${String(day).padStart(2, '0')}/${String(month).padStart(2, '0')}/${year + 543}`;
    };
    const updatePrintField = (name, value) => {
      const displayValue = name === 'startDate' || name === 'endDate'
        ? formatPrintDate(value, name === 'endDate' ? 'ไม่กำหนดวันสิ้นสุดสัญญา' : '—')
        : name === 'rentalPrice' || name === 'deposit'
          ? (value === '' ? '0.00' : Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))
          : value || '—';
      printFields.forEach((field) => {
        if (field.dataset.printField === name) field.textContent = displayValue;
      });
      if (name === 'mainEmRelation') {
        printFields.forEach((field) => {
          if (field.dataset.printField === name) field.textContent = value ? `(${value})` : '';
        });
      }
      if (name === 'mainBirthDate') {
        const birthDate = value ? new Date(`${value}T00:00:00`) : null;
        const startValue = document.querySelector('[data-edit-field="startDate"]')?.value || '';
        const referenceDate = startValue ? new Date(`${startValue}T00:00:00`) : new Date();
        let ageText = '';
        if (birthDate && !Number.isNaN(birthDate.getTime())) {
          let age = referenceDate.getFullYear() - birthDate.getFullYear();
          if (referenceDate.getMonth() < birthDate.getMonth() || (referenceDate.getMonth() === birthDate.getMonth() && referenceDate.getDate() < birthDate.getDate())) age--;
          if (age >= 0 && age <= 120) ageText = ` (อายุ ${age} ปี)`;
        }
        document.querySelector('[data-print-field="mainAge"]').textContent = ageText;
        document.querySelector('[data-print-field="mainBirthDate"]').textContent = formatPrintDate(value);
      }
      if (name === 'startDate') {
        const birthDate = document.querySelector('[data-edit-field="mainBirthDate"]')?.value || '';
        updatePrintField('mainBirthDate', birthDate);
      }
    };
    editFields.forEach((input) => {
      input.addEventListener('input', () => updatePrintField(input.dataset.editField, input.value));
    });
  </script>
  <?php endif; ?>

</body>
</html>