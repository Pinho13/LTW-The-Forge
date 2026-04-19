const loginModal = document.getElementById('login-modal');
const registerModal = document.getElementById('register-modal');
const loginBtn = document.getElementById('login-btn');
const openRegisterBtn = document.getElementById('open-register-btn');
const openLoginBtn = document.getElementById('open-login-btn');

loginBtn.addEventListener('click', () => loginModal.showModal());

openRegisterBtn.addEventListener('click', (e) => {
    e.preventDefault();
    loginModal.close();
    registerModal.showModal();
});

openLoginBtn.addEventListener('click', (e) => {
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
});

document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = btn.previousElementSibling;
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.textContent = isHidden ? '\u{1F576}' : '\u{1F441}';
    });
});
