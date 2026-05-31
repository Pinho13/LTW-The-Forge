const backdrop = document.getElementById('page-backdrop');

function openModal(dialog) {
    dialog.show();
    backdrop.classList.add('modal-backdrop--visible');
}

function closeModal(dialog) {
    dialog.close();
    backdrop.classList.remove('modal-backdrop--visible');
}

// Cancel modal
const cancelModal = document.getElementById('cancel-modal');
const cancelEnrollmentId = document.getElementById('cancel-enrollment-id');
const cancelClassName = document.getElementById('cancel-class-name');

backdrop.addEventListener('click', () => {
    if (cancelModal.open) closeModal(cancelModal);
    if (reviewModal.open) closeModal(reviewModal);
    if (staleModal.open) closeModal(staleModal);
});

cancelModal.querySelector('.auth-modal__close').addEventListener('click', () => closeModal(cancelModal));
document.getElementById('cancel-keep-btn').addEventListener('click', e => {
    e.preventDefault();
    closeModal(cancelModal);
});

cancelModal.querySelector('form').addEventListener('submit', async e => {
    e.preventDefault();
    const enrollmentId = cancelEnrollmentId.value;
    const submitBtn    = cancelModal.querySelector('[type="submit"]');

    submitBtn.disabled    = true;
    submitBtn.textContent = 'Cancelling…';

    try {
        const body = new URLSearchParams({ enrollment_id: enrollmentId, csrf_token: CSRF_TOKEN });
        const res  = await fetch('../actions/action_cancel_enrollment.php', { method: 'POST', body });
        const data = await res.json();

        if (!res.ok || !data.success) {
            submitBtn.disabled    = false;
            submitBtn.textContent = 'Yes, cancel';
            alert(data.error || 'Could not cancel enrollment.');
            return;
        }

        closeModal(cancelModal);
        window.location.reload();
    } catch {
        submitBtn.disabled    = false;
        submitBtn.textContent = 'Yes, cancel';
        alert('Network error. Please try again.');
    }
});

document.addEventListener('click', e => {
    const btn = e.target.closest('[data-cancel-id]');
    if (!btn) return;
    cancelEnrollmentId.value = btn.dataset.cancelId;
    cancelClassName.textContent = btn.dataset.cancelName;
    openModal(cancelModal);
});

// Review modal
const reviewModal = document.getElementById('review-modal');
const reviewClassId = document.getElementById('review-class-id');
const reviewClassName = document.getElementById('review-class-name');
const reviewTrainerName = document.getElementById('review-trainer-name');
const reviewRating = document.getElementById('review-rating');
const reviewComment = document.getElementById('review-comment');
const reviewModalTitle = document.getElementById('review-modal-title');
const reviewSubmitBtn = document.getElementById('review-submit-btn');
const stars = reviewModal.querySelectorAll('.star');

function setStars(value) {
    reviewRating.value = value;
    stars.forEach(s => {
        s.classList.toggle('star--active', parseInt(s.dataset.value, 10) <= value);
    });
}

stars.forEach(star => {
    star.addEventListener('click', () => setStars(parseInt(star.dataset.value, 10)));
    star.addEventListener('mouseenter', () => {
        const val = parseInt(star.dataset.value, 10);
        stars.forEach(s => s.classList.toggle('star--hover', parseInt(s.dataset.value, 10) <= val));
    });
    star.addEventListener('mouseleave', () => stars.forEach(s => s.classList.remove('star--hover')));
});

reviewModal.querySelector('.auth-modal__close').addEventListener('click', () => closeModal(reviewModal));

document.addEventListener('click', e => {
    const btn = e.target.closest('[data-review-class-id]');
    if (!btn) return;

    const hasReview = btn.dataset.reviewHasReview === '1';
    reviewClassId.value = btn.dataset.reviewClassId;
    reviewClassName.textContent = btn.dataset.reviewClassName;
    reviewTrainerName.textContent = btn.dataset.reviewTrainerName || 'TBA';
    reviewModalTitle.textContent = hasReview ? 'Update Review' : 'Leave a Review';
    reviewSubmitBtn.textContent = hasReview ? 'Update review' : 'Submit review';

    const existingRating = parseInt(btn.dataset.reviewRating, 10) || 0;
    setStars(existingRating);
    reviewComment.value = btn.dataset.reviewComment || '';

    openModal(reviewModal);
});

const reviewForm = document.getElementById('review-form');
reviewForm.addEventListener('submit', e => {
    if (parseInt(reviewRating.value, 10) < 1) {
        e.preventDefault();
        reviewModal.querySelector('.star-rating').classList.add('star-rating--error');
    }
});

reviewModal.querySelector('.star-rating').addEventListener('click', () => {
    reviewModal.querySelector('.star-rating').classList.remove('star-rating--error');
});

// Stale enrollment modal
const staleModal = document.getElementById('stale-modal');
const staleBanner = document.getElementById('stale-banner');
const staleBannerText = staleBanner?.querySelector('.stale-banner__text');

