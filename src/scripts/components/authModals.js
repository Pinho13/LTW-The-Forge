export function initAuthModals() {
    const params = new URLSearchParams(window.location.search);

    const loginModal    = document.getElementById('login-modal');
    const registerModal = document.getElementById('register-modal');
    const backdrop      = document.getElementById('modal-backdrop');
    const loginBtn      = document.getElementById('login-btn');
    const openRegisterBtn = document.getElementById('open-register-btn');
    const openLoginBtn    = document.getElementById('open-login-btn');
    const basicMembershipBtn = document.getElementById('basic-membership');
    const premiumMembershipBtn = document.getElementById('premium-membership');
    const membershipOptions = document.querySelectorAll('.auth-modal__membership-option');
    const membershipInput = document.getElementById('membership-input');

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

    basicMembershipBtn?.addEventListener('click', () => openModal(registerModal));

    premiumMembershipBtn?.addEventListener('click', () => openModal(registerModal));

    backdrop?.addEventListener('click', closeAll);

    membershipOptions.forEach(option => {
        option.addEventListener('click', () => {
            const value = option.dataset.value;

            if (membershipInput.value === value) return;

            membershipInput.value = value;

            membershipOptions.forEach(btn => {
                if (btn.dataset.value === value) {
                    btn.classList.remove('btn-outline');
                } else {
                    btn.classList.add('btn-outline');
                }
            });
        });
    });


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

    // Password strength single-line hint for register modal
    const pwInput = document.getElementById('register-password');
    const pwHint  = document.getElementById('pw-hint');

    if (pwInput && pwHint) {
        const rules = [
            { check: v => v.length >= 8,         msg: 'At least 8 characters' },
            { check: v => /[A-Z]/.test(v),        msg: 'One uppercase letter' },
            { check: v => /[a-z]/.test(v),        msg: 'One lowercase letter' },
            { check: v => /[0-9]/.test(v),        msg: 'One number' },
            { check: v => /[^a-zA-Z0-9]/.test(v), msg: 'One special character' },
        ];

        function updatePwHint() {
            if (pwInput.value.includes(' ')) {
                pwInput.value = pwInput.value.replace(/ /g, '');
            }
            const val = pwInput.value;

            if (!rules[0].check(val)) {
                pwHint.textContent = rules[0].msg;
                pwHint.className = 'pw-hint pw-hint--error';
                return;
            }

            const unmet = rules.slice(1).find(r => !r.check(val));
            if (unmet) {
                pwHint.textContent = unmet.msg;
                pwHint.className = 'pw-hint pw-hint--error';
            } else {
                pwHint.textContent = 'All requirements met';
                pwHint.className = 'pw-hint pw-hint--ok';
            }
        }

        updatePwHint();
        pwInput.addEventListener('input', updatePwHint);
    }
}
