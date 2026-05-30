// Intercepts feature toggle forms, handles the 4-item limit with a swap modal.

const MODAL_ID = 'feature-swap-modal';

function ensureModal() {
    if (document.getElementById(MODAL_ID)) return document.getElementById(MODAL_ID);

    const modal = document.createElement('dialog');
    modal.id = MODAL_ID;
    modal.className = 'auth-modal feature-swap-modal';
    modal.innerHTML = `
        <button type="button" class="btn-ghost auth-modal__close" aria-label="Close">&times;</button>
        <h2 class="auth-modal__title">Swap Featured</h2>
        <p class="feature-swap-modal__hint">Featured limit reached. Choose one to replace:</p>
        <ul class="feature-swap-modal__list"></ul>
    `;
    document.body.appendChild(modal);

    modal.querySelector('.auth-modal__close').addEventListener('click', () => modal.close());
    modal.addEventListener('click', e => { if (e.target === modal) modal.close(); });

    return modal;
}

function submitWithSwap(form, swapId) {
    const data = new FormData(form);
    data.set('swap_id', swapId);

    fetch(form.action, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: data,
    })
    .then(r => r.json())
    .then(res => {
        if (res.ok) {
            const returnUrl = form.querySelector('[name="return"]')?.value || window.location.href;
            window.location.href = returnUrl;
        }
    });
}

function handleFeatureForm(form) {
    form.addEventListener('submit', async e => {
        e.preventDefault();

        const data = new FormData(form);
        const res = await fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: data,
        }).then(r => r.json());

        if (res.ok) {
            const returnUrl = form.querySelector('[name="return"]')?.value || window.location.href;
            window.location.href = returnUrl;
            return;
        }

        if (res.limit) {
            const modal = ensureModal();
            const list = modal.querySelector('.feature-swap-modal__list');
            list.innerHTML = '';
            res.featured.forEach(item => {
                const li = document.createElement('li');
                li.className = 'feature-swap-modal__item';
                const btn = document.createElement('button');
                btn.className = 'feature-swap-modal__pick';
                btn.textContent = item.name + (item.type_name ? ` — ${item.type_name}` : '');
                btn.addEventListener('click', () => {
                    modal.close();
                    submitWithSwap(form, item.id);
                });
                li.appendChild(btn);
                list.appendChild(li);
            });
            modal.showModal();
            return;
        }

        alert(res.error || 'Something went wrong.');
    });
}

export function initFeatureSwap() {
    document.querySelectorAll('form[data-feature-toggle]').forEach(handleFeatureForm);
}
