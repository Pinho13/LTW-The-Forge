import { initAuthModals } from './components/authModals.js';
import { initPasswordToggles } from './components/passwordToggles.js';
import { initMobileMenu } from './components/mobileMenu.js';
import { initTerminalTyping } from './components/terminalTyping.js';

initAuthModals();
initPasswordToggles();
initMobileMenu();
initTerminalTyping();

window.addEventListener('pageshow', (e) => {
    if (e.persisted) location.reload();
});