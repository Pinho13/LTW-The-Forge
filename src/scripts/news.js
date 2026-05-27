const backdrop  = document.getElementById('page-backdrop');
const postModal = document.getElementById('post-modal');
const postClose = document.getElementById('post-close');
const newBtn    = document.getElementById('new-post-btn');

function openModal() {
    postModal.show();
    backdrop.classList.add('modal-backdrop--visible');
}
function closeModal() {
    postModal.close();
    backdrop.classList.remove('modal-backdrop--visible');
}

newBtn.addEventListener('click', openModal);
postClose.addEventListener('click', closeModal);
backdrop.addEventListener('click', closeModal);

document.getElementById('news-list')?.addEventListener('submit', async e => {
    const form = e.target.closest('form');
    if (!form) return;

    const action      = form.action;
    const isDelete    = action.includes('delete_announcement');
    const isTogglePin = action.includes('toggle_pin');
    if (!isDelete && !isTogglePin) return;

    e.preventDefault();

    const item = form.closest('[data-announcement-id]');
    const id   = item?.dataset.announcementId;
    if (!id) return;

    if (isDelete && !confirm('Delete this announcement?')) return;

    const btn  = form.querySelector('button');
    btn.disabled = true;

    try {
        const body = new URLSearchParams({ announcement_id: id, csrf_token: CSRF_TOKEN });
        const res  = await fetch(action, { method: 'POST', body });
        const data = await res.json();

        if (!res.ok || !data.success) {
            btn.disabled = false;
            alert(data.error || 'Something went wrong.');
            return;
        }

        if (isDelete) {
            item.remove();
        } else {
            const pinned = data.pinned;
            item.classList.toggle('news-item--pinned', pinned);

            const pinIcon = item.querySelector('.news-pin');
            if (pinned && !pinIcon) {
                item.querySelector('.news-item__title').insertAdjacentHTML(
                    'afterbegin', '<span class="news-pin" title="Pinned">&#128204;</span>'
                );
            } else if (!pinned && pinIcon) {
                pinIcon.remove();
            }

            btn.textContent  = pinned ? 'Unpin' : 'Pin';
            btn.disabled     = false;
        }
    } catch {
        btn.disabled = false;
        alert('Network error. Please try again.');
    }
});
