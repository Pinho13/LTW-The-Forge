const adminModal      = document.getElementById('admin-class-modal');
const adminModalClose = document.getElementById('admin-modal-close');
const newSessionModal = document.getElementById('new-session-modal');
const newClassModal   = document.getElementById('new-class-modal');
const confirmModal    = document.getElementById('admin-confirm-modal');

// ── Room dropdown helpers ─────────────────────────────────────
async function loadRooms(selectEl, datetime, sessionId = 0, currentRoom = '') {
    selectEl.innerHTML = '<option value="">Loading…</option>';
    selectEl.disabled = true;
    const params = new URLSearchParams({ datetime, session_id: sessionId });
    try {
        const res   = await fetch('/src/actions/fetch_available_rooms.php?' + params);
        const rooms = await res.json();
        selectEl.innerHTML = '';
        if (rooms.length === 0) {
            selectEl.innerHTML = '<option value="">No rooms available</option>';
        } else {
            rooms.forEach(name => {
                const opt = document.createElement('option');
                opt.value = name;
                opt.textContent = name;
                if (name === currentRoom) opt.selected = true;
                selectEl.appendChild(opt);
            });
            if (currentRoom && !rooms.includes(currentRoom)) {
                const opt = document.createElement('option');
                opt.value = currentRoom;
                opt.textContent = currentRoom + ' (currently assigned)';
                opt.selected = true;
                selectEl.insertBefore(opt, selectEl.firstChild);
            }
        }
    } catch {
        selectEl.innerHTML = '<option value="">Error loading rooms</option>';
    }
    selectEl.disabled = false;
}

async function loadAllRooms(selectEl, currentRoom = '') {
    selectEl.innerHTML = '<option value="">Loading…</option>';
    selectEl.disabled = true;
    try {
        const res   = await fetch('/src/actions/fetch_available_rooms.php');
        const rooms = await res.json();
        selectEl.innerHTML = '';
        rooms.forEach(name => {
            const opt = document.createElement('option');
            opt.value = name;
            opt.textContent = name;
            if (name === currentRoom) opt.selected = true;
            selectEl.appendChild(opt);
        });
    } catch {
        selectEl.innerHTML = '<option value="">Error loading rooms</option>';
    }
    selectEl.disabled = false;
}

let pendingConfirmFn   = null;
let pendingConfirmFrom = null;

document.getElementById('admin-confirm-cancel').addEventListener('click', () => {
    confirmModal.close();
    if (pendingConfirmFrom) { openModal(pendingConfirmFrom); pendingConfirmFrom = null; }
    pendingConfirmFn = null;
});
document.getElementById('admin-confirm-ok').addEventListener('click', () => {
    confirmModal.close();
    pendingConfirmFrom = null;
    if (pendingConfirmFn) { pendingConfirmFn(); pendingConfirmFn = null; }
});

function adminConfirm(msg, fromModal, fn) {
    document.getElementById('admin-confirm-msg').textContent = msg;
    pendingConfirmFn   = fn;
    pendingConfirmFrom = fromModal;
    if (fromModal) closeModal(fromModal);
    confirmModal.showModal();
}

// ── Open/close helpers ────────────────────────────────────────
function openModal(el) {
    el.show();
    backdrop.classList.add('modal-backdrop--visible');
}
function closeModal(el) {
    el.close();
    backdrop.classList.remove('modal-backdrop--visible');
}

adminModalClose?.addEventListener('click', () => closeModal(adminModal));
document.getElementById('new-session-close')?.addEventListener('click', () => closeModal(newSessionModal));
document.getElementById('new-class-close')?.addEventListener('click',   () => closeModal(newClassModal));
backdrop.addEventListener('click', () => {
    [adminModal, newSessionModal, newClassModal].filter(Boolean).forEach(m => { if (m.open) closeModal(m); });
});


// ── Open edit modal on card click ─────────────────────────────
document.addEventListener('click', e => {
    const card = e.target.closest('.class-card--admin');
    if (!card || e.target.closest('button') || e.target.closest('.class-stack__dot')) return;
    const stack = card.closest('.class-stack');
    if (stack?._wasDragged?.()) return;

    // Populate session tab
    const [dtDate, dtTime] = card.dataset.datetime.split(' ');
    const dtHour = parseInt(dtTime, 10);
    document.getElementById('admin-modal-title').textContent = card.dataset.className;
    document.getElementById('edit-session-id').value         = card.dataset.sessionId;
    document.getElementById('edit-date').value               = dtDate;
    setSelectValue('edit-hour', dtHour);
    document.getElementById('edit-capacity').value           = card.dataset.capacity;
    loadRooms(document.getElementById('edit-room'), card.dataset.datetime, card.dataset.sessionId, card.dataset.room);
    document.getElementById('edit-enrolled-info').textContent =
        `${card.dataset.enrolled} member${card.dataset.enrolled !== '1' ? 's' : ''} currently enrolled`;

    // Populate class tab
    document.getElementById('edit-class-id').value            = card.dataset.classId;
    document.getElementById('edit-class-name').value          = card.dataset.className;
    document.getElementById('edit-class-duration').value      = card.dataset.duration;
    document.getElementById('edit-class-intensity').value     = card.dataset.intensity;
    document.getElementById('edit-class-description').value   = card.dataset.description || '';
    setSelectValue('edit-class-type',    card.dataset.typeId);
    setSelectValue('edit-class-trainer', card.dataset.trainerId || '');

    const isFeatured = card.dataset.isFeatured === '1';
    document.getElementById('feature-class-id').value   = card.dataset.classId;
    document.getElementById('feature-class-btn').textContent = isFeatured
        ? '★ Remove from Homepage'
        : '☆ Feature on Homepage';

    document.getElementById('tab-session').hidden = false;
    document.getElementById('tab-class').hidden   = true;
    clearErrors();

    openModal(adminModal);
});

