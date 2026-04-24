/**
 * Notification System
 * Toast notifications using SweetAlert2
 */

const Notification = {
    /**
     * Show success notification
     * @param {string} message - Success message
     * @param {Object} options - Additional options
     */
    success(message, options = {}) {
        Swal.fire({
            icon: 'success',
            title: 'Sucesso!',
            text: message,
            toast: true,
            position: options.position || 'top-end',
            showConfirmButton: false,
            timer: options.timer || 4000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
            ...options
        });
    },

    /**
     * Show error notification
     * @param {string} message - Error message
     * @param {Object} options - Additional options
     */
    error(message, options = {}) {
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: message,
            toast: true,
            position: options.position || 'top-end',
            showConfirmButton: false,
            timer: options.timer || 5000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
            ...options
        });
    },

    /**
     * Show warning notification
     * @param {string} message - Warning message
     * @param {Object} options - Additional options
     */
    warning(message, options = {}) {
        Swal.fire({
            icon: 'warning',
            title: 'Aviso',
            text: message,
            toast: true,
            position: options.position || 'top-end',
            showConfirmButton: false,
            timer: options.timer || 4000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
            ...options
        });
    },

    /**
     * Show info notification
     * @param {string} message - Info message
     * @param {Object} options - Additional options
     */
    info(message, options = {}) {
        Swal.fire({
            icon: 'info',
            title: 'Informação',
            text: message,
            toast: true,
            position: options.position || 'top-end',
            showConfirmButton: false,
            timer: options.timer || 4000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
            ...options
        });
    },

    /**
     * Show confirmation dialog
     * @param {string} message - Confirmation message
     * @param {Function} onConfirm - Callback if confirmed
     * @param {string} title - Dialog title
     * @param {Object} options - Additional SweetAlert options
     */
    confirm(message, onConfirm, title = 'Confirmar', options = {}) {
        Swal.fire({
            title: title,
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3e7a57',
            cancelButtonColor: '#757575',
            confirmButtonText: 'Sim, confirmar',
            cancelButtonText: 'Cancelar',
            ...options
        }).then((result) => {
            if (result.isConfirmed && onConfirm) {
                onConfirm();
            }
        });
    },

    /**
     * Show loading dialog
     * @param {string} message - Loading message
     */
    loading(message = 'Carregando...') {
        Swal.fire({
            title: message,
            icon: 'info',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    },

    /**
     * Close any open notification
     */
    close() {
        Swal.close();
    }
};

// Expose to global scope
window.Notification = Notification;

function installYiiConfirmAdapter() {
    if (!window.yii) {
        return;
    }

    window.yii.confirm = function(message, ok, cancel) {
        Notification.confirm(message, ok, 'Confirmar ação', {
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar'
        });

        return false;
    };
}

installYiiConfirmAdapter();
document.addEventListener('DOMContentLoaded', installYiiConfirmAdapter);

/**
 * Automatically show validation errors if present
 */
document.addEventListener('DOMContentLoaded', function() {
    // Check for form validation errors
    const formErrors = document.querySelectorAll('.invalid-feedback');
    if (formErrors.length > 0) {
        const errorMessages = Array.from(formErrors)
            .map(el => el.textContent)
            .filter(msg => msg.trim())
            .slice(0, 3);

        if (errorMessages.length > 0) {
            Notification.error(
                'Por favor, corrija os erro(s) no formulário.',
                { timer: 6000 }
            );
        }
    }
});
