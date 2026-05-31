const backdrop   = document.getElementById('page-backdrop');
const classModal = document.getElementById('class-modal');

if (classModal) {

function openModal() {
    classModal.show();
    backdrop.classList.add('modal-backdrop--visible');
}
function closeModal() {
    classModal.close();
    backdrop.classList.remove('modal-backdrop--visible');
}

document.getElementById('class-modal-close').addEventListener('click', closeModal);
backdrop.addEventListener('click', closeModal);

function setIntensityDots(container, value) {
    container.innerHTML = '';
    for (let i = 1; i <= 5; i++) {
        const dot = document.createElement('span');
        dot.className = 'intensity-dot' + (i <= value ? ' filled' : '');
        container.appendChild(dot);
    }
}

function buildStars(rating) {
    const container = document.createElement('span');
    container.className = 'class-modal__stars';
    for (let i = 1; i <= 5; i++) {
        const star = document.createElement('span');
        star.className = 'class-modal__star' + (i <= Math.round(rating) ? ' filled' : '');
        star.textContent = '★';
        container.appendChild(star);
    }
    return container;
}

function buildActionArea(card, sessionId, currentStatus, spotsLeft) {
    const area = document.getElementById('modal-action-area');
    area.innerHTML = '';

    const isPast = false; // calendar only shows current week; past sessions have no future datetime

    if (currentStatus === 'enrolled') {
        const btn = document.createElement('button');
        btn.className = 'btn-danger modal-action-btn';
        btn.textContent = 'Cancel enrollment';
        btn.addEventListener('click', () => handleCancel(card, sessionId, btn));
        area.appendChild(btn);
    } else if (currentStatus === 'waitlisted') {
        const btn = document.createElement('button');
        btn.className = 'btn-outline modal-action-btn';
        btn.textContent = 'Leave waitlist';
        btn.addEventListener('click', () => handleCancel(card, sessionId, btn));
        area.appendChild(btn);
    } else {
        const btn = document.createElement('button');
        btn.className = 'btn-primary modal-action-btn';
        btn.textContent = spotsLeft > 0 ? 'Enroll' : 'Join waitlist';
        btn.addEventListener('click', () => handleEnroll(card, sessionId, btn));
        area.appendChild(btn);
    }
}

async function handleEnroll(card, sessionId, btn) {
    btn.disabled = true;
    btn.textContent = 'Enrolling…';

    const data = new URLSearchParams({ csrf_token: CSRF_TOKEN, session_id: sessionId });
    const res  = await fetch('/src/actions/action_enroll.php', { method: 'POST', body: data });
    const json = await res.json();

    if (!res.ok || !json.success) {
        btn.disabled = false;
        btn.textContent = 'Enroll';
        showModalError(json.error || 'Something went wrong.');
        return;
    }

    const newStatus = json.status; // 'enrolled' | 'waitlisted'
    updateCardStatus(card, newStatus, json.waitlist_position ?? null);
    closeModal();
}

async function handleCancel(card, sessionId, btn) {
    btn.disabled = true;
    btn.textContent = 'Cancelling…';

    const data = new URLSearchParams({ csrf_token: CSRF_TOKEN, session_id: sessionId });
    const res  = await fetch('/src/actions/action_cancel_session.php', { method: 'POST', body: data });
    const json = await res.json();

    if (!res.ok || !json.success) {
        btn.disabled = false;
        btn.textContent = 'Cancel enrollment';
        showModalError(json.error || 'Something went wrong.');
        return;
    }

    const prevStatus = card.dataset.status || null;
    updateCardStatus(card, null, null, prevStatus);
    closeModal();
}

function updateCardStatus(card, status, waitlistPosition = null, prevStatus = null) {
    card.dataset.status = status ?? '';
    if (waitlistPosition !== null) card.dataset.waitlistPosition = waitlistPosition;
    card.classList.remove('class-card--enrolled', 'class-card--waitlisted', 'class-card--full');

    const spotsSpan = card.querySelector('footer > span');
    const spots     = parseInt(card.dataset.spots, 10);

    if (status === 'enrolled') {
        card.classList.add('class-card--enrolled');
        if (spotsSpan) spotsSpan.textContent = 'Enrolled';
    } else if (status === 'waitlisted') {
        card.classList.add('class-card--waitlisted');
        if (spotsSpan) spotsSpan.textContent = 'Waitlisted';
    } else {
        // only free a spot if the user was actually enrolled (not waitlisted)
        const newSpots = (prevStatus === 'enrolled') ? spots + 1 : spots;
        card.dataset.spots = newSpots;
        if (newSpots <= 0) {
            card.classList.add('class-card--full');
            if (spotsSpan) spotsSpan.textContent = 'Full';
        } else {
            if (spotsSpan) spotsSpan.textContent = newSpots + ' left';
        }
    }
}

function showModalError(msg) {
    let err = classModal.querySelector('.modal-error');
    if (!err) {
        err = document.createElement('p');
        err.className = 'modal-error auth-modal__error';
        document.getElementById('modal-action-area').before(err);
    }
    err.textContent = msg;
}

} // end member-only block (stack nav shared below)

