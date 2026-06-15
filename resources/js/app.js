import './bootstrap';
import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

const RESTORE_KEY = 'ksa:form-context';
const FLASH_SELECTOR = '[data-flash-alert], [data-flash-errors]';
const SUBMIT_SELECTOR = 'button:not([type]), button[type="submit"], input[type="submit"]';

const formatLapTimeDigits = (digits) => {
    if (!digits) {
        return '';
    }

    const normalizedDigits = digits.replace(/\D/g, '').slice(-8);

    if (!normalizedDigits) {
        return '';
    }

    const milliseconds = normalizedDigits.slice(-3).padStart(3, '0');
    const secondsSource = normalizedDigits.slice(0, -3);
    const seconds = secondsSource.slice(-2).padStart(2, '0');
    const minutes = secondsSource.slice(0, -2) || '0';

    return `${Number(minutes)}:${seconds}.${milliseconds}`;
};

const currentDigits = (input) => input.dataset.lapTimeDigits ?? input.value.replace(/\D/g, '');
const allTextSelected = (input) => input.selectionStart === 0 && input.selectionEnd === input.value.length;

const closestSectionId = (element) => element?.closest('[data-section-id]')?.dataset.sectionId ?? null;

const preserveFormContext = (form) => {
    const payload = {
        path: `${window.location.pathname}${window.location.search}`,
        scrollY: window.scrollY,
        hash: window.location.hash || '',
        sectionId: closestSectionId(form),
        timestamp: Date.now(),
    };

    sessionStorage.setItem(RESTORE_KEY, JSON.stringify(payload));
};

const setSubmittingState = (button) => {
    if (!(button instanceof HTMLButtonElement || button instanceof HTMLInputElement)) {
        return;
    }

    if (button.dataset.submittingApplied === 'true') {
        return;
    }

    button.dataset.submittingApplied = 'true';
    button.disabled = true;

    const label = button.dataset.submittingLabel || 'Processando...';

    if (button instanceof HTMLInputElement) {
        button.dataset.originalValue = button.value;
        button.value = label;

        return;
    }

    button.dataset.originalHtml = button.innerHTML;
    button.innerHTML = `
        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
        <span>${label}</span>
    `;
};

const handleFormSubmitState = () => {
    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const method = (form.getAttribute('method') || 'GET').toUpperCase();

        if (method === 'GET' || form.dataset.skipSubmitState === 'true') {
            return;
        }

        preserveFormContext(form);

        const submitter = event.submitter instanceof HTMLElement
            ? event.submitter
            : form.querySelector(SUBMIT_SELECTOR);

        form.dataset.submitting = 'true';
        document.body.classList.add('is-submitting');

        form.querySelectorAll(SUBMIT_SELECTOR).forEach((button) => {
            if (!(button instanceof HTMLButtonElement || button instanceof HTMLInputElement)) {
                return;
            }

            if (submitter && button === submitter) {
                setSubmittingState(button);

                return;
            }

            button.disabled = true;
        });
    });
};

const restoreFormContext = () => {
    const raw = sessionStorage.getItem(RESTORE_KEY);

    if (!raw) {
        return;
    }

    sessionStorage.removeItem(RESTORE_KEY);

    let payload = null;

    try {
        payload = JSON.parse(raw);
    } catch {
        return;
    }

    if (!payload || payload.path !== `${window.location.pathname}${window.location.search}`) {
        return;
    }

    if (Date.now() - Number(payload.timestamp || 0) > 10 * 60 * 1000) {
        return;
    }

    window.requestAnimationFrame(() => {
        if (payload.sectionId) {
            const section = document.querySelector(`[data-section-id="${payload.sectionId}"]`);

            if (section) {
                section.scrollIntoView({ block: 'start' });
                history.replaceState(null, '', `#${payload.sectionId}`);

                return;
            }
        }

        if (Number.isFinite(payload.scrollY)) {
            window.scrollTo({ top: payload.scrollY });
        }
    });
};

const setupFlashFeedback = () => {
    document.querySelectorAll('[data-auto-dismiss]').forEach((alert) => {
        const timeout = Number(alert.getAttribute('data-auto-dismiss'));

        if (!timeout || !Number.isFinite(timeout)) {
            return;
        }

        window.setTimeout(() => {
            if (!alert.isConnected) {
                return;
            }

            bootstrap.Alert.getOrCreateInstance(alert).close();
        }, timeout);
    });

    const flash = document.querySelector(FLASH_SELECTOR);

    if (flash instanceof HTMLElement) {
        flash.focus({ preventScroll: true });
    }
};

const bindLapTimeInputs = () => {
    document.querySelectorAll('[data-lap-time-input]').forEach((input) => {
        input.dataset.lapTimeDigits = input.value.replace(/\D/g, '');

        input.addEventListener('focus', () => {
            input.select();
        });

        input.addEventListener('keydown', (event) => {
            if (event.ctrlKey || event.metaKey || event.altKey) {
                return;
            }

            if (event.key === 'Tab' || event.key === 'Enter') {
                return;
            }

            if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].includes(event.key)) {
                return;
            }

            if (event.key === 'Backspace') {
                event.preventDefault();

                const digits = allTextSelected(input) ? '' : currentDigits(input).slice(0, -1);
                input.dataset.lapTimeDigits = digits;
                input.value = formatLapTimeDigits(digits);

                return;
            }

            if (event.key === 'Delete') {
                event.preventDefault();
                input.dataset.lapTimeDigits = '';
                input.value = '';

                return;
            }

            if (!/^\d$/.test(event.key)) {
                event.preventDefault();

                return;
            }

            event.preventDefault();

            const baseDigits = allTextSelected(input) ? '' : currentDigits(input);
            const digits = `${baseDigits}${event.key}`;
            input.dataset.lapTimeDigits = digits;
            input.value = formatLapTimeDigits(digits);
        });

        input.addEventListener('paste', (event) => {
            event.preventDefault();

            const pastedDigits = event.clipboardData?.getData('text')?.replace(/\D/g, '') ?? '';
            const baseDigits = allTextSelected(input) ? '' : currentDigits(input);
            const digits = `${baseDigits}${pastedDigits}`;

            input.dataset.lapTimeDigits = digits;
            input.value = formatLapTimeDigits(digits);
        });

        input.addEventListener('blur', () => {
            input.dataset.lapTimeDigits = input.value.replace(/\D/g, '');
            input.value = formatLapTimeDigits(input.dataset.lapTimeDigits);
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    bindLapTimeInputs();
    handleFormSubmitState();
    restoreFormContext();
    setupFlashFeedback();
});
