// Admin edit mode for the equipment map.
// Depends on: UNIT_MAP, CSRF_TOKEN (injected by equipment-map.php)

const SVG_W          = 650;
const SVG_H          = 425;
const DEFAULT_UNIT_W = 55;
const DEFAULT_UNIT_H = 40;
const MIN_SIZE       = 16;
const HANDLE_R       = 5;   // corner handle radius (SVG units)
const ROT_OFFSET     = 18;  // distance below bottom edge for rotation handle

const editBtn     = document.getElementById('edit-layout-btn');
const editBar     = document.getElementById('edit-toolbar');
const pickerPanel = document.getElementById('picker-panel');
const saveBtn     = document.getElementById('edit-save-btn');
const statusToolbar = document.getElementById('status-toolbar');
const mapHint     = document.getElementById('map-hint');
const svg         = document.querySelector('.gym-svg');

let editMode  = false;
let placed    = {};       // id (string) -> unit object
let snapshot  = null;
let selected  = null;     // id of currently selected unit
let tempId    = -1;

// ── Init from server data ──────────────────────────────────────
function initPlaced() {
    placed = {};
    for (const [id, u] of Object.entries(UNIT_MAP)) {
        if (u.map_x === null) continue;
        placed[id] = {
            id:             u.id,
            equipment_id:   u.equipment_id,
            equipment_name: u.equipment_name,
            photo:          u.photo,
            x:              u.map_x,
            y:              u.map_y,
            w:              u.map_w,
            h:              u.map_h,
            rotation:       u.rotation ?? 0,
        };
    }
}

// ── SVG coordinate helper ──────────────────────────────────────
function toSVG(clientX, clientY) {
    const pt = svg.createSVGPoint();
    pt.x = clientX;
    pt.y = clientY;
    return pt.matrixTransform(svg.getScreenCTM().inverse());
}

// ── Enter / exit edit mode ────────────────────────────────────
function enterEditMode() {
    if (window.__exitStatusMode) window.__exitStatusMode();
    const editStatusBtn = document.getElementById('edit-status-btn');
    if (editStatusBtn) editStatusBtn.disabled = true;
    editMode = true;
    snapshot = JSON.parse(JSON.stringify(placed));
    selected = null;

    editBtn.textContent        = 'Edit Layout';
    editBtn.style.outlineColor = 'var(--color-gold)';
    editBtn.style.color        = 'var(--color-gold)';
    editBar.hidden             = false;
    pickerPanel.hidden         = false;
    mapHint.hidden             = true;
    document.body.classList.add('edit-mode-active');

    svg.classList.add('svg--edit-mode');
    rebuildAll();
}

function exitEditMode(restore) {
    deselect();
    editMode = false;
    if (restore && snapshot) placed = snapshot;
    snapshot = null;

    editBtn.style.outlineColor = '';
    editBtn.style.color        = '';
    const editStatusBtn = document.getElementById('edit-status-btn');
    if (editStatusBtn) editStatusBtn.disabled = false;
    editBar.hidden             = true;
    pickerPanel.hidden         = true;
    mapHint.hidden             = false;
    document.body.classList.remove('edit-mode-active');

    svg.classList.remove('svg--edit-mode');
    rebuildAll();
    if (typeof refreshCounts === 'function') refreshCounts();
}

// ── Build all nodes from scratch ──────────────────────────────
function rebuildAll() {
    svg.querySelectorAll('.equip-node, .sel-overlay').forEach(n => n.remove());
    for (const u of Object.values(placed)) {
        svg.appendChild(buildNode(u));
    }
    if (editMode && selected && placed[selected]) {
        renderSelectionOverlay(placed[selected]);
    }
}

// ── Axis-aligned bounding box for a unit after rotation ───────
// For 90°/270° w and h swap around the center; 0°/180° unchanged.
function aabb(u) {
    const swap = u.rotation === 90 || u.rotation === 270;
    const cx   = u.x + u.w / 2;
    const cy   = u.y + u.h / 2;
    const bw   = swap ? u.h : u.w;
    const bh   = swap ? u.w : u.h;
    return { x: cx - bw / 2, y: cy - bh / 2, w: bw, h: bh };
}

