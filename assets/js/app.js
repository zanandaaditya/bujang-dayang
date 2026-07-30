(() => {
  const countdowns = document.querySelectorAll('[data-countdown]');
  const tick = () => countdowns.forEach(el => {
    const target = new Date(el.dataset.countdown).getTime();
    const diff = Math.max(0, target - Date.now());
    const values = {
      days: Math.floor(diff / 86400000),
      hours: Math.floor(diff % 86400000 / 3600000),
      minutes: Math.floor(diff % 3600000 / 60000),
      seconds: Math.floor(diff % 60000 / 1000)
    };
    Object.entries(values).forEach(([key,val]) => {
      const node = el.querySelector(`[data-${key}]`);
      if (node) node.textContent = String(val).padStart(2,'0');
    });
  });
  tick(); setInterval(tick,1000);

  document.querySelectorAll('[data-vote-finalist]').forEach(button => button.addEventListener('click', () => {
    const modal = document.querySelector('#voteModal'); if (!modal) return;
    modal.querySelector('[name="finalist_id"]').value = button.dataset.id;
    modal.querySelector('[data-modal-name]').textContent = button.dataset.name;
    modal.querySelector('[data-modal-number]').textContent = button.dataset.number;
    modal.querySelector('[data-modal-region]').textContent = button.dataset.region;
    modal.querySelector('[data-modal-category]').textContent = button.dataset.category;
    modal.querySelector('[data-modal-photo]').src = button.dataset.photo;
  }));

  document.querySelectorAll('[data-category-filter]').forEach(btn => btn.addEventListener('click', () => {
    const cat = btn.dataset.categoryFilter;
    document.querySelectorAll('[data-category-filter]').forEach(b => b.classList.toggle('active', b === btn));
    document.querySelectorAll('[data-finalist-category]').forEach(card => card.closest('.finalist-col').classList.toggle('d-none', cat !== 'ALL' && card.dataset.finalistCategory !== cat));
  }));

  const search = document.querySelector('[data-finalist-search]');
  search?.addEventListener('input', () => {
    const query = search.value.toLowerCase().trim();
    document.querySelectorAll('.finalist-col').forEach(col => {
      const text = col.textContent.toLowerCase();
      col.classList.toggle('search-hidden', !text.includes(query));
      if (!col.classList.contains('d-none')) col.style.display = text.includes(query) ? '' : 'none';
    });
  });

  const statusNode = document.querySelector('[data-payment-poll]');
  if (statusNode && statusNode.dataset.status !== 'PAID') {
    const order = statusNode.dataset.paymentPoll;
    const poll = setInterval(async () => {
      try {
        const res = await fetch(`api/payment-status.php?order=${encodeURIComponent(order)}`, {headers:{Accept:'application/json'}});
        const json = await res.json();
        if (json.status && json.status !== statusNode.dataset.status) location.reload();
      } catch (_) {}
    }, 5000);
    setTimeout(() => clearInterval(poll), 10 * 60 * 1000);
  }
})();
