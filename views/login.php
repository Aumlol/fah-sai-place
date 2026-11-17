<?php
require_once __DIR__ . '/../app/helpers.php';
$hasUsers = false;
try {
  $hasUsers = (int) db()->query('SELECT COUNT(*) FROM `User`')->fetchColumn() > 0;
} catch (Throwable) {
}
?>
<!doctype html>
<html lang="th">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>เข้าสู่ระบบ | <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="assets/css/app.css">
</head>

<body class="login-page">
  <main class="login-shell">
    <section class="login-art" aria-label="ฟ้าใสเพลส">
      <div class="brand-mark"><svg viewBox="0 0 32 32" aria-hidden="true">
          <path d="M5 15 16 6l11 9M9 14v11h14V14M7 28c3-2 5-2 9 0s6 2 9 0" />
        </svg></div>
      <p class="eyebrow">FAH SAI PLACE</p>
      <h1>ระบบหอพัก<br>ฟ้าใสเพลส</h1>
      <p class="login-note">ข้อมูลห้องพัก ผู้เช่า ค่าใช้จ่าย และงานซ่อม ในที่เดียว</p>
      <div class="building-art" aria-hidden="true">
        <div class="building"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
        <div class="ground"></div>
      </div>
    </section>
    <section class="login-panel">
      <div class="mobile-brand"><span class="brand-mark"><svg viewBox="0 0 32 32" aria-hidden="true">
            <path d="M5 15 16 6l11 9M9 14v11h14V14M7 28c3-2 5-2 9 0s6 2 9 0" />
          </svg></span><strong>ฟ้าใสเพลส</strong></div>
      <div class="login-form-wrap">
        <p class="eyebrow">WELCOME BACK</p>
        <h2>เข้าสู่ระบบ</h2>
        <p class="muted">ใช้บัญชีของคุณเพื่อจัดการข้อมูลหอพัก</p>
        <?php if ($flashMessage): ?>
          <div class="alert <?= e($flashMessage['type']) ?>"><?= e($flashMessage['message']) ?></div><?php endif; ?>
        <form action="index.php?page=login" method="post" class="form-stack">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="login">
          <label>ชื่อผู้ใช้<input name="username" autocomplete="username" required autofocus
              placeholder="กรอกชื่อผู้ใช้"></label>
          <label>รหัสผ่าน<span class="password-control"><input type="password" name="password" autocomplete="current-password" required
              placeholder="กรอกรหัสผ่าน"><button class="password-toggle" type="button" data-toggle-password="password" aria-label="แสดงรหัสผ่าน">แสดง</button></span></label>
          <button class="button primary full" type="submit">เข้าสู่ระบบ <span aria-hidden="true">→</span></button>
        </form>
        <?php if (!$hasUsers): ?><a class="setup-link"
            href="index.php?page=setup">ตั้งค่าบัญชีผู้ดูแลระบบครั้งแรก</a><?php endif; ?>
      </div>
      <footer>© <?= date('Y') ?> ฟ้าใสเพลส · ระบบบริหารจัดการหอพัก</footer>
    </section>
  </main>
  <script src="assets/js/app.js" defer></script>
</body>

</html>