// ── Build a single equipment <g> node ─────────────────────────
// The <image> carries the rotation transform so it looks right.
// The hit <rect> uses the axis-aligned bounding box so clicks
// always map to what the user actually sees.
function buildNode(u) {
    const ns = 'http://www.w3.org/2000/svg';
    const g  = document.createElementNS(ns, 'g');
    g.classList.add('equip-node', editMode ? 'equip-node--edit' : 'equip-node--readonly');
    g.dataset.unitId = String(u.id);
    // No transform on the <g> — rotation lives on the <image> only

    if (u.photo) {
        const imgWrap = document.createElementNS(ns, 'g');
        applyImgTransform(imgWrap, u);
        const img = document.createElementNS(ns, 'image');
        img.setAttribute('href', `/database/assets/equipment/${u.photo}`);
        img.setAttribute('x', u.x);
        img.setAttribute('y', u.y);
        img.setAttribute('width', u.w);
        img.setAttribute('height', u.h);
        img.setAttribute('preserveAspectRatio', 'xMidYMid meet');
        img.classList.add('equip-img');
        imgWrap.appendChild(img);
        g.appendChild(imgWrap);
    }

    const bb = aabb(u);
    const overlay = document.createElementNS(ns, 'rect');
    overlay.setAttribute('x', bb.x);
    overlay.setAttribute('y', bb.y);
    overlay.setAttribute('width',  bb.w);
    overlay.setAttribute('height', bb.h);
    overlay.setAttribute('rx', '2');
    if (editMode) {
        overlay.setAttribute('fill',   'rgba(78,144,85,0.08)');
        overlay.setAttribute('stroke', 'none');
    } else {
        const info = UNIT_MAP[u.id];
        if (info && info.status === 'maintenance') {
            overlay.setAttribute('fill',   'url(#maintenance-pattern)');
            overlay.setAttribute('stroke', '#c9a227');
            g.classList.add('equip-node--maintenance');
        } else {
            overlay.setAttribute('fill',   '#4e9055');
            overlay.setAttribute('stroke', '#4e9055');
        }
    }
    overlay.classList.add('equip-tint');
    g.appendChild(overlay);

    if (editMode) {
        g.addEventListener('mousedown', e => onNodeMousedown(e, u));
    }

    return g;
}

// Rotation transform on the <image> element only
function applyImgTransform(img, u) {
    if (u.rotation) {
        const cx = u.x + u.w / 2;
        const cy = u.y + u.h / 2;
        img.setAttribute('transform', `rotate(${u.rotation} ${cx} ${cy})`);
    } else {
        img.removeAttribute('transform');
    }
}

function applyTransform(el, u) {
    if (u.rotation) {
        const cx = u.x + u.w / 2;
        const cy = u.y + u.h / 2;
        el.setAttribute('transform', `rotate(${u.rotation} ${cx} ${cy})`);
    } else {
        el.removeAttribute('transform');
    }
}

// ── Selection overlay (border + corner handles + rotation handle) ──
const SEL_NS = 'http://www.w3.org/2000/svg';

