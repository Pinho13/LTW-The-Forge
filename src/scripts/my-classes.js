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
                document.getElementById('load-more-container').classList.add('hidden');
            }
        } catch {
            // leave button re-enabled so user can retry
        } finally {
            loadMoreBtn.disabled = false;
            loadMoreBtn.textContent = 'Load more';
        }
    });
}
