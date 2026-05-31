// Admin card action picker — intercepts card clicks and shows the 4-option picker.
// Loaded after admin-classes.js. Uses getElementById directly to avoid const conflicts.

(function () {

let _pickerCard = null;

function getBackdrop()    { return document.getElementById('page-backdrop'); }
function getPickerModal() { return document.getElementById('card-picker-modal'); }
function getRosterModal() { return document.getElementById('roster-modal'); }
function getAdminModal()  { return document.getElementById('admin-class-modal'); }

function openPicker(card) {
    _pickerCard = card;
    document.getElementById('picker-title').textContent = card.dataset.className;

    const dt      = new Date(card.dataset.datetime.replace(' ', 'T'));
    const timeStr = dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    const dateStr = dt.toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric' });
    document.getElementById('picker-meta').textContent =
        `${dateStr} · ${timeStr} · ${card.dataset.room} · ${card.dataset.enrolled}/${card.dataset.capacity} enrolled`;

    getPickerModal().show();
    getBackdrop().classList.add('modal-backdrop--visible');
}

function closePicker() {
    getPickerModal().close();
    getBackdrop().classList.remove('modal-backdrop--visible');
    _pickerCard = null;
}

function openRoster(card, list) {
    const label = list === 'roster' ? 'Roster' : 'Waitlist';
    document.getElementById('roster-title').textContent = `${card.dataset.className} — ${label}`;
    const body = document.getElementById('roster-body');
    body.innerHTML = '<p class="roster-loading">Loading…</p>';

    getRosterModal().show();
    getBackdrop().classList.add('modal-backdrop--visible');

    fetch(`/src/actions/fetch_roster.php?session_id=${card.dataset.sessionId}&list=${list}`)
        .then(r => r.json())
        .then(data => {
            if (!data.members || data.members.length === 0) {
                body.innerHTML = `<p class="roster-empty">No members ${list === 'roster' ? 'enrolled' : 'on waitlist'}.</p>`;
                return;
            }
            const ul = document.createElement('ul');
            ul.className = 'roster-list';
            data.members.forEach(m => {
                const li = document.createElement('li');
                li.className = 'roster-item';
                const pos = list === 'waitlist' ? `<span class="roster-pos">#${m.position}</span>` : '';
                li.innerHTML = `${pos}<span class="roster-name">${esc(m.name)}</span><span class="roster-handle">@${esc(m.username)}</span>`;
                ul.appendChild(li);
            });
            body.innerHTML = '';
            body.appendChild(ul);
        })
        .catch(() => { body.innerHTML = '<p class="roster-empty">Failed to load members.</p>'; });
}

function closeRoster() {
    getRosterModal().close();
    getBackdrop().classList.remove('modal-backdrop--visible');
}

function esc(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Picker button handlers ────────────────────────────────────
document.getElementById('picker-edit-session').addEventListener('click', () => {
    if (!_pickerCard) return;
    const card = _pickerCard;
    closePicker();

    const [dtDate, dtTime] = card.dataset.datetime.split(' ');
    const dtHour = parseInt(dtTime, 10);
    document.getElementById('admin-modal-title').textContent    = card.dataset.className;
    document.getElementById('admin-modal-subtitle').textContent = 'Editing Session';
    document.getElementById('edit-session-id').value            = card.dataset.sessionId;
    document.getElementById('edit-date').value                  = dtDate;
    document.getElementById('edit-hour').value                  = dtHour;
    document.getElementById('edit-capacity').value              = card.dataset.capacity;
    document.getElementById('edit-enrolled-info').textContent   =
        `${card.dataset.enrolled} member${card.dataset.enrolled !== '1' ? 's' : ''} currently enrolled`;
    document.getElementById('feature-class-id').value          = card.dataset.classId;
    document.getElementById('feature-class-btn').textContent   =
        card.dataset.isFeatured === '1' ? '★ Remove from Homepage' : '☆ Feature on Homepage';

    loadRooms(document.getElementById('edit-room'), card.dataset.datetime, card.dataset.sessionId, card.dataset.room);

    document.getElementById('tab-session').hidden = false;
    document.getElementById('tab-class').hidden   = true;
    clearErrors();

    getAdminModal().show();
    getBackdrop().classList.add('modal-backdrop--visible');
});

document.getElementById('picker-edit-class').addEventListener('click', () => {
    if (!_pickerCard) return;
    const card = _pickerCard;
    closePicker();

    document.getElementById('admin-modal-title').textContent    = card.dataset.className;
    document.getElementById('admin-modal-subtitle').textContent = 'Editing Class';
    document.getElementById('edit-class-id').value              = card.dataset.classId;
    document.getElementById('edit-class-name').value            = card.dataset.className;
    document.getElementById('edit-class-duration').value        = card.dataset.duration;
    document.getElementById('edit-class-intensity').value       = card.dataset.intensity;
    document.getElementById('edit-class-description').value     = card.dataset.description || '';
    document.getElementById('feature-class-id').value           = card.dataset.classId;
    document.getElementById('feature-class-btn').textContent    =
        card.dataset.isFeatured === '1' ? '★ Remove from Homepage' : '☆ Feature on Homepage';

    const typeEl    = document.getElementById('edit-class-type');
    const trainerEl = document.getElementById('edit-class-trainer');
    [...typeEl.options].forEach(o    => { o.selected = String(o.value) === String(card.dataset.typeId); });
    [...trainerEl.options].forEach(o => { o.selected = String(o.value) === String(card.dataset.trainerId || ''); });

    document.getElementById('tab-session').hidden = true;
    document.getElementById('tab-class').hidden   = false;
    clearErrors();

    getAdminModal().show();
    getBackdrop().classList.add('modal-backdrop--visible');
});

document.getElementById('picker-roster').addEventListener('click', () => {
    if (!_pickerCard) return;
    const card = _pickerCard;
    closePicker();
    openRoster(card, 'roster');
});

document.getElementById('picker-waitlist').addEventListener('click', () => {
    if (!_pickerCard) return;
    const card = _pickerCard;
    closePicker();
    openRoster(card, 'waitlist');
});

document.getElementById('picker-close').addEventListener('click', closePicker);
document.getElementById('roster-close').addEventListener('click', closeRoster);

getBackdrop().addEventListener('click', () => {
    if (getPickerModal().open) closePicker();
    if (getRosterModal().open) closeRoster();
});

// ── Intercept card clicks before admin-classes.js ──
document.addEventListener('click', e => {
    const card = e.target.closest('.class-card--admin');
    if (!card || e.target.closest('button') || e.target.closest('.class-stack__dot')) return;
    const stack = card.closest('.class-stack');
    if (stack?._wasDragged?.()) return;

    e.stopImmediatePropagation();
    openPicker(card);
}, true);

})();
