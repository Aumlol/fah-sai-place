<?php
$isManager = in_array($user['role'], ['Admin', 'Staff'], true);

if (!function_exists('extract_be_month_year')) {
    function extract_be_month_year(?string $value): array
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return ['month' => '', 'year' => ''];
        }
        if (preg_match('/^(\d{1,2})\/(\d{4})$/', $raw, $m)) {
            $month = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $yearNum = (int) $m[2];
            if ($yearNum < 2400) {
                $yearNum += 543;
            }
            return ['month' => $month, 'year' => (string) $yearNum];
        }
        $ts = strtotime($raw);
        if ($ts !== false) {
            $month = date('m', $ts);
            $yearNum = (int) date('Y', $ts);
            if ($yearNum < 2400) {
                $yearNum += 543;
            }
            return ['month' => $month, 'year' => (string) $yearNum];
        }
        return ['month' => '', 'year' => ''];
    }
}

$thaiMonthNames = [
    '01' => 'มกราคม (01)',
    '02' => 'กุมภาพันธ์ (02)',
    '03' => 'มีนาคม (03)',
    '04' => 'เมษายน (04)',
    '05' => 'พฤษภาคม (05)',
    '06' => 'มิถุนายน (06)',
    '07' => 'กรกฎาคม (07)',
    '08' => 'สิงหาคม (08)',
    '09' => 'กันยายน (09)',
    '10' => 'ตุลาคม (10)',
    '11' => 'พฤศจิกายน (11)',
    '12' => 'ธันวาคม (12)',
];

