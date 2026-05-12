// Account info unlock / discard / confirm
const accountForm = document.getElementById('account-form');
const accountInputs = accountForm.querySelectorAll('input:not([type="hidden"])');
const unlockBtn = document.getElementById('unlock-btn');
const confirmBtn = document.getElementById('confirm-btn');

const originalValues = {};
accountInputs.forEach(input => { originalValues[input.name] = input.value; });

let locked = !accountForm.dataset.unlocked;

function unlock() {
    accountInputs.forEach(input => input.removeAttribute('readonly'));
    unlockBtn.textContent = 'Discard Changes';
    confirmBtn.disabled = false;
    locked = false;
}

function discard() {
    accountInputs.forEach(input => {
        input.value = originalValues[input.name];
        input.setAttribute('readonly', '');
    });
    unlockBtn.textContent = 'Unlock Info';
    confirmBtn.disabled = true;
    locked = true;
}

unlockBtn.addEventListener('click', () => locked ? unlock() : discard());

// Modal helpers
const backdrop = document.getElementById('page-backdrop');

function openModal(dialog) {
    dialog.show();
    backdrop.classList.add('modal-backdrop--visible');
}

function closeModal(dialog) {
    dialog.close();
    backdrop.classList.remove('modal-backdrop--visible');
}

backdrop.addEventListener('click', () => {
    document.querySelectorAll('dialog[open]').forEach(d => closeModal(d));
});

// Delete account modal
const deleteModal = document.getElementById('delete-modal');
document.getElementById('delete-btn').addEventListener('click', () => openModal(deleteModal));
deleteModal.querySelector('.auth-modal__close').addEventListener('click', () => closeModal(deleteModal));
document.getElementById('delete-cancel-btn').addEventListener('click', e => { e.preventDefault(); closeModal(deleteModal); });
if ('openOnLoad' in deleteModal.dataset) openModal(deleteModal);

// Pause membership modal
const pauseModal = document.getElementById('pause-modal');
const pauseDurationInput = document.getElementById('pause-duration');
const pauseConfirmBtn = document.getElementById('pause-confirm-btn');

document.getElementById('pause-btn').addEventListener('click', () => openModal(pauseModal));
pauseModal.querySelector('.auth-modal__close').addEventListener('click', () => closeModal(pauseModal));

pauseModal.querySelectorAll('.pause-option').forEach(btn => {
    btn.addEventListener('click', () => {
        pauseModal.querySelectorAll('.pause-option').forEach(b => b.classList.remove('pause-option--selected'));
        btn.classList.add('pause-option--selected');
        pauseDurationInput.value = btn.dataset.days;
        pauseConfirmBtn.disabled = false;
    });
});

// Profile picture upload
const pfpBtn     = document.getElementById('pfp-btn');
const pfpInput   = document.getElementById('pfp-input');
const pfpForm    = document.getElementById('pfp-form');
const pfpModal   = document.getElementById('pfp-modal');
const pfpPreview = document.getElementById('pfp-preview');

pfpBtn.addEventListener('click', () => pfpInput.click());

pfpInput.addEventListener('change', () => {
    const file = pfpInput.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        pfpPreview.src = e.target.result;
        openModal(pfpModal);
    };
    reader.readAsDataURL(file);
});

document.getElementById('pfp-confirm-btn').addEventListener('click', () => pfpForm.submit());
document.getElementById('pfp-cancel-btn').addEventListener('click', e => {
    e.preventDefault();
    closeModal(pfpModal);
    pfpInput.value = '';
});
pfpModal.querySelector('.auth-modal__close').addEventListener('click', () => {
    closeModal(pfpModal);
    pfpInput.value = '';
});
if ('openOnLoad' in pfpModal.dataset) openModal(pfpModal);

// Password toggles
document.querySelectorAll('.form__toggle-password').forEach(button => {
    button.addEventListener('click', () => {
        const input = button.previousElementSibling;
        if (!(input instanceof HTMLInputElement)) return;
        const hidden = input.type === 'password';
        input.type = hidden ? 'text' : 'password';
        button.textContent = hidden ? '\u{1F576}' : '\u{1F441}';
    });
});