// ── Stack navigation ──────────────────────────────────────────
function stackGoTo(stack, nextIdx, direction) {
    const cards  = stack.querySelectorAll('.class-stack__card');
    const dots   = stack.querySelectorAll('.class-stack__dot');
    const curIdx = parseInt(stack.dataset.index, 10) || 0;
    if (nextIdx === curIdx || cards.length < 2) return;

    const exitCls = direction === 'forward' ? 'class-stack__card--exit-left' : 'class-stack__card--exit-right';
    const current = cards[curIdx];
    const next    = cards[nextIdx];

    const enterFrom = direction === 'forward' ? '100%' : '-100%';
    next.style.setProperty('--stack-enter-from', enterFrom);

    current.classList.remove('class-stack__card--active');
    current.classList.add(exitCls);
    current.addEventListener('animationend', () => {
        current.classList.remove(exitCls);
    }, { once: true });

    next.classList.add('class-stack__card--active');
    next.addEventListener('animationend', () => {
        next.style.removeProperty('--stack-enter-from');
    }, { once: true });

    stack.dataset.index = nextIdx;
    dots.forEach((d, i) => d.classList.toggle('class-stack__dot--active', i === nextIdx));
}

// Dot clicks
document.addEventListener('click', e => {
    const dot = e.target.closest('.class-stack__dot');
    if (!dot) return;
    const stack = dot.closest('.class-stack');
    const dots  = [...stack.querySelectorAll('.class-stack__dot')];
    const cur   = parseInt(stack.dataset.index, 10) || 0;
    const next  = dots.indexOf(dot);
    if (next === cur) return;
    stackGoTo(stack, next, next > cur ? 'forward' : 'backward');
    e.stopPropagation();
});

// Mouse drag + touch swipe on each stack
document.querySelectorAll('.class-stack').forEach(stack => {
    let startX = null;
    let dragged = false;

    function onStart(x) { startX = x; dragged = false; }
    function onEnd(x) {
        if (startX === null) return;
        const dx = x - startX;
        startX = null;
        if (Math.abs(dx) < 30) return;
        dragged = true;
        const cards = stack.querySelectorAll('.class-stack__card');
        const cur = parseInt(stack.dataset.index, 10) || 0;
        if (dx < 0) {
            stackGoTo(stack, (cur + 1) % cards.length, 'forward');
        } else {
            stackGoTo(stack, (cur - 1 + cards.length) % cards.length, 'backward');
        }
    }

    stack.addEventListener('mousedown',  e => onStart(e.clientX));
    stack.addEventListener('mouseup',    e => onEnd(e.clientX));
    stack.addEventListener('mouseleave', () => { startX = null; });

    stack.addEventListener('touchstart', e => onStart(e.touches[0].clientX), { passive: true });
    stack.addEventListener('touchend',   e => onEnd(e.changedTouches[0].clientX));

    // expose dragged state for the modal click guard below
    stack._wasDragged = () => { const v = dragged; dragged = false; return v; };
});

