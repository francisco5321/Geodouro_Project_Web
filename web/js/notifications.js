const notificationState = {
    toastHost: null,
    loadingOverlay: null,
};

function ensureNotificationStyles() {
    if (document.getElementById('gf-notification-styles')) {
        return;
    }

    const style = document.createElement('style');
    style.id = 'gf-notification-styles';
    style.textContent = `
        .gf-toast-host {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1080;
            display: grid;
            gap: 0.75rem;
            width: min(22rem, calc(100vw - 2rem));
        }
        .gf-toast {
            display: grid;
            gap: 0.2rem;
            padding: 0.9rem 1rem;
            border-radius: 16px;
            color: #173320;
            background: #f4fbf4;
            border: 1px solid rgba(62, 122, 87, 0.18);
            box-shadow: 0 18px 40px rgba(22, 46, 31, 0.16);
            transform: translateY(-8px);
            opacity: 0;
            transition: opacity 0.18s ease, transform 0.18s ease;
        }
        .gf-toast.is-visible {
            opacity: 1;
            transform: translateY(0);
        }
        .gf-toast--success { background: #edf9ef; }
        .gf-toast--error { background: #fff1f0; color: #6d1f1a; border-color: rgba(157, 52, 39, 0.18); }
        .gf-toast--warning { background: #fff8ea; color: #6c4b00; border-color: rgba(177, 134, 25, 0.22); }
        .gf-toast--info { background: #eef6ff; color: #184f84; border-color: rgba(39, 91, 157, 0.2); }
        .gf-toast-title {
            font-size: 0.92rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .gf-toast-text {
            font-size: 0.88rem;
            line-height: 1.45;
        }
        .gf-loading-overlay {
            position: fixed;
            inset: 0;
            z-index: 1090;
            display: grid;
            place-items: center;
            background: rgba(17, 25, 20, 0.34);
            backdrop-filter: blur(4px);
        }
        .gf-loading-card {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            min-width: min(22rem, calc(100vw - 2rem));
            padding: 1rem 1.1rem;
            border-radius: 18px;
            background: #ffffff;
            color: #203528;
            box-shadow: 0 22px 60px rgba(19, 32, 23, 0.2);
        }
        .gf-loading-spinner {
            width: 1.1rem;
            height: 1.1rem;
            border-radius: 50%;
            border: 2px solid rgba(62, 122, 87, 0.18);
            border-top-color: #3e7a57;
            animation: gf-spin 0.8s linear infinite;
        }
        @keyframes gf-spin {
            to { transform: rotate(360deg); }
        }
    `;

    document.head.appendChild(style);
}

function ensureToastHost() {
    ensureNotificationStyles();
    if (!notificationState.toastHost) {
        const host = document.createElement('div');
        host.className = 'gf-toast-host';
        document.body.appendChild(host);
        notificationState.toastHost = host;
    }

    return notificationState.toastHost;
}

function showToast(type, title, message, options = {}) {
    const host = ensureToastHost();
    const toast = document.createElement('section');
    toast.className = `gf-toast gf-toast--${type}`;
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
    toast.innerHTML = `
        <strong class="gf-toast-title"></strong>
        <div class="gf-toast-text"></div>
    `;
    toast.querySelector('.gf-toast-title').textContent = title;
    toast.querySelector('.gf-toast-text').textContent = message;
    host.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.add('is-visible');
    });

    const duration = Math.max(1500, Number(options.timer) || 4000);
    const removeToast = () => {
        toast.classList.remove('is-visible');
        window.setTimeout(() => {
            toast.remove();
        }, 220);
    };

    toast.addEventListener('click', removeToast, { once: true });
    window.setTimeout(removeToast, duration);
}

const Notification = {
    success(message, options = {}) {
        showToast('success', 'Sucesso', message, options);
    },

    error(message, options = {}) {
        showToast('error', 'Erro', message, options);
    },

    warning(message, options = {}) {
        showToast('warning', 'Aviso', message, options);
    },

    info(message, options = {}) {
        showToast('info', 'Informacao', message, options);
    },

    confirm(message, onConfirm, title = 'Confirmar', options = {}) {
        const confirmText = options.confirmButtonText || 'Confirmar';
        const prompt = `${title}\n\n${message}\n\n${confirmText}?`;
        if (window.confirm(prompt) && typeof onConfirm === 'function') {
            onConfirm();
        }
    },

    loading(message = 'Carregando...') {
        this.close();
        ensureNotificationStyles();

        const overlay = document.createElement('div');
        overlay.className = 'gf-loading-overlay';
        overlay.innerHTML = `
            <div class="gf-loading-card" role="status" aria-live="polite">
                <span class="gf-loading-spinner" aria-hidden="true"></span>
                <strong>${message}</strong>
            </div>
        `;

        document.body.appendChild(overlay);
        notificationState.loadingOverlay = overlay;
    },

    close() {
        notificationState.loadingOverlay?.remove();
        notificationState.loadingOverlay = null;
    }
};

// Expose to global scope
window.Notification = Notification;

if (window.yii) {
    window.yii.confirm = function(message, ok, cancel) {
        Notification.confirm(message, () => {
            if (typeof ok === 'function') {
                ok();
            }
        }, 'Confirmar acao', {
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar'
        });

        return false;
    };
}

/**
 * Automatically show validation errors if present
 */
document.addEventListener('DOMContentLoaded', function() {
    const formErrors = document.querySelectorAll('.invalid-feedback');
    if (formErrors.length > 0) {
        const errorMessages = Array.from(formErrors).filter((el) => el.textContent.trim()).slice(0, 3);

        if (errorMessages.length > 0) {
            Notification.error('Por favor, corrija os erro(s) no formulario.', { timer: 6000 });
        }
    }
});
