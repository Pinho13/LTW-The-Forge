const COLOR_GREEN    = '#4e9055';
const COLOR_RED      = '#C85050';
const GRID_START_MIN = 8 * 60;
const GRID_END_MIN   = 22 * 60;
const SLOT_MINS      = 10;
const TOTAL_SLOTS    = (GRID_END_MIN - GRID_START_MIN) / SLOT_MINS; // 84
const ROW_PX         = 10;

// ── Refresh map dots ──────────────────────────────────────────
async function refreshCounts() {
    try {
        const res = await fetch('/src/actions/fetch_equipment_counts.php');
        if (!res.ok) return;
        const data = await res.json();
        for (const [unitId, info] of Object.entries(data)) {
            const sid         = String(unitId);
            const available   = info.is_available;
            const maintenance = UNIT_MAP[unitId]?.status === 'maintenance';
            const clickable   = !maintenance;
            const hex         = available ? COLOR_GREEN : COLOR_RED;
            document.querySelectorAll(`.equip-node[data-unit-id="${sid}"]`).forEach(node => {
                node.classList.toggle('equip-node--clickable', clickable);
                node.classList.toggle('equip-node--readonly',  !clickable);
                node.classList.toggle('equip-node--busy',      clickable && !available);
                node.setAttribute('tabindex', clickable ? '0' : '-1');
                if (!maintenance) {
                    const tint = node.querySelector('.equip-tint');
                    if (tint) {
                        tint.setAttribute('fill',   hex);
                        tint.setAttribute('stroke', hex);
                    }
                }
            });
            if (UNIT_MAP[unitId] !== undefined) UNIT_MAP[unitId].is_available = available;
        }
    } catch (err) {
        console.error('refreshCounts error:', err);
    }
}

const reserveModal = document.getElementById('reserve-modal');
refreshCounts();

