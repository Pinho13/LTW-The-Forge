export function initAuthModals() {
    const params = new URLSearchParams(window.location.search);

    const loginModal    = document.getElementById('login-modal');
    const registerModal = document.getElementById('register-modal');
    const backdrop      = document.getElementById('modal-backdrop');
    const loginBtn      = document.getElementById('login-btn');
    const openRegisterBtn = document.getElementById('open-register-btn');
    const openLoginBtn    = document.getElementById('open-login-btn');

    function openModal(modal) {
        if (modal) {
            modal.show();
            backdrop?.classList.add('modal-backdrop--visible');
        }
    }

    function closeModal(modal) {
        if (modal) {
            modal.close();
            backdrop?.classList.remove('modal-backdrop--visible');
        }
    }

    function closeAll() {
        [loginModal, registerModal].forEach(m => { if (m?.open) closeModal(m); });
    }

    if (params.get('open') === 'register') {
        openModal(registerModal);
        history.replaceState(null, '', window.location.pathname);
    } else if (params.get('open') === 'login') {
        openModal(loginModal);
        history.replaceState(null, '', window.location.pathname);
    }

    loginBtn?.addEventListener('click', () => openModal(loginModal));

    openRegisterBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        closeModal(loginModal);
        openModal(registerModal);
    });

    openLoginBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        closeModal(registerModal);
        openModal(loginModal);
    });

    backdrop?.addEventListener('click', closeAll);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeAll();
    });

    document.querySelectorAll('.auth-modal__close').forEach((button) => {
        button.addEventListener('click', () => closeModal(button.closest('dialog')));
    });

    [loginModal, registerModal].forEach(modal => {
        modal?.addEventListener('close', () => {
            modal.querySelectorAll('.form__toggle-password').forEach((button) => {
                const input = button.previousElementSibling;
                if (input instanceof HTMLInputElement) input.type = 'password';
            });
        });
    });
}