function setSelectValue(id, val) {
    const sel = document.getElementById(id);
    [...sel.options].forEach(o => { o.selected = String(o.value) === String(val); });
}

function clearErrors() {
    ['session-error', 'class-error'].forEach(id => { document.getElementById(id).textContent = ''; });
}

// ── Save session ──────────────────────────────────────────────
document.getElementById('form-session').addEventListener('submit', async e => {
    e.preventDefault();
    const err = document.getElementById('session-error');
    err.textContent = '';
    const btn = e.target.querySelector('[type="submit"]');
    btn.disabled = true;

    const editDate = document.getElementById('edit-date').value;
    const editHour = document.getElementById('edit-hour').value.padStart(2, '0');
    const body = new URLSearchParams({
        csrf_token: CSRF_TOKEN,
        session_id: document.getElementById('edit-session-id').value,
        datetime:   `${editDate} ${editHour}:00:00`,
        room:       document.getElementById('edit-room').value,
        capacity:   document.getElementById('edit-capacity').value,
    });

    try {
        const res  = await fetch('/src/actions/action_edit_class_session.php', { method: 'POST', body });
        const data = await res.json();
        if (!res.ok || !data.success) { err.textContent = data.error || 'Failed to save.'; return; }
        closeModal(adminModal);
        location.reload();
    } finally {
        btn.disabled = false;
    }
});

// ── Delete session ────────────────────────────────────────────
document.getElementById('delete-session-btn').addEventListener('click', () => {
    adminConfirm('Delete this session? Enrolled members will lose their spot.', adminModal, async () => {
        const err = document.getElementById('session-error');
        const body = new URLSearchParams({
            csrf_token: CSRF_TOKEN,
            session_id: document.getElementById('edit-session-id').value,
        });
        const res  = await fetch('/src/actions/action_delete_class_session.php', { method: 'POST', body });
        const data = await res.json();
        if (!res.ok || !data.success) { err.textContent = data.error || 'Failed to delete.'; return; }
        closeModal(adminModal);
        location.reload();
    });
});

// ── Save class ────────────────────────────────────────────────
document.getElementById('form-class').addEventListener('submit', async e => {
    e.preventDefault();
    const err = document.getElementById('class-error');
    err.textContent = '';
    const btn = e.target.querySelector('[type="submit"]');
    btn.disabled = true;

    const body = new URLSearchParams({
        csrf_token:       CSRF_TOKEN,
        class_id:         document.getElementById('edit-class-id').value,
        name:             document.getElementById('edit-class-name').value,
        type_id:          document.getElementById('edit-class-type').value,
        trainer_id:       document.getElementById('edit-class-trainer').value,
        duration_minutes: document.getElementById('edit-class-duration').value,
        intensity:        document.getElementById('edit-class-intensity').value,
        description:      document.getElementById('edit-class-description').value,
    });

    try {
        const res  = await fetch('/src/actions/action_edit_class.php', { method: 'POST', body });
        const data = await res.json();
        if (!res.ok || !data.success) { err.textContent = data.error || 'Failed to save.'; return; }
        closeModal(adminModal);
        location.reload();
    } finally {
        btn.disabled = false;
    }
});

// ── Delete class ─────────────────────────────────────────────
document.getElementById('delete-class-btn').addEventListener('click', () => {
    adminConfirm('Delete this class? All its sessions and enrollments will also be deleted.', adminModal, async () => {
        const err = document.getElementById('class-error');
        const body = new URLSearchParams({
            csrf_token: CSRF_TOKEN,
            class_id:   document.getElementById('edit-class-id').value,
        });
        const res  = await fetch('/src/actions/action_delete_class.php', { method: 'POST', body });
        const data = await res.json();
        if (!res.ok || !data.success) { err.textContent = data.error || 'Failed to delete.'; return; }
        closeModal(adminModal);
        location.reload();
    });
});

