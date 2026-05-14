export function initPasswordToggles() {
    document.querySelectorAll('.form__toggle-password').forEach((button) => {
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
}