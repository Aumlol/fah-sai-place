<?php
$user = require_login();
$allowed = [
    'dashboard' => ['Tenant', 'Staff', 'Admin', 'Technician'],
    'rooms' => ['Staff', 'Admin'],
    'tenants' => ['Staff', 'Admin'],
    'users' => ['Admin'],
    'meters' => ['Staff', 'Admin'],
    'payments' => ['Tenant', 'Staff', 'Admin'],
    'repairs' => ['Tenant', 'Staff', 'Admin', 'Technician'],
    'news' => ['Tenant', 'Staff', 'Admin', 'Technician'],
    'help' => ['Tenant', 'Staff', 'Admin', 'Technician'],
    'reports' => ['Staff', 'Admin'],
    'profile' => ['Tenant', 'Staff', 'Admin', 'Technician'],
];
if (!isset($allowed[$page]) || !in_array($user['role'], $allowed[$page], true)) {
    http_response_code(403);
    $page = 'dashboard';
}

$menuIcons = [
    'dashboard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>',
    'rooms' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/><path d="M9 18v.01"/></svg>',
    'tenants' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'users' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><circle cx="12" cy="10" r="3"/><path d="M7 18.5a5 5 0 0 1 10 0"/></svg>',
    'meters' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>',
    'payments' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="6" y1="15" x2="10" y2="15"/></svg>',
    'repairs' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>',
    'news' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>',
    'help' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    'reports' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
    'profile' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
];

$menus = [
    ['dashboard', 'ภาพรวม', $menuIcons['dashboard'], ['Tenant', 'Staff', 'Admin', 'Technician']],
    ['rooms', 'ห้องพัก', $menuIcons['rooms'], ['Staff', 'Admin']],
    ['tenants', 'ผู้เช่าและสัญญา', $menuIcons['tenants'], ['Staff', 'Admin']],
    ['users', 'บัญชีผู้ใช้งาน', $menuIcons['users'], ['Admin']],
    ['meters', 'มิเตอร์', $menuIcons['meters'], ['Staff', 'Admin']],
    ['payments', 'ใบแจ้งหนี้', $menuIcons['payments'], ['Tenant', 'Admin']],
    ['repairs', 'งานซ่อม', $menuIcons['repairs'], ['Tenant', 'Staff', 'Admin', 'Technician']],
    ['news', 'ข่าวสาร', $menuIcons['news'], ['Tenant', 'Staff', 'Admin', 'Technician']],
    ['reports', 'รายงาน', $menuIcons['reports'], ['Staff', 'Admin']],
];
$tenantContract = null;
if ($user['role'] === 'Tenant') {
    $query = db()->prepare("SELECT c.contractId, c.roomId, c.startDate, c.endDate, c.deposit, r.roomNumber, r.rentalPrice, u1.name AS mainTenantName, u2.name AS coTenantName FROM Contract c JOIN Room r ON r.roomId = c.roomId JOIN `User` u1 ON u1.userId = c.userId LEFT JOIN `User` u2 ON u2.userId = c.coTenantUserId WHERE (c.userId = ? OR c.coTenantUserId = ?) AND c.status = 'ใช้งานอยู่' ORDER BY c.startDate DESC LIMIT 1");
    $query->execute([$user['userId'], $user['userId']]);
    $tenantContract = $query->fetch() ?: null;
}
$title = ($page === 'payments') ? 'ใบแจ้งหนี้' : page_title($page);
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> | <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="assets/css/app.css?v=<?= e((string) filemtime(__DIR__ . '/../assets/css/app.css')) ?>-v6">
  <script defer src="assets/js/app.js?v=<?= e((string) filemtime(__DIR__ . '/../assets/js/app.js')) ?>-v6"></script>
