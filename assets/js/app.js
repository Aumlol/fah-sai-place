function updateAccountPassword(modal) {
  const form = modal.querySelector('form');
  const password = form.querySelector('[data-password]');
  const confirmation = form.querySelector('[data-password-confirm]');
  const strength = modal.querySelector('[data-password-strength]');
  const strengthLabel = modal.querySelector('[data-password-strength-label]');
  const matchLabel = modal.querySelector('[data-password-match]');
  const criteria = {
    length: password.value.length >= 12,
    lowercase: /[a-z]/.test(password.value),
    uppercase: /[A-Z]/.test(password.value),
    number: /[0-9]/.test(password.value),
    symbol: /[^a-zA-Z0-9]/.test(password.value),
  };
  const score = Object.values(criteria).filter(Boolean).length;

  password.closest('label').querySelector('.required-mark').hidden = !password.required;
  modal.querySelectorAll('[data-password-rule]').forEach(rule => {
    rule.classList.toggle('met', criteria[rule.dataset.passwordRule]);
  });
  strength.setAttribute('aria-valuenow', String(score));
  strength.dataset.strength = score === 5 ? 'strong' : score >= 3 ? 'medium' : 'weak';
  strength.querySelector('span').style.width = `${score * 20}%`;
  strengthLabel.textContent = !password.value && !password.required
    ? 'ไม่เปลี่ยนรหัสผ่าน'
    : !password.value ? 'ยังไม่ได้ตั้ง' : score === 5 ? 'แข็งแรง' : score >= 3 ? 'ปานกลาง' : 'อ่อน';
  password.setCustomValidity(password.value && score !== 5
    ? 'กรุณาทำตามเงื่อนไขรหัสผ่านให้ครบทุกข้อ'
    : '');

  confirmation.required = password.required || password.value.length > 0;
  const confirmationMark = confirmation.closest('label').querySelector('.required-mark');
  confirmationMark.hidden = !confirmation.required;
  const matches = password.value !== '' && password.value === confirmation.value;
  confirmation.setCustomValidity(confirmation.required && !matches
    ? 'กรุณายืนยันรหัสผ่านให้ตรงกัน'
    : '');
  if (!password.value && !confirmation.value) {
    matchLabel.textContent = confirmation.required
      ? 'กรอกรหัสผ่านอีกครั้งให้ตรงกัน'
      : 'เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน';
    matchLabel.dataset.state = 'neutral';
  } else if (matches) {
    matchLabel.textContent = '✓ รหัสผ่านตรงกัน';
    matchLabel.dataset.state = 'valid';
  } else if (!confirmation.value) {
    matchLabel.textContent = 'กรอกรหัสผ่านอีกครั้งให้ตรงกัน';
    matchLabel.dataset.state = 'neutral';
  } else {
    matchLabel.textContent = 'รหัสผ่านยังไม่ตรงกัน';
    matchLabel.dataset.state = 'invalid';
  }
}

function resetPasswordVisibility(form) {
  form.querySelectorAll('.password-control').forEach(control => {
    control.querySelector('input').type = 'password';
    const toggle = control.querySelector('[data-toggle-password]');
    toggle.textContent = 'แสดง';
    toggle.setAttribute('aria-label', toggle.dataset.togglePassword === 'password'
      ? 'แสดงรหัสผ่าน'
      : 'แสดงรหัสผ่านที่ยืนยัน');
  });
}

function updateRoomAssignmentForm(modal) {
  const form = modal.querySelector('form');
  const status = form.querySelector('[data-room-status]');
  const assignmentFields = form.querySelector('[data-room-assignment-fields]');
  const tenant = form.querySelector('[data-room-tenant]');
  const startDate = form.querySelector('[data-room-start]');
  const deposit = form.querySelector('[data-room-deposit]');
  const note = form.querySelector('[data-room-assignment-note]');
  const roomId = form.querySelector('[name="roomId"]').value;
  const currentTenantId = form.dataset.currentTenant || '';
  const isOccupied = status.value === 'มีผู้เช่า';
  const hasCurrentLease = currentTenantId !== '' && tenant.value === currentTenantId;
  const needsNewLease = isOccupied && !hasCurrentLease;

  assignmentFields.hidden = !isOccupied;
  tenant.required = isOccupied;
  startDate.required = needsNewLease;
  deposit.required = needsNewLease;
  note.textContent = hasCurrentLease
    ? 'ห้องนี้เชื่อมกับผู้เช่าปัจจุบันแล้ว หากต้องการเปลี่ยนผู้เช่าให้สิ้นสุดสัญญาเดิมก่อน'
    : 'เลือกผู้เช่าที่ยังไม่มีสัญญาใช้งานอยู่ และระบุวันที่เริ่มสัญญา';

  Array.from(tenant.options).forEach(option => {
    const activeRoomId = option.dataset.activeRoom || '';
    option.disabled = activeRoomId !== '' && activeRoomId !== roomId;
  });
}

