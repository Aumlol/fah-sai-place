<<<<<<< HEAD
document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.querySelector('#sidebar');
  const backdrop = document.querySelector('[data-sidebar-backdrop]');
  const mobileMedia = window.matchMedia('(max-width: 780px)');
  const storageKey = 'fahsai_sidebar_collapsed';

  if (sidebar && !mobileMedia.matches && localStorage.getItem(storageKey) === '1') {
    document.body.classList.add('sidebar-collapsed');
  }

  const closeMobileSidebar = () => {
    sidebar?.classList.remove('open');
    backdrop?.classList.remove('open');
=======
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
>>>>>>> 449e13e86e3adc18aa01ae1b7b633c4fa3d8356a
  };

  const toggleSidebar = () => {
    if (!sidebar) return;
    if (mobileMedia.matches) {
      const willOpen = !sidebar.classList.contains('open');
      sidebar.classList.toggle('open', willOpen);
      backdrop?.classList.toggle('open', willOpen);
    } else {
      const collapsed = document.body.classList.toggle('sidebar-collapsed');
      localStorage.setItem(storageKey, collapsed ? '1' : '0');
    }
  };

  document.querySelectorAll('[data-menu-toggle], [data-sidebar-collapse]').forEach((button) => {
    button.addEventListener('click', toggleSidebar);
  });

  backdrop?.addEventListener('click', closeMobileSidebar);

<<<<<<< HEAD
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeMobileSidebar();
=======
function addMobileTableLabels() {
  document.querySelectorAll('.table-scroll table').forEach(table => {
    const labels = Array.from(table.querySelectorAll('thead th'), th => th.textContent.trim());
    table.querySelectorAll('tbody tr').forEach(row => {
      Array.from(row.children).forEach((cell, index) => {
        if (!cell.dataset.label) {
          cell.dataset.label = labels[index] || '';
        }
      });
    });
  });
}

addMobileTableLabels();