if (classModal) {
document.addEventListener('click', e => {
    const card = e.target.closest('.class-card');
    if (!card || e.target.closest('button') || e.target.closest('.class-stack__dot')) return;
    if (card.classList.contains('class-card--admin')) return;

    // Don't open modal if the user just dragged the stack
    const stack = card.closest('.class-stack');
    if (stack?._wasDragged?.()) return;

    const sessionId    = card.dataset.sessionId;
    const className    = card.dataset.className;
    const trainer      = card.dataset.trainer;
    const room         = card.dataset.room;
    const time         = card.dataset.time;
    const intensity    = parseInt(card.dataset.intensity, 10);
    const spotsLeft    = parseInt(card.dataset.spots, 10);
    const capacity     = parseInt(card.dataset.capacity, 10);
    const status          = card.dataset.status || null;
    const waitlistPosition = card.dataset.waitlistPosition ? parseInt(card.dataset.waitlistPosition, 10) : null;
    const type         = card.dataset.type;
    const avgRating    = card.dataset.avgRating ? parseFloat(card.dataset.avgRating) : null;
    const reviewCount  = parseInt(card.dataset.reviewCount, 10) || 0;

    document.getElementById('modal-class-name').textContent = className;
    document.getElementById('modal-meta').textContent = `${time} · ${room} · ${trainer}`;
    document.getElementById('modal-type').textContent = type || '';
    setIntensityDots(document.getElementById('modal-intensity'), intensity);

    const ratingEl = document.getElementById('modal-rating');
    ratingEl.innerHTML = '';
    if (avgRating !== null && reviewCount > 0) {
        ratingEl.appendChild(buildStars(avgRating));
        const label = document.createElement('span');
        label.className = 'class-modal__rating-label';
        label.innerHTML = `${avgRating.toFixed(1)}<span class="class-modal__sep">·</span>${reviewCount} review${reviewCount !== 1 ? 's' : ''}`;
        ratingEl.appendChild(label);
    } else {
        ratingEl.appendChild(buildStars(0));
        const label = document.createElement('span');
        label.className = 'class-modal__rating-label';
        label.textContent = 'No reviews yet';
        ratingEl.appendChild(label);
    }
    ratingEl.hidden = false;

    const spotsEl  = document.getElementById('modal-spots');
    if (status === 'enrolled') {
        spotsEl.textContent = 'You are enrolled in this class.';
    } else if (status === 'waitlisted') {
        spotsEl.textContent = waitlistPosition ? `You are on the waitlist — position #${waitlistPosition}.` : 'You are on the waitlist for this class.';
    } else {
        spotsEl.textContent = spotsLeft > 0
            ? `${spotsLeft} spot${spotsLeft !== 1 ? 's' : ''} left`
            : 'Class is full — you can join the waitlist.';
    }

    // Clear any previous error
    const prevErr = classModal.querySelector('.modal-error');
    if (prevErr) prevErr.remove();

    buildActionArea(card, sessionId, status, spotsLeft);
    openModal();
});

// Auto-open modal when URL contains #session-{id}
(function () {
    const m = window.location.hash.match(/^#session-(\d+)$/);
    if (!m) return;
    const sid  = m[1];
    const card = document.querySelector(`.class-card[data-session-id="${sid}"]`);
    if (!card) return;

    // If the card is inside a stack, activate it instantly (no animation)
    const stack = card.closest('.class-stack');
    if (stack) {
        const cards = [...stack.querySelectorAll('.class-stack__card')];
        const dots  = [...stack.querySelectorAll('.class-stack__dot')];
        const idx   = cards.indexOf(card);
        if (idx > 0) {
            cards.forEach((c, i) => c.classList.toggle('class-stack__card--active', i === idx));
            dots.forEach((d, i) => d.classList.toggle('class-stack__dot--active', i === idx));
            stack.dataset.index = idx;
        }
    }

    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    card.dispatchEvent(new MouseEvent('click', { bubbles: true }));
})();

} // end classModal block
