/**
 * VSMS Control Center — Client Application Runtime
 * Vanilla JavaScript • Micro-interactions • Number Counters
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Alert Banner Dismissal with Smooth Transition
    document.querySelectorAll('[data-dismiss]').forEach((button) => {
        button.addEventListener('click', () => {
            const alert = button.closest('.alert');
            if (alert) {
                alert.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-6px)';
                setTimeout(() => alert.remove(), 200);
            }
        });
    });

    // 2. Destructive Action Confirmation Dialogs
    document.querySelectorAll('[data-confirm]').forEach((element) => {
        element.addEventListener('click', (event) => {
            const message = element.dataset.confirm || 'Are you sure you want to proceed?';
            if (!window.confirm(message)) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });

    // 3. Mobile Sidebar Drawer & Backdrop Toggle
    const menuButton = document.querySelector('[data-menu-toggle]');
    const sidebar = document.querySelector('[data-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    function toggleSidebar() {
        const isOpen = sidebar?.classList.toggle('is-open');
        backdrop?.classList.toggle('is-visible', Boolean(isOpen));
        menuButton?.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    menuButton?.addEventListener('click', toggleSidebar);
    backdrop?.addEventListener('click', toggleSidebar);

    // 4. Auto-submit on Select/Filter Change
    document.querySelectorAll('[data-auto-submit]').forEach((element) => {
        element.addEventListener('change', () => element.form?.submit());
    });

    // 5. Stat Card Number Counter Animation
    const counterElements = document.querySelectorAll('.stat-value');
    counterElements.forEach((el) => {
        const rawText = el.innerText.trim();
        const hasRupee = rawText.includes('₹');
        
        // Extract numeric part (handles commas and decimals)
        const numStr = rawText.replace(/[^0-9.]/g, '');
        const targetValue = parseFloat(numStr);

        if (!isNaN(targetValue) && targetValue > 0) {
            const isDecimal = numStr.includes('.');
            const duration = 900; // ms
            const startTime = performance.now();

            function updateCounter(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                // Ease-out cubic
                const easeOut = 1 - Math.pow(1 - progress, 3);
                const currentValue = targetValue * easeOut;

                let formatted = isDecimal
                    ? formatIndianNumber(currentValue.toFixed(2))
                    : formatIndianNumber(Math.floor(currentValue));

                if (hasRupee) {
                    el.innerHTML = `<span class="inr-symbol">₹</span>${formatted}`;
                } else {
                    el.innerText = formatted;
                }

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    // Final exact value
                    const finalStr = isDecimal
                        ? formatIndianNumber(targetValue.toFixed(2))
                        : formatIndianNumber(targetValue);
                    if (hasRupee) {
                        el.innerHTML = `<span class="inr-symbol">₹</span>${finalStr}`;
                    } else {
                        el.innerText = finalStr;
                    }
                }
            }

            requestAnimationFrame(updateCounter);
        }
    });

    /**
     * Converts a number to Indian numbering system format (e.g. 2,32,000)
     */
    function formatIndianNumber(val) {
        const parts = val.toString().split('.');
        let integerPart = parts[0];
        const decimalPart = parts.length > 1 ? '.' + parts[1] : '';

        if (integerPart.length <= 3) {
            return integerPart + decimalPart;
        }

        const lastThree = integerPart.substring(integerPart.length - 3);
        const otherNumbers = integerPart.substring(0, integerPart.length - 3);
        const formattedOthers = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ',');

        return formattedOthers + ',' + lastThree + decimalPart;
    }
});
