// Account info edit / cancel / save
const accountForm   = document.getElementById('account-form');
const accountInputs = accountForm.querySelectorAll('input:not([type="hidden"])');
const actionsEl     = accountForm.closest('.profile-section').querySelector('.profile-section__actions');

const originalValues = {};
accountInputs.forEach(input => { originalValues[input.name] = input.value; });

let locked = !accountForm.dataset.unlocked;

function renderActions() {
    if (locked) {
        actionsEl.innerHTML = '<button type="button" id="edit-btn" class="btn-ghost profile-edit-btn">Edit Profile</button>';
        actionsEl.querySelector('#edit-btn').addEventListener('click', unlock);
    } else {
        actionsEl.innerHTML =
            '<button type="button" id="cancel-btn" class="btn-ghost profile-edit-cancel">Cancel</button>' +
            '<button type="submit" id="confirm-btn" form="account-form" class="btn-ghost profile-edit-save">Save</button>';
        actionsEl.querySelector('#cancel-btn').addEventListener('click', discard);
    }
}

function unlock() {
    accountInputs.forEach(input => input.removeAttribute('readonly'));
    locked = false;
    renderActions();
}

function discard() {
    accountInputs.forEach(input => {
        input.value = originalValues[input.name];
        input.setAttribute('readonly', '');
    });
    locked = true;
    renderActions();
}

renderActions();
if (!locked) {
    accountInputs.forEach(input => input.removeAttribute('readonly'));
}

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

// Log out modal
const logoutModal = document.getElementById('logout-modal');
document.getElementById('logout-btn').addEventListener('click', () => openModal(logoutModal));
logoutModal.querySelector('.auth-modal__close').addEventListener('click', () => closeModal(logoutModal));
document.getElementById('logout-cancel-btn').addEventListener('click', e => { e.preventDefault(); closeModal(logoutModal); });

// Delete account modal
const deleteModal = document.getElementById('delete-modal');
document.getElementById('delete-btn').addEventListener('click', () => openModal(deleteModal));
deleteModal.querySelector('.auth-modal__close').addEventListener('click', () => closeModal(deleteModal));
document.getElementById('delete-cancel-btn').addEventListener('click', e => { e.preventDefault(); closeModal(deleteModal); });
if ('openOnLoad' in deleteModal.dataset) openModal(deleteModal);

// Plan change modal
const planChangeModal = document.getElementById('plan-change-modal');
const planChangeBtn   = document.getElementById('plan-change-btn');
if (planChangeModal && planChangeBtn) {
    planChangeBtn.addEventListener('click', () => openModal(planChangeModal));
    planChangeModal.querySelector('.auth-modal__close').addEventListener('click', () => closeModal(planChangeModal));
    document.getElementById('plan-change-cancel').addEventListener('click', () => closeModal(planChangeModal));
}

// Pause membership modal
const pauseModal = document.getElementById('pause-modal');
const pauseDurationInput = document.getElementById('pause-duration');
const pauseConfirmBtn = document.getElementById('pause-confirm-btn');

const pauseBtn = document.getElementById('pause-btn');
if (pauseBtn) pauseBtn.addEventListener('click', () => openModal(pauseModal));
if (pauseModal) pauseModal.querySelector('.auth-modal__close').addEventListener('click', () => closeModal(pauseModal));

pauseModal && pauseModal.querySelectorAll('.pause-option').forEach(btn => {
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
