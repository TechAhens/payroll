/**
 * Modal utilities and management
 */

window.ModalUtils = {
    // Open modal
    open: function(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Focus first input
        const firstInput = modal.querySelector('input, select, textarea, button');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
        
        // Add escape key listener
        this.addEscapeListener(modalId);
    },
    
    // Close modal
    close: function(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        
        // Clear form if exists
        const form = modal.querySelector('form');
        if (form && form.dataset.clearOnClose !== 'false') {
            form.reset();
            this.clearFormErrors(form);
        }
        
        // Remove escape key listener
        this.removeEscapeListener(modalId);
    },
    
    // Toggle modal
    toggle: function(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        if (modal.classList.contains('hidden')) {
            this.open(modalId);
        } else {
            this.close(modalId);
        }
    },
    
    // Add escape key listener
    addEscapeListener: function(modalId) {
        const escapeHandler = (e) => {
            if (e.key === 'Escape') {
                this.close(modalId);
            }
        };
        
        document.addEventListener('keydown', escapeHandler);
        
        // Store handler for removal
        const modal = document.getElementById(modalId);
        if (modal) {
            modal._escapeHandler = escapeHandler;
        }
    },
    
    // Remove escape key listener
    removeEscapeListener: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal && modal._escapeHandler) {
            document.removeEventListener('keydown', modal._escapeHandler);
            delete modal._escapeHandler;
        }
    },
    
    // Clear form errors
    clearFormErrors: function(form) {
        form.querySelectorAll('.field-error-message').forEach(el => el.remove());
        form.querySelectorAll('.border-red-300').forEach(el => {
            el.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
        });
    },
    
    // Create dynamic modal
    create: function(options) {
        const modalId = options.id || 'dynamic-modal-' + Date.now();
        const size = options.size || 'md';
        
        const sizeClasses = {
            sm: 'max-w-sm',
            md: 'max-w-md',
            lg: 'max-w-lg',
            xl: 'max-w-xl',
            '2xl': 'max-w-2xl',
            '4xl': 'max-w-4xl'
        };
        
        const modalHtml = `
            <div id="${modalId}" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
                <div class="relative top-20 mx-auto p-5 border w-full ${sizeClasses[size]} shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        ${options.header ? `
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">${options.header}</h3>
                                <button type="button" onclick="ModalUtils.close('${modalId}')" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        ` : ''}
                        
                        <div class="modal-content">
                            ${options.content || ''}
                        </div>
                        
                        ${options.footer ? `
                            <div class="flex items-center justify-end space-x-4 mt-6">
                                ${options.footer}
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Auto-open if specified
        if (options.autoOpen) {
            this.open(modalId);
        }
        
        return modalId;
    },
    
    // Confirm dialog
    confirm: function(message, onConfirm, onCancel) {
        const modalId = this.create({
            header: 'Confirm Action',
            content: `<p class="text-gray-600">${message}</p>`,
            footer: `
                <button type="button" onclick="ModalUtils.close('${modalId}'); ${onCancel ? onCancel.toString() + '()' : ''}" class="btn btn-outline">
                    Cancel
                </button>
                <button type="button" onclick="ModalUtils.close('${modalId}'); ${onConfirm ? onConfirm.toString() + '()' : ''}" class="btn btn-primary">
                    Confirm
                </button>
            `,
            autoOpen: true,
            size: 'sm'
        });
        
        // Auto-remove after closing
        setTimeout(() => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.remove();
            }
        }, 1000);
    },
    
    // Alert dialog
    alert: function(message, type = 'info') {
        const icons = {
            success: 'fa-check-circle text-green-500',
            error: 'fa-exclamation-circle text-red-500',
            warning: 'fa-exclamation-triangle text-yellow-500',
            info: 'fa-info-circle text-blue-500'
        };
        
        const modalId = this.create({
            header: type.charAt(0).toUpperCase() + type.slice(1),
            content: `
                <div class="flex items-center">
                    <i class="fas ${icons[type]} text-2xl mr-3"></i>
                    <p class="text-gray-600">${message}</p>
                </div>
            `,
            footer: `
                <button type="button" onclick="ModalUtils.close('${modalId}')" class="btn btn-primary">
                    OK
                </button>
            `,
            autoOpen: true,
            size: 'sm'
        });
        
        // Auto-remove after closing
        setTimeout(() => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.remove();
            }
        }, 1000);
    }
};

// Auto-setup modal triggers
document.addEventListener('DOMContentLoaded', function() {
    // Setup modal triggers
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-modal-open]')) {
            e.preventDefault();
            const modalId = e.target.dataset.modalOpen;
            ModalUtils.open(modalId);
        }
        
        if (e.target.matches('[data-modal-close]')) {
            e.preventDefault();
            const modalId = e.target.dataset.modalClose;
            ModalUtils.close(modalId);
        }
        
        if (e.target.matches('[data-modal-toggle]')) {
            e.preventDefault();
            const modalId = e.target.dataset.modalToggle;
            ModalUtils.toggle(modalId);
        }
    });
    
    // Close modal when clicking backdrop
    document.addEventListener('click', function(e) {
        if (e.target.matches('.fixed.inset-0.bg-gray-600')) {
            const modalId = e.target.id;
            if (modalId) {
                ModalUtils.close(modalId);
            }
        }
    });
    
    // Confirm dialogs
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-confirm]')) {
            e.preventDefault();
            const message = e.target.dataset.confirm;
            const action = e.target.onclick || function() {
                if (e.target.href) {
                    window.location.href = e.target.href;
                } else if (e.target.form) {
                    e.target.form.submit();
                }
            };
            
            ModalUtils.confirm(message, action);
        }
    });
});

// Export for global access
window.Modal = ModalUtils;