document.addEventListener('click', event => {
  const openButton = event.target.closest('[data-modal-open]');
  if (openButton) {
    const modal = document.getElementById(openButton.dataset.modalOpen);
    if (modal && openButton.dataset.modalOpen === 'room-modal') {
      const form = modal.querySelector('form');
      form.reset();
      form.querySelector('[name="roomId"]').value = '';
      form.dataset.currentTenant = '';
      updateRoomAssignmentForm(modal);
    }
    if (modal && openButton.dataset.modalOpen === 'user-modal' && !openButton.dataset.editAccount) {
      const form = modal.querySelector('form');
      form.reset();
      resetPasswordVisibility(form);
      form.querySelector('[name="userId"]').value = '';
      modal.querySelector('h2').textContent = 'เพิ่มบัญชีผู้ใช้งาน';
      modal.querySelector('[type="submit"]').textContent = 'สร้างบัญชี';
      form.querySelector('[name="password"]').required = true;
      form.querySelector('[name="password_confirm"]').required = true;
      modal.querySelector('[data-password-note]').textContent = 'รหัสผ่านใหม่ต้องผ่านเงื่อนไขครบทุกข้อ';
      updateAccountPassword(modal);
    }
    if (modal && openButton.dataset.modalOpen === 'news-modal') {
      const form = modal.querySelector('form');
      form.reset();
      form.querySelector('[name="newsId"]').value = '';
      modal.querySelector('h2').textContent = 'เพิ่มประกาศ';
      modal.querySelector('[type="submit"]').textContent = 'เผยแพร่';
    }
    if (modal) modal.showModal();
  }

  const imageButton = event.target.closest('[data-image-preview]');
  if (imageButton) {
    event.preventDefault();
    const modal = document.getElementById('image-preview-modal');
    const image = modal?.querySelector('img');
    if (modal && image) {
      image.src = imageButton.href;
      image.alt = imageButton.dataset.imageAlt || 'รูปภาพ';
      modal.showModal();
    }
  }

  const newsButton = event.target.closest('[data-news-preview]');
  if (newsButton) {
    const modal = document.getElementById('news-preview-modal');
    if (modal) {
      const news = JSON.parse(newsButton.dataset.newsPreview);
      modal.querySelector('[data-news-title]').textContent = news.title;
      modal.querySelector('[data-news-detail]').textContent = news.detail;
      const image = modal.querySelector('[data-news-image]');
      if (news.image) {
        image.src = `file.php?news=${encodeURIComponent(news.newsId)}`;
        image.hidden = false;
      } else {
        image.removeAttribute('src');
        image.hidden = true;
      }
      modal.showModal();
    }
  }

  const closeButton = event.target.closest('[data-modal-close]');
  if (closeButton) closeButton.closest('dialog')?.close();

  const passwordToggle = event.target.closest('[data-toggle-password]');
  if (passwordToggle) {
    const input = passwordToggle.closest('.password-control').querySelector('input');
    const visible = input.type === 'password';
    input.type = visible ? 'text' : 'password';
    passwordToggle.textContent = visible ? 'ซ่อน' : 'แสดง';
    passwordToggle.setAttribute('aria-label', visible ? 'ซ่อนรหัสผ่าน' : 'แสดงรหัสผ่าน');
  }

  const dismissButton = event.target.closest('[data-dismiss]');
  if (dismissButton) dismissButton.closest('.alert')?.remove();

  const menuButton = event.target.closest('[data-menu-toggle]');
  if (menuButton) document.getElementById('sidebar')?.classList.toggle('open');

  const editButton = event.target.closest('[data-edit-room]');
  if (editButton) {
    const modal = document.getElementById('room-modal');
    const room = JSON.parse(editButton.dataset.room);
    const form = modal.querySelector('form');
    form.querySelector('[name="roomId"]').value = room.roomId;
    form.querySelector('[name="roomNumber"]').value = room.roomNumber;
    form.querySelector('[name="roomStatus"]').value = room.roomStatus;
    form.querySelector('[name="rentalPrice"]').value = room.rentalPrice;
    form.dataset.currentTenant = room.activeTenantId || '';
    form.querySelector('[name="tenantId"]').value = room.activeTenantId || '';
    form.querySelector('[name="startDate"]').value = room.activeStartDate || '';
    form.querySelector('[name="deposit"]').value = room.activeDeposit ?? '0';
    updateRoomAssignmentForm(modal);
    modal.showModal();
  }

  const editNewsButton = event.target.closest('[data-edit-news]');
  if (editNewsButton) {
    const modal = document.getElementById('news-modal');
    const news = JSON.parse(editNewsButton.dataset.news);
    const form = modal.querySelector('form');
    form.querySelector('[name="newsId"]').value = news.newsId;
    form.querySelector('[name="title"]').value = news.title;
    form.querySelector('[name="detail"]').value = news.detail;
    modal.querySelector('h2').textContent = 'แก้ไขประกาศ';
    modal.querySelector('[type="submit"]').textContent = 'บันทึกการแก้ไข';
    modal.showModal();
  }

  const editAccountButton = event.target.closest('[data-edit-account]');
  if (editAccountButton) {
    const modal = document.getElementById('user-modal');
    const account = JSON.parse(editAccountButton.dataset.account);
    const form = modal.querySelector('form');
    form.reset();
    resetPasswordVisibility(form);
    for (const [field, value] of Object.entries(account)) {
      const input = form.querySelector(`[name="${field}"]`);
      if (input) input.value = value;
    }
    form.querySelector('[name="password"]').value = '';
    form.querySelector('[name="password"]').required = false;
    form.querySelector('[name="password_confirm"]').value = '';
    form.querySelector('[name="password_confirm"]').required = false;
    modal.querySelector('[data-password-note]').textContent = 'เว้นว่างทั้งสองช่องหากไม่ต้องการเปลี่ยนรหัสผ่าน';
    updateAccountPassword(modal);
    modal.querySelector('h2').textContent = 'แก้ไขบัญชีผู้ใช้งาน';
    modal.querySelector('[type="submit"]').textContent = 'บันทึกการแก้ไข';
    modal.showModal();
  }

  const confirmButton = event.target.closest('[data-confirm]');
  if (confirmButton && !window.confirm(confirmButton.dataset.confirm)) event.preventDefault();
});