// Basic plan: map is visible but no reservation UI — stop here
if (!reserveModal) { /* nothing more to wire up */ }
else {

const backdrop      = document.getElementById('page-backdrop');
const reserveClose  = document.getElementById('reserve-close');
const unitIdInput   = document.getElementById('reserve-unit-id');
const equipName     = document.getElementById('reserve-equipment-name');
const formError     = document.getElementById('reserve-error');
const startHidden   = document.getElementById('reserve-start');
const endHidden     = document.getElementById('reserve-end');
const dateInput     = document.getElementById('reserve-date');
const dateHidden    = document.getElementById('reserve-date-hidden');
const reserveCol    = document.getElementById('reserve-col');
const reserveGutter = document.getElementById('reserve-gutter');
const selLabel      = document.getElementById('reserve-selection-label');
const submitBtn     = document.getElementById('reserve-submit');

let takenSlots  = [];
let dragStart   = null;
let dragEnd     = null;
let isDragging  = false;
let selStartMin = null;
let selEndMin   = null;

// ── Helpers ───────────────────────────────────────────────────
function rowToMin(row) { return GRID_START_MIN + row * SLOT_MINS; }
function minToRow(min) { return (min - GRID_START_MIN) / SLOT_MINS; }
function fmtMin(min) {
    return `${String(Math.floor(min/60)).padStart(2,'0')}:${String(min%60).padStart(2,'0')}`;
}
function getRowPx() {
    const val = getComputedStyle(reserveCol).getPropertyValue('--row-px').trim();
    return val ? parseFloat(val) : ROW_PX;
}
function yToRow(y) {
    const cal     = reserveCol.closest('.reserve-cal');
    const calRect = cal.getBoundingClientRect();
    const relY    = y - calRect.top + cal.scrollTop;
    return Math.max(0, Math.min(TOTAL_SLOTS - 1, Math.floor(relY / getRowPx())));
}
function getNowMin() {
    const now = new Date();
    return now.getHours() * 60 + now.getMinutes();
}
function isToday() {
    return dateInput.value === new Date().toISOString().slice(0, 10);
}
function rowBlocked(row) {
    const rowMin = rowToMin(row);
    if (isToday() && rowMin < getNowMin()) return true;
    return takenSlots.some(s => rowMin >= s.start_min && rowMin < s.end_min);
}
function selectionOverlapsTaken(startRow, endRow) {
    const startMin = rowToMin(Math.min(startRow, endRow));
    const endMin   = rowToMin(Math.max(startRow, endRow) + 1);
    return takenSlots.some(s => startMin < s.end_min && endMin > s.start_min);
}
function selectionOverlapsPast(startRow, endRow) {
    if (!isToday()) return false;
    return rowToMin(Math.min(startRow, endRow)) < getNowMin();
}

// ── Modal ─────────────────────────────────────────────────────
function openModal() {
    selStartMin = selEndMin = dragStart = dragEnd = null;
    startHidden.value = endHidden.value = '';
    submitBtn.disabled = true;
    if (formError) formError.textContent = '';
    if (selLabel)  selLabel.textContent  = '';
    reserveModal.show();
    backdrop.classList.add('modal-backdrop--visible');
    loadGrid().then(scrollToNow);
}
function closeModal() {
    reserveModal.close();
    backdrop.classList.remove('modal-backdrop--visible');
}

reserveClose.addEventListener('click', closeModal);
backdrop.addEventListener('click', closeModal);
dateInput.addEventListener('change', () => {
    selStartMin = selEndMin = dragStart = dragEnd = null;
    startHidden.value = endHidden.value = '';
    submitBtn.disabled = true;
    if (selLabel) selLabel.textContent = '';
    loadGrid().then(scrollToNow);
});

// ── Fetch ─────────────────────────────────────────────────────
async function loadGrid() {
    const unitId = unitIdInput.value;
    const date   = dateInput.value;
    dateHidden.value = date;
    reserveCol.innerHTML    = '<span class="reserve-grid-loading">Loading…</span>';
    reserveGutter.innerHTML = '';
    try {
        const res  = await fetch(`/src/actions/fetch_unit_reservations.php?unit_id=${unitId}&date=${date}`);
        const data = await res.json();
        takenSlots = data.slots || [];
    } catch (err) {
        console.error('loadGrid fetch error', err);
        takenSlots = [];
    }
    renderGrid();
}

function scrollToNow() {
    const cal = document.getElementById('reserve-cal');
    if (!cal) return;
    const nowMin = getNowMin();
    const ratio  = Math.max(0, (nowMin - GRID_START_MIN) / (GRID_END_MIN - GRID_START_MIN));
    cal.scrollTop = (cal.scrollHeight - cal.clientHeight) * ratio;
}

// ── Render ────────────────────────────────────────────────────
function renderGrid() {
    reserveCol.innerHTML    = '';
    reserveGutter.innerHTML = '';

    const nowMin = isToday() ? getNowMin() : -1;

    for (let m = GRID_START_MIN; m < GRID_END_MIN; m += 60) {
        const lbl = document.createElement('div');
        lbl.className     = 'reserve-gutter-label';
        lbl.textContent   = fmtMin(m);
        lbl.style.gridRow = `${minToRow(m) + 1} / span 6`;
        reserveGutter.appendChild(lbl);
    }

    if (nowMin > GRID_START_MIN) {
        const pastRows = Math.min(Math.ceil(minToRow(nowMin)), TOTAL_SLOTS);
        if (pastRows > 0) {
            const el = document.createElement('div');
            el.className     = 'reserve-block reserve-block--past';
            el.style.gridRow = `1 / span ${pastRows}`;
            reserveCol.appendChild(el);
        }
    }

    for (const s of takenSlots) {
        const startRow = minToRow(s.start_min) + 1;
        const span     = (s.end_min - s.start_min) / SLOT_MINS;
        const el = document.createElement('div');
        el.className     = 'reserve-block reserve-block--taken';
        el.style.gridRow = `${startRow} / span ${span}`;
        reserveCol.appendChild(el);
    }

    if (selStartMin !== null && selEndMin !== null) {
        const startRow = minToRow(selStartMin) + 1;
        const span     = (selEndMin - selStartMin) / SLOT_MINS;
        const el = document.createElement('div');
        el.className     = 'reserve-block reserve-block--selected';
        el.style.gridRow = `${startRow} / span ${span}`;
        reserveCol.appendChild(el);
    }

    if (isDragging && dragStart !== null && dragEnd !== null) {
        const s = Math.min(dragStart, dragEnd);
        const e = Math.max(dragStart, dragEnd);
        const el = document.createElement('div');
        el.className     = 'reserve-block reserve-block--preview';
        el.style.gridRow = `${s + 1} / span ${e - s + 1}`;
        reserveCol.appendChild(el);
    }
}

// ── Drag via coordinates ──────────────────────────────────────
reserveCol.addEventListener('mousedown', e => {
    e.preventDefault();
    const row = yToRow(e.clientY);
    if (rowBlocked(row)) return;
    isDragging = true;
    dragStart = dragEnd = row;
    renderGrid();
});

reserveCol.addEventListener('mousemove', e => {
    if (!isDragging) return;
    dragEnd = yToRow(e.clientY);
    renderGrid();
});

document.addEventListener('mouseup', e => {
    if (!isDragging) return;
    isDragging = false;
    commitSelection();
});

reserveCol.addEventListener('touchstart', e => {
    e.preventDefault();
    const t = e.touches[0];
    const row = yToRow(t.clientY);
    if (rowBlocked(row)) return;
    isDragging = true;
    dragStart = dragEnd = row;
    renderGrid();
}, { passive: false });

reserveCol.addEventListener('touchmove', e => {
    if (!isDragging) return;
    e.preventDefault();
    dragEnd = yToRow(e.touches[0].clientY);
    renderGrid();
}, { passive: false });

reserveCol.addEventListener('touchend', () => {
    if (!isDragging) return;
    isDragging = false;
    commitSelection();
});

function commitSelection() {
    if (dragStart === null || dragEnd === null) return;
    const startRow = Math.min(dragStart, dragEnd);
    const endRow   = Math.max(dragStart, dragEnd);

    if (selectionOverlapsPast(startRow, endRow) || selectionOverlapsTaken(startRow, endRow)) {
        selStartMin = selEndMin = null;
        startHidden.value = endHidden.value = '';
        submitBtn.disabled = true;
        if (selLabel)  selLabel.textContent  = '';
        if (formError) formError.textContent = 'Selection overlaps a taken or past slot.';
        dragStart = dragEnd = null;
        renderGrid();
        return;
    }

    selStartMin = rowToMin(startRow);
    selEndMin   = rowToMin(endRow + 1);
    startHidden.value = fmtMin(selStartMin);
    endHidden.value   = fmtMin(selEndMin);
    submitBtn.disabled = false;
    if (formError) formError.textContent = '';
    if (selLabel)  selLabel.textContent  = `${fmtMin(selStartMin)} – ${fmtMin(selEndMin)}`;
    dragStart = dragEnd = null;
    renderGrid();
}

// ── Node activate ─────────────────────────────────────────────
function handleNodeActivate(node) {
    const unitId = node.dataset.unitId;
    const info   = UNIT_MAP[unitId];
    if (!info) return;
    unitIdInput.value     = unitId;
    equipName.textContent = info.equipment_name + ' · ' + info.identifier;
    openModal();
}

document.addEventListener('click', e => {
    const node = e.target.closest('.equip-node--clickable');
    if (node) handleNodeActivate(node);
});

document.addEventListener('keydown', e => {
    if (e.key !== 'Enter' && e.key !== ' ') return;
    const node = document.activeElement?.closest('.equip-node--clickable');
    if (node) { e.preventDefault(); handleNodeActivate(node); }
});

// ── Submit ────────────────────────────────────────────────────
document.querySelector('#reserve-modal form')?.addEventListener('submit', async e => {
    e.preventDefault();
    const form   = e.target;
    const submit = form.querySelector('[type="submit"]');

    if (!startHidden.value || !endHidden.value) {
        if (formError) formError.textContent = 'Please select a time range.';
        return;
    }

    const body = new URLSearchParams({
        csrf_token: document.querySelector('[name="csrf_token"]').value,
        unit_id:    unitIdInput.value,
        date:       dateHidden.value,
        start_time: startHidden.value,
        end_time:   endHidden.value,
    });

    submit.disabled    = true;
    submit.textContent = 'Reserving…';
    if (formError) formError.textContent = '';

    try {
        const res  = await fetch(form.action, { method: 'POST', body });
        const data = await res.json();
        if (!res.ok || !data.success) {
            if (formError) formError.textContent = data.error || 'Something went wrong.';
            return;
        }
        closeModal();
        refreshCounts();
    } catch {
        if (formError) formError.textContent = 'Network error. Please try again.';
    } finally {
        submit.disabled    = false;
        submit.textContent = 'Confirm Reservation';
    }
});

} // end Premium-only block

