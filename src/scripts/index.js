const params = new URLSearchParams(window.location.search);

const loginModal = document.getElementById('login-modal');
const registerModal = document.getElementById('register-modal');
const loginBtn = document.getElementById('login-btn');
const openRegisterBtn = document.getElementById('open-register-btn');
const openLoginBtn = document.getElementById('open-login-btn');
const mobileMenuToggle = document.getElementById('mobile-menu-toggle');

function openModal(modal) {
    if (modal && typeof modal.showModal === 'function') {
        modal.showModal();
    }
}

function closeModal(modal) {
    if (modal && typeof modal.close === 'function') {
        modal.close();
    }
}

if (params.get('open') === 'register') {
    openModal(registerModal);
    history.replaceState(null, '', window.location.pathname);
} else if (params.get('open') === 'login') {
    openModal(loginModal);
    history.replaceState(null, '', window.location.pathname);
}

loginBtn?.addEventListener('click', () => {
    openModal(loginModal);
});

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

document.querySelectorAll('.modal-close-btn').forEach((button) => {
    button.addEventListener('click', () => {
        closeModal(button.closest('dialog'));
    });
});

document.querySelectorAll('.auth-modal').forEach((modal) => {
    modal.addEventListener('click', (event) => {
        const rect = modal.getBoundingClientRect();

        const clickedOutside =
            event.clientX < rect.left ||
            event.clientX > rect.right ||
            event.clientY < rect.top ||
            event.clientY > rect.bottom;

        if (clickedOutside) {
            closeModal(modal);
        }
    });

    modal.addEventListener('close', () => {
        modal.querySelectorAll('.toggle-password').forEach((button) => {
            const input = button.previousElementSibling;

            if (input instanceof HTMLInputElement) {
                input.type = 'password';
            }

            button.textContent = '\u{1F441}';
        });
    });
});

document.querySelectorAll('.toggle-password').forEach((button) => {
    button.addEventListener('click', () => {
        const input = button.previousElementSibling;

        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        button.textContent = isHidden ? '\u{1F576}' : '\u{1F441}';
    });
});

document.querySelectorAll('#mobile-menu a').forEach((link) => {
    link.addEventListener('click', () => {
        if (mobileMenuToggle instanceof HTMLInputElement) {
            mobileMenuToggle.checked = false;
        }
    });
});