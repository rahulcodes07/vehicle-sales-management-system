document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-dismiss]').forEach((element) => {
        element.addEventListener('click', () => element.closest('.alert')?.remove());
    });

    document.querySelectorAll('[data-confirm]').forEach((element) => {
        element.addEventListener('click', (event) => {
            if (!window.confirm(element.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    const menuButton = document.querySelector('[data-menu-toggle]');
    const sidebar = document.querySelector('[data-sidebar]');
    menuButton?.addEventListener('click', () => {
        sidebar?.classList.toggle('is-open');
        menuButton.setAttribute(
            'aria-expanded',
            sidebar?.classList.contains('is-open') ? 'true' : 'false'
        );
    });

    document.querySelectorAll('[data-auto-submit]').forEach((element) => {
        element.addEventListener('change', () => element.form?.submit());
    });
});