if ($isManager) {
    $payments = db()->query("SELECT p.*, u.name, r.roomNumber, i.billMonth, f.description AS fineDescription
        FROM Payment p
        JOIN Contract c ON c.contractId = p.contractId
        JOIN `User` u ON u.userId = c.userId
        JOIN Room r ON r.roomId = c.roomId
        LEFT JOIN Invoice i ON i.invoiceId = p.invoiceId
        LEFT JOIN Fine f ON f.fineId = p.fineId
        ORDER BY FIELD(p.status, 'รอตรวจสอบ', 'อนุมัติ', 'ปฏิเสธ'), p.paymentDate DESC, p.paymentId DESC")->fetchAll();
    $activeContracts = db()->query("SELECT c.contractId, c.roomId, u.name, r.roomNumber FROM Contract c JOIN `User` u ON u.userId = c.userId JOIN Room r ON r.roomId = c.roomId WHERE c.status = 'ใช้งานอยู่' ORDER BY r.roomNumber")->fetchAll();
    $invoices = db()->query("SELECT i.*, u.name, r.roomNumber,
        i.rentAmount + (i.waterUnit * i.waterRate) + (i.electricUnit * i.electricRate) AS total
        FROM Invoice i JOIN Contract c ON c.contractId = i.contractId
        JOIN `User` u ON u.userId = c.userId JOIN Room r ON r.roomId = c.roomId
        ORDER BY i.dueDate DESC, r.roomNumber")->fetchAll();
    $fines = db()->query("SELECT f.*, u.name, r.roomNumber
        FROM Fine f JOIN Contract c ON c.contractId = f.contractId
        JOIN `User` u ON u.userId = c.userId JOIN Room r ON r.roomId = c.roomId
        ORDER BY f.fineDate DESC, r.roomNumber")->fetchAll();

    // แยกรายการที่ยังไม่ชำระ (ถ้าชำระแล้วจะย้ายไปอยู่ส่วนตรวจสอบและประวัติชำระเงินด้านล่าง)
    $unpaidInvoicesList = array_values(array_filter(
        $invoices,
        static fn(array $inv): bool => ($inv['status'] ?? '') !== 'ชำระแล้ว'
    ));
    $unpaidFinesList = array_values(array_filter(
        $fines,
        static fn(array $fine): bool => ($fine['status'] ?? '') !== 'ชำระแล้ว'
    ));

    $invoicePayments = [];
    $finePayments = [];
    $pendingInvoiceSlips = 0;
    $pendingFineSlips = 0;

    $invoicePayMonths = [];
    $invoicePayYears = [];
    $finePayMonths = [];
    $finePayYears = [];

    foreach ($payments as $p) {
        $my = extract_be_month_year($p['paymentDate'] ?? '');
        if (!empty($p['fineId'])) {
            $finePayments[] = $p;
            if (($p['status'] ?? '') === 'รอตรวจสอบ') {
                $pendingFineSlips++;
            }
            if ($my['month'] !== '') {
                $finePayMonths[$my['month']] = true;
            }
            if ($my['year'] !== '') {
                $finePayYears[$my['year']] = true;
            }
        } else {
            $invoicePayments[] = $p;
            if (($p['status'] ?? '') === 'รอตรวจสอบ') {
                $pendingInvoiceSlips++;
            }
            if ($my['month'] !== '') {
                $invoicePayMonths[$my['month']] = true;
            }
            if ($my['year'] !== '') {
                $invoicePayYears[$my['year']] = true;
            }
        }
    }

    ksort($invoicePayMonths);
    krsort($invoicePayYears);
    ksort($finePayMonths);
    krsort($finePayYears);

    $invoiceMonthOptions = [];
    $recordedMeterMonths = db()->query('SELECT DISTINCT month FROM MeterRecord')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($recordedMeterMonths as $recordedMonth) {
        $normalizedMonth = normalize_billing_month((string) $recordedMonth);
        if ($normalizedMonth !== null) {
            $invoiceMonthOptions[$normalizedMonth] = true;
        }
    }
    uksort($invoiceMonthOptions, static fn(string $left, string $right): int =>
        (billing_month_index($right) ?? 0) <=> (billing_month_index($left) ?? 0)
    );
    $invoiceTargetMonth = normalize_billing_month((string) ($_GET['invoiceMonth'] ?? ''));
    if ($invoiceTargetMonth === null) {
        $invoiceTargetMonth = array_key_first($invoiceMonthOptions);
    }
    $invoiceDueDate = $invoiceTargetMonth !== null ? billing_due_date($invoiceTargetMonth) : '';
} else {
    $statement = db()->prepare("SELECT p.*, r.roomNumber, i.billMonth, f.description AS fineDescription
        FROM Payment p
        JOIN Contract c ON c.contractId = p.contractId
        JOIN Room r ON r.roomId = c.roomId
        LEFT JOIN Invoice i ON i.invoiceId = p.invoiceId
        LEFT JOIN Fine f ON f.fineId = p.fineId
        WHERE c.userId = ?
        ORDER BY p.paymentDate DESC, p.paymentId DESC");
    $statement->execute([$user['userId']]);
    $payments = $statement->fetchAll();

    $tenantPayMonths = [];
    $tenantPayYears = [];
    foreach ($payments as $payRow) {
        $my = extract_be_month_year($payRow['paymentDate'] ?? '');
        if ($my['month'] !== '') {
            $tenantPayMonths[$my['month']] = true;
        }
        if ($my['year'] !== '') {
            $tenantPayYears[$my['year']] = true;
        }
    }
    ksort($tenantPayMonths);
    krsort($tenantPayYears);

    $statement = db()->prepare("SELECT i.*, r.roomNumber,
        i.rentAmount + (i.waterUnit * i.waterRate) + (i.electricUnit * i.electricRate) AS total,
        EXISTS(SELECT 1 FROM Payment p WHERE p.invoiceId = i.invoiceId AND p.status = 'รอตรวจสอบ') AS pendingPayment
        FROM Invoice i JOIN Contract c ON c.contractId = i.contractId JOIN Room r ON r.roomId = c.roomId
        WHERE c.userId = ? ORDER BY i.dueDate DESC");
    $statement->execute([$user['userId']]);
    $tenantInvoices = $statement->fetchAll();

    $statement = db()->prepare("SELECT f.*, r.roomNumber,
        EXISTS(SELECT 1 FROM Payment p WHERE p.fineId = f.fineId AND p.status = 'รอตรวจสอบ') AS pendingPayment
        FROM Fine f JOIN Contract c ON c.contractId = f.contractId JOIN Room r ON r.roomId = c.roomId
        WHERE c.userId = ? ORDER BY f.fineDate DESC");
    $statement->execute([$user['userId']]);
    $tenantFines = $statement->fetchAll();

    $statement = db()->prepare("SELECT i.invoiceId, i.billMonth, r.roomNumber,
        i.rentAmount + (i.waterUnit * i.waterRate) + (i.electricUnit * i.electricRate) AS amount
        FROM Invoice i JOIN Contract c ON c.contractId = i.contractId JOIN Room r ON r.roomId = c.roomId
        WHERE c.userId = ? AND i.status = 'รอชำระ'
        AND NOT EXISTS(SELECT 1 FROM Payment p WHERE p.invoiceId = i.invoiceId AND p.status = 'รอตรวจสอบ')
        ORDER BY i.dueDate");
    $statement->execute([$user['userId']]);
    $unpaidInvoices = $statement->fetchAll();

    $statement = db()->prepare("SELECT f.fineId, f.description, r.roomNumber, f.amount
        FROM Fine f JOIN Contract c ON c.contractId = f.contractId JOIN Room r ON r.roomId = c.roomId
        WHERE c.userId = ? AND f.status = 'รอชำระ'
        AND NOT EXISTS(SELECT 1 FROM Payment p WHERE p.fineId = f.fineId AND p.status = 'รอตรวจสอบ')
        ORDER BY f.fineDate");
    $statement->execute([$user['userId']]);
    $unpaidFines = $statement->fetchAll();
}
?>

<style>
/* Table Header Filter Controls */
.table-filter-controls {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
}
.table-filter-controls select {
  min-width: 138px;
  height: 36px;
  padding: 0 12px;
  border-radius: 10px;
  border: 1.5px solid #cbd5e1;
  background: #f8fafc;
  color: #0f172a;
  font-weight: 600;
  font-size: 0.88rem;
  cursor: pointer;
}
.table-filter-controls select:focus {
  outline: none;
  border-color: #2563eb;
  background: #ffffff;
}
.payment-filter-empty {
  text-align: center;
  padding: 28px 16px;
  color: #64748b;
  font-size: 0.92rem;
}

/* Admin 2-Tab Navigation */
.admin-pay-nav {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 20px;
}
@media (max-width: 760px) {
  .admin-pay-nav {
    grid-template-columns: 1fr;
  }
}
.admin-pay-tab {
  background: #ffffff;
  border: 1.5px solid #e2e8f0;
  border-radius: 16px;
  padding: 16px 18px;
  text-align: left;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
}
.admin-pay-tab:hover {
  border-color: #93c5fd;
  transform: translateY(-1px);
  box-shadow: 0 8px 18px rgba(37, 99, 235, 0.08);
}
.admin-pay-tab.active {
  background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
  border-color: #2563eb;
  box-shadow: 0 8px 20px rgba(37, 99, 235, 0.12);
}
.admin-tab-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: #f1f5f9;
  color: #475569;
  display: grid;
  place-items: center;
  font-size: 1.2rem;
  font-weight: 700;
  flex-shrink: 0;
  transition: all 0.2s ease;
}
.admin-pay-tab.active .admin-tab-icon {
  background: #2563eb;
  color: #ffffff;
}
.admin-tab-text {
  min-width: 0;
  flex: 1;
}
.admin-tab-step {
  display: block;
  font-size: 0.74rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  color: #64748b;
  text-transform: uppercase;
}
.admin-pay-tab.active .admin-tab-step {
  color: #2563eb;
}
.admin-tab-title {
  display: block;
  font-size: 1.02rem;
  font-weight: 700;
  color: #0f172a;
  margin-top: 2px;
}
.admin-tab-badges {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
  justify-content: flex-end;
}
.admin-tab-badge {
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 700;
  background: #f1f5f9;
  color: #475569;
  white-space: nowrap;
}
.admin-pay-tab.active .admin-tab-badge {
  background: #dbeafe;
  color: #1d4ed8;
}
.admin-tab-badge.alert {
  background: #fef3c7;
  color: #b45309;
  border: 1px solid #fde68a;
}

.admin-tab-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
  animation: fadeSlideIn 0.22s ease;
}
.admin-tab-section[hidden] {
  display: none !important;
}
@keyframes fadeSlideIn {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Tenant E-Bill Styling */
.ebill-layout {
  display: grid;
  grid-template-columns: minmax(0, 1.35fr) minmax(320px, 0.95fr);
  gap: 22px;
  align-items: start;
  margin-bottom: 24px;
}
@media (max-width: 960px) {
  .ebill-layout {
    grid-template-columns: 1fr;
  }
}

.ebill-tabs {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  padding-bottom: 6px;
  margin-bottom: 14px;
}
.ebill-tab {
  border: 1px solid var(--border, #e2e8f0);
  background: #ffffff;
  color: #475569;
  padding: 8px 14px;
  border-radius: 999px;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  white-space: nowrap;
  transition: all 0.18s ease;
}
.ebill-tab:hover {
  border-color: #93c5fd;
  color: #1d4ed8;
}
.ebill-tab.active {
  background: #1d4ed8;
  border-color: #1d4ed8;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(29, 78, 216, 0.22);
}
.ebill-tab-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: currentColor;
}

.ebill-card {
  background: #ffffff;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
  position: relative;
}
.ebill-header {
  background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
  color: #ffffff;
  padding: 22px 26px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
}
.ebill-brand {
  display: flex;
  align-items: center;
  gap: 12px;
}
.ebill-logo {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.16);
  display: grid;
  place-items: center;
  font-size: 1.2rem;
  backdrop-filter: blur(4px);
}
.ebill-brand strong {
  display: block;
  font-size: 1.08rem;
  letter-spacing: 0.01em;
}
.ebill-brand small {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.8rem;
  letter-spacing: 0.06em;
}
.ebill-badge {
  padding: 5px 12px;
  border-radius: 999px;
  font-size: 0.82rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.ebill-badge.unpaid { background: #fef3c7; color: #b45309; }
.ebill-badge.verifying { background: #dbeafe; color: #1d4ed8; }
.ebill-badge.paid { background: #d1fae5; color: #065f46; }

.ebill-hero {
  background: #f8fafc;
  padding: 22px 26px;
  border-bottom: 1.5px dashed #cbd5e1;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  position: relative;
}
.ebill-hero::before,
.ebill-hero::after {
  content: "";
  position: absolute;
  bottom: -10px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: var(--bg, #f1f5f9);
  border: 1px solid var(--border, #e2e8f0);
}
.ebill-hero::before { left: -11px; }
.ebill-hero::after { right: -11px; }

.ebill-total-label {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 600;
  margin-bottom: 4px;
}
.ebill-total-amount {
  font-size: 2.15rem;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.1;
  letter-spacing: -0.02em;
}
.ebill-total-amount span {
  font-size: 1.15rem;
  font-weight: 700;
  color: #475569;
}
.ebill-meta-pills {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 6px;
  font-size: 0.86rem;
}
@media (max-width: 560px) {
  .ebill-meta-pills { align-items: flex-start; }
}
.ebill-meta-item {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  padding: 5px 12px;
  border-radius: 8px;
  color: #334155;
}
.ebill-meta-item strong { color: #0f172a; }

.ebill-body { padding: 22px 26px; }
.ebill-section-title {
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  color: #64748b;
  text-transform: uppercase;
  margin-bottom: 12px;
}
.ebill-line-items {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.ebill-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  padding-bottom: 12px;
  border-bottom: 1px dashed #e2e8f0;
}
.ebill-line:last-child {
  border-bottom: none;
  padding-bottom: 0;
}
.ebill-line-info {
  display: flex;
  align-items: center;
  gap: 12px;
}
.ebill-line-icon {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: grid;
  place-items: center;
  font-size: 1rem;
  flex-shrink: 0;
}
.ebill-line-icon.rent { background: #eff6ff; color: #2563eb; }
.ebill-line-icon.water { background: #ecfeff; color: #0891b2; }
.ebill-line-icon.electric { background: #fefce8; color: #ca8a04; }
.ebill-line-info strong {
  display: block;
  font-size: 0.96rem;
  color: #0f172a;
}
.ebill-line-info small {
  display: block;
  font-size: 0.82rem;
  color: #64748b;
}
.ebill-line-price {
  font-size: 1rem;
  font-weight: 700;
  color: #0f172a;
  white-space: nowrap;
}
.ebill-footer {
  background: #f8fafc;
  padding: 16px 26px;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
}

.paybox-card {
  background: #ffffff;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 18px;
  padding: 22px 24px;
  box-shadow: 0 10px 28px rgba(15, 23, 42, 0.04);
  position: sticky;
  top: 20px;
}
.paybox-amount-banner {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 12px;
  padding: 12px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}
.paybox-amount-banner span {
  font-size: 0.86rem;
  color: #1e40af;
  font-weight: 600;
}
.paybox-amount-banner strong {
  font-size: 1.25rem;
  color: #1d4ed8;
  font-weight: 800;
}
.slip-upload-zone {
  border: 1.5px dashed #93c5fd;
  background: #f8fafc;
  border-radius: 12px;
  padding: 14px;
  text-align: center;
  transition: border-color 0.2s, background 0.2s;
}
.slip-upload-zone:hover {
  border-color: #2563eb;
  background: #eff6ff;
}
.slip-preview-img {
  max-height: 190px;
  max-width: 100%;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  margin: 10px auto 0;
  display: none;
  object-fit: contain;
  background: #ffffff;
}

.fines-compact-list {
  margin-top: 18px;
  background: #ffffff;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 16px;
  padding: 18px 22px;
}
.fine-row-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  padding: 10px 0;
  border-bottom: 1px solid #f1f5f9;
}
.fine-row-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

/* Modern Admin Fine Card Styling */
.fine-modern-wrap {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 20px;
  margin-top: 6px;
}
.fine-grid-top {
  display: grid;
  grid-template-columns: minmax(220px, 1fr) minmax(0, 1.85fr);
  gap: 16px;
  margin-bottom: 16px;
}
.fine-grid-bottom {
  display: grid;
  grid-template-columns: minmax(180px, 1fr) minmax(180px, 1fr) auto;
  gap: 16px;
  align-items: end;
}
@media (max-width: 860px) {
  .fine-grid-top,
  .fine-grid-bottom {
    grid-template-columns: 1fr;
  }
}
.fine-field-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 0;
}
.fine-field-label {
  font-size: 0.84rem;
  font-weight: 700;
  color: #334155;
  display: flex;
  align-items: center;
  gap: 6px;
}
.fine-modern-select,
.fine-modern-input {
  width: 100%;
  height: 44px;
  padding: 0 14px;
  border: 1.5px solid #cbd5e1;
  border-radius: 10px;
  background-color: #ffffff;
  color: #0f172a;
  font-size: 0.92rem;
  font-weight: 500;
  box-sizing: border-box;
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
}
.fine-modern-select {
  appearance: none;
  -webkit-appearance: none;
  padding-right: 38px;
  text-overflow: ellipsis;
  white-space: nowrap;
  overflow: hidden;
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 16px;
}
.fine-modern-select:hover,
.fine-modern-input:hover {
  border-color: #94a3b8;
}
.fine-modern-select:focus,
.fine-modern-input:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3.5px rgba(37, 99, 235, 0.14);
}
.fine-amount-box {
  position: relative;
  display: flex;
  align-items: center;
}
.fine-amount-box .fine-modern-input {
  padding-right: 42px;
  font-weight: 700;
  font-size: 1rem;
  color: #0f172a;
}
.fine-currency-suffix {
  position: absolute;
  right: 14px;
  font-size: 0.9rem;
  font-weight: 700;
  color: #64748b;
  pointer-events: none;
}
.fine-rule-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-top: 12px;
  padding: 8px 14px;
  border-radius: 10px;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  color: #1e40af;
  font-size: 0.84rem;
  font-weight: 600;
}
.fine-submit-btn {
  height: 44px;
  padding: 0 24px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.92rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  white-space: nowrap;
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.22);
}
</style>

<section class="page-heading">
  <div>
    <p class="eyebrow">PAYMENT RECORDS</p>
    <h1>ใบแจ้งหนี้และการชำระเงิน</h1>
    <p class="muted"><?= $isManager ? 'จัดการบิลค่าเช่ารายเดือน ค่าปรับตามสัญญา และตรวจสอบสลิปการชำระเงิน' : 'ตรวจสอบบิลค่าเช่ารายเดือน ค่าสาธารณูปโภค และแจ้งโอนชำระเงิน' ?></p>
  </div>
</section>

<?php if ($isManager): ?>
  <!-- แถบเลือก 2 หัวข้อ: 1. ค่าเช่า | 2. ค่าปรับ -->
  <nav class="admin-pay-nav" aria-label="หมวดหมู่การจัดการใบแจ้งหนี้และการชำระเงิน">
    <button type="button" class="admin-pay-tab active" data-admin-tab-btn="rent" onclick="switchAdminPaymentTab('rent')">
      <div class="admin-tab-icon" aria-hidden="true">▤</div>
      <div class="admin-tab-text">
        <span class="admin-tab-step">หัวข้อที่ 1</span>
        <span class="admin-tab-title">ใบแจ้งหนี้ค่าเช่า</span>
      </div>
      <div class="admin-tab-badges">
        <span class="admin-tab-badge">รอชำระ <?= count($unpaidInvoicesList) ?> บิล</span>
        <?php if ($pendingInvoiceSlips > 0): ?>
          <span class="admin-tab-badge alert">สลิปรอตรวจ <?= $pendingInvoiceSlips ?></span>
        <?php endif; ?>
      </div>
    </button>

    <button type="button" class="admin-pay-tab" data-admin-tab-btn="fines" onclick="switchAdminPaymentTab('fines')">
      <div class="admin-tab-icon" aria-hidden="true">!</div>
      <div class="admin-tab-text">
        <span class="admin-tab-step">หัวข้อที่ 2</span>
        <span class="admin-tab-title">ค่าปรับตามสัญญา</span>
      </div>
      <div class="admin-tab-badges">
        <span class="admin-tab-badge">รอชำระ <?= count($unpaidFinesList) ?> รายการ</span>
        <?php if ($pendingFineSlips > 0): ?>
          <span class="admin-tab-badge alert">สลิปรอตรวจ <?= $pendingFineSlips ?></span>
        <?php endif; ?>
      </div>
    </button>
  </nav>

  <!-- แท็บที่ 1: ค่าเช่า (สร้างใบแจ้งหนี้ + รายการรอชำระ + ตรวจสอบสลิปและประวัติชำระค่าเช่าด้านล่าง) -->
  <div class="admin-tab-section" data-admin-tab-panel="rent">
    <?php if ($user['role'] === 'Admin'): ?>
      <section class="panel form-panel">
        <div class="panel-heading">
          <div>
            <p class="eyebrow">MONTHLY INVOICES</p>
            <h2>สร้างใบแจ้งหนี้ประจำเดือน</h2>
          </div>
        </div>
        <?php if ($activeContracts): ?>
          <form method="post" action="index.php" class="inline-form invoice-form" data-invoice-form>
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="generate_invoices_all">
            <input type="hidden" name="return_page" value="payments">
            <div class="invoice-month-picker">
              <label for="invoice-month-select">เดือน/รอบบิล</label>
              <div class="invoice-month-controls">
                <select id="invoice-month-select" required data-invoice-month>
                  <option value="">เลือกเดือน</option>
                  <?php foreach (array_keys($invoiceMonthOptions) as $monthOption): ?>
                    <option value="<?= e($monthOption) ?>" <?= $monthOption === $invoiceTargetMonth ? 'selected' : '' ?>><?= e($monthOption) ?></option>
                  <?php endforeach; ?>
                </select>
                <input type="hidden" name="billMonth" value="<?= e($invoiceTargetMonth) ?>" data-invoice-month-value>
                <button class="button secondary" type="button" data-invoice-search>ค้นหา</button>
              </div>
            </div>
            <label>วันครบกำหนด (วันที่ 1 ของเดือนถัดไป)
              <input type="date" value="<?= e($invoiceDueDate) ?>" disabled data-invoice-due-date>
            </label>
            <button class="button primary" type="submit" data-confirm="ยืนยันสร้างใบแจ้งหนี้ทุกห้องสำหรับรอบเดือนที่เลือกหรือไม่?" disabled data-invoice-submit>สร้างใบแจ้งหนี้ทุกห้อง</button>
            <div class="invoice-meter-preview" data-invoice-preview aria-live="polite">
              <p class="form-hint" data-invoice-status>เลือกเดือนแล้วกดค้นหาเพื่อดูข้อมูลมิเตอร์ทุกห้อง</p>
              <div class="invoice-meter-table" data-invoice-meter-table></div>
            </div>
          </form>
        <?php else: ?>
          <div class="empty-state compact"><strong>ไม่มีสัญญาเช่าที่กำลังใช้งาน</strong></div>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <section class="panel table-panel">
      <div class="panel-heading" style="padding: 20px 24px 12px;">
        <div>
          <p class="eyebrow">UNPAID INVOICES</p>
          <h2>รายการใบแจ้งหนี้ค่าเช่ารอชำระ</h2>
        </div>
        <span class="panel-caption"><?= count($unpaidInvoicesList) ?> บิล</span>
      </div>
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>เดือน</th>
              <th>ห้อง / ผู้เช่า</th>
              <th>ครบกำหนด</th>
              <th>ยอดรวม</th>
              <th>สถานะ</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($unpaidInvoicesList as $invoice): ?>
              <tr>
                <td><strong><?= e($invoice['billMonth']) ?></strong></td>
                <td>ห้อง <?= e($invoice['roomNumber']) ?> · <?= e($invoice['name']) ?></td>
                <td><?= e(thai_date($invoice['dueDate'])) ?></td>
                <td><strong><?= money($invoice['total']) ?> ฿</strong></td>
                <td><span class="status-pill pending"><?= e($invoice['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (!$unpaidInvoicesList): ?>
          <div class="empty-state compact"><strong>ไม่มีใบแจ้งหนี้ค่าเช่าค้างชำระ</strong></div>
        <?php endif; ?>
      </div>
    </section>

    <section class="panel table-panel" data-history-panel="invoice-payments">
      <div class="panel-heading" style="padding: 20px 24px 12px; flex-wrap: wrap; gap: 12px;">
        <div>
          <p class="eyebrow">RENT SLIP VERIFICATION & HISTORY</p>
          <h2>ตรวจสอบสลิปและประวัติชำระค่าเช่า</h2>
        </div>
        <div class="table-filter-controls">
          <?php if ($invoicePayMonths): ?>
            <select aria-label="เลือกเดือน" data-history-month onchange="filterHistoryTable('invoice-payments')">
              <option value="">ทุกเดือน</option>
              <?php foreach (array_keys($invoicePayMonths) as $mVal): ?>
                <option value="<?= e((string) $mVal) ?>"><?= e($thaiMonthNames[$mVal] ?? (string) $mVal) ?></option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>
          <?php if ($invoicePayYears): ?>
            <select aria-label="เลือกปี พ.ศ." data-history-year onchange="filterHistoryTable('invoice-payments')">
              <option value="">ทุกปี</option>
              <?php foreach (array_keys($invoicePayYears) as $yVal): ?>
                <option value="<?= e((string) $yVal) ?>"><?= e((string) $yVal) ?></option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>
          <span class="panel-caption" data-history-caption><?= count($invoicePayments) ?> รายการ</span>
        </div>
      </div>
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>ผู้เช่า</th>
              <th>ห้อง</th>
              <th>รอบบิล</th>
              <th>วันที่โอน</th>
              <th>ยอดชำระ</th>
              <th>หลักฐาน / ใบเสร็จ</th>
              <th>สถานะ</th>
              <?php if ($user['role'] === 'Admin'): ?><th>ดำเนินการ</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($invoicePayments as $payment):
              $payMY = extract_be_month_year($payment['paymentDate'] ?? '');
            ?>
              <tr data-history-row data-month="<?= e($payMY['month']) ?>" data-year="<?= e($payMY['year']) ?>">
                <td><?= e($payment['name']) ?></td>
                <td><strong><?= e($payment['roomNumber']) ?></strong></td>
                <td><?= !empty($payment['billMonth']) ? 'รอบบิล ' . e($payment['billMonth']) : 'รายการเดิม' ?></td>
                <td><?= e(thai_date($payment['paymentDate'])) ?></td>
                <td><strong><?= money($payment['amount']) ?> ฿</strong></td>
                <td>
                  <?php if ($payment['evidence']): ?>
                    <a class="text-link" href="file.php?id=<?= e($payment['paymentId']) ?>" data-image-preview data-image-alt="หลักฐานการชำระเงิน">ดูสลิป</a>
                  <?php endif; ?>
                  <?php if ($payment['status'] === 'อนุมัติ'): ?>
                    <a class="text-link" href="receipt.php?id=<?= e($payment['paymentId']) ?>">ดูใบเสร็จ</a>
                  <?php endif; ?>
                  <?php if (!$payment['evidence'] && $payment['status'] !== 'อนุมัติ'): ?>—<?php endif; ?>
                </td>
                <td>
                  <span class="status-pill <?= $payment['status'] === 'อนุมัติ' ? 'available' : ($payment['status'] === 'ปฏิเสธ' ? 'rejected' : 'pending') ?>">
                    <?= e($payment['status']) ?>
                  </span>
                </td>
                <?php if ($user['role'] === 'Admin'): ?>
                  <td>
                    <?php if ($payment['status'] === 'รอตรวจสอบ'): ?>
                      <form method="post" action="index.php" class="row-actions">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="paymentId" value="<?= e($payment['paymentId']) ?>">
                        <input type="hidden" name="return_page" value="payments">
                        <button class="button tiny success" name="action" value="review_payment" data-confirm="อนุมัติหลักฐานการชำระค่าเช่านี้หรือไม่?">อนุมัติ</button>
                        <button class="button tiny danger" name="action" value="reject_payment">ปฏิเสธ</button>
                      </form>
                    <?php else: ?>—<?php endif; ?>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (!$invoicePayments): ?>
          <div class="empty-state compact"><strong>ยังไม่มีรายการชำระค่าเช่า</strong></div>
        <?php endif; ?>
        <div class="payment-filter-empty" data-history-empty hidden>ไม่พบรายการในเดือนหรือปีที่เลือก</div>
      </div>
    </section>
  </div>

  <!-- แท็บที่ 2: ค่าปรับ (บันทึกค่าปรับ + รายการรอชำระ + ตรวจสอบสลิปและประวัติชำระค่าปรับด้านล่าง) -->
  <div class="admin-tab-section" data-admin-tab-panel="fines" hidden>
    <?php if ($user['role'] === 'Admin'): ?>
      <section class="panel form-panel">
        <div class="panel-heading" style="margin-bottom: 10px;">
          <div>
            <p class="eyebrow">TENANT FINES</p>
            <h2>บันทึกค่าปรับตามสัญญาเช่า</h2>
          </div>
          <span class="panel-caption">คำนวณยอดตามเงื่อนไขสัญญาอัตโนมัติ</span>
        </div>

        <?php if ($activeContracts): ?>
          <form method="post" action="index.php" id="admin-fine-form" class="fine-modern-wrap">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="create_fine">
            <input type="hidden" name="return_page" value="payments">
            <input type="hidden" name="description" id="fine-description-hidden" required>

            <div class="fine-grid-top">
              <div class="fine-field-group">
                <label class="fine-field-label" for="fine-contract-select">ผู้เช่า / ห้องพัก</label>
                <select name="contractId" id="fine-contract-select" class="fine-modern-select" required>
                  <?php foreach ($activeContracts as $contract): ?>
                    <option value="<?= e($contract['contractId']) ?>">ห้อง <?= e($contract['roomNumber']) ?> · <?= e($contract['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="fine-field-group">
                <label class="fine-field-label" for="fine-rule-select">รายการผิดระเบียบตามสัญญา</label>
                <select id="fine-rule-select" class="fine-modern-select" required onchange="applyContractFineRule()">
                  <option value="">-- เลือกรายการค่าปรับตามสัญญาเช่า --</option>
                  <option value="ข้อ 1. สูบบุหรี่ในห้องพักหรือบริเวณอาคาร" data-amount="1000" data-range="เกณฑ์ตามสัญญา ข้อ 1: ปรับ 1,000 – 5,000 บาท">ข้อ 1. สูบบุหรี่ / บุหรี่ไฟฟ้าในห้องพักหรืออาคาร (1,000 – 5,000 ฿)</option>
                  <option value="ข้อ 2. ส่งเสียงดังรบกวนผู้อื่น" data-amount="500" data-range="เกณฑ์ตามสัญญา ข้อ 2: ปรับ 500 – 1,500 บาท">ข้อ 2. ส่งเสียงดังรบกวนผู้อื่น (500 – 1,500 ฿)</option>
                  <option value="ข้อ 3. ลักลอบเล่นการพนันในห้องพัก" data-amount="2000" data-range="เกณฑ์ตามสัญญา ข้อ 3: ปรับ 2,000 – 5,000 บาท">ข้อ 3. เล่นการพนันในห้องพัก (2,000 – 5,000 ฿)</option>
                  <option value="ข้อ 5. นำสัตว์เลี้ยงเข้ามาเลี้ยงในห้องพัก/อาคาร" data-amount="1000" data-range="เกณฑ์ตามสัญญา ข้อ 5: ปรับ 1,000 – 5,000 บาท">ข้อ 5. เลี้ยงสัตว์ทุกชนิดในห้องพัก/อาคาร (1,000 – 5,000 ฿)</option>
                  <option value="ข้อ 6. เจาะผนัง/ตอกตะปู หรือดัดแปลงสภาพห้องพัก" data-amount="200" data-range="เกณฑ์ตามสัญญา ข้อ 6: ปรับจุดละ 200 – 500 บาท (ทาสีใหม่ 1,000 – 3,000 บาท)">ข้อ 6. เจาะผนัง / ดัดแปลงสภาพห้องพัก (จุดละ 200 – 500 ฿)</option>
                  <option value="ข้อ 6. สีผนังหลุดลอกเสียหาย (ค่าทาสีใหม่)" data-amount="1000" data-range="เกณฑ์ตามสัญญา ข้อ 6: ค่าทาสีใหม่ 1,000 – 3,000 บาท">ข้อ 6. สีผนังเสียหาย / ค่าทาสีใหม่ (1,000 – 3,000 ฿)</option>
                  <option value="ข้อ 7. ปล่อยปละละเลยความสะอาด / ทำท่ออุดตัน" data-amount="300" data-range="เกณฑ์ตามสัญญา ข้อ 7: ปรับ 300 – 500 บาท">ข้อ 7. ปล่อยห้องสกปรกส่งกลิ่น / ท่ออุดตัน (300 – 500 ฿)</option>
                  <option value="ข้อ 8. ค่าชดเชยทรัพย์สินหรือเฟอร์นิเจอร์ภายในห้องเสียหาย" data-amount="" data-range="เกณฑ์ตามสัญญา ข้อ 8: คิดค่าซ่อมแซมตามจริง">ข้อ 8. ทรัพย์สิน/เฟอร์นิเจอร์ชำรุดเสียหาย (คิดตามจริง)</option>
                  <option value="ข้อ 9. ประกอบอาหารที่มีควันและเปลวไฟในห้องพัก" data-amount="1000" data-range="เกณฑ์ตามสัญญา ข้อ 9: ปรับ 1,000 – 3,000 บาท">ข้อ 9. ประกอบอาหารที่มีควัน/เปลวไฟ (1,000 – 3,000 ฿)</option>
                  <option value="ข้อ 10. นำบุคคลภายนอกเข้าพักค้างคืนโดยไม่ได้รับอนุญาต" data-amount="500" data-range="เกณฑ์ตามสัญญา ข้อ 10: ปรับ 500 บาท">ข้อ 10. นำบุคคลภายนอกเข้าพักค้างคืนโดยไม่ได้รับอนุญาต (500 ฿)</option>
                  <option value="ค่าปรับชำระค่าเช่าล่าช้ากว่ากำหนด" data-amount="100" data-range="เกณฑ์ตามสัญญา: ค่าปรับชำระล่าช้า">ชำระค่าเช่าล่าช้ากว่ากำหนด (100 ฿)</option>
                  <option value="__custom__" data-amount="" data-range="ระบุรายละเอียดและจำนวนเงินค่าปรับด้วยตนเอง">อื่นๆ (ระบุรายละเอียดเอง...)</option>
                </select>
                <input type="text" id="fine-custom-desc" class="fine-modern-input" maxlength="500" placeholder="พิมพ์ระบุรายละเอียดค่าปรับเพิ่มเติม..." style="display:none; margin-top:4px;" oninput="syncCustomFineDescription()">
              </div>
            </div>

            <div class="fine-grid-bottom">
              <div class="fine-field-group">
                <label class="fine-field-label" for="fine-amount-input">จำนวนเงินค่าปรับ</label>
                <div class="fine-amount-box">
                  <input type="number" name="amount" id="fine-amount-input" class="fine-modern-input" min="0.01" step="0.01" required placeholder="0.00">
                  <span class="fine-currency-suffix">฿</span>
                </div>
              </div>

              <div class="fine-field-group">
                <label class="fine-field-label" for="fine-date-input">วันที่บันทึก</label>
                <input type="date" name="fineDate" id="fine-date-input" class="fine-modern-input" value="<?= e(date('Y-m-d')) ?>" required>
              </div>

              <button class="button primary fine-submit-btn" type="submit">
                ＋ บันทึกค่าปรับ
              </button>
            </div>

            <div id="fine-rule-range-wrap" style="display:none;">
              <span class="fine-rule-badge" id="fine-rule-range-hint"></span>
            </div>
          </form>
        <?php else: ?>
          <div class="empty-state compact"><strong>ไม่มีสัญญาเช่าที่กำลังใช้งาน</strong></div>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <section class="panel table-panel">
      <div class="panel-heading" style="padding: 20px 24px 12px;">
        <div>
          <p class="eyebrow">UNPAID FINES</p>
          <h2>รายการค่าปรับรอชำระ</h2>
        </div>
        <span class="panel-caption"><?= count($unpaidFinesList) ?> รายการ</span>
      </div>
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>วันที่</th>
              <th>ห้อง / ผู้เช่า</th>
              <th>รายละเอียด</th>
              <th>จำนวนเงิน</th>
              <th>สถานะ</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($unpaidFinesList as $fine): ?>
              <tr>
                <td><?= e(thai_date($fine['fineDate'])) ?></td>
                <td>ห้อง <?= e($fine['roomNumber']) ?> · <?= e($fine['name']) ?></td>
                <td><?= e($fine['description']) ?></td>
                <td><strong><?= money($fine['amount']) ?> ฿</strong></td>
                <td><span class="status-pill pending"><?= e($fine['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (!$unpaidFinesList): ?>
          <div class="empty-state compact"><strong>ไม่มีรายการค่าปรับค้างชำระ</strong></div>
        <?php endif; ?>
      </div>
    </section>

    <section class="panel table-panel" data-history-panel="fine-payments">
      <div class="panel-heading" style="padding: 20px 24px 12px; flex-wrap: wrap; gap: 12px;">
        <div>
          <p class="eyebrow">FINE SLIP VERIFICATION & HISTORY</p>
          <h2>ตรวจสอบสลิปและประวัติชำระค่าปรับ</h2>
        </div>
        <div class="table-filter-controls">
          <?php if ($finePayMonths): ?>
            <select aria-label="เลือกเดือน" data-history-month onchange="filterHistoryTable('fine-payments')">
              <option value="">ทุกเดือน</option>
              <?php foreach (array_keys($finePayMonths) as $mVal): ?>
                <option value="<?= e((string) $mVal) ?>"><?= e($thaiMonthNames[$mVal] ?? (string) $mVal) ?></option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>
          <?php if ($finePayYears): ?>
            <select aria-label="เลือกปี พ.ศ." data-history-year onchange="filterHistoryTable('fine-payments')">
              <option value="">ทุกปี</option>
              <?php foreach (array_keys($finePayYears) as $yVal): ?>
                <option value="<?= e((string) $yVal) ?>"><?= e((string) $yVal) ?></option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>
          <span class="panel-caption" data-history-caption><?= count($finePayments) ?> รายการ</span>
        </div>
      </div>
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>ผู้เช่า</th>
              <th>ห้อง</th>
              <th>รายการค่าปรับ</th>
              <th>วันที่โอน</th>
              <th>ยอดชำระ</th>
              <th>หลักฐาน / ใบเสร็จ</th>
              <th>สถานะ</th>
              <?php if ($user['role'] === 'Admin'): ?><th>ดำเนินการ</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($finePayments as $payment):
              $payMY = extract_be_month_year($payment['paymentDate'] ?? '');
            ?>
              <tr data-history-row data-month="<?= e($payMY['month']) ?>" data-year="<?= e($payMY['year']) ?>">
                <td><?= e($payment['name']) ?></td>
                <td><strong><?= e($payment['roomNumber']) ?></strong></td>
                <td><?= e($payment['fineDescription'] ?? 'ค่าปรับตามสัญญา') ?></td>
                <td><?= e(thai_date($payment['paymentDate'])) ?></td>
                <td><strong><?= money($payment['amount']) ?> ฿</strong></td>
                <td>
                  <?php if ($payment['evidence']): ?>
                    <a class="text-link" href="file.php?id=<?= e($payment['paymentId']) ?>" data-image-preview data-image-alt="หลักฐานการชำระค่าปรับ">ดูสลิป</a>
                  <?php endif; ?>
                  <?php if ($payment['status'] === 'อนุมัติ'): ?>
                    <a class="text-link" href="receipt.php?id=<?= e($payment['paymentId']) ?>">ดูใบเสร็จ</a>
                  <?php endif; ?>
                  <?php if (!$payment['evidence'] && $payment['status'] !== 'อนุมัติ'): ?>—<?php endif; ?>
                </td>
                <td>
                  <span class="status-pill <?= $payment['status'] === 'อนุมัติ' ? 'available' : ($payment['status'] === 'ปฏิเสธ' ? 'rejected' : 'pending') ?>">
                    <?= e($payment['status']) ?>
                  </span>
                </td>
                <?php if ($user['role'] === 'Admin'): ?>
                  <td>
                    <?php if ($payment['status'] === 'รอตรวจสอบ'): ?>
                      <form method="post" action="index.php" class="row-actions">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="paymentId" value="<?= e($payment['paymentId']) ?>">
                        <input type="hidden" name="return_page" value="payments">
                        <button class="button tiny success" name="action" value="review_payment" data-confirm="อนุมัติหลักฐานการชำระค่าปรับนี้หรือไม่?">อนุมัติ</button>
                        <button class="button tiny danger" name="action" value="reject_payment">ปฏิเสธ</button>
                      </form>
                    <?php else: ?>—<?php endif; ?>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (!$finePayments): ?>
          <div class="empty-state compact"><strong>ยังไม่มีรายการชำระค่าปรับ</strong></div>
        <?php endif; ?>
        <div class="payment-filter-empty" data-history-empty hidden>ไม่พบรายการในเดือนหรือปีที่เลือก</div>
      </div>
    </section>
  </div>

  <script>
  function switchAdminPaymentTab(tabKey) {
    document.querySelectorAll('[data-admin-tab-panel]').forEach((panel) => {
      panel.hidden = (panel.dataset.adminTabPanel !== tabKey);
    });
    document.querySelectorAll('[data-admin-tab-btn]').forEach((btn) => {
      btn.classList.toggle('active', btn.dataset.adminTabBtn === tabKey);
    });
    try {
      sessionStorage.setItem('fasai_admin_payment_tab', tabKey);
    } catch (e) {}
  }

  function filterHistoryTable(panelKey) {
    const panel = document.querySelector(`[data-history-panel="${panelKey}"]`);
    if (!panel) return;

    const monthSelect = panel.querySelector('[data-history-month]');
    const yearSelect = panel.querySelector('[data-history-year]');
    const selectedMonth = monthSelect ? monthSelect.value : '';
    const selectedYear = yearSelect ? yearSelect.value : '';

    const rows = Array.from(panel.querySelectorAll('tbody tr[data-history-row]'));
    let visibleCount = 0;

    rows.forEach((tr) => {
      const matchMonth = !selectedMonth || tr.dataset.month === selectedMonth;
      const matchYear = !selectedYear || tr.dataset.year === selectedYear;
      const show = matchMonth && matchYear;
      tr.hidden = !show;
      if (show) visibleCount++;
    });

    const caption = panel.querySelector('[data-history-caption]');
    if (caption) {
      caption.textContent = `${visibleCount} รายการ`;
    }

    const emptyBox = panel.querySelector('[data-history-empty]');
    if (emptyBox) {
      emptyBox.hidden = !(rows.length > 0 && visibleCount === 0);
    }
  }

  function applyContractFineRule() {
    const select = document.getElementById('fine-rule-select');
    const hiddenDesc = document.getElementById('fine-description-hidden');
    const customInput = document.getElementById('fine-custom-desc');
    const amountInput = document.getElementById('fine-amount-input');
    const rangeWrap = document.getElementById('fine-rule-range-wrap');
    const rangeHint = document.getElementById('fine-rule-range-hint');
    if (!select || !hiddenDesc || !amountInput) return;

    const opt = select.options[select.selectedIndex];
    const val = select.value;

    if (!val) {
      hiddenDesc.value = '';
      amountInput.value = '';
      customInput.style.display = 'none';
      customInput.required = false;
      if (rangeWrap) rangeWrap.style.display = 'none';
      return;
    }

    if (val === '__custom__') {
      customInput.style.display = 'block';
      customInput.required = true;
      customInput.focus();
      hiddenDesc.value = customInput.value.trim();
      amountInput.value = '';
    } else {
      customInput.style.display = 'none';
      customInput.required = false;
      customInput.value = '';
      hiddenDesc.value = val;
      amountInput.value = opt.dataset.amount || '';
    }

    if (opt.dataset.range && rangeWrap && rangeHint) {
      rangeHint.textContent = 'ⓘ ' + opt.dataset.range;
      rangeWrap.style.display = 'block';
    } else if (rangeWrap) {
      rangeWrap.style.display = 'none';
    }
  }

  function syncCustomFineDescription() {
    const customInput = document.getElementById('fine-custom-desc');
    const hiddenDesc = document.getElementById('fine-description-hidden');
    if (customInput && hiddenDesc) {
      hiddenDesc.value = customInput.value.trim();
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    try {
      const savedTab = sessionStorage.getItem('fasai_admin_payment_tab');
      if (savedTab && document.querySelector('[data-admin-tab-panel="' + savedTab + '"]')) {
        switchAdminPaymentTab(savedTab);
      }
    } catch (e) {}
  });
  </script>

<?php else: ?>
  <!-- ฝั่งผู้เช่า (Tenant): ดีไซน์ E-Bill ทันสมัย + กล่องแจ้งชำระเงิน + ประวัติการชำระ -->
  <div class="ebill-layout">
    <div>
      <?php if ($tenantInvoices): ?>
        <?php if (count($tenantInvoices) > 1): ?>
          <div class="ebill-tabs" role="tablist" aria-label="เลือกรอบบิล">
            <?php foreach ($tenantInvoices as $idx => $inv): ?>
              <button type="button"
                      class="ebill-tab <?= $idx === 0 ? 'active' : '' ?>"
                      onclick="switchTenantEbill(<?= (int) $idx ?>, this)">
                <span class="ebill-tab-dot"></span>
                รอบบิล <?= e($inv['billMonth']) ?>
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php foreach ($tenantInvoices as $idx => $invoice):
          $waterTotal = (float) $invoice['waterUnit'] * (float) $invoice['waterRate'];
          $electricTotal = (float) $invoice['electricUnit'] * (float) $invoice['electricRate'];
          $isPaid = ($invoice['status'] === 'ชำระแล้ว');
          $isVerifying = (!$isPaid && !empty($invoice['pendingPayment']));
          $canPayThis = (!$isPaid && !$isVerifying);
        ?>
          <article class="ebill-card" data-ebill-panel="<?= (int) $idx ?>" <?= $idx > 0 ? 'hidden' : '' ?>>
            <div class="ebill-header">
              <div class="ebill-brand">
                <div class="ebill-logo" aria-hidden="true">⌂</div>
                <div>
                  <strong>ฟ้าใสเพลส · ใบแจ้งค่าบริการ</strong>
                  <small>ELECTRONIC BILL · ห้องพัก <?= e($invoice['roomNumber']) ?></small>
                </div>
              </div>
              <?php if ($isPaid): ?>
                <span class="ebill-badge paid">✓ ชำระเงินแล้ว</span>
              <?php elseif ($isVerifying): ?>
                <span class="ebill-badge verifying">◷ รอตรวจสอบสลิป</span>
              <?php else: ?>
                <span class="ebill-badge unpaid">● รอชำระเงิน</span>
              <?php endif; ?>
            </div>

            <div class="ebill-hero">
              <div>
                <div class="ebill-total-label">ยอดชำระสุทธิ (รอบบิล <?= e($invoice['billMonth']) ?>)</div>
                <div class="ebill-total-amount"><?= money($invoice['total']) ?> <span>บาท</span></div>
              </div>
              <div class="ebill-meta-pills">
                <div class="ebill-meta-item">ห้องพัก: <strong><?= e($invoice['roomNumber']) ?></strong></div>
                <div class="ebill-meta-item">ครบกำหนดชำระ: <strong><?= e(thai_date($invoice['dueDate'])) ?></strong></div>
              </div>
            </div>

            <div class="ebill-body">
              <div class="ebill-section-title">รายละเอียดค่าใช้จ่าย</div>
              <div class="ebill-line-items">
                <div class="ebill-line">
                  <div class="ebill-line-info">
                    <div class="ebill-line-icon rent" aria-hidden="true">⌂</div>
                    <div>
                      <strong>ค่าเช่าห้องพักประจำเดือน</strong>
                      <small>อัตราค่าเช่ารายเดือน · ห้อง <?= e($invoice['roomNumber']) ?></small>
                    </div>
                  </div>
                  <div class="ebill-line-price"><?= money($invoice['rentAmount']) ?> ฿</div>
                </div>

                <div class="ebill-line">
                  <div class="ebill-line-info">
                    <div class="ebill-line-icon water" aria-hidden="true">💧</div>
                    <div>
                      <strong>ค่าน้ำประปา</strong>
                      <small>ใช้ไป <?= money($invoice['waterUnit']) ?> หน่วย × <?= money($invoice['waterRate']) ?> บาท/หน่วย</small>
                    </div>
                  </div>
                  <div class="ebill-line-price"><?= money($waterTotal) ?> ฿</div>
                </div>

                <div class="ebill-line">
                  <div class="ebill-line-info">
                    <div class="ebill-line-icon electric" aria-hidden="true">⚡</div>
                    <div>
                      <strong>ค่าไฟฟ้า</strong>
                      <small>ใช้ไป <?= money($invoice['electricUnit']) ?> หน่วย × <?= money($invoice['electricRate']) ?> บาท/หน่วย</small>
                    </div>
                  </div>
                  <div class="ebill-line-price"><?= money($electricTotal) ?> ฿</div>
                </div>
              </div>
            </div>

            <div class="ebill-footer">
              <span class="muted" style="font-size: 0.84rem;">เลขที่บิล: <strong class="mono"><?= e($invoice['invoiceId']) ?></strong></span>
              <?php if ($canPayThis): ?>
                <button type="button"
                        class="button primary"
                        onclick="selectBillingItemForPay('invoice:<?= e($invoice['invoiceId']) ?>')">
                  ชำระบิลนี้ (<?= money($invoice['total']) ?> ฿) →
                </button>
              <?php elseif ($isVerifying): ?>
                <span style="font-size: 0.86rem; color: #1d4ed8; font-weight: 600;">ส่งหลักฐานแล้ว กำลังรอผู้ดูแลตรวจสอบ</span>
              <?php else: ?>
                <span style="font-size: 0.86rem; color: #059669; font-weight: 600;">✓ ชำระเรียบร้อยแล้ว</span>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="panel empty-state">
          <span>▤</span>
          <strong>ยังไม่มีใบแจ้งหนี้ในขณะนี้</strong>
          <small>เมื่อผู้ดูแลออกบิลประจำเดือน จะแสดงรูปแบบบิลดิจิทัลที่นี่</small>
        </div>
      <?php endif; ?>

      <div class="fines-compact-list">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
          <div>
            <p class="eyebrow" style="margin:0;">FINES & CHARGES</p>
            <h3 style="margin:2px 0 0; font-size:1.05rem;">รายการค่าปรับ</h3>
          </div>
          <span class="panel-caption"><?= count($tenantFines) ?> รายการ</span>
        </div>

        <?php if ($tenantFines): ?>
          <?php foreach ($tenantFines as $fine):
            $finePaid = ($fine['status'] === 'ชำระแล้ว');
            $fineVerifying = (!$finePaid && !empty($fine['pendingPayment']));
          ?>
            <div class="fine-row-item">
              <div>
                <strong style="display:block; font-size:0.94rem;"><?= e($fine['description']) ?></strong>
                <small class="muted">ห้อง <?= e($fine['roomNumber']) ?> · วันที่ <?= e(thai_date($fine['fineDate'])) ?></small>
              </div>
              <div style="display:flex; align-items:center; gap:10px;">
                <strong><?= money($fine['amount']) ?> ฿</strong>
                <span class="status-pill <?= $finePaid ? 'available' : 'pending' ?>">
                  <?= e($finePaid ? 'ชำระแล้ว' : ($fineVerifying ? 'รอตรวจสอบ' : 'รอชำระ')) ?>
                </span>
                <?php if (!$finePaid && !$fineVerifying): ?>
                  <button type="button" class="button tiny primary" onclick="selectBillingItemForPay('fine:<?= e($fine['fineId']) ?>')">ชำระ</button>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="muted" style="margin: 8px 0 0; font-size: 0.9rem;">ไม่มีรายการค่าปรับค้างชำระ</p>
        <?php endif; ?>
      </div>
    </div>

    <aside class="paybox-card" id="tenant-paybox">
      <div class="panel-heading" style="margin-bottom: 14px;">
        <div>
          <p class="eyebrow">SUBMIT PAYMENT</p>
          <h2 style="font-size: 1.22rem;">แจ้งชำระเงิน / แนบสลิป</h2>
        </div>
      </div>

      <?php if ($unpaidInvoices || $unpaidFines): ?>
        <div class="paybox-amount-banner" id="paybox-summary-banner" style="display:none;">
          <span>ยอดที่ต้องโอนชำระ</span>
          <strong id="paybox-summary-amount">0.00 ฿</strong>
        </div>

        <form method="post" action="index.php" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:14px;">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="submit_payment">
          <input type="hidden" name="return_page" value="payments">

          <label>เลือกรายการที่ต้องการชำระ
            <select name="billingItem" id="billing-item-select" required onchange="updatePayboxSummary()">
              <option value="">-- เลือกบิลหรือค่าปรับ --</option>
              <?php if ($unpaidInvoices): ?>
                <optgroup label="ใบแจ้งหนี้รายเดือน">
                  <?php foreach ($unpaidInvoices as $invoice): ?>
                    <option value="invoice:<?= e($invoice['invoiceId']) ?>" data-amount="<?= e(money($invoice['amount'])) ?>">
                      รอบบิล <?= e($invoice['billMonth']) ?> · ห้อง <?= e($invoice['roomNumber']) ?> (<?= money($invoice['amount']) ?> ฿)
                    </option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endif; ?>
              <?php if ($unpaidFines): ?>
                <optgroup label="รายการค่าปรับ">
                  <?php foreach ($unpaidFines as $fine): ?>
                    <option value="fine:<?= e($fine['fineId']) ?>" data-amount="<?= e(money($fine['amount'])) ?>">
                      ค่าปรับ: <?= e($fine['description']) ?> (<?= money($fine['amount']) ?> ฿)
                    </option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endif; ?>
            </select>
          </label>

          <label>วันที่โอนชำระ
            <input type="date" name="paymentDate" value="<?= e(date('Y-m-d')) ?>" required>
          </label>

          <div>
            <span style="display:block; font-weight:600; font-size:0.9rem; margin-bottom:6px;">แนบหลักฐานการโอน (สลิป)</span>
            <div class="slip-upload-zone">
              <input type="file" name="evidence" id="slip-file-input" accept="image/jpeg,image/png,image/webp" required onchange="previewPaymentSlip(this)" style="width:100%;">
              <small class="muted" style="display:block; margin-top:6px;">รองรับไฟล์ JPG, PNG, WEBP</small>
              <img id="slip-preview-img" class="slip-preview-img" alt="ตัวอย่างสลิปการโอนเงิน">
            </div>
          </div>

          <button class="button primary" type="submit" style="width:100%; justify-content:center; padding: 12px;">
            ส่งหลักฐานการชำระเงิน
          </button>
        </form>
      <?php else: ?>
        <div class="empty-state compact" style="padding: 28px 16px;">
          <span>✓</span>
          <strong>ไม่มีรายการค้างชำระ</strong>
          <small>คุณชำระบิลทั้งหมดครบถ้วนแล้ว หรืออยู่ระหว่างรอตรวจสอบสลิป</small>
        </div>
      <?php endif; ?>
    </aside>
  </div>

  <section class="panel table-panel" data-history-panel="payments">
    <div class="panel-heading" style="padding: 20px 24px 12px; flex-wrap: wrap; gap: 12px;">
      <div>
        <p class="eyebrow">HISTORY</p>
        <h2>ประวัติการชำระเงิน</h2>
      </div>
      <div class="table-filter-controls">
        <?php if ($tenantPayMonths): ?>
          <select aria-label="เลือกเดือน" data-history-month onchange="filterHistoryTable('payments')">
            <option value="">ทุกเดือน</option>
            <?php foreach (array_keys($tenantPayMonths) as $mVal): ?>
              <option value="<?= e((string) $mVal) ?>"><?= e($thaiMonthNames[$mVal] ?? (string) $mVal) ?></option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
        <?php if ($tenantPayYears): ?>
          <select aria-label="เลือกปี พ.ศ." data-history-year onchange="filterHistoryTable('payments')">
            <option value="">ทุกปี</option>
            <?php foreach (array_keys($tenantPayYears) as $yVal): ?>
              <option value="<?= e((string) $yVal) ?>"><?= e((string) $yVal) ?></option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
        <span class="panel-caption" data-history-caption><?= count($payments) ?> รายการ</span>
      </div>
    </div>
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>ห้อง</th>
            <th>รายการ</th>
            <th>วันที่</th>
            <th>ยอดชำระ</th>
            <th>หลักฐาน / ใบเสร็จ</th>
            <th>สถานะ</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $payment):
            $payMY = extract_be_month_year($payment['paymentDate'] ?? '');
          ?>
            <tr data-history-row data-month="<?= e($payMY['month']) ?>" data-year="<?= e($payMY['year']) ?>">
              <td><strong><?= e($payment['roomNumber']) ?></strong></td>
              <td>
                <?php if (!empty($payment['billMonth'])): ?>
                  ใบแจ้งหนี้รอบ <?= e($payment['billMonth']) ?>
                <?php elseif (!empty($payment['fineDescription'])): ?>
                  ค่าปรับ: <?= e($payment['fineDescription']) ?>
                <?php else: ?>
                  รายการเดิม
                <?php endif; ?>
              </td>
              <td><?= e(thai_date($payment['paymentDate'])) ?></td>
              <td><strong><?= money($payment['amount']) ?> ฿</strong></td>
              <td>
                <?php if ($payment['evidence']): ?>
                  <a class="text-link" href="file.php?id=<?= e($payment['paymentId']) ?>" data-image-preview data-image-alt="หลักฐานการชำระเงิน">ดูสลิป</a>
                <?php endif; ?>
                <?php if ($payment['status'] === 'อนุมัติ'): ?>
                  <a class="text-link" href="receipt.php?id=<?= e($payment['paymentId']) ?>">ดูใบเสร็จ</a>
                <?php endif; ?>
                <?php if (!$payment['evidence'] && $payment['status'] !== 'อนุมัติ'): ?>—<?php endif; ?>
              </td>
              <td>
                <span class="status-pill <?= $payment['status'] === 'อนุมัติ' ? 'available' : ($payment['status'] === 'ปฏิเสธ' ? 'rejected' : 'pending') ?>">
                  <?= e($payment['status']) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php if (!$payments): ?>
        <div class="empty-state">
          <span>฿</span>
          <strong>ยังไม่มีประวัติการชำระ</strong>
          <small>รายการชำระเงินจะแสดงที่นี่</small>
        </div>
      <?php endif; ?>
      <div class="payment-filter-empty" data-history-empty hidden>ไม่พบประวัติการชำระเงินในเดือนหรือปีที่เลือก</div>
    </div>
  </section>

  <script>
  function switchTenantEbill(index, tabBtn) {
    document.querySelectorAll('[data-ebill-panel]').forEach((panel) => {
      panel.hidden = (Number(panel.dataset.ebillPanel) !== index);
    });
    document.querySelectorAll('.ebill-tab').forEach((btn) => btn.classList.remove('active'));
    if (tabBtn) tabBtn.classList.add('active');
  }

  function filterHistoryTable(panelKey) {
    const panel = document.querySelector(`[data-history-panel="${panelKey}"]`);
    if (!panel) return;

    const monthSelect = panel.querySelector('[data-history-month]');
    const yearSelect = panel.querySelector('[data-history-year]');
    const selectedMonth = monthSelect ? monthSelect.value : '';
    const selectedYear = yearSelect ? yearSelect.value : '';

    const rows = Array.from(panel.querySelectorAll('tbody tr[data-history-row]'));
    let visibleCount = 0;

    rows.forEach((tr) => {
      const matchMonth = !selectedMonth || tr.dataset.month === selectedMonth;
      const matchYear = !selectedYear || tr.dataset.year === selectedYear;
      const show = matchMonth && matchYear;
      tr.hidden = !show;
      if (show) visibleCount++;
    });

    const caption = panel.querySelector('[data-history-caption]');
    if (caption) {
      caption.textContent = `${visibleCount} รายการ`;
    }

    const emptyBox = panel.querySelector('[data-history-empty]');
    if (emptyBox) {
      emptyBox.hidden = !(rows.length > 0 && visibleCount === 0);
    }
  }

  function updatePayboxSummary() {
    const select = document.getElementById('billing-item-select');
    const banner = document.getElementById('paybox-summary-banner');
    const amountEl = document.getElementById('paybox-summary-amount');
    if (!select || !banner || !amountEl) return;

    const opt = select.options[select.selectedIndex];
    if (opt && opt.dataset.amount) {
      amountEl.textContent = opt.dataset.amount + ' ฿';
      banner.style.display = 'flex';
    } else {
      banner.style.display = 'none';
    }
  }

  function selectBillingItemForPay(itemValue) {
    const select = document.getElementById('billing-item-select');
    const paybox = document.getElementById('tenant-paybox');
    if (!select) return;
    select.value = itemValue;
    updatePayboxSummary();
    if (paybox) {
      paybox.scrollIntoView({ behavior: 'smooth', block: 'center' });
      select.focus();
    }
  }

  function previewPaymentSlip(input) {
    const img = document.getElementById('slip-preview-img');
    const file = input.files && input.files[0];
    if (!img) return;
    if (!file) {
      img.style.display = 'none';
      img.src = '';
      return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
      img.src = e.target.result;
      img.style.display = 'block';
    };
    reader.readAsDataURL(file);
  }

  document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('billing-item-select');
    if (select && select.options.length === 2) {
      select.selectedIndex = 1;
    }
    updatePayboxSummary();
  });
  </script>
<?php endif; ?>