const cancelModal = document.getElementById('cancel-modal');
const backdrop = document.getElementById('page-backdrop');
const cancelEnrollmentId = document.getElementById('cancel-enrollment-id');
const cancelClassName = document.getElementById('cancel-class-name');

function openModal(dialog) {
    dialog.show();
    backdrop.classList.add('modal-backdrop--visible');
}

function closeModal(dialog) {
    dialog.close();
    backdrop.classList.remove('modal-backdrop--visible');
}

backdrop.addEventListener('click', () => closeModal(cancelModal));
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