// Work on a mutable copy — splice entries as they're resolved
const pendingStale = STALE_ENROLLMENTS ? [...STALE_ENROLLMENTS] : [];

function formatStaleDate(datetimeStr) {
    const d = new Date(datetimeStr);
    return d.toLocaleDateString('en-US', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function updateStaleBanner() {
    if (!staleBanner) return;
    if (pendingStale.length === 0) {
        staleBanner.remove();
        return;
    }
    if (staleBannerText) {
        staleBannerText.textContent = pendingStale.length === 1
            ? '1 class is awaiting a status update.'
            : `${pendingStale.length} classes are awaiting a status update.`;
    }
}

function populateStaleModal() {
    const e = pendingStale[0];
    document.getElementById('stale-class-name').textContent = e.class_name;
    document.getElementById('stale-trainer-name').textContent = e.trainer_name || 'TBA';
    document.getElementById('stale-class-date').textContent = formatStaleDate(e.datetime);
    document.getElementById('stale-progress').textContent = `1 of ${pendingStale.length}`;
    document.getElementById('stale-error').textContent = '';
}

async function resolveStale(status) {
    const e = pendingStale[0];
    const staleError = document.getElementById('stale-error');

    const body = new URLSearchParams({ enrollment_id: e.id, status, csrf_token: CSRF_TOKEN });
    try {
        const res = await fetch('../actions/action_update_enrollment_status.php', { method: 'POST', body });
        const data = await res.json();

        if (!res.ok || !data.success) {
            staleError.textContent = data.error || 'Something went wrong.';
            return;
        }
    } catch {
        staleError.textContent = 'Network error. Please try again.';
        return;
    }

    // Update status badge in the DOM card if visible
    const card = document.querySelector(`[data-enrollment-id="${e.id}"]`);
    if (card) {
        const badge = card.querySelector('.status');
        if (badge) {
            badge.className = `status status--${status}`;
            badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        }
    }

    // Remove resolved entry and update banner
    pendingStale.splice(0, 1);
    updateStaleBanner();

    if (pendingStale.length > 0) {
        populateStaleModal();
    } else {
        closeModal(staleModal);
    }
}

if (pendingStale.length > 0) {
    staleModal.querySelector('.auth-modal__close').addEventListener('click', () => closeModal(staleModal));
    document.getElementById('stale-later-btn').addEventListener('click', e => {
        e.preventDefault();
        closeModal(staleModal);
    });
    document.getElementById('stale-completed-btn').addEventListener('click', () => resolveStale('completed'));
    document.getElementById('stale-missed-btn').addEventListener('click', () => resolveStale('missed'));

    document.getElementById('stale-open-btn').addEventListener('click', () => {
        populateStaleModal();
        openModal(staleModal);
    });

    document.getElementById('stale-dismiss-btn').addEventListener('click', () => {
        if (staleBanner) staleBanner.remove();
    });
}

// Load more
const loadMoreBtn = document.getElementById('load-more-btn');
if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', async () => {
        const offset = parseInt(loadMoreBtn.dataset.offset, 10);
        loadMoreBtn.disabled = true;
        loadMoreBtn.textContent = 'Loading…';

        try {
            const tab = loadMoreBtn.dataset.tab;
            const res = await fetch(`../actions/fetch_enrollments.php?tab=${tab}&offset=${offset}`);
            const data = await res.json();

            const list = document.getElementById('enrollment-list');
            list.insertAdjacentHTML('beforeend', data.html);

            loadMoreBtn.dataset.offset = offset + 30;

            if (!data.hasMore) {
                document.getElementById('load-more-container').style.display = 'none';
            }
        } catch {
            // leave button re-enabled so user can retry
        } finally {
            loadMoreBtn.disabled = false;
            loadMoreBtn.textContent = 'Load more';
        }
    });
}

// Sort
const sortBtns = document.querySelectorAll('.enrollment-sort__btn');
const enrollmentList = document.getElementById('enrollment-list');

function applySort(by) {
    if (!enrollmentList) return;
    const items = [...enrollmentList.querySelectorAll('.enrollment')];
    items.sort((a, b) => {
        if (by === 'type') {
            const na = a.querySelector('.enrollment__name')?.textContent || '';
            const nb = b.querySelector('.enrollment__name')?.textContent || '';
            return na.localeCompare(nb);
        }
        if (by === 'intensity') {
            const diff = parseInt(b.dataset.intensity, 10) - parseInt(a.dataset.intensity, 10);
            return diff !== 0 ? diff : a.dataset.date.localeCompare(b.dataset.date);
        }
        return a.dataset.date.localeCompare(b.dataset.date);
    });
    items.forEach(item => enrollmentList.appendChild(item));
}

sortBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        sortBtns.forEach(b => b.classList.remove('enrollment-sort__btn--active'));
        btn.classList.add('enrollment-sort__btn--active');
        applySort(btn.dataset.sort);
    });
});
