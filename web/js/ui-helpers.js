/**
 * UI Helpers
 * Common UI utilities and effects
 */

const UIHelpers = {
    /**
     * Add loading state to button
     * @param {HTMLElement} button - Button element
     */
    setButtonLoading(button) {
        button.disabled = true;
        button.classList.add('btn-loading');
        button.dataset.originalText = button.innerHTML;
    },

    /**
     * Remove loading state from button
     * @param {HTMLElement} button - Button element
     */
    unsetButtonLoading(button) {
        button.disabled = false;
        button.classList.remove('btn-loading');
        if (button.dataset.originalText) {
            button.innerHTML = button.dataset.originalText;
        }
    },

    /**
     * Setup file upload drag-and-drop
     * @param {HTMLElement} zone - Upload zone element
     * @param {HTMLInputElement} input - File input element
     * @param {Function} onFilesSelect - Callback with files
     */
    setupFileUpload(zone, input, onFilesSelect) {
        if (!zone || !input) return;

        // Click to browse
        zone.addEventListener('click', () => input.click());

        // Drag and drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            zone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            zone.addEventListener(eventName, () => {
                zone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            zone.addEventListener(eventName, () => {
                zone.classList.remove('is-dragover');
            });
        });

        // Handle drop
        zone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (onFilesSelect) onFilesSelect(files);
        });

        // Handle input change
        input.addEventListener('change', (e) => {
            if (onFilesSelect) onFilesSelect(e.target.files);
        });
    },

    /**
     * Initialize lightbox gallery
     * Auto-initialize FSLightbox if library is available
     */
    initGallery() {
        if (typeof FsLightbox !== 'undefined') {
            const galleries = document.querySelectorAll('[data-lightbox]');
            galleries.forEach(link => {
                link.setAttribute('data-fslightbox', 'gallery');
            });
            // Reinitialize FSLightbox after adding attributes
            if (window.refreshFsLightbox) {
                window.refreshFsLightbox();
            }
        }
    },

    /**
     * Setup table sorting
     * @param {HTMLElement} table - Table element
     */
    setupTableSorting(table) {
        if (!table) return;

        const headers = table.querySelectorAll('th[data-sortable]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', (e) => {
                const column = header.dataset.column;
                const direction = header.dataset.direction === 'asc' ? 'desc' : 'asc';
                
                // Update all headers
                headers.forEach(h => {
                    h.classList.remove('sorted-asc', 'sorted-desc');
                    h.dataset.direction = '';
                });
                
                header.classList.add(`sorted-${direction}`);
                header.dataset.direction = direction;

                // Trigger sort event
                const event = new CustomEvent('table:sort', {
                    detail: { column, direction }
                });
                table.dispatchEvent(event);
            });
        });
    },

    /**
     * Add tooltip to element
     * @param {HTMLElement} element - Element to add tooltip
     * @param {string} text - Tooltip text
     */
    addTooltip(element, text) {
        element.setAttribute('title', text);
        element.setAttribute('data-bs-toggle', 'tooltip');
        element.setAttribute('data-bs-placement', 'top');
        
        // Initialize Bootstrap tooltip if available
        if (typeof bootstrap !== 'undefined') {
            new bootstrap.Tooltip(element);
        }
    },

    /**
     * Format number with thousands separator
     * @param {number} num - Number to format
     * @returns {string} Formatted number
     */
    formatNumber(num) {
        return new Intl.NumberFormat('pt-PT').format(num);
    },

    /**
     * Format date to Portuguese locale
     * @param {Date|string} date - Date to format
     * @param {string} format - 'short', 'long', 'datetime'
     * @returns {string} Formatted date
     */
    formatDate(date, format = 'short') {
        if (typeof date === 'string') {
            date = new Date(date);
        }

        const options = {
            short: { year: 'numeric', month: '2-digit', day: '2-digit' },
            long: { year: 'numeric', month: 'long', day: 'numeric' },
            datetime: { 
                year: 'numeric', month: '2-digit', day: '2-digit',
                hour: '2-digit', minute: '2-digit'
            }
        };

        return new Intl.DateTimeFormat('pt-PT', options[format] || options.short).format(date);
    },

    /**
     * Copy text to clipboard
     * @param {string} text - Text to copy
     */
    copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                Notification.success('Copiado para a área de transferência');
            }).catch(err => {
                Notification.error('Erro ao copiar para clipboard');
            });
        } else {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            Notification.success('Copiado para a área de transferência');
        }
    },

    /**
     * Disable form until file upload completes
     * @param {HTMLFormElement} form - Form element
     * @param {boolean} disabled - Disable state
     */
    setFormDisabled(form, disabled) {
        const inputs = form.querySelectorAll('input, select, textarea, button');
        inputs.forEach(input => {
            input.disabled = disabled;
        });
    },

    /**
     * Get geolocation
     * @param {Function} onSuccess - Success callback with {lat, lng}
     * @param {Function} onError - Error callback
     */
    getGeolocation(onSuccess, onError) {
        if (!navigator.geolocation) {
            onError('Geolocalização não suportada');
            return;
        }

        Notification.info('Obtendo localização...');
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                onSuccess({ lat: latitude, lng: longitude });
                Notification.close();
            },
            (error) => {
                let message = 'Erro ao obter localização';
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        message = 'Permissão de localização negada';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        message = 'Informação de localização não disponível';
                        break;
                    case error.TIMEOUT:
                        message = 'Timeout obtendo localização';
                        break;
                }
                onError(message);
                Notification.error(message);
            }
        );
    },

    /**
     * Format file size in human-readable format
     * @param {number} bytes - File size in bytes
     * @returns {string} Formatted size
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return (bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i];
    },

    /**
     * Validate file type
     * @param {File} file - File to validate
     * @param {string[]} allowedTypes - Allowed MIME types
     * @returns {boolean}
     */
    isValidFileType(file, allowedTypes) {
        return allowedTypes.some(type => {
            if (type.includes('*')) {
                // Handle wildcards like 'image/*'
                const [category] = type.split('/');
                return file.type.startsWith(category + '/');
            }
            return file.type === type;
        });
    },

    /**
     * Auto-resize textarea to content
     * @param {HTMLTextAreaElement} textarea - Textarea element
     */
    autoResizeTextarea(textarea) {
        if (!textarea) return;

        function resize() {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        }

        resize();
        textarea.addEventListener('input', resize);
    },

    /**
     * Highlight element briefly
     * @param {HTMLElement} element - Element to highlight
     * @param {string} color - Highlight color
     */
    highlight(element, color = 'rgba(62, 122, 87, 0.1)') {
        const originalBg = element.style.backgroundColor;
        element.style.backgroundColor = color;
        setTimeout(() => {
            element.style.transition = 'background-color 0.3s ease';
            element.style.backgroundColor = originalBg;
            setTimeout(() => {
                element.style.transition = '';
            }, 300);
        }, 100);
    },

    /**
     * Setup user dropdown menu toggle
     * @param {HTMLElement} dropdown - Dropdown container
     */
    setupUserDropdown(dropdown) {
        if (!dropdown) return;

        const button = dropdown.querySelector('.user-button');
        if (!button) return;

        button.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropdown.classList.toggle('is-open');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('is-open');
            }
        });

        // Close dropdown when clicking on a menu item
        dropdown.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', () => {
                dropdown.classList.remove('is-open');
            });
        });
    }
};

// Expose to global scope
window.UIHelpers = UIHelpers;

// Auto-initialize galleries on document ready
document.addEventListener('DOMContentLoaded', () => {
    UIHelpers.initGallery();

    // Setup file uploads
    document.querySelectorAll('[data-file-upload]').forEach(zone => {
        const input = zone.querySelector('input[type="file"]');
        if (input) {
            UIHelpers.setupFileUpload(zone, input, (files) => {
                const event = new CustomEvent('files:selected', { detail: { files } });
                zone.dispatchEvent(event);
            });
        }
    });

    // Setup table sorting
    document.querySelectorAll('[data-sortable-table]').forEach(table => {
        UIHelpers.setupTableSorting(table);
    });

    // Auto-resize textareas
    document.querySelectorAll('textarea[data-auto-resize]').forEach(textarea => {
        UIHelpers.autoResizeTextarea(textarea);
    });

    // Setup user dropdown menu
    const userDropdown = document.querySelector('.user-dropdown');
    if (userDropdown) {
        UIHelpers.setupUserDropdown(userDropdown);
    }
});

// Handle CSRF token for AJAX requests
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrfToken) {
        // Store CSRF token for AJAX requests
        window.csrfToken = csrfToken;
    }
});
