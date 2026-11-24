<?php
$hasAdmin = (int) db()->query("SELECT COUNT(*) FROM `User` WHERE role = 'Admin'")->fetchColumn() > 0;
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>เข้าสู่ระบบ | <?= e(APP_NAME) ?></title>
<<<<<<< HEAD
  <link rel="stylesheet" href="assets/css/app.css?v=<?= e((string) filemtime(__DIR__ . '/../assets/css/app.css')) ?>-login5">
  <style>
    /* === Modern Ambient Background + Centered Login Card === */
    body.login-page {
      min-height: 100vh;
      margin: 0;
      padding: 24px;
      display: grid;
      place-items: center;
      position: relative;
      overflow-x: hidden;
      background-color: #eef6fc !important;
      /* ลายจุดเรขาคณิตจางๆ ผสมพื้นหลังไล่เฉด */
      background-image:
        radial-gradient(rgba(56, 152, 200, 0.22) 1.25px, transparent 1.25px),
        radial-gradient(circle at 15% 20%, rgba(125, 211, 252, 0.45) 0%, transparent 45%),
        radial-gradient(circle at 85% 80%, rgba(147, 197, 253, 0.45) 0%, transparent 45%),
        linear-gradient(135deg, #f3f9ff 0%, #e3effa 100%) !important;
      background-size: 26px 26px, 100% 100%, 100% 100%, 100% 100% !important;
    }

    /* วงแสงฟุ้งลอยช้าๆ ด้านหลังการ์ด */
    .bg-orb {
      position: fixed;
      border-radius: 50%;
      filter: blur(65px);
      z-index: 0;
      pointer-events: none;
      animation: floatOrb 12s ease-in-out infinite alternate;
    }

    .bg-orb-1 {
      width: 380px;
      height: 380px;
      background: rgba(56, 189, 248, 0.28);
      top: -80px;
      left: 8%;
    }

    .bg-orb-2 {
      width: 340px;
      height: 340px;
      background: rgba(59, 130, 246, 0.22);
      bottom: -60px;
      right: 10%;
      animation-delay: -5s;
    }

    .bg-orb-3 {
      width: 240px;
      height: 240px;
      background: rgba(165, 243, 252, 0.45);
      top: 45%;
      left: 52%;
      transform: translate(-50%, -50%);
      animation-duration: 16s;
    }

    @keyframes floatOrb {
      0% { transform: translate(0, 0) scale(1); }
      100% { transform: translate(24px, -20px) scale(1.08); }
    }

    /* วงแหวนเส้นบางๆ ประดับฉากหลังให้ดูมีเลเยอร์ */
    .bg-ring {
      position: fixed;
      width: 640px;
      height: 640px;
      border-radius: 50%;
      border: 1px solid rgba(56, 152, 200, 0.14);
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      pointer-events: none;
      z-index: 0;
    }

    .bg-ring::after {
      content: "";
      position: absolute;
      inset: 75px;
      border-radius: 50%;
      border: 1px dashed rgba(56, 152, 200, 0.16);
    }

    .single-login-card {
      width: min(430px, 100%);
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.95);
      border-radius: 28px;
      padding: 54px 36px 28px;
      box-shadow:
        0 25px 65px rgba(15, 41, 66, 0.12),
        0 6px 20px rgba(34, 111, 168, 0.06),
        inset 0 1px 0 rgba(255, 255, 255, 1);
      position: relative;
      z-index: 2;
      animation: cardFadeUp 0.35s ease-out;
    }

    @keyframes cardFadeUp {
      from { opacity: 0; transform: translateY(12px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* ปุ่มสลับภาษา TH / EN มุมขวาบน */
    .lang-pill-switch {
      position: absolute;
      top: 18px;
      right: 20px;
      display: inline-flex;
      align-items: center;
      background: #f1f5f9;
      border: 1px solid #e2e8f0;
      border-radius: 99px;
      padding: 3px;
      gap: 2px;
      z-index: 5;
    }

    .lang-pill-btn {
      border: 0;
      background: transparent;
      color: #64748b;
      font-size: 11.5px;
      font-weight: 700;
      padding: 4px 11px;
      border-radius: 99px;
      cursor: pointer;
      transition: all 0.18s ease;
    }

    .lang-pill-btn:hover {
      color: #0f172a;
    }

    .lang-pill-btn.active {
      background: #ffffff;
      color: #1d6fa5;
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }

    /* โลโก้จัดกึ่งกลาง */
    .center-brand-header {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      margin-bottom: 24px;
    }

    .center-brand-icon {
      width: 52px;
      height: 52px;
      border-radius: 16px;
      background: linear-gradient(135deg, #3898c8 0%, #226fa8 100%);
      color: #ffffff;
      display: grid;
      place-items: center;
      box-shadow: 0 10px 22px rgba(34, 111, 168, 0.25);
      margin-bottom: 12px;
    }

    .center-brand-icon svg {
      width: 26px;
      height: 26px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2.2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .center-brand-title {
      font-size: 19px;
      font-weight: 800;
      color: #0f172a;
      line-height: 1.25;
      margin: 0;
    }

    .center-brand-sub {
      font-size: 12.5px;
      font-weight: 500;
      color: #64748b;
      margin-top: 2px;
    }

    .header-divider {
      width: 40px;
      height: 3px;
      border-radius: 99px;
      background: #e2e8f0;
      margin: 14px auto 12px;
    }

    /* หัวข้อเข้าสู่ระบบขนาดพอดีไม่ใหญ่เกิน */
    .login-section-title {
      font-size: 17px;
      font-weight: 700;
      color: #334155;
      margin: 0 0 4px;
      line-height: 1.3;
    }

    .login-section-desc {
      font-size: 13px;
      color: #64748b;
      margin: 0;
      line-height: 1.45;
    }

    /* ฟอร์มกรอกข้อมูล */
    .modern-form {
      display: grid;
      gap: 16px;
    }

    .field-block {
      display: grid;
      gap: 6px;
    }

    .field-block label {
      font-size: 13px;
      font-weight: 700;
      color: #334155;
    }

    .input-shell {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-icon {
      position: absolute;
      left: 14px;
      color: #94a3b8;
      display: inline-flex;
      pointer-events: none;
      transition: color 0.18s ease;
    }

    .input-shell input {
      width: 100%;
      height: 46px;
      border: 1.5px solid #e2e8f0;
      border-radius: 13px;
      padding: 0 14px 0 42px;
      font-size: 14.5px;
      color: #0f172a;
      background: #f8fafc;
      transition: all 0.18s ease;
    }

    .input-shell input::placeholder {
      color: #94a3b8;
    }

    .input-shell input:focus {
      background: #ffffff;
      border-color: #3898c8;
      box-shadow: 0 0 0 4px rgba(56, 152, 200, 0.14);
      outline: none;
    }

    .input-shell:focus-within .input-icon {
      color: #2563eb;
    }

    .input-shell input[name="password"] {
      padding-right: 72px;
    }

    .pw-eye-btn {
      position: absolute;
      right: 8px;
      border: 0;
      background: #e2eef8;
      color: #1d6fa5;
      font-size: 12px;
      font-weight: 700;
      padding: 5px 10px;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.16s ease;
    }

    .pw-eye-btn:hover {
      background: #cfe4f5;
    }

    .login-cta-btn {
      width: 100%;
      height: 48px;
      margin-top: 6px;
      border: 0;
      border-radius: 13px;
      background: linear-gradient(135deg, #3490cb 0%, #1e6aa3 100%);
      color: #ffffff;
      font-size: 15px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      cursor: pointer;
      box-shadow: 0 10px 24px rgba(30, 106, 163, 0.26);
      transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .login-cta-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 14px 28px rgba(30, 106, 163, 0.34);
    }

    .login-cta-btn:active {
      transform: translateY(0);
    }

    .setup-admin-link {
      display: block;
      text-align: center;
      margin-top: 16px;
      font-size: 13px;
      font-weight: 600;
      color: #2563eb;
      text-decoration: none;
    }

    .setup-admin-link:hover {
      text-decoration: underline;
    }

    .card-footer {
      margin-top: 24px;
      padding-top: 15px;
      border-top: 1px solid #f1f5f9;
      text-align: center;
      font-size: 11.5px;
      color: #94a3b8;
    }

    @media (max-width: 480px) {
      .single-login-card {
        padding: 52px 22px 22px;
        border-radius: 22px;
      }
      .bg-ring {
        width: 420px;
        height: 420px;
      }
    }
  </style>
=======
  <link rel="stylesheet" href="assets/css/app.css?v=<?= e((string) filemtime(__DIR__ . '/../assets/css/app.css')) ?>-heading-spacing">
>>>>>>> 449e13e86e3adc18aa01ae1b7b633c4fa3d8356a
</head>
<body class="login-page">
<<<<<<< HEAD
  <!-- ลูกเล่นพื้นหลัง: แสงฟุ้ง + วงแหวนบางๆ -->
  <div class="bg-orb bg-orb-1" aria-hidden="true"></div>
  <div class="bg-orb bg-orb-2" aria-hidden="true"></div>
  <div class="bg-orb bg-orb-3" aria-hidden="true"></div>
  <div class="bg-ring" aria-hidden="true"></div>
=======
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
>>>>>>> 449e13e86e3adc18aa01ae1b7b633c4fa3d8356a

  <main class="single-login-card">
    <!-- ปุ่มเปลี่ยนภาษา มุมขวาบน -->
    <div class="lang-pill-switch" role="group" aria-label="Language Switcher">
      <button type="button" class="lang-pill-btn active" data-lang-btn="th">ไทย</button>
      <button type="button" class="lang-pill-btn" data-lang-btn="en">EN</button>
    </div>

    <!-- โลโก้ + หัวข้อ จัดกึ่งกลาง -->
    <header class="center-brand-header">
      <span class="center-brand-icon" aria-hidden="true">
        <svg viewBox="0 0 32 32"><path d="M5 15 16 6l11 9M9 14v11h14V14M7 28c3-2 5-2 9 0s6 2 9 0" /></svg>
      </span>
      <div class="center-brand-title" data-i18n="brandName">ฟ้าใสเพลส</div>
      <span class="center-brand-sub" data-i18n="brandSub">ระบบบริหารจัดการหอพัก</span>

      <div class="header-divider" aria-hidden="true"></div>

      <h1 class="login-section-title" data-i18n="heading">เข้าสู่ระบบ</h1>
      <p class="login-section-desc" data-i18n="subheading">กรอกชื่อผู้ใช้งานและรหัสผ่านเพื่อเข้าใช้งานระบบ</p>
    </header>

    <?php if ($flashMessage): ?>
      <div class="alert <?= e($flashMessage['type']) ?>" style="margin-bottom: 18px;"><?= e($flashMessage['message']) ?></div>
    <?php endif; ?>

    <!-- ฟอร์มเข้าสู่ระบบ -->
    <form method="post" action="index.php" class="modern-form">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="action" value="login">

      <div class="field-block">
        <label for="login-username" data-i18n="usernameLabel">ชื่อผู้ใช้งาน</label>
        <div class="input-shell">
          <span class="input-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </span>
          <input id="login-username" name="username" autocomplete="username" placeholder="กรอกชื่อผู้ใช้งาน" data-i18n-placeholder="usernamePlaceholder" required autofocus>
        </div>
      </div>

      <div class="field-block">
        <label for="login-password" data-i18n="passwordLabel">รหัสผ่าน</label>
        <div class="input-shell">
          <span class="input-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input id="login-password" type="password" name="password" autocomplete="current-password" placeholder="กรอกรหัสผ่าน" data-i18n-placeholder="passwordPlaceholder" required>
          <button type="button" class="pw-eye-btn" id="toggle-login-pw" data-i18n="showPw">แสดง</button>
        </div>
      </div>

      <button class="login-cta-btn" type="submit">
        <span data-i18n="submitBtn">เข้าสู่ระบบ</span>
        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </button>
    </form>

    <?php if (!$hasAdmin): ?>
      <a class="setup-admin-link" href="index.php?setup=1" data-i18n="setupLink">ตั้งค่าผู้ดูแลระบบเริ่มต้น</a>
    <?php endif; ?>

    <footer class="card-footer" data-i18n="footerText">© <?= e(date('Y')) ?> ฟ้าใสเพลส · ระบบบริหารจัดการหอพัก</footer>
  </main>

  <script>
    (function () {
      const translations = {
        th: {
          pageTitle: 'เข้าสู่ระบบ | ฟ้าใสเพลส',
          brandName: 'ฟ้าใสเพลส',
          brandSub: 'ระบบบริหารจัดการหอพัก',
          heading: 'เข้าสู่ระบบ',
          subheading: 'กรอกชื่อผู้ใช้งานและรหัสผ่านเพื่อเข้าใช้งานระบบ',
          usernameLabel: 'ชื่อผู้ใช้งาน',
          usernamePlaceholder: 'กรอกชื่อผู้ใช้งาน',
          passwordLabel: 'รหัสผ่าน',
          passwordPlaceholder: 'กรอกรหัสผ่าน',
          showPw: 'แสดง',
          hidePw: 'ซ่อน',
          submitBtn: 'เข้าสู่ระบบ',
          setupLink: 'ตั้งค่าผู้ดูแลระบบเริ่มต้น',
          footerText: '© <?= e(date('Y')) ?> ฟ้าใสเพลส · ระบบบริหารจัดการหอพัก'
        },
        en: {
          pageTitle: 'Sign In | Fah Sai Place',
          brandName: 'Fah Sai Place',
          brandSub: 'Dormitory Management System',
          heading: 'Sign In',
          subheading: 'Enter your username and password to continue',
          usernameLabel: 'Username',
          usernamePlaceholder: 'Enter your username',
          passwordLabel: 'Password',
          passwordPlaceholder: 'Enter your password',
          showPw: 'Show',
          hidePw: 'Hide',
          submitBtn: 'Sign In',
          setupLink: 'Initial Admin Setup',
          footerText: '© <?= e(date('Y')) ?> Fah Sai Place · Dormitory Management System'
        }
      };

      let currentLang = localStorage.getItem('fahsai_login_lang') || 'th';
      const pwInput = document.getElementById('login-password');
      const pwToggle = document.getElementById('toggle-login-pw');
      const langBtns = document.querySelectorAll('[data-lang-btn]');

      function applyLanguage(lang) {
        currentLang = translations[lang] ? lang : 'th';
        localStorage.setItem('fahsai_login_lang', currentLang);
        document.documentElement.lang = currentLang;

        const dict = translations[currentLang];
        document.title = dict.pageTitle;

        document.querySelectorAll('[data-i18n]').forEach((el) => {
          const key = el.getAttribute('data-i18n');
          if (dict[key]) {
            el.textContent = dict[key];
          }
        });

        document.querySelectorAll('[data-i18n-placeholder]').forEach((el) => {
          const key = el.getAttribute('data-i18n-placeholder');
          if (dict[key]) {
            el.setAttribute('placeholder', dict[key]);
          }
        });

        if (pwInput && pwToggle) {
          pwToggle.textContent = pwInput.type === 'password' ? dict.showPw : dict.hidePw;
        }

        langBtns.forEach((btn) => {
          btn.classList.toggle('active', btn.getAttribute('data-lang-btn') === currentLang);
        });
      }

      if (pwInput && pwToggle) {
        pwToggle.addEventListener('click', () => {
          const isHidden = pwInput.type === 'password';
          pwInput.type = isHidden ? 'text' : 'password';
          const dict = translations[currentLang];
          pwToggle.textContent = isHidden ? dict.hidePw : dict.showPw;
        });
      }

      langBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
          applyLanguage(btn.getAttribute('data-lang-btn'));
        });
      });

      applyLanguage(currentLang);
    })();
  </script>
</body>
</html>