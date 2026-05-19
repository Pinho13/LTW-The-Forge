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
let staleIndex = 0;

function formatStaleDate(datetimeStr) {
    const d = new Date(datetimeStr);
    return d.toLocaleDateString('en-US', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function populateStaleModal(index) {
    const e = STALE_ENROLLMENTS[index];
    document.getElementById('stale-class-name').textContent = e.class_name;
    document.getElementById('stale-trainer-name').textContent = e.trainer_name || 'TBA';
    document.getElementById('stale-class-date').textContent = formatStaleDate(e.datetime);
    document.getElementById('stale-progress').textContent = `${index + 1} of ${STALE_ENROLLMENTS.length}`;
    document.getElementById('stale-error').textContent = '';
}

async function resolveStale(status) {
    const e = STALE_ENROLLMENTS[staleIndex];
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

    // Update status badge in DOM if the card is visible
    const card = document.querySelector(`[data-enrollment-id="${e.id}"]`);
    if (card) {
        const badge = card.querySelector('.status');
        if (badge) {
            badge.className = `status status--${status}`;
            badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        }
    }

    staleIndex++;
    if (staleIndex < STALE_ENROLLMENTS.length) {
        populateStaleModal(staleIndex);
    } else {
        closeModal(staleModal);
        if (staleBanner) staleBanner.remove();
    }
}

if (STALE_ENROLLMENTS.length > 0) {
    staleModal.querySelector('.auth-modal__close').addEventListener('click', () => closeModal(staleModal));
    document.getElementById('stale-later-btn').addEventListener('click', e => {
        e.preventDefault();
        closeModal(staleModal);
    });
    document.getElementById('stale-completed-btn').addEventListener('click', () => resolveStale('completed'));
    document.getElementById('stale-missed-btn').addEventListener('click', () => resolveStale('missed'));

    document.getElementById('stale-open-btn').addEventListener('click', () => {
        staleIndex = 0;
        populateStaleModal(0);
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
