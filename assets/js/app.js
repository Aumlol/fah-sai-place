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
  };

  document.querySelectorAll('[data-repair-result-form]').forEach((form) => {
    const status = form.querySelector('[data-repair-status]');
    const image = form.querySelector('[data-repair-completion-image]');
    if (!status || !image) return;

    const syncCompletionImageRequirement = () => {
      image.required = status.value === 'ซ่อมเสร็จแล้ว' && form.dataset.hasCompletionImage !== '1';
    };

    status.addEventListener('change', syncCompletionImageRequirement);
    syncCompletionImageRequirement();
  });

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

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeMobileSidebar();
  });

  mobileMedia.addEventListener('change', (event) => {
    if (event.matches) {
      document.body.classList.remove('sidebar-collapsed');
      closeMobileSidebar();
    } else if (localStorage.getItem(storageKey) === '1') {
      document.body.classList.add('sidebar-collapsed');
    }
  });

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
      dialog.showModal();
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

  const meterBatchForm = document.querySelector('[data-meter-batch-form]');
  document.querySelectorAll('[data-repair-cost-form]').forEach((form) => {
    const amounts = Array.from(form.querySelectorAll('[data-repair-cost-amount]'));
    const total = form.querySelector('[data-repair-cost-total]');
    const formatCurrency = new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

    const updateTotal = () => {
      const amountSum = amounts.reduce((sum, input) => {
        const amount = Number(input.value);
        return sum + (Number.isFinite(amount) && amount > 0 ? amount : 0);
      }, 0);
      total.textContent = formatCurrency.format(amountSum);
    };

    amounts.forEach((input) => {
      input.addEventListener('input', updateTotal);
      input.addEventListener('change', updateTotal);
    });
    updateTotal();
  });

  if (meterBatchForm) {
    const meterRows = Array.from(meterBatchForm.querySelectorAll('[data-meter-row]'));
    const meterFilter = meterBatchForm.querySelector('[data-meter-filter]');
    const meterCount = meterBatchForm.querySelector('[data-meter-count]');
    const meterMonthInput = meterBatchForm.querySelector('[data-meter-month]');
    const meterMonthValue = meterBatchForm.querySelector('[data-meter-month-value]');
    const meterSearchButton = meterBatchForm.querySelector('[data-meter-search]');
    const meterReturnScroll = meterBatchForm.querySelector('[data-meter-return-scroll]');
    const loadedMonth = meterMonthInput.value.trim();
    let monthNavigationStarted = false;
    const formatMeterUsage = value => new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(value);

    const updateMeterUsage = (row) => {
      const waterInput = row.querySelector('[name$="[waterReading]"]');
      const electricInput = row.querySelector('[name$="[electricReading]"]');
      const waterUsed = row.querySelector('[data-water-used]');
      const electricUsed = row.querySelector('[data-electric-used]');
      const status = row.querySelector('[data-meter-status]');

      if (row.dataset.persisted === '1') {
        const suffix = row.dataset.savedUsageCalculated === '1' ? '' : ' (ข้อมูลเดิม)';
        waterUsed.textContent = `${formatMeterUsage(Number(row.dataset.savedWaterUsed))}${suffix}`;
        electricUsed.textContent = `${formatMeterUsage(Number(row.dataset.savedElectricUsed))}${suffix}`;
        status.textContent = 'บันทึกแล้ว · แก้ไขจากประวัติ';
        row.dataset.entryState = 'complete';
        row.classList.remove('meter-row-warning');
        return;
      }

      const updateReading = (input, output, previous) => {
        input.setCustomValidity('');
        if (input.value === '') {
          output.textContent = '—';
          return 'empty';
        }
        const current = Number(input.value);
        if (!Number.isFinite(current) || current < previous) {
          input.setCustomValidity('เลขมิเตอร์ต่ำกว่างวดก่อน กรุณาตรวจสอบอีกครั้ง');
          output.textContent = 'ตรวจสอบเลข';
          return 'invalid';
        }
        output.textContent = formatMeterUsage(current - previous);
        return 'valid';
      };

      const waterState = updateReading(waterInput, waterUsed, Number(row.dataset.previousWater));
      const electricState = updateReading(electricInput, electricUsed, Number(row.dataset.previousElectric));
      const hasError = waterState === 'invalid' || electricState === 'invalid';
      const isComplete = waterState === 'valid' && electricState === 'valid' && !hasError;

      row.dataset.entryState = hasError ? 'invalid' : (isComplete ? 'complete' : 'pending');
      row.classList.toggle('meter-row-warning', hasError);
      status.textContent = hasError
        ? 'ตรวจเลขมิเตอร์'
        : (isComplete
          ? (row.dataset.persisted === '1' ? 'แก้ไขแล้ว' : 'กรอกครบแล้ว')
          : (waterState === 'empty' && electricState === 'empty' ? 'ยังไม่อ่าน' : 'กรอกไม่ครบ'));
    };

    const updateMeterList = () => {
      meterRows.forEach(updateMeterUsage);
      const completed = meterRows.filter(row => row.dataset.entryState === 'complete').length;
      const pending = meterRows.length - completed;
      meterCount.textContent = `กรอกครบ ${completed}/${meterRows.length} ห้อง · เหลือ ${pending} ห้อง`;

      meterRows.forEach((row) => {
        const filter = meterFilter.value;
        const shouldHide = filter === 'complete'
          ? row.dataset.entryState !== 'complete'
          : (filter === 'pending' && row.dataset.entryState === 'complete');
        row.hidden = shouldHide && row.dataset.entryState !== 'invalid';
      });
    };

    const handleMeterInput = (row) => {
      updateMeterList();
      if (row.hidden) {
        const nextRow = meterRows.find(candidate => !candidate.hidden && candidate.dataset.entryState !== 'complete');
        const nextInput = nextRow?.querySelector('[name$="[waterReading]"]');
        if (nextInput) nextInput.focus();
        else meterBatchForm.querySelector('[type="submit"]')?.focus();
      }
    };

    meterRows.forEach((row) => {
      row.querySelectorAll('.meter-reading-input').forEach((input) => {
        input.addEventListener('input', () => handleMeterInput(row));
        input.addEventListener('change', () => handleMeterInput(row));
        input.addEventListener('keydown', (event) => {
          if (event.key !== 'Enter') return;
          event.preventDefault();
          const inputs = meterRows
            .filter(candidate => !candidate.hidden)
            .flatMap(candidate => Array.from(candidate.querySelectorAll('.meter-reading-input')));
          const nextInput = inputs[inputs.indexOf(input) + 1];
          nextInput?.focus();
        });
      });
    });

    meterFilter.addEventListener('change', updateMeterList);
    updateMeterList();

    const switchMeterMonth = () => {
      if (monthNavigationStarted) return true;
      const month = meterMonthInput.value.trim();
      meterMonthInput.setCustomValidity('');
      if (month === loadedMonth) return false;
      if (!/^(0?[1-9]|1[0-2])\/\d{4}$/.test(month)) {
        meterMonthInput.setCustomValidity('กรุณาระบุรอบบิลรูปแบบ MM/YYYY');
        meterMonthInput.reportValidity();
        return true;
      }
      if (meterMonthValue) meterMonthValue.value = month;

      const hasUnsavedReadings = meterRows.some((row) => (
        row.dataset.persisted !== '1'
        && Array.from(row.querySelectorAll('.meter-reading-input')).some(input => input.value.trim() !== '')
      ));
      if (hasUnsavedReadings && !window.confirm('เปลี่ยนรอบบิลแล้วข้อมูลที่กรอกแต่ยังไม่บันทึกจะหายไป ต้องการเปลี่ยนรอบหรือไม่?')) {
        meterMonthInput.value = loadedMonth;
        return true;
      }

      const url = new URL(window.location.href);
      url.searchParams.set('page', 'meters');
      url.searchParams.set('month', month);
      monthNavigationStarted = true;
      window.location.assign(url.toString());
      return true;
    };

    meterSearchButton?.addEventListener('click', switchMeterMonth);
    meterMonthInput?.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        event.preventDefault();
        switchMeterMonth();
      }
    });

    meterBatchForm.addEventListener('submit', (event) => {
      if (meterReturnScroll) meterReturnScroll.value = String(window.scrollY || window.pageYOffset || 0);
      if (switchMeterMonth()) {
        event.preventDefault();
        return;
      }
      updateMeterList();
      const hasNewReadings = meterRows.some((row) => (
        row.dataset.persisted !== '1'
        && Array.from(row.querySelectorAll('.meter-reading-input')).some(input => input.value.trim() !== '')
      ));
      if (!hasNewReadings) {
        event.preventDefault();
        meterCount.textContent = 'ไม่มีข้อมูลใหม่ให้บันทึก หากแก้ไขรายการเดิมให้ใช้ปุ่มแก้ไขด้านล่าง';
        return;
      }
      const invalidInput = meterBatchForm.querySelector(':invalid');
      if (invalidInput) {
        event.preventDefault();
        invalidInput.reportValidity();
      }
    });
  }

  const meterHistoryForm = document.querySelector('[data-meter-history-form]');
  if (meterHistoryForm) {
    const meterHistoryTable = document.querySelector('[data-meter-history-table]');
    const selectAll = meterHistoryTable?.querySelector('[data-meter-select-all]');
    const selectAllCell = selectAll?.closest('th');
    const selections = Array.from(meterHistoryTable?.querySelectorAll('[data-meter-select]') || []);
    const deleteSelected = document.querySelector('[data-meter-delete-selected]');
    const selectedCount = document.querySelector('[data-meter-selected-count]');

    const updateSelection = () => {
      const selected = selections.filter(input => input.checked);
      selections.forEach(input => {
        input.closest('tr')?.classList.toggle('is-selected', input.checked);
      });
      if (selectedCount) selectedCount.textContent = `เลือก ${selected.length} รายการ`;
      if (deleteSelected) deleteSelected.disabled = selected.length === 0;
      if (selectAll) selectAll.checked = selections.length > 0 && selected.length === selections.length;
    };

    selectAll?.addEventListener('change', () => {
      selections.forEach(input => { input.checked = selectAll.checked; });
      updateSelection();
    });
    selectAllCell?.addEventListener('click', event => {
      if (event.target instanceof Element && event.target.closest('input')) return;
      if (!selectAll) return;
      selectAll.checked = !selectAll.checked;
      selections.forEach(input => { input.checked = selectAll.checked; });
      updateSelection();
    });
    selections.forEach(input => {
      input.addEventListener('change', updateSelection);
      const row = input.closest('tr');
      if (!row) return;
      row.classList.add('is-clickable');
      row.addEventListener('click', event => {
        if (!(event.target instanceof Element) || event.target.closest('button,a,input,select,textarea,label,form')) return;
        input.checked = !input.checked;
        updateSelection();
      });
    });
    updateSelection();
  }

  document.querySelector('[data-meter-history-month]')?.addEventListener('change', (event) => {
    const url = new URL(window.location.href);
    if (event.target.value) url.searchParams.set('historyMonth', event.target.value);
    else url.searchParams.delete('historyMonth');
    url.searchParams.set('page', 'meters');
    window.location.assign(url.toString());
  });

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

