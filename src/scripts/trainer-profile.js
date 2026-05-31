const backdrop   = document.getElementById('page-backdrop');
const pfpModal   = document.getElementById('pfp-modal');
const pfpBtn     = document.getElementById('pfp-btn');
const pfpInput   = document.getElementById('pfp-input');
const pfpForm    = document.getElementById('pfp-form');
const pfpPreview = document.getElementById('pfp-preview');

function openModal() {
    pfpModal.show();
    backdrop.classList.add('modal-backdrop--visible');
}
function closeModal() {
    pfpModal.close();
    backdrop.classList.remove('modal-backdrop--visible');
    pfpInput.value = '';
}

pfpBtn.addEventListener('click', () => pfpInput.click());

pfpInput.addEventListener('change', () => {
    const file = pfpInput.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        pfpPreview.src = e.target.result;
        openModal();
    };
    reader.readAsDataURL(file);
});

document.getElementById('pfp-confirm-btn').addEventListener('click', () => {
    const src = document.getElementById('pfp-preview').src;
    const previewImg = document.getElementById('preview-avatar-img');
    const editImg    = document.getElementById('edit-avatar-img');
    if (previewImg) previewImg.src = src;
    if (editImg)    editImg.src    = src;
    pfpForm.submit();
});
document.getElementById('pfp-cancel-btn').addEventListener('click', closeModal);
document.getElementById('pfp-close').addEventListener('click', closeModal);
backdrop.addEventListener('click', closeModal);

// ── Live preview ──────────────────────────────────────────────
function esc(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function renderTags(containerId, sectionId, val, cls) {
    const section = document.getElementById(sectionId);
    const list    = document.getElementById(containerId);
    const tags    = val.split(',').map(s => s.trim()).filter(Boolean);
    if (tags.length === 0) {
        section.hidden = true;
        return;
    }
    section.hidden = false;
    list.innerHTML = tags.map(t => `<li class="${cls}">${esc(t)}</li>`).join('');
}

document.getElementById('tp-bio').addEventListener('input', e => {
    const el = document.getElementById('preview-bio');
    el.textContent = e.target.value || 'No bio provided.';
});

document.getElementById('tp-spec').addEventListener('input', e => {
    renderTags('preview-spec-list', 'preview-spec-section', e.target.value, 'tag');
});

document.getElementById('tp-cert').addEventListener('input', e => {
    renderTags('preview-cert-list', 'preview-cert-section', e.target.value, 'tag tag--cert');
});

