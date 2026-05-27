document.addEventListener('click', async e => {
    const btn = e.target.closest('.res-item__action [type="submit"]');
    if (!btn) return;

    const form = btn.closest('form');
    const item = btn.closest('[data-reservation-id]');
    if (!form || !item) return;

    btn.disabled    = true;
    btn.textContent = 'Cancelling…';

    try {
        const body = new URLSearchParams({ reservation_id: item.dataset.reservationId, csrf_token: CSRF_TOKEN });
        const res  = await fetch(form.action, { method: 'POST', body });
        const data = await res.json();

        if (!res.ok || !data.success) {
            btn.disabled    = false;
            btn.textContent = 'Cancel';
            alert(data.error || 'Could not cancel reservation.');
            return;
        }

        item.remove();

        const list = document.querySelector('.res-list');
        if (list && list.querySelectorAll('.res-item').length === 0) {
            list.insertAdjacentHTML('afterend', '<p class="res-empty">No equipment reservations yet. <a href="/src/pages/equipment-map.php">Browse equipment</a>.</p>');
            list.remove();
        }
    } catch {
        btn.disabled    = false;
        btn.textContent = 'Cancel';
        alert('Network error. Please try again.');
    }
});

// Sort
const resSortBtns = document.querySelectorAll('#res-sort .enrollment-sort__btn');
const resList = document.getElementById('res-list');

function applyResSort(by) {
    if (!resList) return;
    const items = [...resList.querySelectorAll('.res-item')];
    items.sort((a, b) => {
        if (by === 'name') return (a.dataset.name || '').localeCompare(b.dataset.name || '');
        return (a.dataset.date || '').localeCompare(b.dataset.date || '');
    });
    items.forEach(item => resList.appendChild(item));
}

resSortBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        resSortBtns.forEach(b => b.classList.remove('enrollment-sort__btn--active'));
        btn.classList.add('enrollment-sort__btn--active');
        applyResSort(btn.dataset.sort);
    });
});