function renderSelectionOverlay(u) {
    removeSelectionOverlay();

    const bb = aabb(u);
    const g  = document.createElementNS(SEL_NS, 'g');
    g.classList.add('sel-overlay');
    // No transform — overlay uses axis-aligned bounding box directly

    // Dashed border
    const border = document.createElementNS(SEL_NS, 'rect');
    border.setAttribute('x',      bb.x);
    border.setAttribute('y',      bb.y);
    border.setAttribute('width',  bb.w);
    border.setAttribute('height', bb.h);
    border.classList.add('sel-border');
    g.appendChild(border);

    // Corner handles at AABB corners
    const corners = [
        { id: 'nw', cx: bb.x,          cy: bb.y },
        { id: 'ne', cx: bb.x + bb.w,   cy: bb.y },
        { id: 'se', cx: bb.x + bb.w,   cy: bb.y + bb.h },
        { id: 'sw', cx: bb.x,          cy: bb.y + bb.h },
    ];
    for (const c of corners) {
        const circle = document.createElementNS(SEL_NS, 'circle');
        circle.setAttribute('cx', c.cx);
        circle.setAttribute('cy', c.cy);
        circle.setAttribute('r', HANDLE_R);
        circle.classList.add('sel-handle', `sel-handle--${c.id}`);
        circle.dataset.corner = c.id;
        circle.addEventListener('mousedown', e => onResizeMousedown(e, u, c.id));
        g.appendChild(circle);
    }

    // Rotation stem from AABB bottom-center
    const stemX = bb.x + bb.w / 2;
    const stem = document.createElementNS(SEL_NS, 'line');
    stem.setAttribute('x1', stemX);
    stem.setAttribute('y1', bb.y + bb.h);
    stem.setAttribute('x2', stemX);
    stem.setAttribute('y2', bb.y + bb.h + ROT_OFFSET);
    stem.classList.add('sel-rot-stem');
    g.appendChild(stem);

    // Rotation handle — circle + arc arrow drawn inside
    const rcy = bb.y + bb.h + ROT_OFFSET;
    const rotHandle = document.createElementNS(SEL_NS, 'circle');
    rotHandle.setAttribute('cx', stemX);
    rotHandle.setAttribute('cy', rcy);
    rotHandle.setAttribute('r', HANDLE_R);
    rotHandle.classList.add('sel-handle', 'sel-handle--rot');
    rotHandle.addEventListener('mousedown', e => onRotateMousedown(e, u));
    g.appendChild(rotHandle);

    // Arc arrow — smaller, radius 2px, 270° sweep with arrowhead
    const ar = HANDLE_R - 3;
    const arcPath = document.createElementNS(SEL_NS, 'path');
    const sx = stemX,      sy = rcy - ar;  // top (start)
    const ex = stemX - ar, ey = rcy;       // left (end, after 270° CW)
    const arrowSize = 1.2;
    const d = [
        `M ${sx} ${sy}`,
        `A ${ar} ${ar} 0 1 1 ${ex} ${ey}`,
        `L ${ex - arrowSize} ${ey - arrowSize}`,
        `M ${ex} ${ey}`,
        `L ${ex + arrowSize} ${ey - arrowSize}`,
    ].join(' ');
    arcPath.setAttribute('d', d);
    arcPath.classList.add('sel-rot-icon');
    arcPath.addEventListener('mousedown', e => onRotateMousedown(e, u));
    g.appendChild(arcPath);

    svg.appendChild(g);
}

function removeSelectionOverlay() {
    svg.querySelectorAll('.sel-overlay').forEach(n => n.remove());
}

function deselect() {
    selected = null;
    removeSelectionOverlay();
    svg.querySelectorAll('.equip-node--selected').forEach(n => n.classList.remove('equip-node--selected'));
}

function selectUnit(u) {
    deselect();
    selected = String(u.id);
    const node = svg.querySelector(`.equip-node[data-unit-id="${u.id}"]`);
    if (node) {
        node.classList.add('equip-node--selected');
        // Move to end of SVG so it renders on top of all other nodes
        svg.appendChild(node);
    }
    renderSelectionOverlay(u);
}

// ── Drag-to-rotate: hold + drag in circle, snaps to 0/90/180/270 ──
function onRotateMousedown(e, u) {
    e.stopPropagation();
    e.preventDefault();

    // Center of the unit in SVG space (unrotated)
    const cx = u.x + u.w / 2;
    const cy = u.y + u.h / 2;

    const onMove = ev => {
        const pt      = toSVG(ev.clientX, ev.clientY);
        // Angle from center to mouse, 0 = up, clockwise
        const dx      = pt.x - cx;
        const dy      = pt.y - cy;
        const angleDeg = (Math.atan2(dy, dx) * 180 / Math.PI + 90 + 360) % 360;
        // Snap to nearest 90°
        const snapped = Math.round(angleDeg / 90) * 90 % 360;
        if (snapped !== u.rotation) {
            u.rotation = snapped;
            const node = svg.querySelector(`.equip-node[data-unit-id="${u.id}"]`);
            if (node) applyTransform(node, u);
            renderSelectionOverlay(u);
        }
    };

    const onUp = () => {
        document.removeEventListener('mousemove', onMove);
        document.removeEventListener('mouseup',   onUp);
        svg.classList.remove('svg--rotating');
    };

    svg.classList.add('svg--rotating');
    document.addEventListener('mousemove', onMove);
    document.addEventListener('mouseup',   onUp);
}

