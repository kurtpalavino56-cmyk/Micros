function initializeFlashToasts() {
    document.querySelectorAll('[data-flash-toast]').forEach((toast) => {
        let dismissTimer;

        const dismiss = () => {
            window.clearTimeout(dismissTimer);
            toast.classList.add('is-leaving');
            toast.addEventListener('animationend', () => {
                toast.closest('[data-flash-toast-region]')?.remove();
            }, { once: true });
        };

        const startDismissTimer = () => {
            window.clearTimeout(dismissTimer);
            dismissTimer = window.setTimeout(dismiss, 5000);
        };

        toast.querySelector('[data-dismiss-flash-toast]')?.addEventListener('click', dismiss);
        toast.addEventListener('mouseenter', () => window.clearTimeout(dismissTimer));
        toast.addEventListener('mouseleave', startDismissTimer);
        toast.addEventListener('focusin', () => window.clearTimeout(dismissTimer));
        toast.addEventListener('focusout', startDismissTimer);

        startDismissTimer();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeFlashToasts, { once: true });
} else {
    initializeFlashToasts();
}
