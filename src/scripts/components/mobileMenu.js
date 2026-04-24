export function initMobileMenu() {
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');

    document.querySelectorAll('#mobile-menu a').forEach((link) => {
        link.addEventListener('click', () => {
            if (mobileMenuToggle instanceof HTMLInputElement) {
                mobileMenuToggle.checked = false;
            }
        });
    });
}