// ── Admin: Edit Status mode ───────────────────────────────────
if (IS_ADMIN) {
    const editStatusBtn  = document.getElementById('edit-status-btn');
    const editLayoutBtn  = document.getElementById('edit-layout-btn');
    let statusMode = false;

    const statusToolbar  = document.getElementById('status-toolbar');
    const statusDoneBtn  = document.getElementById('status-done-btn');
    statusDoneBtn?.addEventListener('click', () => exitStatusMode());

    function exitStatusMode() {
        statusMode = false;
        editStatusBtn.style.outlineColor = '';
        editStatusBtn.style.color        = '';
        document.body.classList.remove('status-edit-mode');
        if (statusToolbar) statusToolbar.hidden = true;
        if (editLayoutBtn) editLayoutBtn.disabled = false;
    }

    window.__exitStatusMode = exitStatusMode;

    editStatusBtn?.addEventListener('click', () => {
        if (statusMode) {
            exitStatusMode();
        } else {
            statusMode = true;
            editStatusBtn.style.outlineColor = 'var(--color-gold)';
            editStatusBtn.style.color        = 'var(--color-gold)';
            document.body.classList.add('status-edit-mode');
            if (statusToolbar) statusToolbar.hidden = false;
            if (editLayoutBtn) editLayoutBtn.disabled = true;
        }
    });

    document.addEventListener('click', async e => {
        if (!statusMode) return;
        let node = e.target.closest('.equip-node');
        if (!node) {
            // SVG elements may not support closest — walk up manually
            let el = e.target;
            while (el && el !== document) {
                if (el.classList && el.classList.contains('equip-node')) { node = el; break; }
                el = el.parentElement;
            }
        }
        if (!node) return;
        e.stopImmediatePropagation();

        const unitId  = node.dataset.unitId;
        const info    = UNIT_MAP[unitId];
        if (!info) return;

        const next = info.status === 'maintenance' ? 'available' : 'maintenance';

        const body = new URLSearchParams({ unit_id: unitId, status: next, csrf_token: CSRF_TOKEN });
        const res  = await fetch('/src/actions/action_set_equipment_status.php', { method: 'POST', body });
        const data = await res.json();
        if (!res.ok || !data.success) return;

        info.status = next;

        const tint = node.querySelector('.equip-tint');
        if (!tint) return;

        if (next === 'maintenance') {
            tint.setAttribute('fill',   'url(#maintenance-pattern)');
            tint.setAttribute('stroke', '#c9a227');
            node.classList.add('equip-node--maintenance');
            node.classList.remove('equip-node--clickable', 'equip-node--busy');
            node.classList.add('equip-node--readonly');
            node.setAttribute('tabindex', '-1');
        } else {
            tint.setAttribute('fill',   COLOR_GREEN);
            tint.setAttribute('stroke', COLOR_GREEN);
            node.classList.remove('equip-node--maintenance', 'equip-node--readonly', 'equip-node--busy');
            node.classList.add('equip-node--clickable');
            node.setAttribute('tabindex', '0');
            refreshCounts();
        }
    }, true);
}