</head>
<body class="app-body">
<div class="sidebar-backdrop" data-sidebar-backdrop></div>
<div class="app-shell">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
      <a class="sidebar-brand" href="index.php">
        <span class="brand-mark small">
          <svg viewBox="0 0 32 32" aria-hidden="true"><path d="M5 15 16 6l11 9M9 14v11h14V14M7 28c3-2 5-2 9 0s6 2 9 0" /></svg>
        </span>
        <span class="brand-copy">
          <strong>ฟ้าใสเพลส</strong>
          <small>RESIDENTIAL · CHIANG MAI</small>
        </span>
      </a>
      <button class="sidebar-collapse-btn" type="button" data-sidebar-collapse aria-label="ย่อหรือขยายแถบเมนู" title="ย่อ/ขยายเมนู">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
    </div>

    <div class="sidebar-scroll">
      <div class="sidebar-label">เมนูจัดการระบบ</div>
      <nav class="main-nav">
        <?php foreach ($menus as [$key, $label, $iconSvg, $roles]): if (!in_array($user['role'], $roles, true)) continue; ?>
          <a class="nav-link <?= $page === $key ? 'active' : '' ?>" href="index.php?page=<?= e($key) ?>" title="<?= e($label) ?>">
            <span class="nav-icon" aria-hidden="true"><?= $iconSvg ?></span>
            <span class="nav-text"><?= e($label) ?></span>
            <?php if ($key === 'payments' && $user['role'] === 'Admin'): ?><span class="nav-dot" title="มีรายการรอตรวจสอบ"></span><?php endif; ?>
          </a>
        <?php endforeach; ?>
      </nav>
    </div>

    <div class="sidebar-bottom">
      <a class="help-note <?= $page === 'help' ? 'active' : '' ?>" href="index.php?page=help" title="ศูนย์ช่วยเหลือ">
        <span class="help-orbit" aria-hidden="true"><?= $menuIcons['help'] ?></span>
        <div class="help-copy">
          <strong>ศูนย์ช่วยเหลือ</strong>
          <small>คำแนะนำและติดต่อผู้ดูแล</small>
        </div>
      </a>
      <a class="nav-link <?= $page === 'profile' ? 'active' : '' ?>" href="index.php?page=profile" title="ข้อมูลส่วนตัว">
        <span class="nav-icon" aria-hidden="true"><?= $menuIcons['profile'] ?></span>
        <span class="nav-text">ข้อมูลส่วนตัว</span>
      </a>
    </div>
  </aside>
  <main class="main-area">
    <header class="topbar">
      <button class="icon-button sidebar-toggle-btn" type="button" aria-label="เปิดหรือปิดแถบเมนู" title="เปิด/ปิดแถบเมนู" data-menu-toggle>
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <div class="breadcrumb"><span>ฟ้าใสเพลส</span><span class="breadcrumb-divider">/</span><strong><?= e($title) ?></strong></div>
      <div class="topbar-actions"><span class="date-stamp"><?= e(thai_date(date('Y-m-d'))) ?></span><span class="avatar"><?= e(mb_substr($user['name'], 0, 1, 'UTF-8')) ?></span><div class="user-meta"><strong><?= e($user['name']) ?></strong><small><?= e($user['role']) ?></small></div><form method="post" action="index.php" class="logout-form" id="logout-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="logout"><button class="button danger" type="button" onclick="document.getElementById('logout-confirm-modal').showModal()"><span aria-hidden="true">↪</span>ออกจากระบบ</button></form></div>
    </header>
    <div class="content-wrap">
      <?php if ($flashMessage): ?><div class="alert <?= e($flashMessage['type']) ?>" data-alert><?= e($flashMessage['message']) ?><button type="button" aria-label="ปิด" data-dismiss>×</button></div><?php endif; ?>

      <?php if ($page === 'dashboard'): ?>
        <?php
        if ($user['role'] === 'Tenant') {
          $repairCountStatement = db()->prepare('SELECT COUNT(*) FROM Repair WHERE userId = ?');
          $repairCountStatement->execute([$user['userId']]);
            $metrics = [
                ['ห้องพักของฉัน', $tenantContract ? $tenantContract['roomNumber'] : 'ยังไม่มีสัญญา', '⌂', 'blue'],
                ['ค่าเช่ารายเดือน', $tenantContract ? money($tenantContract['rentalPrice']) . ' ฿' : '—', '฿', 'mint'],
                ['คำร้องแจ้งซ่อม', (string) $repairCountStatement->fetchColumn(), '⌕', 'orange'],
                ['สถานะสัญญา', $tenantContract ? 'ใช้งานอยู่' : 'ไม่มีสัญญา', '▣', 'blue'],
            ];
        } else {
            $roomCount = (int) db()->query('SELECT COUNT(*) FROM Room')->fetchColumn();
            $occupiedCount = (int) db()->query("SELECT COUNT(*) FROM Room WHERE roomStatus = 'มีผู้เช่า'")->fetchColumn();
            $openRepairs = (int) db()->query("SELECT COUNT(*) FROM Repair WHERE repairStatus NOT IN ('ซ่อมเสร็จแล้ว', 'ยกเลิก')")->fetchColumn();
            $pendingPayments = $user['role'] === 'Admin' ? (int) db()->query("SELECT COUNT(*) FROM Payment WHERE status = 'รอตรวจสอบ'")->fetchColumn() : 0;
            $metrics = [
                ['ห้องพักทั้งหมด', (string) $roomCount, '⌂', 'blue'],
                ['มีผู้เช่า', (string) $occupiedCount, '♙', 'mint'],
                ['งานซ่อมที่เปิดอยู่', (string) $openRepairs, '⌕', 'orange'],
                [$user['role'] === 'Admin' ? 'รอตรวจสอบการชำระ' : 'ห้องว่าง', $user['role'] === 'Admin' ? (string) $pendingPayments : (string) max(0, $roomCount - $occupiedCount), '฿', 'blue'],
            ];
        }
        ?>
        <section class="page-heading"><div><p class="eyebrow">FAH SAI PLACE / OVERVIEW</p><h1>สวัสดี, <?= e($user['name']) ?></h1><p class="muted">ภาพรวมการดำเนินงานหอพัก ณ วันนี้</p></div><?php if ($user['role'] === 'Admin'): ?><a class="button primary" href="index.php?page=rooms">จัดการห้องพัก <span>↗</span></a><?php endif; ?></section>
        <section class="overview-clock panel"><span class="clock-icon" aria-hidden="true">◷</span><div><small>เวลาปัจจุบัน · ประเทศไทย</small><strong data-digital-clock><?= e(date('H:i:s')) ?></strong></div><span class="clock-date"><?= e('วัน' . ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'][date('w')] . ', ' . thai_date(date('Y-m-d'))) ?></span></section>
        <section class="metric-grid"><?php foreach ($metrics as [$label, $value, $icon, $color]): ?><article class="metric-card"><div class="metric-top"><span><?= e($label) ?></span><span class="metric-icon <?= e($color) ?>"><?= e($icon) ?></span></div><strong><?= e($value) ?></strong><small><?= $label === 'ค่าเช่ารายเดือน' ? 'บาทต่อเดือน' : 'ข้อมูลจากระบบปัจจุบัน' ?></small></article><?php endforeach; ?></section>
        <?php if ($user['role'] === 'Tenant' && $tenantContract): ?>
          <section class="panel lease-summary">
            <div class="panel-heading">
              <div><p class="eyebrow">ACTIVE LEASE</p><h2>รายละเอียดสัญญาเช่า</h2></div>
              <div style="display:flex; align-items:center; gap:10px;">
                <span class="status-pill available">ใช้งานอยู่</span>
                <a class="button tiny primary" href="contract_print.php?id=<?= e($tenantContract['contractId']) ?>" target="_blank" rel="noopener">ดู/พิมพ์สัญญา</a>
              </div>
            </div>
            <div class="lease-details">
              <div><small>เลขที่สัญญา</small><strong class="mono"><?= e($tenantContract['contractId']) ?></strong></div>
              <div><small>ห้องพัก</small><strong><?= e($tenantContract['roomNumber']) ?></strong></div>
              <div><small>ผู้พักอาศัย</small><strong><?= e($tenantContract['mainTenantName']) ?><?= !empty($tenantContract['coTenantName']) ? ' & ' . e($tenantContract['coTenantName']) : '' ?></strong></div>
              <div><small>ระยะเวลาสัญญา</small><strong><?= e(thai_date($tenantContract['startDate'])) ?> – <?= $tenantContract['endDate'] ? e(thai_date($tenantContract['endDate'])) : 'ไม่กำหนดวันสิ้นสุด' ?></strong></div>
              <div><small>เงินประกัน</small><strong><?= money($tenantContract['deposit']) ?> ฿</strong></div>
            </div>
          </section>
        <?php endif; ?>
        <section class="dashboard-grid">
          <article class="panel overview-panel"><div class="panel-heading"><div><p class="eyebrow">QUICK ACCESS</p><h2>รายการที่ใช้งานบ่อย</h2></div><span class="panel-caption">เลือกทำรายการ</span></div><div class="quick-grid">
          <?php foreach (array_filter($menus, fn($item) => in_array($user['role'], $item[3], true) && $item[0] !== 'dashboard') as [$key, $label, $iconSvg]): ?><a class="quick-link" href="index.php?page=<?= e($key) ?>"><span class="quick-icon" aria-hidden="true"><?= $iconSvg ?></span><span><strong><?= e($label) ?></strong><small>เปิดหน้าจัดการ</small></span><b>↗</b></a><?php endforeach; ?>
          </div></article>
          <article class="panel news-panel"><div class="panel-heading"><div><p class="eyebrow">NOTICE BOARD</p><h2>ประกาศล่าสุด</h2></div><a class="text-link" href="index.php?page=news">ดูทั้งหมด →</a></div><?php $latestNews = db()->query('SELECT * FROM News ORDER BY publishDate DESC LIMIT 3')->fetchAll(); if (!$latestNews): ?><div class="empty-state compact"><span>▤</span><strong>ยังไม่มีประกาศ</strong><small>ประกาศจากหอพักจะแสดงที่นี่</small></div><?php else: foreach ($latestNews as $item): ?><button class="news-item news-preview-trigger" type="button" data-news-preview="<?= e(json_encode($item, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>"><span class="news-date"><?= e(date('d M', strtotime($item['publishDate']))) ?></span><span><strong><?= e($item['title']) ?></strong><span class="news-summary"><?= e(mb_strimwidth($item['detail'], 0, 100, '...', 'UTF-8')) ?></span><?php if (!empty($item['image'])): ?><small class="news-image-hint">แตะเพื่อดูรูปประกาศ</small><?php endif; ?></span><span class="news-open" aria-hidden="true">↗</span></button><?php endforeach; endif; ?></article>
        </section>

      <?php elseif ($page === 'rooms'): ?>
        <?php $rooms = db()->query("SELECT r.*, c.userId AS activeTenantId, c.startDate AS activeStartDate, c.deposit AS activeDeposit, CONCAT_WS(' / ', u1.name, u2.name) AS activeTenantName FROM Room r LEFT JOIN Contract c ON c.contractId = (SELECT c2.contractId FROM Contract c2 WHERE c2.roomId = r.roomId AND c2.status = 'ใช้งานอยู่' ORDER BY c2.startDate DESC, c2.contractId DESC LIMIT 1) LEFT JOIN `User` u1 ON u1.userId = c.userId LEFT JOIN `User` u2 ON u2.userId = c.coTenantUserId ORDER BY r.roomNumber")->fetchAll(); $roomTenants = db()->query("SELECT u.userId, u.name, c.roomId AS activeRoomId FROM `User` u LEFT JOIN Contract c ON (c.userId = u.userId OR c.coTenantUserId = u.userId) AND c.status = 'ใช้งานอยู่' WHERE u.role = 'Tenant' ORDER BY u.name")->fetchAll(); ?>
        <section class="page-heading"><div><p class="eyebrow">ROOM DIRECTORY</p><h1>ห้องพัก</h1><p class="muted">จัดการสถานะห้องพักและอัตราค่าเช่า</p></div><?php if ($user['role'] === 'Admin'): ?><button class="button primary" type="button" data-modal-open="room-modal">＋ เพิ่มห้องพัก</button><?php endif; ?></section>
        <section class="panel table-panel"><div class="table-toolbar"><div class="search-box"><span>⌕</span><input type="search" placeholder="ค้นหาเลขห้อง..." data-table-search></div><span class="panel-caption"><?= count($rooms) ?> ห้อง</span></div><div class="table-scroll"><table><thead><tr><th>ห้อง</th><th>ผู้เช่าปัจจุบัน</th><th>สถานะ</th><th>ค่าเช่าต่อเดือน</th><th></th></tr></thead><tbody><?php foreach ($rooms as $room): ?><tr data-search-row><td><strong class="room-number"><?= e($room['roomNumber']) ?></strong></td><td><?= e($room['activeTenantName'] ?: '—') ?></td><td><span class="status-pill <?= $room['roomStatus'] === 'มีผู้เช่า' ? 'occupied' : ($room['roomStatus'] === 'ว่าง' ? 'available' : 'pending') ?>"><?= e($room['roomStatus']) ?></span></td><td><?= money($room['rentalPrice']) ?> ฿</td><td><?php if (in_array($user['role'], ['Staff', 'Admin'], true)): ?><button class="icon-button" type="button" data-room='<?= e(json_encode($room, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>' data-edit-room aria-label="แก้ไขห้องพัก">✎</button><?php endif; ?></td></tr><?php endforeach; ?></tbody></table><?php if (!$rooms): ?><div class="empty-state"><span>⌂</span><strong>ยังไม่มีข้อมูลห้องพัก</strong><small>เริ่มต้นเพิ่มห้องพักในระบบ</small></div><?php endif; ?></div></section>
        <?php if (in_array($user['role'], ['Staff', 'Admin'], true)): ?>
          <dialog class="modal" id="room-modal">
            <form method="post" action="index.php" class="modal-form">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="action" value="save_room">
              <input type="hidden" name="return_page" value="rooms">
              <input type="hidden" name="roomId">
              <div class="modal-heading"><div><p class="eyebrow">ROOM DETAILS</p><h2>ข้อมูลห้องพัก</h2></div><button class="icon-button" type="button" data-modal-close aria-label="ปิด">×</button></div>
              <label>เลขห้อง<input name="roomNumber" required maxlength="20" placeholder="เช่น 101"></label>
              <label>ค่าเช่าต่อเดือน<input name="rentalPrice" type="number" min="0" step="0.01" required placeholder="เช่น 3500"></label>
              <label>สถานะห้อง<select name="roomStatus" data-room-status><option>ว่าง</option><option>มีผู้เช่า</option><option>ปิดปรับปรุง</option></select></label>
              <div class="room-assignment-fields" data-room-assignment-fields hidden>
                <label>ผู้เช่าที่ทำสัญญา<select name="tenantId" data-room-tenant><option value="">เลือกผู้เช่า</option><?php foreach ($roomTenants as $tenant): ?><option value="<?= e($tenant['userId']) ?>" data-active-room="<?= e($tenant['activeRoomId'] ?? '') ?>"><?= e($tenant['name']) ?></option><?php endforeach; ?></select></label>
                <div class="form-row">
                  <label>วันที่เริ่มสัญญา<input name="startDate" type="date" data-room-start></label>
                  <label>เงินประกัน<input name="deposit" type="number" min="0" step="0.01" value="3000" data-room-deposit></label>
                </div>
                <p class="form-hint" data-room-assignment-note>เลือกผู้เช่าที่ยังไม่มีสัญญาใช้งานอยู่ และระบุวันที่เริ่มสัญญา</p>
              </div>
              <div class="modal-actions"><button class="button secondary" type="button" data-modal-close>ยกเลิก</button><button class="button primary" type="submit">บันทึกข้อมูล</button></div>
            </form>
          </dialog>
        <?php endif; ?>

      <?php elseif ($page === 'tenants'): ?>
        <?php
        $tenants = db()->query("
            SELECT u.userId, u.name, u.phone, u.email,
                   u.birthDate, u.address,
                   u.occupation, u.workplace,
                   u.emergencyContactName, u.emergencyContactRelation, u.emergencyContactPhone,
                   c.contractId, c.userId AS mainUserId, c.coTenantUserId,
                   c.idCardDoc, c.coIdCardDoc, c.tenantSignature AS signedContractDoc,
                   c.startDate, c.endDate, c.deposit, c.status,
                   r.roomNumber, r.rentalPrice,
                   u2.name AS coTenantName, u2.phone AS coTenantPhone
            FROM `User` u
            LEFT JOIN Contract c ON (c.userId = u.userId OR c.coTenantUserId = u.userId) AND c.status = 'ใช้งานอยู่'
            LEFT JOIN Room r ON r.roomId = c.roomId
            LEFT JOIN `User` u2 ON u2.userId = c.coTenantUserId
            WHERE u.role = 'Tenant'
            ORDER BY (c.contractId IS NULL), r.roomNumber, u.name
        ")->fetchAll();
        $availableTenants = db()->query("
            SELECT u.userId, u.name, u.username, u.phone, u.email,
                   u.birthDate, u.address,
                   u.occupation, u.workplace,
                   u.emergencyContactName, u.emergencyContactRelation, u.emergencyContactPhone
            FROM `User` u
            WHERE u.role = 'Tenant'
              AND NOT EXISTS (SELECT 1 FROM Contract c WHERE (c.userId = u.userId OR c.coTenantUserId = u.userId) AND c.status = 'ใช้งานอยู่')
            ORDER BY u.name
        ")->fetchAll();
        $freeRooms = db()->query("SELECT roomId, roomNumber, rentalPrice FROM Room WHERE roomStatus = 'ว่าง' ORDER BY roomNumber")->fetchAll();
        ?>
        <section class="page-heading">
          <div>
            <p class="eyebrow">RESIDENTS & LEASES</p>
            <h1>ผู้เช่าและสัญญา</h1>
            <p class="muted">พิมพ์สัญญาเพื่อลงนามจริง แล้วบันทึกเอกสารที่ลงนามแล้วไว้ตรวจสอบย้อนหลัง</p>
          </div>
            <?php if ($user['role'] === 'Admin'): ?>
              <button class="button primary" type="button" onclick="openAssignContractModal('')">ทำสัญญาเช่าใหม่</button>
            <?php endif; ?>
        </section>

        <section class="panel table-panel">
          <div class="table-toolbar">
            <div class="search-box"><span>⌕</span><input type="search" placeholder="ค้นหาชื่อผู้เช่า หรือเลขห้อง..." data-table-search></div>
            <span class="panel-caption"><?= count($tenants) ?> ราย</span>
          </div>
          <div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th>ผู้เช่า</th>
                  <th>เอกสารสัญญา</th>
                  <th>ห้อง</th>
                  <th>เบอร์โทรศัพท์ / ฉุกเฉิน</th>
                  <th>ที่อยู่ / อาชีพ</th>
                  <th>เริ่มสัญญา</th>
                  <th>สถานะ</th>
                  <th>ดำเนินการ</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($tenants as $tenant):
                  $isCoTenant = !empty($tenant['contractId']) && $tenant['coTenantUserId'] === $tenant['userId'];
                  $docFile = $isCoTenant ? ($tenant['coIdCardDoc'] ?? '') : ($tenant['idCardDoc'] ?? '');
                  $signedDoc = (!empty($tenant['signedContractDoc']) && !str_starts_with((string) $tenant['signedContractDoc'], 'data:image/'))
                      ? (string) $tenant['signedContractDoc']
                      : '';
                  $hasAnyDocs = ($signedDoc !== '' || $docFile !== '');
                  $docModalPayload = [
                      'contractId' => $tenant['contractId'] ?? '',
                      'roomNumber' => $tenant['roomNumber'] ?? '',
                      'mainName' => $tenant['name'] ?? '',
                      'coTenantUserId' => $tenant['coTenantUserId'] ?? '',
                      'coTenantName' => $tenant['coTenantName'] ?? '',
                      'signedContractDoc' => $signedDoc,
                      'idCardDoc' => $tenant['idCardDoc'] ?? '',
                      'coIdCardDoc' => $tenant['coIdCardDoc'] ?? '',
                  ];
                ?>
                  <tr data-search-row>
                    <td>
                      <strong><?= e($tenant['name']) ?></strong>
                      <?php if ($isCoTenant): ?>
                        <small class="table-sub" style="color:#2563eb;">ผู้ร่วมพัก (คนที่ 2)</small>
                      <?php elseif (!empty($tenant['coTenantName'])): ?>
                        <small class="table-sub">พักร่วมกับ: <?= e($tenant['coTenantName']) ?></small>
                      <?php else: ?>
                        <small class="table-sub"><?= e($tenant['email']) ?></small>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($tenant['contractId']): ?>
                        <div style="display:flex; flex-direction:column; gap:4px; align-items:flex-start;">
                          <?php if ($signedDoc !== ''): ?>
                            <button class="text-link contract-doc-link" type="button" data-contract-doc-preview data-contract-id="<?= e($tenant['contractId']) ?>" data-contract-doc-type="signed" data-contract-doc-name="<?= e($signedDoc) ?>" data-contract-doc-title="เอกสารสัญญาที่ลงนามแล้ว">เอกสารสัญญาที่ลงนามแล้ว</button>
                          <?php else: ?>
                            <span style="font-size:0.82rem; color:#d97706;">ยังไม่ได้บันทึกเอกสารสัญญา</span>
                          <?php endif; ?>

                          <?php if ($docFile !== ''): ?>
                            <button class="text-link contract-doc-link" type="button" data-contract-doc-preview data-contract-id="<?= e($tenant['contractId']) ?>" data-contract-doc-type="<?= $isCoTenant ? 'co_id_card' : 'id_card' ?>" data-contract-doc-name="<?= e($docFile) ?>" data-contract-doc-title="สำเนาบัตรประชาชน">สำเนาบัตรประชาชน</button>
                          <?php else: ?>
                            <span style="font-size:0.82rem; color:#d97706;">ยังไม่ได้บันทึกสำเนาบัตรประชาชน</span>
                          <?php endif; ?>
                        </div>
                      <?php else: ?>
                        <span class="muted">—</span>
                      <?php endif; ?>
                    </td>
                    <td><?= $tenant['roomNumber'] ? '<strong>' . e($tenant['roomNumber']) . '</strong>' : '<span class="muted">ยังไม่ทำสัญญา</span>' ?></td>
                    <td>
                      <?= e($tenant['phone']) ?>
                      <?php if (!empty($tenant['emergencyContactName'])): ?>
                        <small class="table-sub">ฉุกเฉิน: <?= e($tenant['emergencyContactName']) ?> (<?= e($tenant['emergencyContactPhone'] ?: '-') ?>)</small>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?= !empty($tenant['occupation']) ? e($tenant['occupation']) : '<span class="muted">ยังไม่ระบุอาชีพ</span>' ?>
                      <?php if (!empty($tenant['address'])): ?>
                        <small class="table-sub" title="<?= e($tenant['address']) ?>"><?= e(mb_strimwidth($tenant['address'], 0, 34, '...', 'UTF-8')) ?></small>
                      <?php endif; ?>
                    </td>
                    <td><?= $tenant['startDate'] ? e(thai_date($tenant['startDate'])) : '—' ?></td>
                    <td><span class="status-pill <?= $tenant['status'] ? 'available' : 'pending' ?>"><?= e($tenant['status'] ?? 'รอทำสัญญา') ?></span></td>
                    <td>
                      <?php if ($tenant['contractId']): ?>
                        <div style="display:flex; gap:6px; flex-wrap:wrap; align-items:center;">
                          <a class="button tiny secondary" href="contract_print.php?id=<?= e($tenant['contractId']) ?>" target="_blank" rel="noopener">แก้ไข/พิมพ์สัญญา</a>
                          <?php if ($user['role'] === 'Admin'): ?>
                            <button class="button tiny <?= $hasAnyDocs ? 'success' : 'primary' ?>" type="button" onclick='openUploadContractDocsModal(<?= e(json_encode($docModalPayload, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>)'>
                              <?= $hasAnyDocs ? 'เอกสารสัญญา' : 'บันทึกหลักฐาน' ?>
                            </button>
                            <form method="post" action="index.php" style="margin:0;">
                              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                              <input type="hidden" name="action" value="end_contract">
                              <input type="hidden" name="contractId" value="<?= e($tenant['contractId']) ?>">
                              <input type="hidden" name="return_page" value="tenants">
                              <button class="button tiny danger" type="submit" data-confirm="สิ้นสุดสัญญาห้อง <?= e($tenant['roomNumber']) ?> และคืนห้องเป็นห้องว่างหรือไม่?">สิ้นสุดสัญญา</button>
                            </form>
                          <?php endif; ?>
                        </div>
                      <?php else: ?>
                        <?php if ($user['role'] === 'Admin'): ?>
                          <button class="button tiny primary" type="button" onclick="openAssignContractModal('<?= e($tenant['userId']) ?>')">ทำสัญญา</button>
                        <?php endif; ?>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <?php if (!$tenants): ?>
              <div class="empty-state">
                <span>♙</span>
                <strong>ยังไม่มีบัญชีผู้เช่าในระบบ</strong>
                <small>กรุณาสร้างบัญชีบทบาท Tenant ในเมนู “บัญชีผู้ใช้งาน” ก่อนทำสัญญา</small>
              </div>
            <?php endif; ?>
          </div>
        </section>

        <style>
        .contract-field-title {
          display: flex;
          align-items: center;
          gap: 6px;
          flex-wrap: wrap;
          font-size: 1.02rem;
          font-weight: 700;
          color: var(--text, #0f172a);
          margin-bottom: 6px;
        }
        .contract-field-sub {
          font-size: 0.84rem;
          font-weight: 400;
          color: var(--muted, #64748b);
        }
        .age-badge {
          display: inline-block;
          background: #dbeafe;
          color: #1d4ed8;
          font-size: 0.8rem;
          font-weight: 700;
          padding: 1px 8px;
          border-radius: 999px;
        }
        #tenant-modal input[type="text"],
        #tenant-modal input[type="tel"],
        #tenant-modal input[type="number"],
        #tenant-modal input[type="date"],
        #tenant-modal textarea,
        #tenant-modal select {
          font-size: 0.9rem;
          padding: 9px 12px;
        }
        #tenant-modal input::placeholder,
        #tenant-modal textarea::placeholder {
          color: #94a3b8;
          font-size: 0.86rem;
        }
        .idcard-doc-box {
          background: #ffffff;
          border: 1.5px dashed #93c5fd;
          border-radius: 10px;
          padding: 12px 14px;
          display: flex;
          flex-direction: column;
          gap: 8px;
        }
        .idcard-doc-note {
          font-size: 0.83rem;
          color: #b45309;
          background: #fffbeb;
          border: 1px solid #fde68a;
          padding: 7px 10px;
          border-radius: 6px;
          line-height: 1.45;
        }
        .doc-preview-thumb {
          max-height: 170px;
          max-width: 100%;
          border-radius: 8px;
          border: 1px solid #cbd5e1;
          object-fit: contain;
          background: #f8fafc;
          margin-top: 4px;
        }
        .contract-doc-link {
          border: 0;
          background: none;
          padding: 0;
          cursor: pointer;
          font: inherit;
          text-align: left;
        }
        .contract-doc-viewer {
          width: 96vw;
          max-width: 1100px;
          height: 90vh;
        }
        .contract-doc-viewer-body {
          height: calc(90vh - 82px);
          display: grid;
          place-items: center;
          background: #f1f5f9;
        }
        .contract-doc-viewer-body img,
        .contract-doc-viewer-body iframe {
          width: 100%;
          height: 100%;
          border: 0;
          object-fit: contain;
          background: #fff;
        }
        .official-contract-paper {
          max-height: 440px;
          overflow-y: auto;
          background: #ffffff;
          border: 1.5px solid #cbd5e1;
          border-radius: 10px;
          padding: 26px 30px;
          line-height: 1.75;
          font-size: 0.96rem;
          color: #1e293b;
          box-shadow: inset 0 2px 6px rgba(15, 23, 42, 0.04);
        }
        .official-doc-header {
          text-align: center;
          border-bottom: 2px solid #0f172a;
          padding-bottom: 14px;
          margin-bottom: 18px;
        }
        .official-doc-header h3 {
          margin: 0 0 4px;
          font-size: 1.25rem;
          font-weight: 800;
          color: #0f172a;
        }
        .official-doc-header p {
          margin: 0;
          font-size: 0.9rem;
          color: #475569;
        }
        .official-section-title {
          font-size: 1.04rem;
          font-weight: 700;
          color: #0f172a;
          background: #f1f5f9;
          padding: 7px 12px;
          border-left: 4px solid #2563eb;
          margin: 18px 0 12px;
        }
        .official-clause {
          margin-bottom: 10px;
          text-align: justify;
        }
        .official-penalty {
          color: #b91c1c;
          font-weight: 700;
        }
        .official-doc-footer {
          margin-top: 20px;
          border-top: 1px solid #cbd5e1;
          font-size: 0.92rem;
          color: #334155;
          background: #f8fafc;
          padding: 14px 16px;
          border-radius: 8px;
        }
        </style>

        <?php if ($user['role'] === 'Admin'): ?>
        <!-- Modal 1: สร้างสัญญาเช่าใหม่ -->
        <dialog class="modal" id="tenant-modal" style="max-width: 820px; width: 96%;">
          <form method="post" action="index.php" enctype="multipart/form-data" class="modal-form" id="contract-create-form" style="gap: 16px;">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="save_tenant">
            <input type="hidden" name="return_page" value="tenants">
            <input type="hidden" name="agree_terms" id="agree-terms-hidden" value="">

            <div class="modal-heading" style="border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 0;">
              <div>
                <p class="eyebrow" id="contract-step-eyebrow">ขั้นตอนที่ 1 จาก 2 · เอกสารสัญญาเช่าและระเบียบหอพัก</p>
                <h2 id="contract-step-title" style="margin: 2px 0 0; font-size: 1.35rem;">ข้อตกลงสัญญาเช่าและกฎระเบียบหอพักฟ้าใสเพลส</h2>
                <p class="muted" id="contract-step-subtitle" style="font-size: 0.88rem; margin: 2px 0 0;">กรุณาอ่านรายละเอียดสัญญาและกฎระเบียบด้านล่างให้ครบถ้วน เพื่อดำเนินการกรอกข้อมูลทำสัญญา</p>
              </div>
              <button class="icon-button" type="button" data-modal-close aria-label="ปิด">×</button>
            </div>

            <div id="contract-step-1" style="display: flex; flex-direction: column; gap: 14px;">
              <div class="official-contract-paper" id="dorm-rules-box" onscroll="checkRulesScrolled(this)">
                <div class="official-doc-header">
                  <h3>หนังสือสัญญาเช่าห้องพักและกฎระเบียบหอพักฟ้าใสเพลส</h3>
                  <p>ฉบับมาตรฐานสำหรับผู้เช่าพักอาศัย · ประจำปี พ.ศ. <?= e((string) (date('Y') + 543)) ?></p>
                </div>

                <p style="margin: 0 0 12px; text-indent: 2em;">
                  หนังสือสัญญาฉบับนี้ทำขึ้นระหว่าง <strong>หอพักฟ้าใสเพลส</strong> ซึ่งต่อไปในสัญญานี้เรียกว่า <strong>“ผู้ให้เช่า”</strong> ฝ่ายหนึ่ง กับผู้เช่าหลักและผู้ร่วมพักอาศัยตามรายชื่อที่ระบุในระบบ ซึ่งต่อไปในสัญญานี้เรียกว่า <strong>“ผู้เช่า”</strong> อีกฝ่ายหนึ่ง ทั้งสองฝ่ายได้ตกลงทำสัญญาเช่าห้องพักและยินยอมปฏิบัติตามเงื่อนไขและกฎระเบียบของหอพักดังต่อไปนี้
                </p>

                <div class="official-section-title">ส่วนที่ 1 : ข้อตกลงและเงื่อนไขการเช่าห้องพัก</div>

                <div class="official-clause">
                  <strong class="clause-num">ข้อ 1. จำนวนผู้พักอาศัย:</strong> ผู้ให้เช่าตกลงให้เช่าและผู้เช่าตกลงเช่าห้องพักเพื่อใช้เป็นที่พักอาศัยเท่านั้น โดยอนุญาตให้เข้าพักอาศัยได้ <strong>ไม่เกิน 2 ท่านต่อ 1 ห้องพัก</strong> ตามรายชื่อและสำเนาบัตรประชาชนที่แนบไว้ในสัญญาเช่าฉบับนี้
                </div>
                <div class="official-clause">
                  <strong class="clause-num">ข้อ 2. การชำระค่าเช่าและค่าสาธารณูปโภค:</strong> ผู้เช่าตกลงชำระค่าเช่าห้องพัก ค่าน้ำประปา และค่าไฟฟ้าประจำเดือน ภายในกำหนดเวลาที่หอพักแจ้งในใบแจ้งหนี้ของทุกเดือน หากชำระล่าช้ากว่ากำหนด ผู้เช่ายินยอมชำระค่าปรับตามอัตราที่หอพักกำหนด
                </div>
                <div class="official-clause">
                  <strong class="clause-num">ข้อ 3. เงินประกันความเสียหายและค่าเช่า:</strong> ผู้เช่าตกลงวางเงินประกันไว้กับผู้ให้เช่าจำนวน <strong>3,000 บาท</strong> ในวันทำสัญญา โดยผู้ให้เช่าจะคืนเงินประกันให้แก่ผู้เช่าเมื่อสัญญาสิ้นสุดลงและผู้เช่าได้ส่งมอบห้องพักคืนในสภาพเรียบร้อย ปราศจากความเสียหาย และไม่มียอดค้างชำระใดๆ
                </div>

                <div class="official-section-title">ส่วนที่ 2 : กฎระเบียบหอพักและอัตราค่าปรับกรณีฝ่าฝืน</div>

                <div class="official-clause">
                  <strong class="clause-num">ข้อ 1. ห้ามสูบบุหรี่ในห้องพักและบริเวณอาคาร:</strong> ห้ามสูบบุหรี่ บุหรี่ไฟฟ้า หรือก่อควันรบกวนภายในห้องพัก ระเบียง ทางเดิน และพื้นที่ส่วนกลางของหอพักโดยเด็ดขาด หากฝ่าฝืนมีโทษปรับ <span class="official-penalty">1,000 – 5,000 บาท</span>
                </div>
                <div class="official-clause">
                  <strong class="clause-num">ข้อ 2. ห้ามส่งเสียงดังรบกวนผู้อื่น:</strong> ผู้เช่าต้องไม่ส่งเสียงดัง เปิดเครื่องเสียง โทรทัศน์ หรือกระทำการใดๆ อันเป็นการรบกวนความสงบสุขของผู้อยู่อาศัยห้องข้างเคียง หากฝ่าฝืนมีโทษปรับ <span class="official-penalty">500 – 1,500 บาท</span>
                </div>
                <div class="official-clause">
                  <strong class="clause-num">ข้อ 3. ห้ามเล่นการพนันในห้องพัก:</strong> ห้ามลักลอบเล่นการพนันหรือจัดให้มีการเล่นการพนันทุกประเภทภายในห้องพักและบริเวณหอพักโดยเด็ดขาด หากฝ่าฝืนมีโทษปรับ <span class="official-penalty">2,000 – 5,000 บาท</span>
                </div>
                <div class="official-clause">
                  <strong class="clause-num">ข้อ 4. ห้ามยุ่งเกี่ยวกับสิ่งผิดกฎหมาย:</strong> ห้ามนำยาเสพติด อาวุธ วัตถุอันตราย หรือสิ่งผิดกฎหมายทุกชนิดเข้ามาเก็บ ซุกซ่อน หรือกระทำผิดกฎหมายภายในหอพัก หากตรวจพบผู้ให้เช่ามีสิทธิ์ <span class="official-penalty">บอกเลิกสัญญาเช่าและให้ออกจากห้องพักทันที</span> โดยไม่คืนเงินประกันและจะดำเนินคดีตามกฎหมาย
                </div>
                <div class="official-clause">
                  <strong class="clause-num">ข้อ 5. ห้ามเลี้ยงสัตว์ทุกชนิด:</strong> ห้ามนำสัตว์เลี้ยงทุกชนิดเข้ามาเลี้ยงหรือพักอาศัยภายในห้องพักและบริเวณอาคารหอพัก หากฝ่าฝืนมีโทษปรับ <span class="official-penalty">1,000 – 5,000 บาท</span>
                </div>
                <div class="official-clause">
                  <strong class="clause-num">ข้อ 6. ห้ามเจาะหรือดัดแปลงสภาพภายในห้องพัก:</strong> ห้ามเจาะผนัง ตอกตะปู ติดสติกเกอร์ หรือต่อเติมดัดแปลงโครงสร้างและอุปกรณ์ภายในห้องพักโดยไม่ได้รับอนุญาต หากฝ่าฝืนคิดค่าปรับ <span class="official-penalty">จุดละ 200 – 500 บาท</span> (กรณีสีผนังหลุดลอกหรือเสียหายหนัก คิดค่าทาสีใหม่ <span class="official-penalty">1,000 – 3,000 บาท</span>)
                </div>
                <div class="official-clause">
                  <strong class="clause-num">ข้อ 7. การรักษาความสะอาด:</strong> ผู้เช่ามีหน้าที่ดูแลรักษาความสะอาดภายในห้องพักและทิ้งขยะในจุดที่จัดเตรียมไว้ให้เรียบร้อย หากปล่อยปละละเลยให้สกปรกส่งกลิ่นเหม็นหรืออุดตันระบบระบายน้ำ มีโทษปรับ <span class="official-penalty">300 – 500 บาท</span>
                </div>
                <div class="official-clause">
                  <strong class="clause-num">ข้อ 8. ความรับผิดชอบต่อทรัพย์สินเสียหายเมื่อย้ายออก:</strong> หากเฟอร์นิเจอร์ เครื่องใช้ไฟฟ้า หรือทรัพย์สินภายในห้องพักเกิดการชำรุดเสียหายและผู้เช่าไม่แจ้งให้ผู้ดูแลทราบล่วงหน้าก่อนวันย้ายออก ผู้ให้เช่าจะดำเนินการ <span class="official-penalty">หักค่าซ่อมแซมหรือค่าชดเชยความเสียหายตามจริงจากเงินประกัน</span>
                </div>
                <div class="official-clause">
                  <strong class="clause-num">ข้อ 9. ห้ามประกอบอาหารที่มีควันและเปลวไฟ:</strong> ห้ามใช้เตาแก๊ส เตาถ่าน หรืออุปกรณ์ประกอบอาหารที่ก่อให้เกิดควันและเปลวไฟภายในห้องพักเพื่อป้องกันอัคคีภัย หากฝ่าฝืนมีโทษปรับ <span class="official-penalty">1,000 – 3,000 บาท</span>
                </div>
                <div class="official-clause">
                  <strong class="clause-num">ข้อ 10. ห้ามนำบุคคลภายนอกเข้าพักค้างคืนโดยไม่ได้รับอนุญาต:</strong> ห้ามนำบุคคลภายนอกที่ไม่มีรายชื่อในสัญญาเช่าเข้ามาพักค้างคืนโดยมิได้แจ้งและได้รับอนุญาตจากผู้ดูแลหอพักล่วงหน้า หากฝ่าฝืนมีโทษปรับ <span class="official-penalty">500 บาท</span>
                </div>

                <div class="official-doc-footer">
                  <strong>ขั้นตอนการลงนามจริง:</strong> เมื่อบันทึกข้อมูลในระบบแล้ว ให้พิมพ์เอกสารสัญญาฉบับนี้ออกมาเป็นกระดาษเพื่อให้ผู้เช่าและผู้ให้เช่าลงลายมือชื่อจริงด้วยปากกา จากนั้นถ่ายรูปเอกสารสัญญาที่เซ็นแล้วพร้อมสำเนาบัตรประชาชนที่เขียนรับรองกำกับอัปโหลดเก็บไว้ในระบบ
                </div>
              </div>

              <div id="rules-scroll-hint" style="display:flex; align-items:center; justify-content:space-between; background: rgba(245, 158, 11, 0.12); color: #b45309; padding: 11px 16px; border-radius: 10px; font-size: 0.9rem; font-weight: 600;">
                <span>กรุณาเลื่อนอ่านเอกสารสัญญาและกฎระเบียบลงมาให้สุดด้านล่างเพื่อดำเนินการต่อ</span>
                <span id="rules-scroll-status">ยังอ่านไม่ครบ</span>
              </div>

              <div class="modal-actions" style="margin-top: 2px;">
                <button class="button secondary" type="button" data-modal-close>ยกเลิก</button>
                <button class="button primary" type="button" id="btn-accept-rules" disabled onclick="goToContractFormStep()">
                  รับทราบและยอมรับข้อตกลง · ไปกรอกข้อมูลสัญญา →
                </button>
              </div>
            </div>

            <div id="contract-step-2" style="display: none; flex-direction: column; gap: 16px;">
              <div style="background: var(--bg-subtle, #f8fafc); border: 1px solid var(--border); border-radius: 12px; padding: 18px; display: flex; flex-direction: column; gap: 14px;">
                <div style="font-size: 0.98rem; font-weight: 700; color: var(--primary, #2563eb);">
                  1. ข้อมูลผู้เข้าพักและประวัติผู้เช่า (สูงสุด 2 ท่าน/ห้อง)
                </div>

                <div class="form-row" style="align-items: start; gap: 16px;">
                  <label style="margin:0;">
                    <span class="contract-field-title">ผู้เช่าหลัก <span style="color:#dc2626;">*</span></span>
                    <select name="userId" id="contract-user-select" required onchange="syncCoTenantOptions()">
                      <option value="">เลือกบัญชีผู้เช่าหลัก...</option>
                      <?php foreach ($availableTenants as $av): ?>
                        <option value="<?= e($av['userId']) ?>"
                                data-birthdate="<?= e($av['birthDate'] ?? '') ?>"
                                data-address="<?= e($av['address'] ?? '') ?>"
                                data-occupation="<?= e($av['occupation'] ?? '') ?>"
                                data-workplace="<?= e($av['workplace'] ?? '') ?>"
                                data-emname="<?= e($av['emergencyContactName'] ?? '') ?>"
                                data-emrelation="<?= e($av['emergencyContactRelation'] ?? '') ?>"
                                data-emphone="<?= e($av['emergencyContactPhone'] ?? '') ?>">
                          <?= e($av['name']) ?> (<?= e($av['phone']) ?>)
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </label>

                  <label style="margin:0;">
                    <span class="contract-field-title">
                      วันเดือนปีเกิดผู้เช่าหลัก <span style="color:#dc2626;">*</span>
                      <span class="age-badge" id="tenant-age-display" style="display:none;"></span>
                    </span>
                    <input type="date" name="birthDate" id="contract-main-birthdate" required onchange="updateTenantAge()">
                  </label>
                </div>

                <label style="margin:0;">
                  <span class="contract-field-title">ที่อยู่ตามทะเบียนบ้าน <span style="color:#dc2626;">*</span></span>
                  <input type="text" name="address" id="contract-main-address" maxlength="255" required placeholder="บ้านเลขที่ หมู่ ถนน ตำบล อำเภอ จังหวัด">
                </label>

                <div class="form-row" style="align-items: start; gap: 16px;">
                  <div style="display:flex; flex-direction:column; gap:8px; margin:0;">
                    <label style="margin:0;">
                      <span class="contract-field-title">อาชีพ / สถานะ <span style="color:#dc2626;">*</span></span>
                      <select name="occupation" id="contract-main-occupation" required onchange="toggleOccupationOther(this.value)">
                        <option value="">เลือกอาชีพ / สถานะ...</option>
                        <option value="นักศึกษา">นักศึกษา</option>
                        <option value="พนักงานบริษัทเอกชน">พนักงานบริษัทเอกชน</option>
                        <option value="ข้าราชการ / พนักงานรัฐวิสาหกิจ">ข้าราชการ / พนักงานรัฐวิสาหกิจ</option>
                        <option value="ค้าขาย / ธุรกิจส่วนตัว">ค้าขาย / ธุรกิจส่วนตัว</option>
                        <option value="อาชีพอิสระ / ฟรีแลนซ์">อาชีพอิสระ / ฟรีแลนซ์</option>
                        <option value="รับจ้างทั่วไป">รับจ้างทั่วไป</option>
                        <option value="อื่นๆ">อื่นๆ (ระบุเพิ่มเติม)</option>
                      </select>
                    </label>
                    <input type="text" name="occupation_other" id="contract-main-occupation-other" maxlength="100" placeholder="ระบุอาชีพอื่นๆ..." style="display:none;">
                  </div>

                  <label style="margin:0;">
                    <span class="contract-field-title">สถานที่ทำงาน / สถานศึกษา <span style="color:#dc2626;">*</span></span>
                    <input type="text" name="workplace" id="contract-main-workplace" maxlength="150" required placeholder="ชื่อบริษัท หรือ มหาวิทยาลัย">
                  </label>
                </div>

                <div class="form-row" style="align-items: start; gap: 16px;">
                  <label style="margin:0;">
                    <span class="contract-field-title">บุคคลติดต่อฉุกเฉิน <span style="color:#dc2626;">*</span></span>
                    <input type="text" name="emergencyContactName" id="contract-main-emname" maxlength="100" required placeholder="ชื่อ-นามสกุล ผู้ปกครองหรือญาติ">
                  </label>

                  <div class="form-row" style="align-items: start; gap: 10px; margin:0;">
                    <label style="margin:0;">
                      <span class="contract-field-title">ความสัมพันธ์ <span style="color:#dc2626;">*</span></span>
                      <input type="text" name="emergencyContactRelation" id="contract-main-emrelation" maxlength="50" required placeholder="เช่น บิดา, มารดา">
                    </label>
                    <label style="margin:0;">
                      <span class="contract-field-title">เบอร์โทรฉุกเฉิน <span style="color:#dc2626;">*</span></span>
                      <input type="tel" name="emergencyContactPhone" id="contract-main-emphone" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" required placeholder="08xxxxxxxx">
                    </label>
                  </div>
                </div>

                <div style="border-top: 1px dashed #cbd5e1; padding-top: 12px; display: flex; flex-direction: column; gap: 12px;">
                  <label style="margin:0;">
                    <span class="contract-field-title">ผู้ร่วมพักคนที่ 2 <span class="contract-field-sub">(ถ้ามี)</span></span>
                    <select name="coTenantUserId" id="contract-cotenant-select">
                      <option value="">ไม่มีผู้ร่วมพัก (พักคนเดียว)</option>
                      <?php foreach ($availableTenants as $av): ?>
                        <option value="<?= e($av['userId']) ?>">
                          <?= e($av['name']) ?> (<?= e($av['phone']) ?>)
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </label>
                </div>
              </div>

              <div style="background: var(--bg-subtle, #f8fafc); border: 1px solid var(--border); border-radius: 12px; padding: 18px; display: flex; flex-direction: column; gap: 14px;">
                <div style="font-size: 0.98rem; font-weight: 700; color: var(--primary, #2563eb);">
                  2. รายละเอียดห้องพักและระยะเวลาสัญญา
                </div>

                <div class="form-row" style="align-items: start; gap: 16px;">
                  <label style="margin:0;">
                    <span class="contract-field-title">ห้องพักที่ว่าง <span style="color:#dc2626;">*</span></span>
                    <select name="roomId" required>
                      <option value="">เลือกห้องพักที่ว่าง...</option>
                      <?php foreach ($freeRooms as $room): ?>
                        <option value="<?= e($room['roomId']) ?>">ห้อง <?= e($room['roomNumber']) ?> (<?= money($room['rentalPrice']) ?> ฿/เดือน)</option>
                      <?php endforeach; ?>
                    </select>
                  </label>

                  <label style="margin:0;">
                    <span class="contract-field-title">เงินประกันมาตรฐาน (บาท) <span class="contract-field-sub">(อัตราคงที่)</span></span>
                    <input type="text" value="3,000 บาท" readonly style="background: #eef2f6; font-weight: 700; color: #0f172a; cursor: not-allowed;">
                    <input type="hidden" name="deposit" value="3000">
                  </label>
                </div>

                <div class="form-row" style="align-items: start; gap: 16px;">
                  <label style="margin:0;">
                    <span class="contract-field-title">วันที่เริ่มสัญญา <span style="color:#dc2626;">*</span></span>
                    <input type="date" name="startDate" id="contract-start-date" value="<?= e(date('Y-m-d')) ?>" required onchange="calculateContractEndDate()">
                  </label>

                  <label style="margin:0;">
                    <span class="contract-field-title">ระยะเวลาสัญญา</span>
                    <select id="contract-duration-select" onchange="calculateContractEndDate()">
                      <option value="6">6 เดือน</option>
                      <option value="12" selected>1 ปี (12 เดือน)</option>
                      <option value="custom">กำหนดเอง / ไม่กำหนด</option>
                    </select>
                  </label>
                </div>

                <div class="form-row" style="align-items: start; gap: 16px;">
                  <label style="margin:0;">
                    <span class="contract-field-title">วันสิ้นสุดสัญญา <span class="contract-field-sub">(คำนวณอัตโนมัติ / แก้ไขได้)</span></span>
                    <input type="date" name="endDate" id="contract-end-date">
                  </label>
                  <div style="display:flex; align-items:center; height:100%; padding-top: 26px;">
                    <span style="font-size: 0.85rem; color: #2563eb; font-weight: 600;">บันทึกแล้วเลือก “แก้ไข/พิมพ์สัญญา” เพื่อลงนามจริง จากนั้นบันทึกเอกสารสัญญาในระบบ</span>
                  </div>
                </div>
              </div>

              <?php if (!$availableTenants): ?>
                <p class="form-hint" style="color:#b45309; margin:0;">* ไม่มีบัญชีผู้เช่าที่รอทำสัญญา หากมีผู้เช่าใหม่ให้สร้างบัญชีบทบาท <strong>Tenant</strong> ที่เมนู “บัญชีผู้ใช้งาน” ก่อน</p>
              <?php endif; ?>

              <div class="modal-actions" style="margin-top: 4px; justify-content: space-between;">
                <button class="button secondary" type="button" onclick="backToRulesStep()">← กลับไปอ่านสัญญาและกฎระเบียบ</button>
                <div style="display:flex; gap:8px;">
                  <button class="button secondary" type="button" data-modal-close>ยกเลิก</button>
                  <button class="button primary" type="submit" <?= (!$availableTenants || !$freeRooms) ? 'disabled' : '' ?>>บันทึกข้อมูลสัญญา</button>
                </div>
              </div>
            </div>
          </form>
        </dialog>
        <?php endif; ?>

        <?php if ($user['role'] === 'Admin'): ?>
        <!-- Modal 2: บันทึกหลักฐานเอกสารหลังลงนาม -->
        <dialog class="modal" id="upload-docs-modal" style="max-width: 720px; width: 96%;">
          <form method="post" action="index.php" enctype="multipart/form-data" class="modal-form" style="gap: 16px;">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="upload_contract_docs">
            <input type="hidden" name="return_page" value="tenants">
            <input type="hidden" name="contractId" id="upload-docs-contract-id">

            <div class="modal-heading" style="border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 0;">
              <div>
                <p class="eyebrow">CONTRACT DOCUMENTS</p>
                <h2 style="margin: 2px 0 0; font-size: 1.3rem;">เอกสารสัญญา</h2>
                <p class="muted" id="upload-docs-subtitle" style="font-size: 0.88rem; margin: 2px 0 0;">ห้อง — · ผู้เช่า —</p>
              </div>
              <button class="icon-button" type="button" data-modal-close aria-label="ปิด">×</button>
            </div>

            <div class="idcard-doc-box">
              <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap;">
                <span class="contract-field-title" style="margin:0;">1. สัญญาเช่าที่ลงนามแล้ว <span style="color:#dc2626;">*</span></span>
                <span id="status-signed-doc" class="status-pill pending" style="font-size:0.78rem;">ยังไม่ได้อัปโหลด</span>
              </div>
              <p class="muted" style="margin:0; font-size:0.84rem;">อัปโหลดภาพหรือ PDF ของสัญญาฉบับที่ลงนามแล้ว</p>
              <input type="file" name="signedContractDoc" accept="image/*,application/pdf" capture="environment" onchange="previewSelectedDoc(this, 'preview-signed-doc')">
              <div id="existing-signed-doc-wrap" style="display:none; margin-top:4px;">
                <img id="preview-signed-doc" class="doc-preview-thumb" alt="ตัวอย่างใบสัญญาที่เซ็นแล้ว">
                <div style="margin-top:4px;">
                  <button id="link-signed-doc" class="text-link contract-doc-link" type="button" style="font-size:0.85rem;" data-contract-doc-preview>ดูเอกสารสัญญา</button>
                </div>
              </div>
            </div>

            <div class="idcard-doc-box">
              <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap;">
                <span class="contract-field-title" style="margin:0;">2. รูปถ่ายสำเนาบัตรประชาชนผู้เช่าหลัก <span style="color:#dc2626;">*</span></span>
                <span id="status-idcard-doc" class="status-pill pending" style="font-size:0.78rem;">ยังไม่ได้อัปโหลด</span>
              </div>
              <div class="idcard-doc-note">
                <strong>ตรวจสอบก่อนถ่ายรูป:</strong> สำเนาบัตรประชาชนต้องมีลายมือชื่อรับรองสำเนาถูกต้อง พร้อมขีดคร่อมเขียนกำกับว่า <strong>“ใช้สำหรับการเช่าหอพักฟ้าใสเพลสเท่านั้น”</strong>
              </div>
              <input type="file" name="idCardDoc" accept="image/*,application/pdf" capture="environment" onchange="previewSelectedDoc(this, 'preview-idcard-doc')">
              <div id="existing-idcard-doc-wrap" style="display:none; margin-top:4px;">
                <img id="preview-idcard-doc" class="doc-preview-thumb" alt="ตัวอย่างสำเนาบัตรประชาชน">
                <div style="margin-top:4px;">
                  <button id="link-idcard-doc" class="text-link contract-doc-link" type="button" style="font-size:0.85rem;" data-contract-doc-preview>ดูสำเนาบัตรประชาชน</button>
                </div>
              </div>
            </div>

            <div class="idcard-doc-box" id="upload-co-idcard-box" style="display:none;">
              <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap;">
                <span class="contract-field-title" style="margin:0;">3. รูปถ่ายสำเนาบัตรประชาชนผู้ร่วมพักคนที่ 2 (<span id="upload-co-name-label"></span>)</span>
                <span id="status-co-idcard-doc" class="status-pill pending" style="font-size:0.78rem;">ยังไม่ได้อัปโหลด</span>
              </div>
              <input type="file" name="coIdCardDoc" accept="image/*,application/pdf" capture="environment" onchange="previewSelectedDoc(this, 'preview-co-idcard-doc')">
              <div id="existing-co-idcard-doc-wrap" style="display:none; margin-top:4px;">
                <img id="preview-co-idcard-doc" class="doc-preview-thumb" alt="ตัวอย่างสำเนาบัตรประชาชนผู้ร่วมพัก">
                <div style="margin-top:4px;">
                  <button id="link-co-idcard-doc" class="text-link contract-doc-link" type="button" style="font-size:0.85rem;" data-contract-doc-preview>ดูสำเนาบัตรประชาชนผู้ร่วมพัก</button>
                </div>
              </div>
            </div>

            <div class="modal-actions" style="margin-top: 4px;">
              <button class="button secondary" type="button" data-modal-close>ปิดหน้าต่าง</button>
              <button class="button primary" type="submit">บันทึกเอกสารสัญญา</button>
            </div>
          </form>
        </dialog>
        <?php endif; ?>

        <script>
        function updateTenantAge() {
          const birthInput = document.getElementById('contract-main-birthdate');
          const ageBadge = document.getElementById('tenant-age-display');
          if (!birthInput || !ageBadge) return;
          const val = birthInput.value;
          if (!val) {
            ageBadge.style.display = 'none';
            ageBadge.textContent = '';
            return;
          }
          const dob = new Date(val);
          if (isNaN(dob.getTime())) {
            ageBadge.style.display = 'none';
            return;
          }
          const today = new Date();
          let age = today.getFullYear() - dob.getFullYear();
          const m = today.getMonth() - dob.getMonth();
          if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
            age--;
          }
          if (age >= 0 && age <= 120) {
            ageBadge.textContent = `อายุ ${age} ปี`;
            ageBadge.style.display = 'inline-block';
          } else {
            ageBadge.style.display = 'none';
          }
        }

        function toggleOccupationOther(val) {
          const otherInput = document.getElementById('contract-main-occupation-other');
          if (!otherInput) return;
          if (val === 'อื่นๆ') {
            otherInput.style.display = 'block';
            otherInput.required = true;
          } else {
            otherInput.style.display = 'none';
            otherInput.required = false;
            otherInput.value = '';
          }
        }

        function checkRulesScrolled(box) {
          if (!box) return;
          const isAtBottom = box.scrollTop + box.clientHeight >= box.scrollHeight - 14;
          if (isAtBottom) {
            const btn = document.getElementById('btn-accept-rules');
            const hint = document.getElementById('rules-scroll-hint');
            const status = document.getElementById('rules-scroll-status');
            if (btn) btn.disabled = false;
            if (hint) {
              hint.style.background = 'rgba(16, 185, 129, 0.14)';
              hint.style.color = '#059669';
              hint.firstElementChild.textContent = '✓ อ่านเอกสารสัญญาและกฎระเบียบครบถ้วนแล้ว สามารถกดปุ่มดำเนินการต่อได้';
            }
            if (status) status.textContent = 'อ่านครบแล้ว';
          }
        }

        function resetRulesStep() {
          const step1 = document.getElementById('contract-step-1');
          const step2 = document.getElementById('contract-step-2');
          const box = document.getElementById('dorm-rules-box');
          const btn = document.getElementById('btn-accept-rules');
          const hint = document.getElementById('rules-scroll-hint');
          const status = document.getElementById('rules-scroll-status');
          const agreeHidden = document.getElementById('agree-terms-hidden');

          if (step1) step1.style.display = 'flex';
          if (step2) step2.style.display = 'none';
          if (agreeHidden) agreeHidden.value = '';
          if (box) box.scrollTop = 0;
          if (btn) btn.disabled = true;
          if (hint) {
            hint.style.background = 'rgba(245, 158, 11, 0.12)';
            hint.style.color = '#b45309';
            hint.firstElementChild.textContent = 'กรุณาเลื่อนอ่านเอกสารสัญญาและกฎระเบียบลงมาให้สุดด้านล่างเพื่อดำเนินการต่อ';
          }
          if (status) status.textContent = 'ยังอ่านไม่ครบ';

          document.getElementById('contract-step-eyebrow').textContent = 'ขั้นตอนที่ 1 จาก 2 · เอกสารสัญญาเช่าและระเบียบหอพัก';
          document.getElementById('contract-step-title').textContent = 'ข้อตกลงสัญญาเช่าและกฎระเบียบหอพักฟ้าใสเพลส';
          document.getElementById('contract-step-subtitle').textContent = 'กรุณาอ่านรายละเอียดสัญญาและกฎระเบียบด้านล่างให้ครบถ้วน เพื่อดำเนินการกรอกข้อมูลทำสัญญา';
        }

        function goToContractFormStep() {
          document.getElementById('agree-terms-hidden').value = '1';
          document.getElementById('contract-step-1').style.display = 'none';
          document.getElementById('contract-step-2').style.display = 'flex';

          document.getElementById('contract-step-eyebrow').textContent = 'ขั้นตอนที่ 2 จาก 2 · บันทึกข้อมูลทำสัญญาเช่า';
          document.getElementById('contract-step-title').textContent = 'กรอกข้อมูลทำสัญญาเช่าห้องพัก';
          document.getElementById('contract-step-subtitle').textContent = 'ระบุข้อมูลผู้เช่า ห้องพัก และระยะเวลาสัญญา จากนั้นพิมพ์สัญญาไปลงนามจริง';
        }

        function backToRulesStep() {
          document.getElementById('contract-step-2').style.display = 'none';
          document.getElementById('contract-step-1').style.display = 'flex';

          document.getElementById('contract-step-eyebrow').textContent = 'ขั้นตอนที่ 1 จาก 2 · เอกสารสัญญาเช่าและระเบียบหอพัก';
          document.getElementById('contract-step-title').textContent = 'ข้อตกลงสัญญาเช่าและกฎระเบียบหอพักฟ้าใสเพลส';
          document.getElementById('contract-step-subtitle').textContent = 'ตรวจสอบรายละเอียดสัญญาเช่าและกฎระเบียบหอพัก';
        }

        function syncCoTenantOptions() {
          const mainSelect = document.getElementById('contract-user-select');
          const coSelect = document.getElementById('contract-cotenant-select');
          const occSelect = document.getElementById('contract-main-occupation');
          const occOther = document.getElementById('contract-main-occupation-other');
          if (!mainSelect || !coSelect) return;

          const opt = mainSelect.options[mainSelect.selectedIndex];
          const setField = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
          if (opt && opt.value !== '') {
            setField('contract-main-birthdate', opt.dataset.birthdate);
            setField('contract-main-address', opt.dataset.address);
            setField('contract-main-workplace', opt.dataset.workplace);
            setField('contract-main-emname', opt.dataset.emname);
            setField('contract-main-emrelation', opt.dataset.emrelation);
            setField('contract-main-emphone', opt.dataset.emphone);
            updateTenantAge();

            const savedOcc = (opt.dataset.occupation || '').trim();
            if (occSelect) {
              const hasOption = Array.from(occSelect.options).some(o => o.value === savedOcc && savedOcc !== 'อื่นๆ');
              if (savedOcc === '') {
                occSelect.value = '';
                toggleOccupationOther('');
              } else if (hasOption) {
                occSelect.value = savedOcc;
                toggleOccupationOther(savedOcc);
              } else {
                occSelect.value = 'อื่นๆ';
                toggleOccupationOther('อื่นๆ');
                if (occOther) occOther.value = savedOcc;
              }
            }
          } else {
            ['contract-main-birthdate','contract-main-address','contract-main-workplace','contract-main-emname','contract-main-emrelation','contract-main-emphone'].forEach(id => setField(id, ''));
            updateTenantAge();
            if (occSelect) occSelect.value = '';
            toggleOccupationOther('');
          }

          const mainVal = mainSelect.value;
          Array.from(coSelect.options).forEach((o) => {
            if (o.value === '') return;
            o.disabled = (o.value === mainVal);
            if (o.disabled && coSelect.value === o.value) {
              coSelect.value = '';
            }
          });
        }

        function calculateContractEndDate() {
          const startInput = document.getElementById('contract-start-date');
          const durationSelect = document.getElementById('contract-duration-select');
          const endInput = document.getElementById('contract-end-date');
          if (!startInput || !durationSelect || !endInput) return;

          const startVal = startInput.value;
          const duration = durationSelect.value;
          if (!startVal || duration === 'custom') {
            if (duration === 'custom') endInput.value = '';
            return;
          }

          const months = parseInt(duration, 10);
          const [y, m, d] = startVal.split('-').map(Number);
          const dateObj = new Date(y, m - 1 + months, d);
          if (dateObj.getDate() !== d) {
            dateObj.setDate(0);
          }
          const yyyy = dateObj.getFullYear();
          const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
          const dd = String(dateObj.getDate()).padStart(2, '0');
          endInput.value = `${yyyy}-${mm}-${dd}`;
        }

        function openAssignContractModal(userId) {
          const modal = document.getElementById('tenant-modal');
          const select = document.getElementById('contract-user-select');
          const coSelect = document.getElementById('contract-cotenant-select');
          resetRulesStep();
          if (select) {
            select.value = userId || '';
            syncCoTenantOptions();
          }
          if (coSelect) {
            coSelect.value = '';
          }
          calculateContractEndDate();
          if (modal) {
            modal.showModal();
            setTimeout(() => {
              const box = document.getElementById('dorm-rules-box');
              if (box && box.scrollHeight <= box.clientHeight + 5) {
                checkRulesScrolled(box);
              }
            }, 60);
          }
        }

        function configureExistingDocPreview(wrapId, imgId, linkId, statusId, fileName, contractId, docType, title) {
          const wrap = document.getElementById(wrapId);
          const img = document.getElementById(imgId);
          const link = document.getElementById(linkId);
          const status = document.getElementById(statusId);

          if (fileName) {
            const fileUrl = `file.php?contract=${encodeURIComponent(contractId)}&type=${encodeURIComponent(docType)}&raw=1`;
            status.textContent = 'อัปโหลดเก็บไว้แล้ว';
            status.className = 'status-pill available';
            wrap.style.display = 'block';
            link.dataset.contractId = contractId;
            link.dataset.contractDocType = docType;
            link.dataset.contractDocName = fileName;
            link.dataset.contractDocTitle = title;
            if (fileName.toLowerCase().endsWith('.pdf')) {
              img.style.display = 'none';
            } else {
              img.style.display = 'block';
              img.src = fileUrl;
            }
          } else {
            status.textContent = 'ยังไม่ได้อัปโหลด';
            status.className = 'status-pill pending';
            wrap.style.display = 'none';
            img.src = '';
            delete link.dataset.contractId;
            delete link.dataset.contractDocType;
            delete link.dataset.contractDocName;
            delete link.dataset.contractDocTitle;
          }
        }

        function previewSelectedDoc(input, imgId) {
          const file = input.files && input.files[0];
          const img = document.getElementById(imgId);
          const wrap = img ? img.parentElement : null;
          if (!file || !img || !wrap) return;

          if (file.type === 'application/pdf') {
            wrap.style.display = 'block';
            img.style.display = 'none';
            return;
          }

          const reader = new FileReader();
          reader.onload = function (evt) {
            wrap.style.display = 'block';
            img.style.display = 'block';
            img.src = evt.target.result;
          };
          reader.readAsDataURL(file);
        }

        function openUploadContractDocsModal(data) {
          const modal = document.getElementById('upload-docs-modal');
          if (!modal) return;

          modal.querySelector('form').reset();
          document.getElementById('upload-docs-contract-id').value = data.contractId || '';
          document.getElementById('upload-docs-subtitle').textContent =
            `เลขที่สัญญา ${data.contractId} · ห้อง ${data.roomNumber} · ผู้เช่าหลัก: ${data.mainName}`;

          configureExistingDocPreview(
            'existing-signed-doc-wrap',
            'preview-signed-doc',
            'link-signed-doc',
            'status-signed-doc',
            data.signedContractDoc,
            data.contractId,
            'signed',
            'เอกสารสัญญาที่ลงนามแล้ว'
          );

          configureExistingDocPreview(
            'existing-idcard-doc-wrap',
            'preview-idcard-doc',
            'link-idcard-doc',
            'status-idcard-doc',
            data.idCardDoc,
            data.contractId,
            'id_card',
            'สำเนาบัตรประชาชนผู้เช่าหลัก'
          );

          const coBox = document.getElementById('upload-co-idcard-box');
          if (data.coTenantUserId) {
            coBox.style.display = 'flex';
            document.getElementById('upload-co-name-label').textContent = data.coTenantName || 'ผู้ร่วมพัก';
            configureExistingDocPreview(
              'existing-co-idcard-doc-wrap',
              'preview-co-idcard-doc',
              'link-co-idcard-doc',
              'status-co-idcard-doc',
              data.coIdCardDoc,
              data.contractId,
              'co_id_card',
              'สำเนาบัตรประชาชนผู้ร่วมพัก'
            );
          } else {
            coBox.style.display = 'none';
          }

          modal.showModal();
        }

        document.addEventListener('DOMContentLoaded', () => {
          calculateContractEndDate();
        });
        document.addEventListener('click', (event) => {
          const button = event.target.closest('[data-contract-doc-preview]');
          if (!button) return;

          event.preventDefault();
          const dialog = document.getElementById('contract-doc-preview-modal');
          const image = dialog.querySelector('[data-contract-doc-image]');
          const frame = dialog.querySelector('[data-contract-doc-frame]');
          const isPdf = (button.dataset.contractDocName || '').toLowerCase().endsWith('.pdf');
          const url = `file.php?contract=${encodeURIComponent(button.dataset.contractId)}&type=${encodeURIComponent(button.dataset.contractDocType)}&raw=1`;

          dialog.querySelector('[data-contract-doc-title]').textContent = button.dataset.contractDocTitle || 'เอกสารสัญญา';
          image.hidden = isPdf;
          frame.hidden = !isPdf;
          image.removeAttribute('src');
          frame.removeAttribute('src');
          if (isPdf) {
            frame.src = url;
          } else {
            image.src = url;
          }
          dialog.showModal();
        });
        </script>

      <?php elseif ($page === 'users'): ?>
        <?php $accounts = db()->query("SELECT userId, username, name, phone, email, role, idCardNumber, birthDate, address, occupation, workplace, emergencyContactName, emergencyContactRelation, emergencyContactPhone FROM `User` ORDER BY role, name")->fetchAll(); ?>
        <section class="page-heading"><div><p class="eyebrow">ACCESS CONTROL</p><h1>บัญชีผู้ใช้งาน</h1><p class="muted">สร้างบัญชี กำหนดบทบาท และข้อมูลประวัติผู้ใช้งาน</p></div><button class="button primary" type="button" data-modal-open="user-modal">＋ เพิ่มบัญชี</button></section>
        <section class="panel table-panel"><div class="table-toolbar"><div class="search-box"><span>⌕</span><input type="search" placeholder="ค้นหาชื่อหรือ Username..." data-table-search></div><span class="panel-caption"><?= count($accounts) ?> บัญชี</span></div><div class="table-scroll"><table><thead><tr><th>ชื่อผู้ใช้งาน</th><th>Username</th><th>เบอร์โทรศัพท์</th><th>อีเมล</th><th>บทบาท</th><th>ปรับสิทธิ์</th><th></th></tr></thead><tbody><?php foreach ($accounts as $account): ?><tr data-search-row><td><strong><?= e($account['name']) ?></strong></td><td class="mono"><?= e($account['username']) ?></td><td><?= e($account['phone']) ?></td><td><?= e($account['email']) ?></td><td><span class="status-pill <?= $account['role'] === 'Admin' ? 'occupied' : 'available' ?>"><?= e($account['role']) ?></span></td><td><form method="post" action="index.php" class="role-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="update_user_role"><input type="hidden" name="userId" value="<?= e($account['userId']) ?>"><input type="hidden" name="return_page" value="users"><select name="role"><option <?= $account['role'] === 'Tenant' ? 'selected' : '' ?>>Tenant</option><option <?= $account['role'] === 'Staff' ? 'selected' : '' ?>>Staff</option><option <?= $account['role'] === 'Technician' ? 'selected' : '' ?>>Technician</option><option <?= $account['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option></select><button class="button tiny secondary" type="submit">บันทึก</button></form></td><td class="row-actions"><button class="icon-button" type="button" data-edit-account data-account="<?= e(json_encode($account, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>" aria-label="แก้ไขบัญชี">✎</button><?php if ($account['userId'] !== $user['userId']): ?><form method="post" action="index.php"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="userId" value="<?= e($account['userId']) ?>"><input type="hidden" name="return_page" value="users"><button class="icon-button danger-icon" type="submit" data-confirm="ลบบัญชีนี้หรือไม่?" aria-label="ลบบัญชี">×</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></section>
        <dialog class="modal account-modal" id="user-modal" style="max-width: 720px; width: 96%;"><form method="post" action="index.php" class="modal-form account-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save_user"><input type="hidden" name="userId"><input type="hidden" name="return_page" value="users">
          <div class="modal-heading"><div><p class="eyebrow">ACCOUNT DETAILS</p><h2>เพิ่มบัญชีผู้ใช้งาน</h2><p class="muted">กรอกข้อมูลบัญชีและประวัติผู้ใช้งานให้ครบถ้วน</p></div><button class="icon-button" type="button" data-modal-close aria-label="ปิด">×</button></div>
          <section class="account-section"><h3>ข้อมูลบัญชีและข้อมูลส่วนตัว</h3><div class="form-row"><label class="required-field"><span>ชื่อ-นามสกุล <span class="required-mark" aria-hidden="true">*</span></span><input name="name" maxlength="100" autocomplete="name" required></label><label class="required-field"><span>Username <span class="required-mark" aria-hidden="true">*</span></span><input name="username" maxlength="50" autocomplete="username" required></label></div>
            <div class="form-row"><label class="required-field"><span>เบอร์โทรศัพท์ <span class="required-mark" aria-hidden="true">*</span></span><input name="phone" type="tel" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" autocomplete="tel" required></label><label class="required-field"><span>อีเมล <span class="required-mark" aria-hidden="true">*</span></span><input type="email" name="email" maxlength="100" autocomplete="email" required></label></div>
            <div class="form-row"><label><span>เลขบัตรประชาชน (13 หลัก)</span><input name="idCardNumber" inputmode="numeric" pattern="[0-9]{13}" maxlength="13" placeholder="เช่น 1509901234567"></label><label><span>วันเดือนปีเกิด</span><input type="date" name="birthDate"></label></div>
            <div class="form-row"><label class="required-field"><span>บทบาท <span class="required-mark" aria-hidden="true">*</span></span><select name="role" required><option>Tenant</option><option>Staff</option><option>Technician</option><option>Admin</option></select></label></div>
          </section>
          <section class="account-section"><h3>ที่อยู่ตามทะเบียนบ้าน</h3>
            <label><span>ที่อยู่ปัจจุบัน / ตามทะเบียนบ้าน</span><textarea name="address" maxlength="255" rows="2" placeholder="บ้านเลขที่ หมู่ ถนน ตำบล อำเภอ จังหวัด รหัสไปรษณีย์"></textarea></label>
          </section>
          <section class="account-section"><h3>ข้อมูลอาชีพ / การศึกษา</h3>
            <div class="form-row"><label><span>อาชีพ / สถานะ</span><input name="occupation" maxlength="100" placeholder="เช่น พนักงานบริษัท / นักศึกษา"></label><label><span>สถานที่ทำงาน / สถานศึกษา</span><input name="workplace" maxlength="150" placeholder="ชื่อบริษัท หรือ มหาวิทยาลัย"></label></div>
          </section>
          <section class="account-section"><h3>บุคคลติดต่อฉุกเฉิน</h3>
            <div class="form-row"><label><span>ชื่อ-นามสกุล ผู้ติดต่อฉุกเฉิน</span><input name="emergencyContactName" maxlength="100" placeholder="ชื่อผู้ปกครอง หรือ ญาติ"></label><label><span>ความสัมพันธ์</span><input name="emergencyContactRelation" maxlength="50" placeholder="เช่น บิดา, มารดา, พี่สาว"></label></div>
            <label><span>เบอร์โทรศัพท์ผู้ติดต่อฉุกเฉิน (10 หลัก)</span><input name="emergencyContactPhone" type="tel" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" placeholder="08xxxxxxxx"></label>
          </section>
          <section class="account-section password-section"><h3>ตั้งรหัสผ่าน</h3><p class="form-hint" data-password-note>รหัสผ่านใหม่ต้องผ่านเงื่อนไขครบทุกข้อ</p>
            <div class="form-row"><label class="password-label required-field"><span>รหัสผ่าน <span class="required-mark" aria-hidden="true">*</span></span><span class="password-control"><input type="password" name="password" minlength="12" autocomplete="new-password" data-password required><button class="password-toggle" type="button" data-toggle-password="password" aria-label="แสดงรหัสผ่าน">แสดง</button></span></label>
              <label class="password-label required-field"><span>ยืนยันรหัสผ่าน <span class="required-mark" aria-hidden="true">*</span></span><span class="password-control"><input type="password" name="password_confirm" minlength="12" autocomplete="new-password" data-password-confirm required><button class="password-toggle" type="button" data-toggle-password="password_confirm" aria-label="แสดงรหัสผ่านที่ยืนยัน">แสดง</button></span><small data-password-match aria-live="polite">กรอกรหัสผ่านอีกครั้งให้ตรงกัน</small></label>
            </div>
            <div class="password-strength"><div class="strength-heading"><strong>ความปลอดภัยของรหัสผ่าน</strong><span data-password-strength-label>ยังไม่ได้ตั้ง</span></div><div class="strength-track" data-password-strength role="progressbar" aria-label="ระดับความปลอดภัยของรหัสผ่าน" aria-valuemin="0" aria-valuemax="5" aria-valuenow="0"><span></span></div>
              <ul class="password-checklist"><li data-password-rule="length">อย่างน้อย 12 ตัวอักษร</li><li data-password-rule="lowercase">มีตัวพิมพ์เล็ก (a-z)</li><li data-password-rule="uppercase">มีตัวพิมพ์ใหญ่ (A-Z)</li><li data-password-rule="number">มีตัวเลข (0-9)</li><li data-password-rule="symbol">มีอักขระพิเศษ เช่น ! @ #</li></ul>
            </div>
          </section>
          <div class="modal-actions"><button class="button secondary" type="button" data-modal-close>ยกเลิก</button><button class="button primary" type="submit">สร้างบัญชี</button></div>
        </form></dialog>

      <?php elseif ($page === 'meters'): ?>
        <?php 
        $meters = db()->query('SELECT m.*, r.roomNumber FROM MeterRecord m JOIN Room r ON r.roomId = m.roomId ORDER BY m.recordDate DESC, r.roomNumber LIMIT 100')->fetchAll(); 
        $allRooms = db()->query('SELECT roomId, roomNumber FROM Room ORDER BY roomNumber')->fetchAll(); 
        $meterTargetMonth = normalize_billing_month((string) ($_GET['month'] ?? '')) ?? (date('m/') . (date('Y') + 543));
        $meterTargetIndex = billing_month_index($meterTargetMonth);
        $meterTargetVariants = billing_month_variants($meterTargetMonth);
        $lastMeterRows = db()->query('SELECT roomId, month, waterUnit, electricUnit, waterReading, electricReading, usageCalculated, waterReset, electricReset, recordDate FROM MeterRecord')->fetchAll();
        $lastMetersByRoom = [];
        $metersForTargetMonth = [];
        foreach ($lastMeterRows as $lastMeter) { 
            $monthIndex = billing_month_index((string) $lastMeter['month']); 
            if (in_array((string) $lastMeter['month'], $meterTargetVariants, true)) {
                $metersForTargetMonth[$lastMeter['roomId']] = $lastMeter;
            }
            $lastIndex = isset($lastMetersByRoom[$lastMeter['roomId']]) ? billing_month_index((string) $lastMetersByRoom[$lastMeter['roomId']]['month']) : null; 
            if ($monthIndex !== null && $monthIndex < $meterTargetIndex && ($lastIndex === null || $monthIndex > $lastIndex)) {
                $lastMeter['waterReading'] = $lastMeter['waterReading'] !== null ? $lastMeter['waterReading'] : $lastMeter['waterUnit'];
                $lastMeter['electricReading'] = $lastMeter['electricReading'] !== null ? $lastMeter['electricReading'] : $lastMeter['electricUnit'];
                $lastMetersByRoom[$lastMeter['roomId']] = $lastMeter; 
            } 
        } 
        ?>
        <section class="page-heading"><div><p class="eyebrow">UTILITY READINGS</p><h1>บันทึกมิเตอร์</h1><p class="muted">กรอกเลขมิเตอร์สะสมของทุกห้องในหน้าเดียว ระบบคำนวณหน่วยที่ใช้ให้อัตโนมัติ</p></div></section>
        <section class="panel form-panel">
          <div class="panel-heading"><div><p class="eyebrow">MONTHLY METER READINGS</p><h2>บันทึกเลขมิเตอร์ประจำเดือน</h2></div><span class="panel-caption"><?= count($allRooms) ?> ห้อง</span></div>
          <form method="post" action="index.php" class="meter-batch-form" data-meter-batch-form>
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="save_meter_batch">
            <input type="hidden" name="return_page" value="meters">
            <input type="hidden" name="return_month" value="<?= e($meterTargetMonth) ?>">
            <label class="meter-month-field">เดือน/ปี (MM/YYYY)<input name="month" required maxlength="20" value="<?= e($meterTargetMonth) ?>" placeholder="เช่น 10/2569" data-meter-month></label>
            <div class="meter-batch-tools">
              <label>แสดงห้อง
                <select data-meter-filter aria-label="กรองห้องตามสถานะการกรอก">
                  <option value="all">ทุกห้อง</option>
                  <option value="pending">ยังไม่ครบ</option>
                  <option value="complete">กรอกครบแล้ว</option>
                </select>
              </label>
              <p class="form-hint" data-meter-count aria-live="polite"></p>
            </div>
            <div class="meter-batch-table table-scroll">
              <table>
                <thead><tr><th>ห้อง</th><th>งวดก่อน</th><th>มิเตอร์น้ำ (เดิม → ปัจจุบัน)</th><th>ส่วนต่างน้ำ</th><th>มิเตอร์ไฟ (เดิม → ปัจจุบัน)</th><th>ส่วนต่างไฟ</th></tr></thead>
                <tbody>
                  <?php foreach ($allRooms as $room):
                    $lastMeter = $lastMetersByRoom[$room['roomId']] ?? null;
                    $existingMeter = $metersForTargetMonth[$room['roomId']] ?? null;
                    $lastWater = (float) ($lastMeter['waterReading'] ?? 0);
                    $lastElectric = (float) ($lastMeter['electricReading'] ?? 0);
                    $currentWater = $existingMeter ? (float) ($existingMeter['waterReading'] ?? $existingMeter['waterUnit']) : null;
                    $currentElectric = $existingMeter ? (float) ($existingMeter['electricReading'] ?? $existingMeter['electricUnit']) : null;
                    ?>
                    <tr data-meter-row class="<?= $existingMeter ? 'meter-row-saved' : '' ?>" data-persisted="<?= $existingMeter ? '1' : '0' ?>" data-saved-water-used="<?= e((string) ($existingMeter['waterUnit'] ?? '')) ?>" data-saved-electric-used="<?= e((string) ($existingMeter['electricUnit'] ?? '')) ?>" data-saved-usage-calculated="<?= e((string) ($existingMeter['usageCalculated'] ?? '1')) ?>" data-previous-water="<?= e((string) $lastWater) ?>" data-previous-electric="<?= e((string) $lastElectric) ?>">
                      <td data-label="ห้อง"><strong><?= e($room['roomNumber']) ?></strong><small class="meter-row-status" data-meter-status>ยังไม่อ่าน</small></td>
                      <td data-label="งวดก่อน"><?= $lastMeter ? e($lastMeter['month']) : 'เริ่มต้นที่ 0' ?></td>
                      <td data-label="มิเตอร์น้ำ (เดิม → ปัจจุบัน)"><div class="meter-reading-entry"><span class="meter-reading-before"><?= meter_reading_display($lastWater) ?> →</span><input class="meter-reading-input" type="text" inputmode="decimal" pattern="[0-9]+([.][0-9]{1,2})?" maxlength="12" name="meters[<?= e($room['roomId']) ?>][waterReading]" value="<?= $currentWater !== null ? e(meter_reading_display($currentWater)) : '' ?>" aria-label="เลขมิเตอร์น้ำปัจจุบัน ห้อง <?= e($room['roomNumber']) ?>" <?= $existingMeter ? 'disabled' : '' ?>><label class="meter-reset-option"><input class="meter-reset-input" type="checkbox" name="meters[<?= e($room['roomId']) ?>][waterReset]" value="1" <?= !empty($existingMeter['waterReset']) ? 'checked' : '' ?> <?= $existingMeter ? 'disabled' : '' ?>> เปลี่ยน/รีเซ็ตมิเตอร์</label></div></td>
                      <td data-label="ส่วนต่างน้ำ"><strong data-water-used><?= $existingMeter ? money($existingMeter['waterUnit']) . (empty($existingMeter['usageCalculated']) ? ' (ข้อมูลเดิม)' : '') : '—' ?></strong></td>
                      <td data-label="มิเตอร์ไฟ (เดิม → ปัจจุบัน)"><div class="meter-reading-entry"><span class="meter-reading-before"><?= meter_reading_display($lastElectric) ?> →</span><input class="meter-reading-input" type="text" inputmode="decimal" pattern="[0-9]+([.][0-9]{1,2})?" maxlength="12" name="meters[<?= e($room['roomId']) ?>][electricReading]" value="<?= $currentElectric !== null ? e(meter_reading_display($currentElectric)) : '' ?>" aria-label="เลขมิเตอร์ไฟปัจจุบัน ห้อง <?= e($room['roomNumber']) ?>" <?= $existingMeter ? 'disabled' : '' ?>><label class="meter-reset-option"><input class="meter-reset-input" type="checkbox" name="meters[<?= e($room['roomId']) ?>][electricReset]" value="1" <?= !empty($existingMeter['electricReset']) ? 'checked' : '' ?> <?= $existingMeter ? 'disabled' : '' ?>> เปลี่ยน/รีเซ็ตมิเตอร์</label></div></td>
                      <td data-label="ส่วนต่างไฟ"><strong data-electric-used><?= $existingMeter ? money($existingMeter['electricUnit']) . (empty($existingMeter['usageCalculated']) ? ' (ข้อมูลเดิม)' : '') : '—' ?></strong></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div class="meter-batch-actions"><p class="form-hint">กรอกเฉพาะเลขปัจจุบัน ระบบคำนวณส่วนต่างอัตโนมัติ เลขที่บันทึกแล้วของรอบนี้จะล็อกในตารางนี้ หากต้องแก้ให้ใช้ปุ่มแก้ไขในประวัติมิเตอร์ด้านล่าง</p><button class="button primary" type="submit">บันทึกมิเตอร์</button></div>
          </form>
        </section>
        <section class="panel table-panel">
          <div class="panel-heading" style="padding: 20px 24px 12px;"><div><p class="eyebrow">RECENT READINGS</p><h2>ประวัติมิเตอร์</h2></div></div>
          <div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th>วันที่บันทึก</th>
                  <th>ห้อง</th>
                  <th>เดือน</th>
                  <th>ใช้น้ำรอบนี้</th>
                  <th>ใช้ไฟรอบนี้</th>
                  <th>ค่าน้ำ/ไฟโดยประมาณ</th>
                  <?php if ($user['role'] === 'Admin'): ?><th>จัดการ</th><?php endif; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($meters as $meter): ?>
                  <tr>
                    <td><?= e(thai_date($meter['recordDate'])) ?></td>
                    <td><strong><?= e($meter['roomNumber']) ?></strong></td>
                    <td><?= e($meter['month']) ?></td>
                    <td><?= money($meter['waterUnit']) ?><?= empty($meter['usageCalculated']) ? ' (ข้อมูลเดิม)' : '' ?></td>
                    <td><?= money($meter['electricUnit']) ?><?= empty($meter['usageCalculated']) ? ' (ข้อมูลเดิม)' : '' ?></td>
                    <td><?= money(((float) $meter['waterUnit'] * WATER_RATE) + ((float) $meter['electricUnit'] * ELECTRIC_RATE)) ?> ฿</td>
                    <?php if ($user['role'] === 'Admin'): ?>
                      <td class="row-actions">
                        <button class="icon-button" type="button" onclick='openEditMeterModal(<?= e(json_encode($meter, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>)' aria-label="แก้ไขมิเตอร์" title="แก้ไขมิเตอร์">✎</button>
                        <form method="post" action="index.php" style="display:inline;">
                          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                          <input type="hidden" name="action" value="delete_meter">
                          <input type="hidden" name="meterId" value="<?= e($meter['meterId']) ?>">
                          <input type="hidden" name="return_page" value="meters">
                          <button class="icon-button danger-icon" type="submit" data-confirm="ยืนยันการลบข้อมูลมิเตอร์ห้อง <?= e($meter['roomNumber']) ?> เดือน <?= e($meter['month']) ?> หรือไม่?" aria-label="ลบมิเตอร์" title="ลบมิเตอร์">×</button>
                        </form>
                      </td>
                    <?php endif; ?>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <?php if (!$meters): ?><div class="empty-state compact"><strong>ยังไม่มีรายการมิเตอร์</strong></div><?php endif; ?>
          </div>
        </section>
        <?php if ($user['role'] === 'Admin'): ?>
          <dialog class="modal" id="meter-edit-modal">
            <form method="post" action="index.php" class="modal-form">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="action" value="save_meter">
              <input type="hidden" name="return_page" value="meters">
              <input type="hidden" name="meterId" id="edit-meter-id">
              <div class="modal-heading">
                <div><p class="eyebrow">EDIT METER RECORD</p><h2>แก้ไขข้อมูลมิเตอร์</h2></div>
                <button class="icon-button" type="button" data-modal-close aria-label="ปิด">×</button>
              </div>
              <label>ห้องพัก
                <select name="roomId" id="edit-meter-room" required>
                  <?php foreach ($allRooms as $room): ?>
                    <option value="<?= e($room['roomId']) ?>"><?= e($room['roomNumber']) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label>เดือน/ปี (MM/YYYY)<input name="month" id="edit-meter-month" required maxlength="20"></label>
              <div class="form-row">
                <label>เลขมิเตอร์น้ำปัจจุบัน<input type="text" inputmode="decimal" pattern="[0-9]+([.][0-9]{1,2})?" maxlength="12" name="waterReading" id="edit-meter-water" required></label>
                <label>เลขมิเตอร์ไฟปัจจุบัน<input type="text" inputmode="decimal" pattern="[0-9]+([.][0-9]{1,2})?" maxlength="12" name="electricReading" id="edit-meter-electric" required></label>
              </div>
              <div class="form-row">
                <label class="meter-reset-option"><input type="checkbox" name="waterReset" value="1" id="edit-meter-water-reset"> เปลี่ยน/รีเซ็ตมิเตอร์น้ำ</label>
                <label class="meter-reset-option"><input type="checkbox" name="electricReset" value="1" id="edit-meter-electric-reset"> เปลี่ยน/รีเซ็ตมิเตอร์ไฟ</label>
              </div>
              <p class="form-hint">ระบบคำนวณหน่วยที่ใช้จากเลขสะสมงวดก่อนและอัปเดตใบแจ้งหนี้ที่ยังรอชำระ</p>
              <div class="modal-actions">
                <button class="button secondary" type="button" data-modal-close>ยกเลิก</button>
                <button class="button primary" type="submit">บันทึกการแก้ไข</button>
              </div>
            </form>
          </dialog>
          <script>
          function openEditMeterModal(meter) {
            const formatReading = value => {
              const normalized = Number(value).toFixed(2).replace(/\.?0+$/, '');
              const [whole, fraction] = normalized.split('.');
              return whole.padStart(4, '0') + (fraction ? `.${fraction}` : '');
            };
            document.getElementById('edit-meter-id').value = meter.meterId;
            document.getElementById('edit-meter-room').value = meter.roomId;
            document.getElementById('edit-meter-month').value = meter.month;
            document.getElementById('edit-meter-water').value = formatReading(meter.waterReading ?? meter.waterUnit);
            document.getElementById('edit-meter-electric').value = formatReading(meter.electricReading ?? meter.electricUnit);
            document.getElementById('edit-meter-water-reset').checked = Number(meter.waterReset) === 1;
            document.getElementById('edit-meter-electric-reset').checked = Number(meter.electricReset) === 1;
            document.getElementById('meter-edit-modal').showModal();
          }
          </script>
        <?php endif; ?>

      <?php elseif ($page === 'payments'): ?>
        <?php require __DIR__ . '/payments.php'; ?>

      <?php elseif ($page === 'repairs'): ?>
        <?php if ($user['role'] === 'Tenant'): $statement = db()->prepare('SELECT rp.*, r.roomNumber FROM Repair rp JOIN Room r ON r.roomId = rp.roomId WHERE rp.userId = ? ORDER BY rp.repairDate DESC'); $statement->execute([$user['userId']]); $repairs = $statement->fetchAll(); $repairRooms = $tenantContract ? [['roomId' => $tenantContract['roomId'], 'roomNumber' => $tenantContract['roomNumber']]] : []; else: $query = "SELECT rp.*, u.name, r.roomNumber, tech.name AS technicianName FROM Repair rp JOIN `User` u ON u.userId = rp.userId JOIN Room r ON r.roomId = rp.roomId LEFT JOIN `User` tech ON tech.userId = rp.maintenanceUserId"; if ($user['role'] === 'Technician') { $statement = db()->prepare($query . ' WHERE rp.maintenanceUserId = ? ORDER BY rp.repairDate DESC'); $statement->execute([$user['userId']]); $repairs = $statement->fetchAll(); } else { $repairs = db()->query($query . ' ORDER BY rp.repairDate DESC')->fetchAll(); } $repairRooms = []; endif; $technicians = $user['role'] === 'Staff' ? db()->query("SELECT userId, name FROM `User` WHERE role = 'Technician' ORDER BY name")->fetchAll() : []; $repairCostTypes = $user['role'] === 'Technician' ? db()->query('SELECT costTypeId, costTypeName FROM CostType ORDER BY costTypeId')->fetchAll() : []; ?>
        <section class="page-heading"><div><p class="eyebrow">MAINTENANCE DESK</p><h1>งานซ่อม</h1><p class="muted">Staff มอบหมายช่างและส่งราคาที่ช่างประเมินให้ Admin อนุมัติก่อนเริ่มงาน</p></div></section>
        <?php if ($user['role'] === 'Tenant'): ?><section class="panel form-panel"><div class="panel-heading"><div><p class="eyebrow">NEW REQUEST</p><h2>ส่งคำร้องแจ้งซ่อม</h2></div></div><?php if ($repairRooms): ?><form method="post" action="index.php" enctype="multipart/form-data" class="inline-form repair-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="create_repair"><input type="hidden" name="return_page" value="repairs"><input type="hidden" name="roomId" value="<?= e($repairRooms[0]['roomId']) ?>"><label>ห้องพัก<input value="<?= e($repairRooms[0]['roomNumber']) ?>" disabled></label><label class="wide">รายละเอียดปัญหา<textarea name="detail" maxlength="500" required placeholder="อธิบายปัญหาที่พบ"></textarea></label><label>แนบรูปภาพ<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label><button class="button primary" type="submit">ส่งคำร้อง</button></form><?php else: ?><div class="empty-state compact"><strong>ต้องมีสัญญาเช่าที่ใช้งานก่อนแจ้งซ่อม</strong></div><?php endif; ?></section><?php endif; ?>
        <section class="repair-grid">
          <?php foreach ($repairs as $repair):
            $costQuery = db()->prepare('SELECT SUM(amount) FROM RepairDetail WHERE repairId = ?');
            $costQuery->execute([$repair['repairId']]);
            $costTotal = (float) $costQuery->fetchColumn();

            $costItemsStmt = db()->prepare('SELECT rd.costTypeId, rd.equipment, rd.amount, ct.costTypeName FROM RepairDetail rd JOIN CostType ct ON ct.costTypeId = rd.costTypeId WHERE rd.repairId = ?');
            $costItemsStmt->execute([$repair['repairId']]);
            $costItems = $costItemsStmt->fetchAll();
            $costItemsByType = [];
            foreach ($costItems as $costItem) {
              $costItemsByType[$costItem['costTypeId']] = $costItem;
            }

            $isApprovedToWork = in_array($repair['repairStatus'], ['อนุมัติค่าใช้จ่ายแล้ว', 'กำลังซ่อม'], true);
            $canQuote = $user['role'] === 'Technician'
              && $repair['maintenanceUserId'] === $user['userId']
              && in_array($repair['repairStatus'], ['มอบหมายช่างแล้ว', 'รอแก้ไขค่าใช้จ่าย'], true);
          ?>
            <article class="panel repair-card">
              <div class="repair-card-top">
                <span class="status-pill <?= $repair['repairStatus'] === 'ซ่อมเสร็จแล้ว' ? 'available' : 'pending' ?>"><?= e($repair['repairStatus']) ?></span>
                <span class="mono muted"><?= e($repair['repairId']) ?></span>
              </div>

              <h2><?= e(mb_strimwidth($repair['detail'], 0, 90, '...', 'UTF-8')) ?></h2>
              <p class="muted"><?= e($repair['name'] ?? $user['name']) ?> · ห้อง <?= e($repair['roomNumber'] ?? '') ?> · <?= e(thai_date($repair['repairDate'])) ?></p>

              <?php if (!empty($repair['image'])): ?>
                <a class="text-link" href="file.php?repair=<?= e($repair['repairId']) ?>" target="_blank" rel="noopener">ดูรูปประกอบ ↗</a>
              <?php endif; ?>

              <?php if (!empty($repair['technicianName'])): ?>
                <p class="repair-assignee">ช่างผู้รับผิดชอบ <strong><?= e($repair['technicianName']) ?></strong></p>
              <?php endif; ?>

              <?php if ($costItems): ?>
                <div class="repair-assignee" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                  <div>ราคาประเมินรวม: <strong><?= money($costTotal) ?> ฿</strong></div>
                  <?php foreach ($costItems as $ci): ?>
                    <small class="muted">• <?= e($ci['costTypeName']) ?><?= trim((string) $ci['equipment']) !== (string) $ci['costTypeName'] && trim((string) $ci['equipment']) !== '' ? ' · ' . e($ci['equipment']) : '' ?> — <?= money($ci['amount']) ?> ฿</small>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <?php if ($user['role'] === 'Staff' && empty($repair['maintenanceUserId']) && in_array($repair['repairStatus'], ['รอรับเรื่อง', 'รอตรวจสอบ', 'รอผู้ดูแลมอบหมาย'], true)): ?>
                <form method="post" action="index.php" class="repair-controls">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="assign_repair">
                  <input type="hidden" name="repairId" value="<?= e($repair['repairId']) ?>">
                  <input type="hidden" name="return_page" value="repairs">
                  <select name="maintenanceUserId" required>
                    <option value="">เลือกช่างซ่อม</option>
                    <?php foreach ($technicians as $technician): ?>
                      <option value="<?= e($technician['userId']) ?>"><?= e($technician['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button class="button tiny primary" type="submit">มอบหมายช่าง</button>
                </form>
              <?php endif; ?>

              <?php if ($user['role'] === 'Admin' && $repair['repairStatus'] === 'รออนุมัติค่าใช้จ่าย'): ?>
                <form method="post" action="index.php" class="repair-controls">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="repairId" value="<?= e($repair['repairId']) ?>">
                  <input type="hidden" name="return_page" value="repairs">
                  <button class="button tiny success" name="action" value="approve_repair_cost" type="submit" data-confirm="ยืนยันอนุมัติราคาประเมิน <?= money($costTotal) ?> ฿ เพื่อให้ช่างเริ่มทำงานหรือไม่?">อนุมัติราคา</button>
                  <button class="button tiny danger" name="action" value="reject_repair_cost" type="submit" data-confirm="ส่งราคาประเมินกลับให้ช่างแก้ไขหรือไม่?">ส่งกลับแก้ไข</button>
                </form>
              <?php endif; ?>

              <?php if ($user['role'] === 'Staff' && $repair['repairStatus'] === 'รอ Staff ตรวจสอบราคา'): ?>
                <form method="post" action="index.php" class="repair-controls">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="repairId" value="<?= e($repair['repairId']) ?>">
                  <input type="hidden" name="return_page" value="repairs">
                  <button class="button tiny success" name="action" value="submit_repair_quote" type="submit" data-confirm="ยืนยันส่งราคาที่ช่างเสนอให้ Admin อนุมัติหรือไม่?">ส่งให้ Admin อนุมัติ</button>
                  <button class="button tiny danger" name="action" value="return_repair_quote" type="submit" data-confirm="ส่งราคากลับให้ช่างแก้ไขหรือไม่?">ส่งกลับให้ช่างแก้ไข</button>
                </form>
              <?php elseif ($user['role'] === 'Staff' && $repair['repairStatus'] === 'รออนุมัติค่าใช้จ่าย'): ?>
                <p class="repair-flow-note">ส่งราคาที่ช่างเสนอให้ Admin แล้ว · รออนุมัติ</p>
              <?php endif; ?>

              <?php if ($user['role'] === 'Staff' && $repair['repairStatus'] === 'รอแก้ไขค่าใช้จ่าย'): ?>
                <p class="repair-flow-note">ส่งราคากลับให้ช่างแก้ไขแล้ว · รอช่างส่งให้ Staff ตรวจอีกครั้ง</p>
              <?php endif; ?>

              <?php if (!empty($repair['repairResult'])): ?>
                <p class="repair-assignee">ผลการซ่อม <strong><?= e($repair['repairResult']) ?></strong></p>
              <?php endif; ?>
              <?php if (!empty($repair['completionImage'])): ?>
                <a class="text-link" href="file.php?after=<?= e($repair['repairId']) ?>" target="_blank" rel="noopener">ดูรูปหลังซ่อม ↗</a>
              <?php endif; ?>

              <?php if ($canQuote): ?>
                <form method="post" action="index.php" class="repair-quote-form" data-repair-cost-form>
                  <strong>เสนอราคาค่าซ่อมให้ Staff</strong>
                  <p class="form-hint">กรอกจำนวนเงินในประเภทค่าใช้จ่ายที่เกี่ยวข้อง เช่น ค่าแรง 200 และค่าอุปกรณ์ 70 ระบบจะรวมยอดให้อัตโนมัติ เว้นประเภทที่ไม่มีค่าใช้จ่ายได้</p>
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="save_repair_cost">
                  <input type="hidden" name="repairId" value="<?= e($repair['repairId']) ?>">
                  <input type="hidden" name="return_page" value="repairs">
                  <div class="repair-cost-list">
                    <?php foreach ($repairCostTypes as $costType):
                      $existingCost = $costItemsByType[$costType['costTypeId']] ?? null;
                    ?>
                      <div class="repair-cost-line">
                        <label><?= e($costType['costTypeName']) ?> (บาท)
                          <input type="number" name="costs[<?= e($costType['costTypeId']) ?>][amount]" min="0" max="99999999.99" step="0.01" value="<?= e((string) ($existingCost['amount'] ?? '')) ?>" placeholder="0.00" data-repair-cost-amount>
                        </label>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <div class="repair-quote-summary" aria-live="polite"><span>รวมค่าใช้จ่ายทั้งหมด</span><strong><span data-repair-cost-total>0.00</span> ฿</strong></div>
                  <button class="button tiny primary" type="submit" data-confirm="ยืนยันส่งราคาประเมินทุกรายการให้ Staff ตรวจสอบหรือไม่?">ส่งให้ Staff ตรวจสอบ</button>
                </form>
              <?php elseif ($user['role'] === 'Technician' && $repair['repairStatus'] === 'รอ Staff ตรวจสอบราคา'): ?>
                <p class="repair-flow-note">ส่งราคาประเมินให้ Staff แล้ว · รอ Staff ตรวจสอบและส่ง Admin อนุมัติ</p>
              <?php endif; ?>

              <?php if ($user['role'] === 'Technician' && $repair['repairStatus'] !== 'ซ่อมเสร็จแล้ว'): ?>
                <?php if ($repair['repairStatus'] === 'รออนุมัติค่าใช้จ่าย'): ?>
                  <p class="repair-flow-note">Staff ส่งราคาประเมินแล้ว · รอ Admin อนุมัติก่อนเริ่มซ่อม</p>
                <?php elseif ($repair['repairStatus'] === 'รอแก้ไขค่าใช้จ่าย'): ?>
                  <p class="repair-flow-note">ราคาถูกส่งกลับให้แก้ไขแล้ว · โปรดส่งให้ Staff ตรวจสอบอีกครั้ง</p>
                <?php elseif ($isApprovedToWork): ?>
                  <form method="post" action="index.php" enctype="multipart/form-data" class="repair-result-form" style="border-top: 1px solid var(--border); padding-top: 12px;">
                    <strong style="font-size: 0.9rem; color: #059669;">✓ อนุมัติราคาแล้ว — บันทึกการทำงานพร้อมแนบรูป</strong>
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="update_repair">
                    <input type="hidden" name="repairId" value="<?= e($repair['repairId']) ?>">
                    <input type="hidden" name="return_page" value="repairs">
                    <label>สถานะการดำเนินงาน
                      <select name="repairStatus" required>
                        <option value="กำลังซ่อม" <?= $repair['repairStatus'] === 'กำลังซ่อม' ? 'selected' : '' ?>>กำลังซ่อม</option>
                        <option value="ซ่อมเสร็จแล้ว">ซ่อมเสร็จแล้ว (ปิดงาน)</option>
                      </select>
                    </label>
                    <label>รายละเอียดผลการซ่อม
                      <textarea name="repairResult" maxlength="2000" rows="2" required placeholder="บันทึกสิ่งที่ดำเนินการซ่อม"><?= e($repair['repairResult'] ?? '') ?></textarea>
                    </label>
                    <label>แนบรูปหลังซ่อม <small>(ไม่บังคับ)</small>
                      <input type="file" name="completionImage" accept="image/jpeg,image/png,image/webp">
                    </label>
                    <button class="button tiny success" type="submit" data-confirm="ยืนยันบันทึกผลการซ่อมนี้หรือไม่?">บันทึกการทำงาน</button>
                  </form>
                <?php endif; ?>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </section><?php if (!$repairs): ?><div class="panel empty-state"><span>⌕</span><strong>ไม่พบรายการแจ้งซ่อม</strong><small>รายการใหม่จะแสดงที่นี่</small></div><?php endif; ?>

      <?php elseif ($page === 'news'): ?>
        <?php $news = db()->query('SELECT * FROM News ORDER BY publishDate DESC')->fetchAll(); ?>
        <section class="page-heading"><div><p class="eyebrow">NOTICE BOARD</p><h1>ข่าวสารและประกาศ</h1><p class="muted">ติดตามประกาศและข่าวสารจากหอพัก</p></div><?php if ($user['role'] === 'Admin'): ?><button class="button primary" type="button" data-modal-open="news-modal">＋ เพิ่มประกาศ</button><?php endif; ?></section>
        <section class="news-list"><?php foreach ($news as $item): ?><article class="panel news-card"><button class="news-card-open" type="button" data-news-preview="<?= e(json_encode($item, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>"><span class="news-card-date"><strong><?= e(date('d', strtotime($item['publishDate']))) ?></strong><span><?= e(thai_month_year($item['publishDate'])) ?></span></span><span class="news-card-body"><strong class="news-card-title"><?= e($item['title']) ?></strong><span class="news-card-summary"><?= e(mb_strimwidth($item['detail'], 0, 180, '...', 'UTF-8')) ?></span><?php if (!empty($item['image'])): ?><small class="news-image-hint">แตะเพื่ออ่านรายละเอียดและดูรูปภาพ</small><?php endif; ?></span><span class="news-open" aria-hidden="true">↗</span></button><?php if ($user['role'] === 'Admin'): ?><button class="icon-button" type="button" data-edit-news data-news="<?= e(json_encode($item, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>" aria-label="แก้ไขประกาศ">✎</button><form method="post" action="index.php"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete_news"><input type="hidden" name="newsId" value="<?= e($item['newsId']) ?>"><input type="hidden" name="return_page" value="news"><button class="icon-button danger-icon" type="submit" data-confirm="ลบประกาศนี้หรือไม่?" aria-label="ลบประกาศ">×</button></form><?php endif; ?></article><?php endforeach; ?><?php if (!$news): ?><div class="panel empty-state"><span>▤</span><strong>ยังไม่มีประกาศ</strong><small>ประกาศใหม่จะแสดงที่หน้านี้</small></div><?php endif; ?></section>
        <?php if ($user['role'] === 'Admin'): ?><dialog class="modal" id="news-modal"><form method="post" action="index.php" enctype="multipart/form-data" class="modal-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save_news"><input type="hidden" name="newsId"><input type="hidden" name="return_page" value="news"><div class="modal-heading"><div><p class="eyebrow">NOTICE BOARD</p><h2>เพิ่มประกาศ</h2></div><button class="icon-button" type="button" data-modal-close>×</button></div><label>หัวข้อ<input name="title" maxlength="200" required></label><label>รายละเอียด<textarea name="detail" maxlength="1000" required rows="5"></textarea></label><label>รูปประกาศ<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label><div class="modal-actions"><button class="button secondary" type="button" data-modal-close>ยกเลิก</button><button class="button primary" type="submit">เผยแพร่</button></div></form></dialog><?php endif; ?>

      <?php elseif ($page === 'reports'): ?>
        <?php
        $income = (float) db()->query("SELECT COALESCE(SUM(amount), 0) FROM Payment WHERE status = 'อนุมัติ' AND DATE_FORMAT(paymentDate, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')")->fetchColumn();
        $due = db()->query("SELECT r.roomNumber, CONCAT_WS(' / ', u1.name, u2.name) AS name, r.rentalPrice FROM Contract c JOIN Room r ON r.roomId = c.roomId JOIN `User` u1 ON u1.userId = c.userId LEFT JOIN `User` u2 ON u2.userId = c.coTenantUserId WHERE c.status = 'ใช้งานอยู่' ORDER BY r.roomNumber")->fetchAll();
        $balances = db()->query("SELECT r.roomNumber, CONCAT_WS(' / ', u1.name, u2.name) AS name,
            COALESCE((SELECT SUM(i.rentAmount + i.waterUnit * i.waterRate + i.electricUnit * i.electricRate) FROM Invoice i WHERE i.contractId = c.contractId AND i.status = 'รอชำระ'), 0)
            + COALESCE((SELECT SUM(f.amount) FROM Fine f WHERE f.contractId = c.contractId AND f.status = 'รอชำระ'), 0) AS balance
            FROM Contract c JOIN Room r ON r.roomId = c.roomId JOIN `User` u1 ON u1.userId = c.userId LEFT JOIN `User` u2 ON u2.userId = c.coTenantUserId
            WHERE c.status = 'ใช้งานอยู่' HAVING balance > 0 ORDER BY r.roomNumber")->fetchAll();
        $reportRepairs = db()->query('SELECT repairStatus, COUNT(*) AS total FROM Repair GROUP BY repairStatus ORDER BY total DESC')->fetchAll();
        $recentRepairs = db()->query("SELECT rp.repairDate, rp.repairStatus, rp.detail, r.roomNumber, u.name
            FROM Repair rp JOIN Room r ON r.roomId = rp.roomId JOIN `User` u ON u.userId = rp.userId
            WHERE rp.repairDate >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 DAY)
            ORDER BY rp.repairDate DESC, rp.repairId DESC")->fetchAll();
        $todayRepairs = db()->query("SELECT rp.repairDate, rp.repairStatus, rp.detail, r.roomNumber, u.name
            FROM Repair rp JOIN Room r ON r.roomId = rp.roomId JOIN `User` u ON u.userId = rp.userId
            WHERE rp.repairDate = CURRENT_DATE()
            ORDER BY rp.repairId DESC")->fetchAll();
        $roomSummary = db()->query('SELECT roomStatus, COUNT(*) AS total FROM Room GROUP BY roomStatus')->fetchAll();
        ?>
        <section class="page-heading"><div><p class="eyebrow">OPERATIONS REPORT</p><h1>รายงานสรุป</h1><p class="muted">ข้อมูลภาพรวมรายรับ ห้องพัก ยอดค้าง และงานซ่อม</p></div><span class="period-chip">เดือนนี้ · <?= e(date('m/') . (date('Y') + 543)) ?></span></section>
        <section class="metric-grid"><article class="metric-card"><div class="metric-top"><span>รายรับที่อนุมัติเดือนนี้</span><span class="metric-icon mint">฿</span></div><strong><?= money($income) ?> ฿</strong><small>รวมรายการชำระที่อนุมัติแล้ว</small></article><article class="metric-card"><div class="metric-top"><span>ห้องที่มีสัญญาใช้งาน</span><span class="metric-icon blue">♙</span></div><strong><?= count($due) ?></strong><small>ผู้เช่าปัจจุบัน</small></article><article class="metric-card"><div class="metric-top"><span>ยอดค้างชำระรวม</span><span class="metric-icon orange">฿</span></div><strong><?= money(array_sum(array_column($balances, 'balance'))) ?> ฿</strong><small><?= count($balances) ?> ห้องมีรายการค้าง</small></article><article class="metric-card"><div class="metric-top"><span>รายการแจ้งซ่อม</span><span class="metric-icon orange">⌕</span></div><strong><?= array_sum(array_column($reportRepairs, 'total')) ?></strong><small>รายการทั้งหมด</small></article><article class="metric-card"><div class="metric-top"><span>ห้องพักทั้งหมด</span><span class="metric-icon blue">⌂</span></div><strong><?= array_sum(array_column($roomSummary, 'total')) ?></strong><small>ทุกสถานะห้อง</small></article></section>
        <section class="dashboard-grid"><article class="panel"><div class="panel-heading"><div><p class="eyebrow">ROOM STATUS</p><h2>สถานะห้องพัก</h2></div></div><?php foreach ($roomSummary as $status): ?><div class="summary-row"><span><?= e($status['roomStatus']) ?></span><strong><?= e((string) $status['total']) ?> ห้อง</strong></div><?php endforeach; ?></article><article class="panel"><div class="panel-heading"><div><p class="eyebrow">REPAIR STATUS</p><h2>สถานะงานซ่อม</h2></div></div><?php foreach ($reportRepairs as $status): ?><div class="summary-row"><span><?= e($status['repairStatus']) ?></span><strong><?= e((string) $status['total']) ?> งาน</strong></div><?php endforeach; ?></article></section>
        <section class="panel table-panel"><div class="panel-heading" style="padding: 20px 24px 12px;"><div><p class="eyebrow">OUTSTANDING BY ROOM</p><h2>ยอดค้างชำระรายห้อง</h2></div></div><div class="table-scroll"><table><thead><tr><th>ห้อง</th><th>ผู้เช่า</th><th>ยอดค้างชำระ</th></tr></thead><tbody><?php foreach ($balances as $balance): ?><tr><td><?= e($balance['roomNumber']) ?></td><td><?= e($balance['name']) ?></td><td><strong><?= money($balance['balance']) ?> ฿</strong></td></tr><?php endforeach; ?></tbody></table><?php if (!$balances): ?><div class="empty-state compact"><strong>ไม่มีรายการค้างชำระ</strong></div><?php endif; ?></div></section>
        <section class="panel table-panel"><div class="panel-heading" style="padding: 20px 24px 12px;"><div><p class="eyebrow">REPAIR REQUESTS · TODAY</p><h2>รายการแจ้งซ่อมประจำวัน</h2></div><span class="panel-caption"><?= count($todayRepairs) ?> รายการ</span></div><div class="table-scroll"><table><thead><tr><th>วันที่</th><th>ห้อง</th><th>ผู้แจ้ง</th><th>รายละเอียด</th><th>สถานะ</th></tr></thead><tbody><?php foreach ($todayRepairs as $repair): ?><tr><td><?= e(thai_date($repair['repairDate'])) ?></td><td><?= e($repair['roomNumber']) ?></td><td><?= e($repair['name']) ?></td><td><?= e(mb_strimwidth($repair['detail'], 0, 100, '...', 'UTF-8')) ?></td><td><?= e($repair['repairStatus']) ?></td></tr><?php endforeach; ?></tbody></table><?php if (!$todayRepairs): ?><div class="empty-state compact"><strong>ไม่มีรายการแจ้งซ่อมวันนี้</strong></div><?php endif; ?></div></section>
        <section class="panel table-panel"><div class="panel-heading" style="padding: 20px 24px 12px;"><div><p class="eyebrow">REPAIR REQUESTS · LAST 7 DAYS</p><h2>รายการแจ้งซ่อมประจำสัปดาห์</h2></div><span class="panel-caption"><?= count($recentRepairs) ?> รายการ</span></div><div class="table-scroll"><table><thead><tr><th>วันที่</th><th>ห้อง</th><th>ผู้แจ้ง</th><th>รายละเอียด</th><th>สถานะ</th></tr></thead><tbody><?php foreach ($recentRepairs as $repair): ?><tr><td><?= e(thai_date($repair['repairDate'])) ?></td><td><?= e($repair['roomNumber']) ?></td><td><?= e($repair['name']) ?></td><td><?= e(mb_strimwidth($repair['detail'], 0, 100, '...', 'UTF-8')) ?></td><td><?= e($repair['repairStatus']) ?></td></tr><?php endforeach; ?></tbody></table><?php if (!$recentRepairs): ?><div class="empty-state compact"><strong>ไม่มีรายการแจ้งซ่อมใน 7 วันที่ผ่านมา</strong></div><?php endif; ?></div></section>
        <section class="panel table-panel"><div class="panel-heading" style="padding: 20px 24px 12px;"><div><p class="eyebrow">ACTIVE LEASES</p><h2>สัญญาเช่าที่ใช้งานอยู่</h2></div></div><div class="table-scroll"><table><thead><tr><th>ห้อง</th><th>ผู้เช่า</th><th>ค่าเช่าต่อเดือน</th></tr></thead><tbody><?php foreach ($due as $lease): ?><tr><td><?= e($lease['roomNumber']) ?></td><td><?= e($lease['name']) ?></td><td><?= money($lease['rentalPrice']) ?> ฿</td></tr><?php endforeach; ?></tbody></table></div></section>

      <?php elseif ($page === 'help'): ?>
        <?php $support = db()->query("SELECT name, email, phone FROM `User` WHERE role = 'Admin' ORDER BY name LIMIT 1")->fetch(); ?>
        <section class="page-heading"><div><p class="eyebrow">HELP & SUPPORT</p><h1>ศูนย์ช่วยเหลือ</h1><p class="muted">คำแนะนำการใช้งานและช่องทางติดต่อผู้ดูแลหอพัก</p></div></section>
        <section class="help-grid">
          <article class="panel help-card"><span class="help-card-icon">⌂</span><h2>การใช้งานระบบ</h2><ul><li>อัปเดตชื่อ เบอร์โทร อีเมล หรือรหัสผ่านได้ที่เมนู “ข้อมูลส่วนตัว”</li><li>ผู้เช่าดูรายละเอียดสัญญา ส่งหลักฐานชำระเงิน และติดตามงานซ่อมได้จากเมนูของตน</li><li>แตะรายการในกระดานข่าวเพื่ออ่านรายละเอียดและขยายดูรูปประกาศ</li><li>กด “ดูสลิป” เพื่อเปิดหลักฐานในหน้าต่าง และใช้ปุ่มปิดเพื่อกลับสู่รายการ</li></ul></article>
          <article class="panel help-card"><span class="help-card-icon">☎</span><h2>ติดต่อผู้ดูแลหอพัก</h2><?php if ($support): ?><p>ผู้ดูแล: <strong><?= e($support['name']) ?></strong></p><a class="support-link" href="mailto:<?= e($support['email']) ?>?subject=<?= rawurlencode('สอบถามการใช้งานระบบฟ้าใสเพลส') ?>">ส่งอีเมล <?= e($support['email']) ?></a><a class="support-link" href="tel:<?= e($support['phone']) ?>">โทร <?= e($support['phone']) ?></a><?php else: ?><p class="muted">ยังไม่มีข้อมูลช่องทางติดต่อผู้ดูแล กรุณาติดต่อผู้ดูแลหอพักโดยตรง</p><?php endif; ?></article>
        </section>

      <?php elseif ($page === 'profile'): ?>
        <?php $profileStatement = db()->prepare('SELECT userId, username, name, phone, email, role, idCardNumber, birthDate, address, occupation, workplace, emergencyContactName, emergencyContactRelation, emergencyContactPhone FROM `User` WHERE userId = ?'); $profileStatement->execute([$user['userId']]); $profile = $profileStatement->fetch(); ?>
        <section class="page-heading"><div><p class="eyebrow">ACCOUNT SETTINGS</p><h1>ข้อมูลส่วนตัว</h1><p class="muted">บัญชีและข้อมูลประวัติผู้เช่าของคุณ</p></div></section>
        <section class="panel profile-panel"><div class="profile-avatar"><?= e(mb_substr($profile['name'], 0, 1, 'UTF-8')) ?></div><div class="profile-details"><span class="status-pill available"><?= e($profile['role']) ?></span><h2><?= e($profile['name']) ?></h2><form method="post" action="index.php" class="profile-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="update_profile"><input type="hidden" name="return_page" value="profile">
          <label>ชื่อ-นามสกุล<input name="name" value="<?= e($profile['name']) ?>" required maxlength="100"></label>
          <div class="form-row"><label>เบอร์โทรศัพท์<input name="phone" value="<?= e($profile['phone']) ?>" required pattern="[0-9]{10}" maxlength="10"></label><label>อีเมล<input type="email" name="email" value="<?= e($profile['email']) ?>" required maxlength="100"></label></div>
          <label>ที่อยู่ตามทะเบียนบ้าน<input name="address" value="<?= e($profile['address'] ?? '') ?>" maxlength="255" placeholder="บ้านเลขที่ หมู่ ถนน ตำบล อำเภอ จังหวัด"></label>
          <div class="form-row"><label>อาชีพ<input name="occupation" value="<?= e($profile['occupation'] ?? '') ?>" maxlength="100"></label><label>สถานที่ทำงาน / สถานศึกษา<input name="workplace" value="<?= e($profile['workplace'] ?? '') ?>" maxlength="150"></label></div>
          <div class="form-row"><label>ผู้ติดต่อฉุกเฉิน<input name="emergencyContactName" value="<?= e($profile['emergencyContactName'] ?? '') ?>" maxlength="100"></label><label>ความสัมพันธ์<input name="emergencyContactRelation" value="<?= e($profile['emergencyContactRelation'] ?? '') ?>" maxlength="50"></label></div>
          <label>เบอร์โทรศัพท์ผู้ติดต่อฉุกเฉิน<input name="emergencyContactPhone" value="<?= e($profile['emergencyContactPhone'] ?? '') ?>" pattern="[0-9]{10}" maxlength="10"></label>
          <label>เปลี่ยนรหัสผ่าน <small>(เว้นว่างหากไม่ต้องการเปลี่ยน)</small><input type="password" name="password" minlength="10" autocomplete="new-password"></label>
          <p class="profile-username">Username <strong><?= e($profile['username']) ?></strong> · รหัส <span class="mono"><?= e($profile['userId']) ?></span></p>
          <button class="button primary" type="submit">บันทึกข้อมูล</button>
        </form></div></section>
      <?php endif; ?>
    </div>
  </main>
</div>

<dialog class="modal image-preview-modal" id="image-preview-modal"><div class="preview-modal-content"><div class="modal-heading"><h2>หลักฐานการชำระเงิน</h2><button class="icon-button" type="button" data-modal-close aria-label="ปิด">×</button></div><img alt="รูปภาพ"><button class="button secondary" type="button" data-modal-close>ปิดหน้าต่าง</button></div></dialog>
<dialog class="modal news-preview-modal" id="news-preview-modal"><div class="preview-modal-content"><div class="modal-heading"><h2 data-news-title></h2><button class="icon-button" type="button" data-modal-close aria-label="ปิด">×</button></div><img data-news-image alt="รูปประกอบประกาศ" hidden><p data-news-detail></p><button class="button secondary" type="button" data-modal-close>ปิดหน้าต่าง</button></div></dialog>
<dialog class="modal contract-doc-viewer" id="contract-doc-preview-modal"><div class="preview-modal-content"><div class="modal-heading"><h2 data-contract-doc-title>เอกสารสัญญา</h2><button class="icon-button" type="button" data-modal-close aria-label="ปิด">×</button></div><div class="contract-doc-viewer-body"><img data-contract-doc-image alt="เอกสารสัญญา"><iframe data-contract-doc-frame title="เอกสารสัญญา PDF" hidden></iframe></div></div></dialog>

<dialog class="modal logout-confirm-modal" id="logout-confirm-modal" onclick="if(event.target === this) this.close()">
  <div class="logout-modal-box">
    <div class="logout-modal-icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
        <polyline points="16 17 21 12 16 7"/>
        <line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
    </div>
    <h3>ยืนยันการออกจากระบบ</h3>
    <p>คุณต้องการออกจากระบบหอพักฟ้าใสเพลสใช่หรือไม่?<br><small>หากออกจากระบบแล้ว คุณจะต้องเข้าสู่ระบบใหม่อีกครั้ง</small></p>
    <div class="logout-modal-actions">
      <button type="button" class="button secondary" onclick="document.getElementById('logout-confirm-modal').close()">ยกเลิก</button>
      <button type="button" class="button logout-confirm-btn" onclick="document.getElementById('logout-form').submit()">ยืนยันออกจากระบบ</button>
    </div>
  </div>
</dialog>

<dialog class="modal logout-confirm-modal" id="action-confirm-modal" onclick="if(event.target === this) this.close()">
  <div class="logout-modal-box">
    <div class="logout-modal-icon" id="action-confirm-icon" aria-hidden="true" style="background: rgba(59, 130, 246, 0.12); color: #2563eb;">
      <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
    </div>
    <h3 id="action-confirm-title">ยืนยันการทำรายการ</h3>
    <p id="action-confirm-message" style="margin-bottom: 20px;">คุณต้องการดำเนินการนี้ใช่หรือไม่?</p>
    <div class="logout-modal-actions">
      <button type="button" class="button secondary" onclick="document.getElementById('action-confirm-modal').close()">ยกเลิก</button>
      <button type="button" class="button primary" id="action-confirm-submit">ยืนยัน</button>
    </div>
  </div>
</dialog>

<dialog class="modal logout-confirm-modal" id="system-alert-modal" style="z-index: 99999;" onclick="if(event.target === this) closeSystemPopup()">
  <div class="logout-modal-box">
    <div class="logout-modal-icon" id="system-alert-icon" aria-hidden="true" style="background: rgba(239, 68, 68, 0.12); color: #dc2626;">
      <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
    </div>
    <h3 id="system-alert-title">ไม่สามารถบันทึกข้อมูลได้</h3>
    <p id="system-alert-message" style="margin-bottom: 20px;">กรุณาตรวจสอบข้อมูลให้ครบถ้วนและถูกต้อง</p>
    <div class="logout-modal-actions" style="justify-content: center;">
      <button type="button" class="button primary" id="system-alert-ok-btn" style="min-width: 140px;" onclick="closeSystemPopup()">รับทราบ / ตกลง</button>
    </div>
  </div>
</dialog>

<script>
let pendingInvalidField = null;

function showSystemPopup(title, message, type = 'error', focusField = null) {
  const modal = document.getElementById('system-alert-modal');
  const icon = document.getElementById('system-alert-icon');
  const titleEl = document.getElementById('system-alert-title');
  const msgEl = document.getElementById('system-alert-message');
  const okBtn = document.getElementById('system-alert-ok-btn');

  if (!modal) {
    alert(title + ': ' + message);
    return;
  }

  pendingInvalidField = focusField;
  titleEl.textContent = title;
  msgEl.textContent = message;

  if (type === 'warning') {
    icon.style.background = 'rgba(245, 158, 11, 0.14)';
    icon.style.color = '#d97706';
    okBtn.className = 'button primary';
  } else {
    icon.style.background = 'rgba(239, 68, 68, 0.12)';
    icon.style.color = '#dc2626';
    okBtn.className = 'button danger';
  }

  if (!modal.open) {
    modal.showModal();
  }
}

function closeSystemPopup() {
  const modal = document.getElementById('system-alert-modal');
  if (modal && modal.open) {
    modal.close();
  }
  if (pendingInvalidField) {
    const target = pendingInvalidField;
    pendingInvalidField = null;
    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(() => target.focus(), 120);
  }
}

document.addEventListener('click', (e) => {
  const editAccountBtn = e.target.closest('[data-edit-account]');
  if (!editAccountBtn) return;
  try {
    const data = JSON.parse(editAccountBtn.getAttribute('data-account') || '{}');
    const modal = document.getElementById('user-modal');
    if (!modal) return;
    const setVal = (name, val) => {
      const el = modal.querySelector(`[name="${name}"]`);
      if (el) el.value = val ?? '';
    };
    setTimeout(() => {
      setVal('idCardNumber', data.idCardNumber);
      setVal('birthDate', data.birthDate);
      setVal('address', data.address);
      setVal('occupation', data.occupation);
      setVal('workplace', data.workplace);
      setVal('emergencyContactName', data.emergencyContactName);
      setVal('emergencyContactRelation', data.emergencyContactRelation);
      setVal('emergencyContactPhone', data.emergencyContactPhone);
    }, 10);
  } catch (err) {}
});

document.addEventListener('invalid', (e) => {
  e.preventDefault();
  const field = e.target;
  if (document.getElementById('system-alert-modal')?.open) return;

  let fieldLabel = '';
  const labelEl = field.closest('label');
  if (labelEl) {
    const titleSpan = labelEl.querySelector('.contract-field-title') || labelEl.querySelector('span');
    if (titleSpan) {
      const clone = titleSpan.cloneNode(true);
      clone.querySelectorAll('.age-badge, .contract-field-sub').forEach(el => el.remove());
      fieldLabel = clone.textContent.replace('*', '').trim();
    } else {
      const clone = labelEl.cloneNode(true);
      clone.querySelectorAll('input, select, textarea, small').forEach(el => el.remove());
      fieldLabel = clone.textContent.replace('*', '').trim();
    }
  } else if (field.id === 'contract-main-occupation-other') {
    fieldLabel = 'ระบุอาชีพอื่นๆ';
  }

  let reason = 'กรุณาระบุข้อมูลให้ครบถ้วนและถูกต้อง';
  if (field.validity.valueMissing) {
    reason = fieldLabel ? `กรุณาระบุข้อมูลในช่อง "${fieldLabel}"` : 'กรุณากรอกข้อมูลในช่องที่จำเป็นให้ครบถ้วน';
  } else if (field.validity.patternMismatch) {
    reason = fieldLabel ? `รูปแบบข้อมูลในช่อง "${fieldLabel}" ไม่ถูกต้อง (กรุณาตรวจสอบจำนวนหลัก)` : 'รูปแบบข้อมูลไม่ถูกต้อง';
  } else if (field.validity.rangeUnderflow) {
    reason = fieldLabel ? `ค่าในช่อง "${fieldLabel}" ต้องไม่น้อยกว่าที่กำหนด` : 'ตัวเลขน้อยกว่าค่าที่กำหนด';
  } else if (field.validity.typeMismatch) {
    reason = fieldLabel ? `กรุณากรอก "${fieldLabel}" ให้ถูกรูปแบบ` : 'รูปแบบข้อมูลไม่ถูกต้อง';
  }

  showSystemPopup('ข้อมูลไม่ครบถ้วนหรือรูปแบบไม่ถูกต้อง', reason, 'warning', field);
}, true);

document.addEventListener('DOMContentLoaded', () => {
  <?php if (!empty($flashMessage) && ($flashMessage['type'] ?? '') === 'error'): ?>
    showSystemPopup('ไม่สามารถบันทึกข้อมูลได้', <?= json_encode((string) ($flashMessage['message'] ?? 'เกิดข้อผิดพลาด กรุณาตรวจสอบข้อมูลอีกครั้ง'), JSON_UNESCAPED_UNICODE) ?>, 'error');
  <?php endif; ?>

  const formModals = document.querySelectorAll('dialog.modal:not(#logout-confirm-modal):not(#action-confirm-modal):not(#image-preview-modal):not(#news-preview-modal):not(#system-alert-modal)');

  formModals.forEach((modal) => {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        e.stopImmediatePropagation();
      }
    }, true);

    modal.addEventListener('cancel', (e) => {
      e.preventDefault();
    });
  });

  const confirmModal = document.getElementById('action-confirm-modal');
  const confirmTitle = document.getElementById('action-confirm-title');
  const confirmMessage = document.getElementById('action-confirm-message');
  const confirmSubmitBtn = document.getElementById('action-confirm-submit');
  const confirmIcon = document.getElementById('action-confirm-icon');
  let pendingConfirmTarget = null;

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-confirm]');
    if (!btn) return;

    if (btn.form && !btn.form.checkValidity()) {
      btn.form.reportValidity();
      e.preventDefault();
      e.stopImmediatePropagation();
      return;
    }

    e.preventDefault();
    e.stopImmediatePropagation();

    pendingConfirmTarget = btn;
    const msg = btn.getAttribute('data-confirm') || 'คุณต้องการดำเนินการนี้ใช่หรือไม่?';
    const isDanger = btn.classList.contains('danger') || btn.classList.contains('danger-icon') || msg.includes('ลบ') || msg.includes('ปฏิเสธ') || msg.includes('สิ้นสุด') || msg.includes('ส่งกลับ');

    confirmTitle.textContent = isDanger ? 'ยืนยันการดำเนินการ' : 'ยืนยันการทำรายการ';
    confirmMessage.textContent = msg;

    if (isDanger) {
      confirmIcon.style.background = 'rgba(239, 68, 68, 0.12)';
      confirmIcon.style.color = '#dc2626';
      confirmSubmitBtn.className = 'button danger';
      confirmSubmitBtn.textContent = 'ยืนยัน';
    } else {
      confirmIcon.style.background = 'rgba(16, 185, 129, 0.14)';
      confirmIcon.style.color = '#059669';
      confirmSubmitBtn.className = 'button success';
      confirmSubmitBtn.textContent = 'ยืนยัน';
    }

    confirmModal.showModal();
  }, true);

  confirmSubmitBtn.addEventListener('click', () => {
    confirmModal.close();
    if (pendingConfirmTarget) {
      const target = pendingConfirmTarget;
      pendingConfirmTarget = null;
      target.removeAttribute('data-confirm');
      if (target.form && typeof target.form.requestSubmit === 'function') {
        target.form.requestSubmit(target);
      } else {
        target.click();
      }
    }
  });
});

document.addEventListener('click', (e) => {
  const slipLink = e.target.closest('a[href*="file.php?id="]');
  if (!slipLink) return;
  const modal = document.getElementById('image-preview-modal');
  const img = modal ? modal.querySelector('img') : null;
  if (modal && img) {
    e.preventDefault();
    const url = new URL(slipLink.href, window.location.href);
    url.searchParams.set('raw', '1');
    img.src = url.toString();
    modal.showModal();
  }
});
</script>

</body>
</html>