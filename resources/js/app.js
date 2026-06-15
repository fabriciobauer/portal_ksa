const RESTORE_KEY = 'ksa:form-context';
const SUBMIT_SELECTOR = 'button:not([type]), button[type="submit"], input[type="submit"]';

// ── Lap time input formatting ────────────────────────────────────────────────

const formatLapTimeDigits = (digits) => {
    if (!digits) return '';
    const normalizedDigits = digits.replace(/\D/g, '').slice(-8);
    if (!normalizedDigits) return '';
    const milliseconds = normalizedDigits.slice(-3).padStart(3, '0');
    const secondsSource = normalizedDigits.slice(0, -3);
    const seconds = secondsSource.slice(-2).padStart(2, '0');
    const minutes = secondsSource.slice(0, -2) || '0';
    return `${Number(minutes)}:${seconds}.${milliseconds}`;
};

const currentDigits = (input) => input.dataset.lapTimeDigits ?? input.value.replace(/\D/g, '');
const allTextSelected = (input) => input.selectionStart === 0 && input.selectionEnd === input.value.length;

const bindLapTimeInputs = () => {
    document.querySelectorAll('[data-lap-time-input]').forEach((input) => {
        input.dataset.lapTimeDigits = input.value.replace(/\D/g, '');

        input.addEventListener('focus', () => input.select());

        input.addEventListener('keydown', (event) => {
            if (event.ctrlKey || event.metaKey || event.altKey) return;
            if (event.key === 'Tab' || event.key === 'Enter') return;
            if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].includes(event.key)) return;

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

// ── Form submit state (loading / disable) ────────────────────────────────────

const closestSectionId = (element) => element?.closest('[data-section-id]')?.dataset.sectionId ?? null;

const preserveFormContext = (form) => {
    sessionStorage.setItem(RESTORE_KEY, JSON.stringify({
        path: `${window.location.pathname}${window.location.search}`,
        scrollY: window.scrollY,
        hash: window.location.hash || '',
        sectionId: closestSectionId(form),
        timestamp: Date.now(),
    }));
};

const setSubmittingState = (button) => {
    if (!(button instanceof HTMLButtonElement || button instanceof HTMLInputElement)) return;
    if (button.dataset.submittingApplied === 'true') return;

    button.dataset.submittingApplied = 'true';
    button.disabled = true;
    const label = button.dataset.submittingLabel || 'Processando...';

    if (button instanceof HTMLInputElement) {
        button.dataset.originalValue = button.value;
        button.value = label;
        return;
    }

    button.dataset.originalHtml = button.innerHTML;
    button.innerHTML = `<span class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></span><span>${label}</span>`;
};

const handleFormSubmitState = () => {
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        const method = (form.getAttribute('method') || 'GET').toUpperCase();
        if (method === 'GET' || form.dataset.skipSubmitState === 'true') return;

        preserveFormContext(form);

        const submitter = event.submitter instanceof HTMLElement
            ? event.submitter
            : form.querySelector(SUBMIT_SELECTOR);

        form.dataset.submitting = 'true';
        document.body.classList.add('is-submitting');

        form.querySelectorAll(SUBMIT_SELECTOR).forEach((button) => {
            if (!(button instanceof HTMLButtonElement || button instanceof HTMLInputElement)) return;
            if (submitter && button === submitter) {
                setSubmittingState(button);
                return;
            }
            button.disabled = true;
        });
    });
};

// ── Scroll / section restoration after form POST ─────────────────────────────

const restoreFormContext = () => {
    const raw = sessionStorage.getItem(RESTORE_KEY);
    if (!raw) return;

    sessionStorage.removeItem(RESTORE_KEY);

    let payload = null;
    try { payload = JSON.parse(raw); } catch { return; }

    if (!payload || payload.path !== `${window.location.pathname}${window.location.search}`) return;
    if (Date.now() - Number(payload.timestamp || 0) > 10 * 60 * 1000) return;

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

// ── Flash alerts (auto-dismiss) ───────────────────────────────────────────────

const setupFlashFeedback = () => {
    document.querySelectorAll('[data-auto-dismiss]').forEach((alert) => {
        const timeout = Number(alert.getAttribute('data-auto-dismiss'));
        if (!timeout || !Number.isFinite(timeout)) return;

        window.setTimeout(() => {
            if (!alert.isConnected) return;
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, timeout);
    });

    const flash = document.querySelector('[data-flash-alert], [data-flash-errors]');
    if (flash instanceof HTMLElement) flash.focus({ preventScroll: true });
};

// ── Mobile menu drawer ────────────────────────────────────────────────────────

const setupMobileMenu = () => {
    const trigger  = document.getElementById('menu-trigger');
    const drawer   = document.getElementById('mobile-menu');
    const backdrop = document.getElementById('mobile-menu-backdrop');

    if (!trigger || !drawer) return;

    const open = () => {
        drawer.classList.remove('translate-x-full');
        backdrop?.classList.remove('opacity-0', 'pointer-events-none');
        backdrop?.classList.add('opacity-100');
        document.body.style.overflow = 'hidden';
    };

    const close = () => {
        drawer.classList.add('translate-x-full');
        backdrop?.classList.add('opacity-0', 'pointer-events-none');
        backdrop?.classList.remove('opacity-100');
        document.body.style.overflow = '';
    };

    trigger.addEventListener('click', open);
    backdrop?.addEventListener('click', close);

    drawer.querySelectorAll('[data-menu-link]').forEach(link => {
        link.addEventListener('click', close);
    });
};

// ── Page nav active link (anchor-based) ──────────────────────────────────────

const setupPageNavHighlight = () => {
    const nav = document.querySelector('.page-nav');
    if (!nav) return;

    const links = [...nav.querySelectorAll('a[href^="#"]')];
    if (!links.length) return;

    const activate = (hash) => {
        links.forEach(a => {
            a.classList.toggle('active', a.getAttribute('href') === hash);
        });
    };

    const observer = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                activate(`#${entry.target.id}`);
                break;
            }
        }
    }, { rootMargin: '-40% 0px -55% 0px', threshold: 0 });

    links.forEach(link => {
        const target = document.querySelector(link.getAttribute('href'));
        if (target) observer.observe(target);
    });
};

// ── PWA service worker registration ──────────────────────────────────────────

const registerServiceWorker = () => {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }
};

// ── Boot ──────────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    bindLapTimeInputs();
    handleFormSubmitState();
    restoreFormContext();
    setupFlashFeedback();
    setupMobileMenu();
    setupPageNavHighlight();
    registerServiceWorker();
});