const invoiceForm = document.querySelector('[data-invoice-form]');
if (invoiceForm) {
  const monthInput = invoiceForm.querySelector('[data-invoice-month]');
  const monthValue = invoiceForm.querySelector('[data-invoice-month-value]');
  const dueDateInput = invoiceForm.querySelector('[data-invoice-due-date]');
  const monthSearch = invoiceForm.querySelector('[data-invoice-search]');
  const preview = invoiceForm.querySelector('[data-invoice-preview]');
  const submitButton = invoiceForm.querySelector('[data-invoice-submit]');
  const meterTable = invoiceForm.querySelector('[data-invoice-meter-table]');
  let lookupController;

  const resetPreview = message => {
    preview.dataset.state = 'idle';
    preview.querySelector('[data-invoice-status]').textContent = message;
    if (meterTable) meterTable.innerHTML = '';
    if (submitButton) submitButton.disabled = true;
  };

  const updateDueDate = () => {
    const match = /^(\d{1,2})\/(\d{4})$/.exec(monthInput.value);
    if (!dueDateInput) return;
    if (!match) {
      dueDateInput.value = '';
      return;
    }

    const month = Number(match[1]);
    let year = Number(match[2]) - 543;
    const nextMonth = month === 12 ? 1 : month + 1;
    if (month === 12) year += 1;
    dueDateInput.value = `${year}-${String(nextMonth).padStart(2, '0')}-01`;
  };

  const lookupMeter = async () => {
    lookupController?.abort();
    const selectedMonth = monthValue?.value || monthInput.value;
    if (!selectedMonth.trim()) {
      resetPreview('เลือกเดือนแล้วกดค้นหาเพื่อดูข้อมูลมิเตอร์ทุกห้อง');
      return;
    }

    lookupController = new AbortController();
    const controller = lookupController;
    const status = preview.querySelector('[data-invoice-status]');
    preview.dataset.state = 'loading';
    status.textContent = 'กำลังโหลดข้อมูลห้องและมิเตอร์...';
    if (submitButton) submitButton.disabled = true;

    const formAction = invoiceForm.getAttribute('action') || 'index.php';
    const url = new URL(formAction, window.location.href);
    url.searchParams.set('page', 'invoice_meter_all');
    url.searchParams.set('billMonth', selectedMonth.trim());

    try {
      const response = await fetch(url, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
        signal: controller.signal,
      });
      const contentType = response.headers.get('content-type') || '';
      const responseText = await response.text();
      let data = {};
      if (contentType.includes('application/json')) {
        try {
          data = JSON.parse(responseText);
        } catch (_error) {
          throw new Error('เซิร์ฟเวอร์ส่งข้อมูลไม่ถูกต้อง กรุณาตรวจสอบการเชื่อมต่อ');
        }
      } else if (!response.ok) {
        throw new Error('ไม่สามารถโหลดข้อมูลมิเตอร์ได้ กรุณาเปิดใหม่และลองอีกครั้ง');
      } else {
        throw new Error('เซิร์ฟเวอร์ส่งข้อมูลกลับไม่ใช่ JSON');
      }
      if (!response.ok) throw new Error(data.error || 'โหลดข้อมูลมิเตอร์ไม่สำเร็จ');
      if (lookupController !== controller) return;

      const formatMoney = amount => new Intl.NumberFormat('th-TH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(amount) + ' ฿';
      const rooms = data.rooms || [];
      const readyRooms = rooms.filter(room => room.contractId && room.meterAvailable && !room.invoiceExists);
      meterTable.innerHTML = `
        <table class="invoice-meter-list">
          <thead><tr><th>ห้อง</th><th>ผู้เช่า</th><th>ค่าเช่า</th><th>น้ำ</th><th>ไฟ</th><th>ยอดรวม</th><th>สถานะ</th></tr></thead>
          <tbody>${rooms.map(room => {
            const waterAmount = Number(room.waterUnit || 0) * Number(room.waterRate);
            const electricAmount = Number(room.electricUnit || 0) * Number(room.electricRate);
            const total = Number(room.rentalPrice) + waterAmount + electricAmount;
            let state = 'พร้อมสร้าง';
            if (room.invoiceExists) state = 'มีใบแจ้งหนี้แล้ว';
            else if (!room.contractId) state = 'ไม่มีสัญญาเช่าที่ใช้งานอยู่';
            else if (!room.meterAvailable) state = 'ยังไม่มีมิเตอร์';
            return `<tr><td><strong>${room.roomNumber}</strong></td><td>${room.name || '—'}</td><td>${formatMoney(Number(room.rentalPrice))}</td><td>${room.meterAvailable ? `${room.waterUnit} หน่วย` : '—'}</td><td>${room.meterAvailable ? `${room.electricUnit} หน่วย` : '—'}</td><td><strong>${room.meterAvailable ? formatMoney(total) : '—'}</strong></td><td>${state}</td></tr>`;
          }).join('')}</tbody>
        </table>`;
      status.textContent = `พบ ${rooms.length} ห้อง · พร้อมสร้าง ${readyRooms.length} ห้อง`;
      preview.dataset.state = readyRooms.length ? 'ready' : 'unavailable';
      submitButton.disabled = readyRooms.length === 0;
    } catch (error) {
      if (error.name === 'AbortError' || lookupController !== controller) return;
      resetPreview(error.message || 'โหลดข้อมูลมิเตอร์ไม่สำเร็จ กรุณาลองใหม่');
      preview.dataset.state = 'unavailable';
    }
  };

  monthInput.addEventListener('change', () => {
    if (monthValue) monthValue.value = monthInput.value;
    updateDueDate();
    lookupMeter();
  });
  monthSearch.addEventListener('click', () => {
    if (monthValue) monthValue.value = monthInput.value;
    updateDueDate();
    lookupMeter();
  });
  invoiceForm.addEventListener('submit', event => {
    if (preview.dataset.state !== 'ready') {
      event.preventDefault();
      preview.querySelector('[data-invoice-status]').textContent = 'เลือกห้องที่มีข้อมูลมิเตอร์ของรอบบิลก่อนสร้างใบแจ้งหนี้';
    }
  });
  if (monthInput.value) {
    updateDueDate();
    lookupMeter();
  }
}
