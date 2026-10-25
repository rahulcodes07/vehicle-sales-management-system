/* Ghost Vehicle Sales: interface behaviour */

document.addEventListener('DOMContentLoaded', () => {
    // Close alert banners
    document.querySelectorAll('[data-dismiss]').forEach((button) => {
        button.addEventListener('click', () => button.closest('.alert')?.remove());
    });

    // Ask before destructive actions
    document.querySelectorAll('[data-confirm]').forEach((element) => {
        element.addEventListener('click', (event) => {
            if (!window.confirm(element.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    // Mobile menu
    const menuButton = document.querySelector('[data-menu-toggle]');
    const sidebar = document.querySelector('[data-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    function setMenu(open) {
        sidebar?.classList.toggle('is-open', open);
        backdrop?.classList.toggle('is-visible', open);
        menuButton?.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    menuButton?.addEventListener('click', () => setMenu(!sidebar?.classList.contains('is-open')));
    backdrop?.addEventListener('click', () => setMenu(false));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setMenu(false);
    });

    // Filters that apply as soon as they change
    document.querySelectorAll('[data-auto-submit]').forEach((element) => {
        element.addEventListener('change', () => element.form?.submit());
    });

    // Stop double submits on forms that save data
    document.querySelectorAll('form[method="POST"]').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');
            if (button) {
                window.setTimeout(() => { button.disabled = true; }, 0);
            }
        });
    });
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('form[method="POST"] button[type="submit"]').forEach((button) => {
            button.disabled = false;
        });
    });
});