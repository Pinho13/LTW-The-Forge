const backdrop  = document.getElementById('page-backdrop');
const postModal = document.getElementById('post-modal');
const postClose = document.getElementById('post-close');
const newBtn    = document.getElementById('new-post-btn');

const deleteModal      = document.getElementById('delete-confirm-modal');
const deleteCancelBtn  = document.getElementById('delete-cancel-btn');
const deleteConfirmBtn = document.getElementById('delete-confirm-btn');

const editModal = document.getElementById('edit-modal');
const editClose = document.getElementById('edit-close');

const pinSwapModal = document.getElementById('pin-swap-modal');
const pinSwapClose = document.getElementById('pin-swap-close');
const pinSwapList  = document.getElementById('pin-swap-list');

function openModal() {
    postModal.show();
    backdrop.classList.add('modal-backdrop--visible');
}
function closeModal() {
    postModal.close();
    backdrop.classList.remove('modal-backdrop--visible');
}
function openEditModal(btn) {
    document.getElementById('edit-announcement-id').value = btn.dataset.id;
    document.getElementById('edit-title').value           = btn.dataset.title;
    document.getElementById('edit-body').value            = btn.dataset.body;
    document.getElementById('edit-read-time').value       = btn.dataset.readTime;
    document.getElementById('edit-pinned').checked        = btn.dataset.pinned === '1';
    const typeSelect = document.getElementById('edit-type');
    [...typeSelect.options].forEach(o => { o.selected = o.value === btn.dataset.type; });
    editModal.show();
    backdrop.classList.add('modal-backdrop--visible');
}
function closeEditModal() {
    editModal.close();
    backdrop.classList.remove('modal-backdrop--visible');
}

newBtn.addEventListener('click', openModal);
postClose.addEventListener('click', closeModal);
editClose.addEventListener('click', closeEditModal);
pinSwapClose.addEventListener('click', () => { pinSwapModal.close(); backdrop.classList.remove('modal-backdrop--visible'); });
backdrop.addEventListener('click', () => {
    closeModal();
    closeEditModal();
    if (pinSwapModal.open) { pinSwapModal.close(); backdrop.classList.remove('modal-backdrop--visible'); }
    if (deleteModal.open)  { deleteModal.close();  backdrop.classList.remove('modal-backdrop--visible'); }
});

document.getElementById('news-list')?.addEventListener('click', e => {
    const btn = e.target.closest('.js-edit-btn');
    if (btn) openEditModal(btn);
});
deleteCancelBtn.addEventListener('click', () => { deleteModal.close(); backdrop.classList.remove('modal-backdrop--visible'); });

let pendingDeleteForm = null;

deleteConfirmBtn.addEventListener('click', async () => {
    deleteModal.close();
    backdrop.classList.remove('modal-backdrop--visible');
    if (!pendingDeleteForm) return;
    await submitNewsForm(pendingDeleteForm, true);
    pendingDeleteForm = null;
});

async function handlePinToggle(form) {
    const item = form.closest('[data-announcement-id]');
    const id   = item?.dataset.announcementId;
    if (!id) return;

    const btn = form.querySelector('button');
    btn.disabled = true;

    try {
        const body = new URLSearchParams({ announcement_id: id, csrf_token: CSRF_TOKEN });
        const res  = await fetch(form.action, { method: 'POST', body });
        const data = await res.json();

        if (data.success) {
            location.reload();
            return;
        }

        if (data.must_swap) {
            // Populate swap modal with candidates
            pinSwapList.innerHTML = '';
            if (!data.candidates || data.candidates.length === 0) {
                const li = document.createElement('li');
                li.className = 'feature-swap-modal__item';
                li.textContent = 'No other posts available to pin.';
                pinSwapList.appendChild(li);
            } else {
                data.candidates.forEach(candidate => {
                    const li  = document.createElement('li');
                    li.className = 'feature-swap-modal__item';
                    const pickBtn = document.createElement('button');
                    pickBtn.className = 'feature-swap-modal__pick';
                    pickBtn.textContent = candidate.title;
                    pickBtn.addEventListener('click', async () => {
                        pinSwapModal.close();
                        const swapBody = new URLSearchParams({
                            announcement_id: id,
                            swap_id: candidate.id,
                            action: 'swap',
                            csrf_token: CSRF_TOKEN,
                        });
                        await fetch(form.action, { method: 'POST', body: swapBody });
                        location.reload();
                    });
                    li.appendChild(pickBtn);
                    pinSwapList.appendChild(li);
                });
            }
            pinSwapModal.show();
            backdrop.classList.add('modal-backdrop--visible');
            btn.disabled = false;
            return;
        }

        btn.disabled = false;
    } catch {
        btn.disabled = false;
    }
}

async function submitNewsForm(form, confirmed = false) {
    const action      = form.action;
    const isDelete    = action.includes('delete_announcement');
    const isTogglePin = action.includes('toggle_pin');
    if (!isDelete && !isTogglePin) return;

    if (isTogglePin) {
        await handlePinToggle(form);
        return;
    }

    const item = form.closest('[data-announcement-id]');
    const id   = item?.dataset.announcementId;
    if (!id) return;

    if (isDelete && !confirmed) {
        const titleEl = item.querySelector('.news-hero__title, .news-card__title');
        document.getElementById('delete-confirm-title').textContent = titleEl?.textContent.trim() ?? 'this announcement';
        pendingDeleteForm = form;
        deleteModal.show();
        backdrop.classList.add('modal-backdrop--visible');
        return;
    }

    const btn = form.querySelector('button');
    btn.disabled = true;

    try {
        const body = new URLSearchParams({ announcement_id: id, csrf_token: CSRF_TOKEN });
        const res  = await fetch(action, { method: 'POST', body });

        if (!res.ok) {
            btn.disabled = false;
            return;
        }

        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch { data = null; }

        if (data && !data.success) {
            btn.disabled = false;
            return;
        }

        location.reload();

    } catch {
        btn.disabled = false;
    }
}

document.getElementById('news-list')?.addEventListener('submit', async e => {
    const form = e.target.closest('form');
    if (!form) return;
    e.preventDefault();
    await submitNewsForm(form);
});

// ── File input labels ─────────────────────────────────────────
[['post-image', 'post-image-name'], ['edit-image', 'edit-image-name']].forEach(([inputId, nameId]) => {
    const input = document.getElementById(inputId);
    const label = document.getElementById(nameId);
    if (!input || !label) return;
    input.addEventListener('change', () => {
        label.textContent = input.files[0]?.name ?? 'No file chosen';
    });
});
