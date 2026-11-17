<?php
require_once __DIR__ . '/../app/helpers.php';
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ตั้งค่าผู้ดูแลระบบ | <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="login-page">
  <main class="setup-card">
    <a class="back-link" href="index.php?page=login">← กลับหน้าเข้าสู่ระบบ</a>
    <p class="eyebrow">FIRST-TIME SETUP</p>
    <h1>สร้างบัญชีผู้ดูแล</h1>
    <p class="muted">ใช้ได้เฉพาะตอนฐานข้อมูลยังไม่มีบัญชีผู้ใช้</p>
    <?php if ($flashMessage): ?><div class="alert <?= e($flashMessage['type']) ?>"><?= e($flashMessage['message']) ?></div><?php endif; ?>
    <form action="index.php?page=setup" method="post" class="form-stack">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="action" value="setup_admin">
      <label>ชื่อ-นามสกุล<input name="name" required maxlength="100"></label>
      <label>ชื่อผู้ใช้<input name="username" required maxlength="50" autocomplete="username"></label>
      <label>อีเมล<input type="email" name="email" required maxlength="100"></label>
      <label>เบอร์โทรศัพท์<input name="phone" required inputmode="numeric" pattern="[0-9]{10}" maxlength="10"></label>
      <label>รหัสผ่าน<input type="password" name="password" required minlength="10" autocomplete="new-password"></label>
      <button class="button primary full" type="submit">สร้างบัญชีผู้ดูแล</button>
    </form>
  </main>
</body>
</html>