// ── Drag to move ───────────────────────────────────────────────
function onNodeMousedown(e, u) {
    if (e.target.classList.contains('sel-handle')) return;
    e.stopPropagation();
    e.preventDefault();

    const start  = toSVG(e.clientX, e.clientY);
    const origX  = u.x;
    const origY  = u.y;
    let   moved  = false;

    const onMove = ev => {
        moved = true;
        const pt = toSVG(ev.clientX, ev.clientY);
        u.x = Math.round(Math.max(0, Math.min(SVG_W - u.w, origX + pt.x - start.x)));
        u.y = Math.round(Math.max(0, Math.min(SVG_H - u.h, origY + pt.y - start.y)));
        updateNodeGeometry(u);
    };

    const onUp = () => {
        document.removeEventListener('mousemove', onMove);
        document.removeEventListener('mouseup',   onUp);
        if (!moved) {
            if (selected === String(u.id)) { deselect(); } else { selectUnit(u); }
        } else {
            if (selected === String(u.id)) renderSelectionOverlay(u);
        }
    };

    document.addEventListener('mousemove', onMove);
    document.addEventListener('mouseup',   onUp);
}

// ── Resize via corner handles (aspect-ratio locked) ───────────
// Operates on the visual AABB, converts back to u.x/y/w/h.
// For 90°/270° the stored w/h are swapped vs the visual bb.
function onResizeMousedown(e, u, corner) {
    e.stopPropagation();
    e.preventDefault();

    const origBB   = aabb(u);
    const swap     = u.rotation === 90 || u.rotation === 270;
    const ratio    = origBB.w / origBB.h; // visual aspect ratio

    const onMove = ev => {
        const pt = toSVG(ev.clientX, ev.clientY);
        let bx = origBB.x, by = origBB.y, bw, bh;

        if (corner === 'se') {
            bw = Math.max(MIN_SIZE, pt.x - origBB.x);
            bh = bw / ratio;
        } else if (corner === 'nw') {
            bw = Math.max(MIN_SIZE, origBB.x + origBB.w - pt.x);
            bh = bw / ratio;
            bx = origBB.x + origBB.w - bw;
            by = origBB.y + origBB.h - bh;
        } else if (corner === 'ne') {
            bw = Math.max(MIN_SIZE, pt.x - origBB.x);
            bh = bw / ratio;
            by = origBB.y + origBB.h - bh;
        } else if (corner === 'sw') {
            bw = Math.max(MIN_SIZE, origBB.x + origBB.w - pt.x);
            bh = bw / ratio;
            bx = origBB.x + origBB.w - bw;
        }

        // Convert bb back to stored u.x/y/w/h
        // Center stays at bb center; stored w/h = visual bw/bh but swapped for 90/270
        const cx = bx + bw / 2;
        const cy = by + bh / 2;
        const sw = swap ? bh : bw;
        const sh = swap ? bw : bh;
        u.w = Math.round(sw);
        u.h = Math.round(sh);
        u.x = Math.round(cx - sw / 2);
        u.y = Math.round(cy - sh / 2);

        updateNodeGeometry(u);
    };

    const onUp = () => {
        document.removeEventListener('mousemove', onMove);
        document.removeEventListener('mouseup',   onUp);
        renderSelectionOverlay(u);
    };

    document.addEventListener('mousemove', onMove);
    document.addEventListener('mouseup',   onUp);
}