// ── Reload rooms when edit date/hour changes ──────────────────
function editDatetime() {
    const d = document.getElementById('edit-date').value;
    const h = document.getElementById('edit-hour').value.padStart(2, '0');
    return d ? `${d} ${h}:00:00` : '';
}
['edit-date', 'edit-hour'].forEach(id => {
    document.getElementById(id).addEventListener('change', () => {
        const dt = editDatetime();
        if (!dt) return;
        const sessionId = document.getElementById('edit-session-id').value;
        const current   = document.getElementById('edit-room').value;
        loadRooms(document.getElementById('edit-room'), dt, sessionId, current);
    });
});

// ── New session ───────────────────────────────────────────────
document.getElementById('new-session-btn')?.addEventListener('click', () => {
    document.getElementById('form-new-session').reset();
    document.getElementById('new-session-error').textContent = '';
    loadAllRooms(document.getElementById('ns-room'));
    openModal(newSessionModal);
});


['ns-date', 'ns-hour'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', () => {
        const d  = document.getElementById('ns-date').value;
        const h  = document.getElementById('ns-hour').value.padStart(2, '0');
        if (!d) return;
        loadRooms(document.getElementById('ns-room'), `${d} ${h}:00:00`, 0, document.getElementById('ns-room').value);
    });
});

document.getElementById('form-new-session')?.addEventListener('submit', async e => {
    e.preventDefault();
    const err = document.getElementById('new-session-error');
    err.textContent = '';
    const btn = e.target.querySelector('[type="submit"]');
    btn.disabled = true;

    const nsDate = document.getElementById('ns-date').value;
    const nsHour = document.getElementById('ns-hour').value.padStart(2, '0');
    const body = new URLSearchParams({
        csrf_token: CSRF_TOKEN,
        class_id:   document.getElementById('ns-class').value,
        datetime:   `${nsDate} ${nsHour}:00:00`,
        room:       document.getElementById('ns-room').value,
        capacity:   document.getElementById('ns-capacity').value,
    });

    try {
        const res  = await fetch('/src/actions/action_create_class_session.php', { method: 'POST', body });
        const data = await res.json();
        if (!res.ok || !data.success) { err.textContent = data.error || 'Failed to create.'; return; }
        closeModal(newSessionModal);
        location.reload();
    } finally {
        btn.disabled = false;
    }
});

// ── New class ─────────────────────────────────────────────────
document.getElementById('new-class-btn')?.addEventListener('click', () => {
    document.getElementById('form-new-class').reset();
    document.getElementById('new-class-error').textContent = '';
    openModal(newClassModal);
});

document.getElementById('form-new-class')?.addEventListener('submit', async e => {
    e.preventDefault();
    const err = document.getElementById('new-class-error');
    err.textContent = '';
    const btn = e.target.querySelector('[type="submit"]');
    btn.disabled = true;

    const body = new URLSearchParams({
        csrf_token:       CSRF_TOKEN,
        name:             document.getElementById('nc-name').value,
        type_id:          document.getElementById('nc-type').value,
        trainer_id:       document.getElementById('nc-trainer').value,
        duration_minutes: document.getElementById('nc-duration').value,
        intensity:        document.getElementById('nc-intensity').value,
        description:      document.getElementById('nc-description').value,
    });

    try {
        const res  = await fetch('/src/actions/action_create_class.php', { method: 'POST', body });
        const data = await res.json();
        if (!res.ok || !data.success) { err.textContent = data.error || 'Failed to create.'; return; }
        closeModal(newClassModal);
        location.reload();
    } finally {
        btn.disabled = false;
    }
});

// ── Dashboard filter highlight ──
if (ACTIVE_FILTER) {
    const todayStr = new Date().toISOString().slice(0, 10);

    const cardMatches = card => {
        const enrolled   = parseInt(card.dataset.enrolled,   10);
        const capacity   = parseInt(card.dataset.capacity,   10);
        const waitlisted = parseInt(card.dataset.waitlisted, 10);
        const trainerId  = parseInt(card.dataset.trainerId,  10);
        const isFuture   = (card.dataset.datetime ?? '').slice(0, 10) >= todayStr;

        if (ACTIVE_FILTER === 'no_trainer')  return isFuture && trainerId === 0;
        if (ACTIVE_FILTER === 'at_capacity') return isFuture && enrolled >= capacity;
        if (ACTIVE_FILTER === 'waitlisted')  return isFuture && waitlisted > 0;
        if (ACTIVE_FILTER === 'empty')       return isFuture && enrolled === 0;
        return false;
    };

    let first = null;

    document.querySelectorAll('.calendar-day-column > .class-stack').forEach(stack => {
        const cards  = Array.from(stack.querySelectorAll('.class-card--admin'));
        const anyHit = cards.some(cardMatches);

        if (anyHit) {
            // Highlight the stack wrapper so it stands out as a unit
            stack.classList.add('class-card--highlight');
            if (!first) first = stack;
        } else {
            stack.classList.add('class-card--highlight-dim');
        }
    });

    document.querySelectorAll('.calendar-day-column > .class-card--admin').forEach(card => {
        if (cardMatches(card)) {
            card.classList.add('class-card--highlight');
            if (!first) first = card;
        } else {
            card.classList.add('class-card--highlight-dim');
        }
    });

    if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
