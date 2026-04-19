const params = new URLSearchParams(window.location.search);
if (params.get('open') === 'register') {
    document.getElementById('register-modal')?.showModal();
    history.replaceState(null, '', window.location.pathname);
} else if (params.get('open') === 'login') {
    document.getElementById('login-modal')?.showModal();
    history.replaceState(null, '', window.location.pathname);
}

const loginModal     = document.getElementById('login-modal');
const registerModal  = document.getElementById('register-modal');
const loginBtn       = document.getElementById('login-btn');
const openRegisterBtn = document.getElementById('open-register-btn');
const openLoginBtn   = document.getElementById('open-login-btn');

loginBtn?.addEventListener('click', () => loginModal.showModal());

openRegisterBtn?.addEventListener('click', (e) => {
    e.preventDefault();
    loginModal.close();
    registerModal.showModal();
});

openLoginBtn?.addEventListener('click', (e) => {
    e.preventDefault();
    registerModal.close();
    loginModal.showModal();
});

document.querySelectorAll('.modal-close-btn').forEach(btn => {
    btn.addEventListener('click', () => btn.closest('dialog').close());
});

document.querySelectorAll('.auth-modal').forEach(modal => {
    modal.addEventListener('click', (e) => {
        const rect = modal.getBoundingClientRect();
        if (e.clientX < rect.left || e.clientX > rect.right ||
            e.clientY < rect.top  || e.clientY > rect.bottom) {
            modal.close();
        }
    });

    modal.addEventListener('close', () => {
        modal.querySelector('form')?.reset();
        modal.querySelector('.form-error')?.remove();
        modal.querySelectorAll('.toggle-password').forEach(btn => {
            btn.previousElementSibling.type = 'password';
            btn.textContent = '\u{1F441}';
        });
    });
});

document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = btn.previousElementSibling;
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.textContent = isHidden ? '\u{1F576}' : '\u{1F441}';
    });
});
