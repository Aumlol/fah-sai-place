document.addEventListener('click', event => {
  const openButton = event.target.closest('[data-modal-open]');
  if (openButton) {
    const modal = document.getElementById(openButton.dataset.modalOpen);
    if (modal && openButton.dataset.modalOpen === 'room-modal') {
      const form = modal.querySelector('form');
      form.reset();
      form.querySelector('[name="roomId"]').value = '';
    }
    if (modal && openButton.dataset.modalOpen === 'user-modal' && !openButton.dataset.editAccount) {
      const form = modal.querySelector('form');
      form.reset();
      form.querySelector('[name="userId"]').value = '';
      modal.querySelector('h2').textContent = 'เพิ่มบัญชีผู้ใช้งาน';
      modal.querySelector('[type="submit"]').textContent = 'สร้างบัญชี';
      form.querySelector('[name="password"]').required = true;
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

  const dismissButton = event.target.closest('[data-dismiss]');
  if (dismissButton) dismissButton.closest('.alert')?.remove();

  const menuButton = event.target.closest('[data-menu-toggle]');
  if (menuButton) document.getElementById('sidebar')?.classList.toggle('open');

  const editButton = event.target.closest('[data-edit-room]');
  if (editButton) {
    const modal = document.getElementById('room-modal');
    const room = JSON.parse(editButton.dataset.room);
    modal.querySelector('[name="roomId"]').value = room.roomId;
    modal.querySelector('[name="roomNumber"]').value = room.roomNumber;
    modal.querySelector('[name="roomStatus"]').value = room.roomStatus;
    modal.querySelector('[name="rentalPrice"]').value = room.rentalPrice;
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
    for (const [field, value] of Object.entries(account)) {
      const input = form.querySelector(`[name="${field}"]`);
      if (input) input.value = value;
    }
    form.querySelector('[name="password"]').value = '';
    form.querySelector('[name="password"]').required = false;
    modal.querySelector('h2').textContent = 'แก้ไขบัญชีผู้ใช้งาน';
    modal.querySelector('[type="submit"]').textContent = 'บันทึกการแก้ไข';
    modal.showModal();
  }

  const confirmButton = event.target.closest('[data-confirm]');
  if (confirmButton && !window.confirm(confirmButton.dataset.confirm)) event.preventDefault();
});

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