// ── Update node geometry after move/resize/rotate ────────────
function updateNodeGeometry(u) {
    const node = svg.querySelector(`.equip-node[data-unit-id="${u.id}"]`);
    if (!node) return;
    // No transform on the <g> itself
    node.removeAttribute('transform');

    const imgWrap = node.querySelector('g');
    const img     = node.querySelector('image');
    if (img) {
        img.setAttribute('x',      u.x);
        img.setAttribute('y',      u.y);
        img.setAttribute('width',  u.w);
        img.setAttribute('height', u.h);
    }
    if (imgWrap) applyImgTransform(imgWrap, u);

    const bb   = aabb(u);
    const rect = node.querySelector('rect.equip-tint');
    if (rect) {
        rect.setAttribute('x',      bb.x);
        rect.setAttribute('y',      bb.y);
        rect.setAttribute('width',  bb.w);
        rect.setAttribute('height', bb.h);
    }

    if (selected === String(u.id)) renderSelectionOverlay(u);
}

// ── Click on SVG background → deselect ───────────────────────
svg.addEventListener('click', e => {
    if (!editMode) return;
    if (!e.target.closest('.equip-node') && !e.target.closest('.sel-overlay')) {
        deselect();
    }
});

// ── Delete selected unit with Backspace/Delete ────────────────
document.addEventListener('keydown', e => {
    if (!editMode || !selected) return;
    if (e.key === 'Delete' || e.key === 'Backspace') {
        const node = svg.querySelector(`.equip-node[data-unit-id="${selected}"]`);
        if (node) node.remove();
        delete placed[selected];
        deselect();
    }
});

// ── Picker drag-and-drop ──────────────────────────────────────
function initPickerDrag() {
    document.querySelectorAll('.picker-item').forEach(item => {
        item.addEventListener('dragstart', e => {
            e.dataTransfer.setData('eq-id',    item.dataset.eqId);
            e.dataTransfer.setData('eq-name',  item.dataset.eqName);
            e.dataTransfer.setData('eq-photo', item.dataset.eqPhoto);
            e.dataTransfer.setData('eq-w',     item.dataset.eqW);
            e.dataTransfer.setData('eq-h',     item.dataset.eqH);
            e.dataTransfer.effectAllowed = 'copy';
        });
    });

    svg.addEventListener('dragover', e => {
        if (!editMode) return;
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
    });

    svg.addEventListener('drop', e => {
        if (!editMode) return;
        e.preventDefault();

        const eqId    = e.dataTransfer.getData('eq-id');
        const eqName  = e.dataTransfer.getData('eq-name');
        const eqPhoto = e.dataTransfer.getData('eq-photo');
        const eqW     = parseInt(e.dataTransfer.getData('eq-w')) || DEFAULT_UNIT_W;
        const eqH     = parseInt(e.dataTransfer.getData('eq-h')) || DEFAULT_UNIT_H;
        if (!eqId) return;

        const pt = toSVG(e.clientX, e.clientY);
        const x  = Math.round(Math.max(0, Math.min(SVG_W - eqW, pt.x - eqW / 2)));
        const y  = Math.round(Math.max(0, Math.min(SVG_H - eqH, pt.y - eqH / 2)));

        const id = tempId--;
        const u  = { id, equipment_id: parseInt(eqId), equipment_name: eqName, photo: eqPhoto, x, y, w: eqW, h: eqH, rotation: 0 };
        placed[String(id)] = u;

        const node = buildNode(u);
        svg.appendChild(node);
        selectUnit(u);
    });
}

// ── Save ──────────────────────────────────────────────────────
saveBtn.addEventListener('click', async () => {
    deselect();
    saveBtn.disabled    = true;
    saveBtn.textContent = 'Saving…';

    const units = Object.values(placed).map(u => ({
        id:       u.id > 0 ? u.id : null,
        eq_id:    u.equipment_id,
        x:        u.x,
        y:        u.y,
        w:        u.w,
        h:        u.h,
        rotation: u.rotation,
    }));

    try {
        const res  = await fetch('/src/actions/action_save_equipment_layout.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ csrf_token: CSRF_TOKEN, units }),
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.error || 'Save failed');
        window.location.reload();
    } catch (err) {
        alert('Could not save layout: ' + err.message);
        saveBtn.disabled    = false;
        saveBtn.textContent = 'Save Layout';
    }
});

editBtn.addEventListener('click', () => {
    if (editMode) exitEditMode(true);
    else enterEditMode();
});

initPlaced();
initPickerDrag();

