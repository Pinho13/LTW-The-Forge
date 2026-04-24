export function initTerminalTyping() {
    const terminalText = document.querySelector('.terminal-text');

    if (!(terminalText instanceof HTMLElement)) {
        return;
    }

    const fullText = terminalText.dataset.text ?? '';
    if (!fullText) {
        return;
    }

    let index = 0;
    terminalText.textContent = '';

    function getCharacterDelay(char) {
        let delay = 18 + Math.random() * 14;

        if (char === ',' || char === ';') {
            delay += 120 + Math.random() * 80;
        }

        if (char === '.' || char === '!' || char === '?') {
            delay += 220 + Math.random() * 140;
        }

        if (char === ' ' && Math.random() < 0.15) {
            delay += 260 + Math.random() * 280;
        }

        return delay;
    }

    function typeNextCharacter() {
        if (index >= fullText.length) {
            return;
        }

        const char = fullText.charAt(index);
        terminalText.textContent += char;
        index++;

        window.setTimeout(typeNextCharacter, getCharacterDelay(char));
    }

    // slight initial delay for realism
    window.setTimeout(typeNextCharacter, 350);
}