function resetPasswordVisibility(form) {
  form.querySelectorAll('.password-control').forEach(control => {
    control.querySelector('input').type = 'password';
    const toggle = control.querySelector('[data-toggle-password]');
    toggle.textContent = 'แสดง';
    toggle.setAttribute('aria-label', toggle.dataset.togglePassword === 'password'
      ? 'แสดงรหัสผ่าน'
      : 'แสดงรหัสผ่านที่ยืนยัน');
>>>>>>> 449e13e86e3adc18aa01ae1b7b633c4fa3d8356a
  });

<<<<<<< HEAD
  mobileMedia.addEventListener('change', (event) => {
    if (event.matches) {
      document.body.classList.remove('sidebar-collapsed');
      closeMobileSidebar();
    } else if (localStorage.getItem(storageKey) === '1') {
      document.body.classList.add('sidebar-collapsed');
    }
  });
=======
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
>>>>>>> 449e13e86e3adc18aa01ae1b7b633c4fa3d8356a

  document.querySelectorAll('.sidebar .nav-link, .sidebar .help-note').forEach((link) => {
    link.addEventListener('click', () => {
      if (mobileMedia.matches) closeMobileSidebar();
    });
  });

  const applyMobileTableLabels = (root = document) => {
    root.querySelectorAll('.table-scroll table').forEach((table) => {
      const headers = Array.from(table.querySelectorAll('thead th')).map((th) => th.textContent.trim());
      if (!headers.length) return;
      table.querySelectorAll('tbody tr').forEach((row) => {
        let columnIndex = 0;
        Array.from(row.children).forEach((cell) => {
          if (cell.tagName !== 'TD') return;
          if (!cell.hasAttribute('data-label')) {
            cell.setAttribute('data-label', headers[columnIndex] || '');
          }
          columnIndex += Number(cell.getAttribute('colspan') || 1);
        });
      });
    });
  };
  applyMobileTableLabels();

  document.querySelectorAll('[data-dismiss]').forEach((button) => {
    button.addEventListener('click', () => button.closest('[data-alert]')?.remove());
  });

  document.querySelectorAll('[data-modal-open]').forEach((button) => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById(button.dataset.modalOpen);
      const form = dialog?.querySelector('form');
      if (dialog?.id === 'user-modal') {
        setAccountModalMode(dialog, false);
        form?.reset();
        resetPasswordMeter(dialog);
      } else {
        form?.reset();
        form?.querySelectorAll('input[type="hidden"][name$="Id"]').forEach((field) => {
          field.value = '';
        });
        if (dialog?.id === 'room-modal') syncRoomAssignmentFields(dialog);
      }
      dialog?.showModal();
    });
  });

  document.querySelectorAll('[data-modal-close]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
  });

  document.querySelectorAll('dialog.modal').forEach((dialog) => {
    dialog.addEventListener('click', (event) => {
      if (event.target === dialog) dialog.close();
    });
  });

  const syncRoomAssignmentFields = (dialog, room = null) => {
    const statusField = dialog.querySelector('[data-room-status]');
    const assignmentFields = dialog.querySelector('[data-room-assignment-fields]');
    const tenantField = dialog.querySelector('[data-room-tenant]');
    const startField = dialog.querySelector('[data-room-start]');
    const depositField = dialog.querySelector('[data-room-deposit]');
    const note = dialog.querySelector('[data-room-assignment-note]');
    if (!statusField || !assignmentFields || !tenantField || !startField || !depositField) return;

    const currentRoomId = room?.roomId || dialog.querySelector('[name="roomId"]')?.value || '';
    Array.from(tenantField.options).forEach((option) => {
      if (!option.value) return;
      const activeRoomId = option.dataset.activeRoom || '';
      option.hidden = Boolean(activeRoomId && activeRoomId !== currentRoomId);
    });

    if (room) {
      tenantField.value = room.activeTenantId || '';
      startField.value = room.activeStartDate || new Date().toISOString().slice(0, 10);
      depositField.value = room.activeDeposit ?? '0';
    } else if (!startField.value) {
      startField.value = new Date().toISOString().slice(0, 10);
    }

    const occupied = statusField.value === 'มีผู้เช่า';
    assignmentFields.hidden = !occupied;
    tenantField.required = occupied;
    startField.required = occupied;
    if (note) {
      note.textContent = room?.activeTenantId
        ? 'สามารถเปลี่ยนผู้เช่าหรือแก้ไขวันที่เริ่มสัญญาและเงินประกันของห้องนี้ได้'
        : 'เลือกผู้เช่าที่ยังไม่มีสัญญาใช้งานอยู่ และระบุวันที่เริ่มสัญญา';
    }
  };

  const roomDialog = document.getElementById('room-modal');
  roomDialog?.querySelector('[data-room-status]')?.addEventListener('change', () => {
    syncRoomAssignmentFields(roomDialog);
  });

  document.querySelectorAll('[data-edit-room]').forEach((button) => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById('room-modal');
      const room = JSON.parse(button.dataset.room);
      dialog.querySelector('[name="roomId"]').value = room.roomId;
      dialog.querySelector('[name="roomNumber"]').value = room.roomNumber;
      dialog.querySelector('[name="rentalPrice"]').value = room.rentalPrice;
      dialog.querySelector('[name="roomStatus"]').value = room.roomStatus;
      syncRoomAssignmentFields(dialog, room);
      dialog.showModal();
    });
  });

  document.querySelectorAll('[data-edit-news]').forEach((button) => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById('news-modal');
      const news = JSON.parse(button.dataset.news);
      dialog.querySelector('[name="newsId"]').value = news.newsId;
      dialog.querySelector('[name="title"]').value = news.title;
      dialog.querySelector('[name="detail"]').value = news.detail;
      dialog.showModal();
    });
  });

  const setAccountModalMode = (dialog, editing) => {
    if (!dialog) return;
    const heading = dialog.querySelector('.modal-heading h2');
    const subtitle = dialog.querySelector('.modal-heading .muted');
    const submit = dialog.querySelector('button[type="submit"]');
    const password = dialog.querySelector('[data-password]');
    const confirm = dialog.querySelector('[data-password-confirm]');
    const note = dialog.querySelector('[data-password-note]');
    if (heading) heading.textContent = editing ? 'แก้ไขบัญชีผู้ใช้งาน' : 'เพิ่มบัญชีผู้ใช้งาน';
    if (subtitle) subtitle.textContent = editing ? 'แก้ไขข้อมูลส่วนตัว บทบาท หรือเปลี่ยนรหัสผ่านใหม่' : 'กรอกข้อมูลให้ครบ แล้วกำหนดรหัสผ่านที่คาดเดายาก';
    if (submit) submit.textContent = editing ? 'บันทึกการแก้ไข' : 'สร้างบัญชี';
    if (password) password.required = !editing;
    if (confirm) confirm.required = !editing;
    if (note) note.textContent = editing ? 'เว้นว่างช่องรหัสผ่านหากไม่ต้องการเปลี่ยนรหัสผ่านเดิม' : 'รหัสผ่านใหม่ต้องผ่านเงื่อนไขครบทุกข้อ';
    dialog.querySelectorAll('.password-section .required-mark').forEach((mark) => {
      mark.hidden = editing;
    });
  };

  document.querySelectorAll('[data-edit-account]').forEach((button) => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById('user-modal');
      const account = JSON.parse(button.dataset.account);
      setAccountModalMode(dialog, true);
      dialog.querySelector('[name="userId"]').value = account.userId;
      dialog.querySelector('[name="name"]').value = account.name;
      dialog.querySelector('[name="username"]').value = account.username;
      dialog.querySelector('[name="phone"]').value = account.phone;
      dialog.querySelector('[name="email"]').value = account.email;
      dialog.querySelector('[name="role"]').value = account.role;
      const password = dialog.querySelector('[data-password]');
      const confirm = dialog.querySelector('[data-password-confirm]');
      if (password) password.value = '';
      if (confirm) confirm.value = '';
      resetPasswordMeter(dialog);
      dialog.showModal();
    });
  });

  document.querySelectorAll('[data-image-preview]').forEach((button) => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById('image-preview-modal');
      if (!dialog) return;
      dialog.querySelector('h2').textContent = button.dataset.previewTitle || 'หลักฐานการชำระเงิน';
      dialog.querySelector('img').src = button.dataset.imagePreview;
      dialog.showModal();
    });
  });

  document.querySelectorAll('[data-news-preview]').forEach((button) => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById('news-preview-modal');
      if (!dialog) return;
      const news = JSON.parse(button.dataset.newsPreview);
      dialog.querySelector('[data-news-title]').textContent = news.title || '';
      dialog.querySelector('[data-news-detail]').textContent = news.detail || '';
      const image = dialog.querySelector('[data-news-image]');
      if (news.image) {
        image.src = `file.php?news=${encodeURIComponent(news.newsId)}`;
        image.hidden = false;
      } else {
        image.removeAttribute('src');
        image.hidden = true;
      }
<<<<<<< HEAD
      dialog.showModal();
=======
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
>>>>>>> 449e13e86e3adc18aa01ae1b7b633c4fa3d8356a
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
    setInterval(updateClock, 1000);
  }

  const search = document.querySelector('[data-table-search]');
  if (search) {
    search.addEventListener('input', () => {
      const term = search.value.trim().toLowerCase();
      document.querySelectorAll('[data-search-row]').forEach((row) => {
        row.hidden = term !== '' && !row.textContent.toLowerCase().includes(term);
      });
    });
  }

  document.querySelectorAll('[data-confirm]').forEach((button) => {
    button.addEventListener('click', (event) => {
      if (!window.confirm(button.dataset.confirm)) event.preventDefault();
    });
  });

  const passwordDialog = document.getElementById('user-modal');
  const passwordInput = passwordDialog?.querySelector('[data-password]');
  const confirmInput = passwordDialog?.querySelector('[data-password-confirm]');
  const strengthBar = passwordDialog?.querySelector('[data-password-strength]');
  const strengthLabel = passwordDialog?.querySelector('[data-password-strength-label]');
  const matchLabel = passwordDialog?.querySelector('[data-password-match]');

  function resetPasswordMeter(dialog) {
    if (!dialog) return;
    dialog.querySelectorAll('[data-password-rule]').forEach((item) => item.classList.remove('met'));
    const bar = dialog.querySelector('[data-password-strength]');
    const label = dialog.querySelector('[data-password-strength-label]');
    const match = dialog.querySelector('[data-password-match]');
    if (bar) {
      bar.dataset.strength = 'none';
      bar.setAttribute('aria-valuenow', '0');
      const fill = bar.querySelector('span');
      if (fill) fill.style.width = '0%';
    }
    if (label) label.textContent = 'ยังไม่ได้ตั้ง';
    if (match) {
      match.textContent = 'กรอกรหัสผ่านอีกครั้งให้ตรงกัน';
      delete match.dataset.state;
    }
    dialog.querySelectorAll('[data-toggle-password]').forEach((toggle) => {
      const target = dialog.querySelector(`[name="${toggle.dataset.togglePassword}"]`);
      if (target) target.type = 'password';
      toggle.textContent = 'แสดง';
      toggle.setAttribute('aria-pressed', 'false');
    });
    passwordInput?.setCustomValidity('');
    confirmInput?.setCustomValidity('');
  }

  const updatePasswordState = () => {
    if (!passwordDialog || !passwordInput || !confirmInput) return;
    const value = passwordInput.value;
    const editing = Boolean(passwordDialog.querySelector('[name="userId"]')?.value);
    const rules = {
      length: value.length >= 12,
      lowercase: /[a-z]/.test(value),
      uppercase: /[A-Z]/.test(value),
      number: /[0-9]/.test(value),
      symbol: /[^A-Za-z0-9\s]/.test(value),
    };
    let passed = 0;
    Object.entries(rules).forEach(([rule, met]) => {
      if (met) passed += 1;
      passwordDialog.querySelector(`[data-password-rule="${rule}"]`)?.classList.toggle('met', met);
    });

    if (strengthBar && strengthLabel) {
      const percent = value === '' ? 0 : Math.round((passed / 5) * 100);
      const level = passed <= 2 ? 'weak' : passed <= 4 ? 'medium' : 'strong';
      strengthBar.dataset.strength = value === '' ? 'none' : level;
      strengthBar.setAttribute('aria-valuenow', String(passed));
      const fill = strengthBar.querySelector('span');
      if (fill) fill.style.width = `${percent}%`;
      strengthLabel.textContent = value === '' ? 'ยังไม่ได้ตั้ง' : passed < 5 ? `ผ่าน ${passed}/5 ข้อ` : 'ปลอดภัยสูง';
    }

    passwordInput.setCustomValidity(value !== '' && passed < 5 ? 'กรุณาตั้งรหัสผ่านให้ครบตามเงื่อนไขความปลอดภัย' : '');

    if (matchLabel) {
      if (editing && value === '' && confirmInput.value === '') {
        matchLabel.textContent = 'ไม่เปลี่ยนรหัสผ่านเดิม';
        delete matchLabel.dataset.state;
        confirmInput.setCustomValidity('');
      } else if (confirmInput.value === '') {
        matchLabel.textContent = 'กรอกรหัสผ่านอีกครั้งให้ตรงกัน';
        delete matchLabel.dataset.state;
        confirmInput.setCustomValidity('');
      } else if (value === confirmInput.value) {
        matchLabel.textContent = 'รหัสผ่านตรงกัน';
        matchLabel.dataset.state = 'valid';
        confirmInput.setCustomValidity('');
      } else {
        matchLabel.textContent = 'รหัสผ่านยังไม่ตรงกัน';
        matchLabel.dataset.state = 'invalid';
        confirmInput.setCustomValidity('รหัสผ่านยืนยันไม่ตรงกัน');
      }
    }
  };

  passwordInput?.addEventListener('input', updatePasswordState);
  confirmInput?.addEventListener('input', updatePasswordState);

  passwordDialog?.querySelectorAll('[data-toggle-password]').forEach((button) => {
    button.addEventListener('click', () => {
      const target = passwordDialog.querySelector(`[name="${button.dataset.togglePassword}"]`);
      if (!target) return;
      const show = target.type === 'password';
      target.type = show ? 'text' : 'password';
      button.textContent = show ? 'ซ่อน' : 'แสดง';
      button.setAttribute('aria-pressed', String(show));
    });
  });

  const invoiceForm = document.querySelector('[data-invoice-form]');
  if (invoiceForm) {
    const contractSelect = invoiceForm.querySelector('[data-invoice-contract]');
    const monthInput = invoiceForm.querySelector('[data-invoice-month]');
    const rentInput = invoiceForm.querySelector('[data-invoice-rent]');
    const waterInput = invoiceForm.querySelector('[data-invoice-water]');
    const electricInput = invoiceForm.querySelector('[data-invoice-electric]');
    const statusText = invoiceForm.querySelector('[data-meter-status]');
    const waterText = invoiceForm.querySelector('[data-meter-water]');
    const electricText = invoiceForm.querySelector('[data-meter-electric]');
    const submitButton = invoiceForm.querySelector('button[type="submit"]');
    let requestToken = 0;

    const resetMeterPreview = (message) => {
      if (statusText) statusText.textContent = message;
      if (waterText) waterText.textContent = '—';
      if (electricText) electricText.textContent = '—';
      if (waterInput) waterInput.value = '';
      if (electricInput) electricInput.value = '';
    };

    const syncSelectedRent = () => {
      const option = contractSelect?.selectedOptions?.[0];
      if (rentInput && option?.dataset.rent) {
        rentInput.value = option.dataset.rent;
      }
    };

    const lookupMeterReading = async () => {
      const contractId = contractSelect?.value || '';
      const month = monthInput?.value.trim() || '';
      if (!contractId || !month) {
        resetMeterPreview('เลือกผู้เช่าและระบุรอบเดือนเพื่อดึงข้อมูลมิเตอร์');
        return;
      }

      const token = ++requestToken;
      if (statusText) statusText.textContent = 'กำลังตรวจสอบบันทึกมิเตอร์...';
      if (submitButton) submitButton.disabled = true;

      try {
        const query = new URLSearchParams({ action: 'meter_lookup', contractId, month });
        const response = await fetch(`index.php?${query.toString()}`, {
          headers: { Accept: 'application/json' },
        });
        const data = await response.json();
        if (token !== requestToken) return;

        if (response.ok && data.found) {
          if (rentInput && data.rentalPrice !== undefined) rentInput.value = data.rentalPrice;
          if (waterInput) waterInput.value = data.waterUnit;
          if (electricInput) electricInput.value = data.electricUnit;
          if (waterText) waterText.textContent = `${data.waterFormatted} หน่วย`;
          if (electricText) electricText.textContent = `${data.electricFormatted} หน่วย`;
          if (statusText) {
            statusText.textContent = `พบข้อมูลมิเตอร์ห้อง ${data.roomNumber} รอบเดือน ${data.month} (บันทึกเมื่อ ${data.recordDate})`;
          }
        } else {
          resetMeterPreview(data.message || 'ไม่พบข้อมูลมิเตอร์ของห้องและเดือนที่เลือก');
        }
      } catch (error) {
        if (token === requestToken) {
          resetMeterPreview('ไม่สามารถดึงข้อมูลมิเตอร์ได้ในขณะนี้');
        }
      } finally {
        if (token === requestToken && submitButton) submitButton.disabled = false;
      }
    };

    contractSelect?.addEventListener('change', () => {
      syncSelectedRent();
      lookupMeterReading();
    });
    monthInput?.addEventListener('input', lookupMeterReading);
    monthInput?.addEventListener('change', lookupMeterReading);
  }

  const meterForm = document.querySelector('[data-meter-form]');
  if (meterForm) {
    const roomSelect = meterForm.querySelector('[data-meter-room]');
    const waterInput = meterForm.querySelector('[name="waterUnit"]');
    const electricInput = meterForm.querySelector('[name="electricUnit"]');
    const previousMeter = meterForm.parentElement?.querySelector('[data-previous-meter]');

    const updatePreviousMeter = () => {
      const option = roomSelect?.selectedOptions?.[0];
      if (!option || !option.value) {
        if (previousMeter) previousMeter.textContent = 'เลือกห้องเพื่อดูบันทึกมิเตอร์ครั้งก่อน';
        if (waterInput) waterInput.min = '0';
        if (electricInput) electricInput.min = '0';
        return;
      }
      const lastMonth = option.dataset.lastMonth || '';
      const lastWater = option.dataset.lastWater || '';
      const lastElectric = option.dataset.lastElectric || '';
      const lastDate = option.dataset.lastDate || '';
      if (!lastMonth) {
        if (previousMeter) previousMeter.textContent = `ห้อง ${option.textContent.trim()} ยังไม่มีบันทึกมิเตอร์ก่อนหน้า`;
        if (waterInput) waterInput.min = '0';
        if (electricInput) electricInput.min = '0';
        return;
      }
      if (waterInput) waterInput.min = lastWater || '0';
      if (electricInput) electricInput.min = lastElectric || '0';
      if (previousMeter) {
        previousMeter.textContent = `มิเตอร์ล่าสุดห้อง ${option.textContent.trim()} (${lastMonth}, บันทึก ${lastDate}): น้ำ ${lastWater} หน่วย · ไฟ ${lastElectric} หน่วย`;
      }
    };

    roomSelect?.addEventListener('change', updatePreviousMeter);
    updatePreviousMeter();
  }

  // แสดงวันที่ พ.ศ. (วว/ดด/พ.ศ.) ทับในช่อง input[type="date"] โดยตรง เพื่อไม่ให้เสีย Layout และไม่โชว์ ค.ศ.
  const formatThaiBE = (isoDate) => {
    if (!isoDate || !/^\d{4}-\d{2}-\d{2}$/.test(isoDate)) return '';
    const [y, m, d] = isoDate.split('-');
    return `${d}/${m}/${Number(y) + 543}`;
  };

  // ซ่อนตัวหนังสือ ค.ศ. ภายใน ::-webkit-datetime-edit ของ Chrome/Edge ไม่ให้ซ้อนกับข้อความ พ.ศ.
  if (!document.getElementById('be-date-overlay-style')) {
    const style = document.createElement('style');
    style.id = 'be-date-overlay-style';
    style.textContent = `
      input[type="date"].be-date-masked::-webkit-datetime-edit,
      input[type="date"].be-date-masked::-webkit-datetime-edit-fields-wrapper,
      input[type="date"].be-date-masked::-webkit-datetime-edit-text,
      input[type="date"].be-date-masked::-webkit-datetime-edit-month-field,
      input[type="date"].be-date-masked::-webkit-datetime-edit-day-field,
      input[type="date"].be-date-masked::-webkit-datetime-edit-year-field {
        color: transparent !important;
      }
      input[type="date"].be-date-masked::-webkit-calendar-picker-indicator {
        opacity: 1 !important;
        cursor: pointer;
      }
    `;
    document.head.appendChild(style);
  }

  document.querySelectorAll('input[type="date"]').forEach((input) => {
    input.classList.add('be-date-masked');
    const wrapper = document.createElement('div');
    wrapper.style.cssText = 'position:relative; display:block; width:100%;';
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    // ซ่อนตัวอักษร ค.ศ. เดิมใน input แต่ยังคงปุ่มปฏิทินด้านขวาไว้กดได้ปกติ
    input.style.color = 'transparent';
    input.style.caretColor = 'transparent';
    input.style.userSelect = 'none';

    const overlay = document.createElement('span');
    overlay.style.cssText = [
      'position:absolute',
      'left:14px',
      'top:50%',
      'transform:translateY(-50%)',
      'pointer-events:none',
      'font:inherit',
      'color:var(--text, #0f172a)',
      'white-space:nowrap',
      'letter-spacing:0.01em'
    ].join(';');

    const syncOverlay = () => {
      const beText = formatThaiBE(input.value);
      if (beText) {
        overlay.textContent = beText;
        overlay.style.opacity = '1';
      } else {
        overlay.textContent = 'วว/ดด/พ.ศ.';
        overlay.style.opacity = '0.45';
      }
    };

    wrapper.appendChild(overlay);
    syncOverlay();

    input.addEventListener('change', syncOverlay);
    input.addEventListener('input', syncOverlay);

    // รองรับกรณี JS อื่น (เช่น ปุ่มแก้ไขห้องพัก) เปลี่ยนค่า input.value ผ่านโค้ด
    const observer = new MutationObserver(syncOverlay);
    observer.observe(input, { attributes: true, attributeFilter: ['value'] });
    const descriptor = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
    if (descriptor && descriptor.set) {
      Object.defineProperty(input, 'value', {
        get() { return descriptor.get.call(this); },
        set(val) {
          descriptor.set.call(this, val);
          syncOverlay();
        }
      });
    }
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
    submitButton.disabled = true;
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