const accountForm = document.querySelector('#user-modal form');
if (accountForm) {
  const accountModal = document.getElementById('user-modal');
  accountForm.addEventListener('input', event => {
    if (event.target.matches('[data-password], [data-password-confirm]')) {
      updateAccountPassword(accountModal);
    }
  });
  accountForm.addEventListener('submit', () => updateAccountPassword(accountModal));
}

const roomModal = document.getElementById('room-modal');
if (roomModal) {
  roomModal.querySelector('[data-room-status]').addEventListener('change', () => {
    updateRoomAssignmentForm(roomModal);
  });
  roomModal.querySelector('[data-room-tenant]').addEventListener('change', () => {
    updateRoomAssignmentForm(roomModal);
  });
}

document.querySelectorAll('dialog').forEach(modal => {
  modal.addEventListener('click', event => {
    if (event.target === modal) modal.close();
  });
});

const clock = document.querySelector('[data-digital-clock]');
if (clock) {
  const updateClock = () => {
    clock.textContent = new Intl.DateTimeFormat('th-TH', {
      timeZone: 'Asia/Bangkok',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: false,
    }).format(new Date());
  };
  updateClock();
  window.setInterval(updateClock, 1000);
}

document.querySelectorAll('[data-table-search]').forEach(input => {
  input.addEventListener('input', () => {
    const query = input.value.trim().toLocaleLowerCase();
    input.closest('.table-panel')?.querySelectorAll('[data-search-row]').forEach(row => {
      row.hidden = !row.textContent.toLocaleLowerCase().includes(query);
    });
  });
});

const meterForm = document.querySelector('[data-meter-form]');
if (meterForm) {
  const roomSelect = meterForm.querySelector('[data-meter-room]');
  const previousMeter = document.querySelector('[data-previous-meter]');
  const updatePreviousMeter = () => {
    const option = roomSelect.selectedOptions[0];
    if (!roomSelect.value) {
      previousMeter.textContent = 'เลือกห้องเพื่อดูบันทึกมิเตอร์ครั้งก่อน';
    } else if (!option.dataset.lastMonth) {
      previousMeter.textContent = 'ห้องนี้ยังไม่มีบันทึกมิเตอร์ก่อนหน้า';
    } else {
      previousMeter.textContent = `บันทึกครั้งก่อน เดือน ${option.dataset.lastMonth} (${option.dataset.lastDate}): น้ำ ${option.dataset.lastWater} หน่วย · ไฟฟ้า ${option.dataset.lastElectric} หน่วย`;
    }
  };
  roomSelect.addEventListener('change', updatePreviousMeter);
  updatePreviousMeter();
}

