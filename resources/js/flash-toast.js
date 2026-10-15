function initializeFlashToasts() {
    const dismissDelay = 5000;

    document.querySelectorAll('[data-flash-toast]').forEach((toast) => {
        const timer = toast.querySelector('[data-flash-toast-timer]');
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
            if (timer) {
                timer.classList.remove('is-paused', 'is-running');
                void timer.offsetWidth;
                timer.classList.add('is-running');
            }
            dismissTimer = window.setTimeout(dismiss, dismissDelay);
        };

        const pauseDismissTimer = () => {
            window.clearTimeout(dismissTimer);
            timer?.classList.add('is-paused');
        };

        toast.querySelector('[data-dismiss-flash-toast]')?.addEventListener('click', dismiss);
        toast.addEventListener('mouseenter', pauseDismissTimer);
        toast.addEventListener('mouseleave', startDismissTimer);
        toast.addEventListener('focusin', pauseDismissTimer);
        toast.addEventListener('focusout', startDismissTimer);

        startDismissTimer();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeFlashToasts, { once: true });
} else {
    initializeFlashToasts();
}
