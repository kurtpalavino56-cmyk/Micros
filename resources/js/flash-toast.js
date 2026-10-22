function initializeFlashToast(toast) {
    const dismissDelay = 5000;
    const timer = toast.querySelector('[data-flash-toast-timer]');
    let dismissTimer;

    const dismiss = () => {
        window.clearTimeout(dismissTimer);
        toast.classList.add('is-leaving');
        toast.addEventListener('animationend', () => {
            const region = toast.closest('[data-flash-toast-region]');
            toast.remove();
            if (region && !region.querySelector('[data-flash-toast]')) {
                region.remove();
            }
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
}

function initializeFlashToasts() {
    document.querySelectorAll('[data-flash-toast]').forEach(initializeFlashToast);
}

window.showFlashToast = function (message, type = 'success') {
    const toastType = ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';
    let region = document.querySelector('[data-flash-toast-region]');
    if (!region) {
        region = document.createElement('div');
        region.className = 'flash-toast-region';
        region.dataset.flashToastRegion = '';
        region.setAttribute('aria-live', 'polite');
        region.setAttribute('aria-relevant', 'additions');
        document.body.appendChild(region);
    }

    const toast = document.createElement('div');
    toast.className = 'flash-toast flash-toast--' + toastType;
    toast.dataset.flashToast = '';
    toast.setAttribute('role', toastType === 'error' ? 'alert' : 'status');
    toast.tabIndex = -1;

    const icon = document.createElement('span');
    icon.className = 'flash-toast__icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.innerHTML = toastType === 'success'
        ? '<svg viewBox="0 0 20 20" fill="none"><path d="m5 10 3.2 3.2L15 6.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        : '<svg viewBox="0 0 20 20" fill="none"><path d="M10 8.5v4M10 5.8h.01M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>';

    const toastMessage = document.createElement('span');
    toastMessage.className = 'flash-toast__message';
    toastMessage.textContent = String(message ?? '');

    const dismissButton = document.createElement('button');
    dismissButton.className = 'flash-toast__dismiss';
    dismissButton.type = 'button';
    dismissButton.dataset.dismissFlashToast = '';
    dismissButton.setAttribute('aria-label', 'Dismiss message');
    dismissButton.innerHTML = '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m5 5 10 10M15 5 5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>';

    const timer = document.createElement('span');
    timer.className = 'flash-toast__timer';
    timer.dataset.flashToastTimer = '';
    timer.setAttribute('aria-hidden', 'true');

    toast.append(icon, toastMessage, dismissButton, timer);
    region.appendChild(toast);
    initializeFlashToast(toast);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeFlashToasts, { once: true });
} else {
    initializeFlashToasts();
}