const invoiceForm = document.querySelector('[data-invoice-form]');
if (invoiceForm) {
  const contractInput = invoiceForm.querySelector('[data-invoice-contract]');
  const monthInput = invoiceForm.querySelector('[data-invoice-month]');
  const preview = invoiceForm.querySelector('[data-invoice-preview]');
  const submitButton = invoiceForm.querySelector('[type="submit"]');
  let lookupController;

  const resetPreview = message => {
    preview.dataset.state = 'idle';
    preview.querySelector('[data-invoice-status]').textContent = message;
    preview.querySelector('[data-invoice-rent]').textContent = '—';
    preview.querySelector('[data-invoice-water]').textContent = '—';
    preview.querySelector('[data-invoice-electric]').textContent = '—';
    preview.querySelector('[data-invoice-total]').textContent = '—';
    submitButton.disabled = false;
  };

  const lookupMeter = async () => {
    lookupController?.abort();
    if (!contractInput.value || !monthInput.value.trim()) {
      resetPreview('เลือกห้องและระบุรอบบิลเพื่อดูข้อมูลมิเตอร์');
      return;
    }

    lookupController = new AbortController();
    const controller = lookupController;
    const status = preview.querySelector('[data-invoice-status]');
    preview.dataset.state = 'loading';
    status.textContent = 'กำลังโหลดข้อมูลห้องและมิเตอร์...';
    submitButton.disabled = true;

    const url = new URL(invoiceForm.action, window.location.href);
    url.searchParams.set('page', 'invoice_meter');
    url.searchParams.set('contractId', contractInput.value);
    url.searchParams.set('billMonth', monthInput.value.trim());

    try {
      const response = await fetch(url, {
        headers: { Accept: 'application/json' },
        signal: controller.signal,
      });
      const data = await response.json();
      if (!response.ok) throw new Error(data.error || 'โหลดข้อมูลมิเตอร์ไม่สำเร็จ');
      if (lookupController !== controller) return;

      if (data.invoiceExists) {
        resetPreview(`รอบบิลนี้มีใบแจ้งหนี้ของห้อง ${data.roomNumber} แล้ว`);
        preview.dataset.state = 'unavailable';
        return;
      }
      if (!data.meterAvailable) {
        resetPreview(`ไม่พบข้อมูลมิเตอร์ของห้อง ${data.roomNumber} ในรอบบิลนี้`);
        preview.dataset.state = 'unavailable';
        return;
      }

      const formatMoney = amount => new Intl.NumberFormat('th-TH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(amount) + ' ฿';
      const waterAmount = Number(data.waterUnit) * Number(data.waterRate);
      const electricAmount = Number(data.electricUnit) * Number(data.electricRate);
      const total = Number(data.rentalPrice) + waterAmount + electricAmount;

      preview.querySelector('[data-invoice-rent]').textContent = formatMoney(Number(data.rentalPrice));
      preview.querySelector('[data-invoice-water]').textContent = `${data.waterUnit} หน่วย · ${formatMoney(waterAmount)}`;
      preview.querySelector('[data-invoice-electric]').textContent = `${data.electricUnit} หน่วย · ${formatMoney(electricAmount)}`;
      preview.querySelector('[data-invoice-total]').textContent = formatMoney(total);
      status.textContent = `ข้อมูลของห้อง ${data.roomNumber} · ${data.name}`;
      preview.dataset.state = 'ready';
      submitButton.disabled = false;
    } catch (error) {
      if (error.name === 'AbortError' || lookupController !== controller) return;
      resetPreview(error.message || 'โหลดข้อมูลมิเตอร์ไม่สำเร็จ กรุณาลองใหม่');
      preview.dataset.state = 'unavailable';
    }
  };

  contractInput.addEventListener('change', lookupMeter);
  monthInput.addEventListener('input', lookupMeter);
  invoiceForm.addEventListener('submit', event => {
    if (preview.dataset.state !== 'ready') {
      event.preventDefault();
      preview.querySelector('[data-invoice-status]').textContent = 'เลือกห้องที่มีข้อมูลมิเตอร์ของรอบบิลก่อนสร้างใบแจ้งหนี้';
    }
  });
}
