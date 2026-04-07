const modal = document.getElementById('login-modal');
const loginBtn = document.getElementById('login-btn');
const closeBtn = document.getElementById('modal-close-btn');
const togglePassword = document.getElementById('toggle-password');
const passwordInput = document.getElementById('password');

loginBtn.addEventListener('click', () => modal.showModal());

closeBtn.addEventListener('click', () => modal.close());

modal.addEventListener('click', (e) => {
    if (e.target === modal) modal.close();
});

togglePassword.addEventListener('click', () => {
    const isHidden = passwordInput.type === 'password';
    passwordInput.type = isHidden ? 'text' : 'password';
    togglePassword.textContent = isHidden ? '\u{1F576}' : '\u{1F441}';
});
