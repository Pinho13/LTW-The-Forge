const backdrop  = document.getElementById('page-backdrop');
const postModal = document.getElementById('post-modal');
const postClose = document.getElementById('post-close');
const newBtn    = document.getElementById('new-post-btn');

const deleteModal      = document.getElementById('delete-confirm-modal');
const deleteCancelBtn  = document.getElementById('delete-cancel-btn');
const deleteConfirmBtn = document.getElementById('delete-confirm-btn');

const editModal = document.getElementById('edit-modal');
const editClose = document.getElementById('edit-close');

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
backdrop.addEventListener('click', () => { closeModal(); closeEditModal(); });

document.getElementById('news-list')?.addEventListener('click', e => {
    const btn = e.target.closest('.js-edit-btn');
    if (btn) openEditModal(btn);
});
deleteCancelBtn.addEventListener('click', () => deleteModal.close());

let pendingDeleteForm = null;

deleteConfirmBtn.addEventListener('click', async () => {
    deleteModal.close();
    if (!pendingDeleteForm) return;
    await submitNewsForm(pendingDeleteForm, true);
    pendingDeleteForm = null;
});

async function submitNewsForm(form, confirmed = false) {
    const action      = form.action;
    const isDelete    = action.includes('delete_announcement');
    const isTogglePin = action.includes('toggle_pin');
    if (!isDelete && !isTogglePin) return;

    const item = form.closest('[data-announcement-id]');
    const id   = item?.dataset.announcementId;
    if (!id) return;

    if (isDelete && !confirmed) {
        const titleEl = item.querySelector('.news-hero__title, .news-card__title');
        document.getElementById('delete-confirm-title').textContent = titleEl?.textContent.trim() ?? 'this announcement';
        pendingDeleteForm = form;
        deleteModal.showModal();
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
