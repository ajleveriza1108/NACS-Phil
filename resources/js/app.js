document.addEventListener('DOMContentLoaded', () => {
    const button = document.querySelector('[data-menu-toggle]');
    const menu = document.querySelector('[data-mobile-menu]');

    if (button && menu) {
        button.addEventListener('click', () => {
            const isHidden = menu.classList.toggle('hidden');
            button.setAttribute('aria-expanded', String(!isHidden));
        });
    }
});
