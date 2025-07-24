/**
 * Form Validation JavaScript
 */

// Form validation utilities
window.FormValidation = {
    rules: {
        required: function(value) {
            return value && value.toString().trim().length > 0;
        },
        
        email: function(value) {
            if (!value) return true; // Optional field
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(value);
        },
        
        minLength: function(value, min) {
            if (!value) return true; // Optional field
            return value.length >= parseInt(min);
        },
        
        maxLength: function(value, max) {
            if (!value) return true; // Optional field
            return value.length <= parseInt(max);
        },
        
        numeric: function(value) {
            if (!value) return true; // Optional field
            return !isNaN(value) && !isNaN(parseFloat(value));
        },
        
        integer: function(value) {
            if (!value) return true; // Optional field
            return Number.isInteger(parseFloat(value));
        },
        
        min: function(value, min) {
            if (!value) return true; // Optional field
            return parseFloat(value) >= parseFloat(min);
        },
        
        max: function(value, max) {
            if (!value) return true; // Optional field
            return parseFloat(value) <= parseFloat(max);
        },
        
        pan: function(value) {
            if (!value) return true; // Optional field
            return PayrollUtils.validatePAN(value);
        },
        
        aadhaar: function(value) {
            if (!value) return true; // Optional field
            return PayrollUtils.validateAadhaar(value);
        },
        
        ifsc: function(value) {
            if (!value) return true; // Optional field
            return PayrollUtils.validateIFSC(value);
        },
        
        uan: function(value) {
            if (!value) return true; // Optional field
            return PayrollUtils.validateUAN(value);
        },
        
        phone: function(value) {
            if (!value) return true; // Optional field
            const phoneRegex = /^[6-9]\d{9}$/;
            return phoneRegex.test(value.replace(/\D/g, ''));
        },
        
        date: function(value) {
            if (!value) return true; // Optional field
            const date = new Date(value);
            return date instanceof Date && !isNaN(date);
        },
        
        url: function(value) {
            if (!value) return true; // Optional field
            try {
                new URL(value);
                return true;
            } catch {
                return false;
            }
        }
    },
    
    messages: {
        required: 'This field is required',
        email: 'Please enter a valid email address',
        minLength: 'Minimum {0} characters required',
        maxLength: 'Maximum {0} characters allowed',
        numeric: 'Please enter a valid number',
        integer: 'Please enter a whole number',
        min: 'Value must be at least {0}',
        max: 'Value must not exceed {0}',
        pan: 'Please enter a valid PAN number (e.g., ABCDE1234F)',
        aadhaar: 'Please enter a valid 12-digit Aadhaar number',
        ifsc: 'Please enter a valid IFSC code (e.g., SBIN0001234)',
        uan: 'Please enter a valid 12-digit UAN number',
        phone: 'Please enter a valid 10-digit phone number',
        date: 'Please enter a valid date',
        url: 'Please enter a valid URL'
    },
    
    // Validate a single field
    validateField: function(field) {
        const value = field.value;
        const rules = field.dataset.validate;
        
        if (!rules) return true;
        
        const ruleList = rules.split('|');
        
        for (let rule of ruleList) {
            const [ruleName, ruleValue] = rule.split(':');
            
            if (this.rules[ruleName]) {
                const isValid = ruleValue ? 
                    this.rules[ruleName](value, ruleValue) : 
                    this.rules[ruleName](value);
                
                if (!isValid) {
                    this.showFieldError(field, this.getErrorMessage(ruleName, ruleValue));
                    return false;
                }
            }
        }
        
        this.clearFieldError(field);
        return true;
    },
    
    // Validate entire form
    validateForm: function(form) {
        const fields = form.querySelectorAll('[data-validate]');
        let isValid = true;
        
        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    },
    
    // Show field error
    showFieldError: function(field, message) {
        this.clearFieldError(field);
        
        field.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error-message text-red-600 text-sm mt-1';
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
    },
    
    // Clear field error
    clearFieldError: function(field) {
        field.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
        
        const errorMessage = field.parentNode.querySelector('.field-error-message');
        if (errorMessage) {
            errorMessage.remove();
        }
    },
    
    // Get error message with parameter substitution
    getErrorMessage: function(ruleName, ruleValue) {
        let message = this.messages[ruleName] || 'Invalid value';
        
        if (ruleValue) {
            message = message.replace('{0}', ruleValue);
        }
        
        return message;
    },
    
    // Setup real-time validation
    setupRealTimeValidation: function(form) {
        const fields = form.querySelectorAll('[data-validate]');
        
        fields.forEach(field => {
            // Validate on blur
            field.addEventListener('blur', () => {
                this.validateField(field);
            });
            
            // Clear error on input
            field.addEventListener('input', () => {
                if (field.parentNode.querySelector('.field-error-message')) {
                    this.clearFieldError(field);
                }
            });
        });
        
        // Validate on form submit
        form.addEventListener('submit', (e) => {
            if (!this.validateForm(form)) {
                e.preventDefault();
                
                // Focus first invalid field
                const firstError = form.querySelector('.border-red-300');
                if (firstError) {
                    firstError.focus();
                }
                
                showMessage('Please correct the errors below', 'error');
            }
        });
    }
};

// Auto-format inputs
document.addEventListener('DOMContentLoaded', function() {
    // PAN number formatting
    document.addEventListener('input', function(e) {
        if (e.target.matches('[data-format="pan"]')) {
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            if (value.length > 10) value = value.substring(0, 10);
            e.target.value = value;
        }
    });

    // Aadhaar number formatting
    document.addEventListener('input', function(e) {
        if (e.target.matches('[data-format="aadhaar"]')) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 4 && value.length <= 8) {
                value = value.slice(0, 4) + ' ' + value.slice(4);
            } else if (value.length > 8) {
                value = value.slice(0, 4) + ' ' + value.slice(4, 8) + ' ' + value.slice(8, 12);
            }
            e.target.value = value;
        }
    });

    // IFSC code formatting
    document.addEventListener('input', function(e) {
        if (e.target.matches('[data-format="ifsc"]')) {
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            if (value.length > 11) value = value.substring(0, 11);
            e.target.value = value;
        }
    });

    // Phone number formatting
    document.addEventListener('input', function(e) {
        if (e.target.matches('[data-format="phone"]')) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 10) value = value.substring(0, 10);
            e.target.value = value;
        }
    });

    // Currency formatting
    document.addEventListener('blur', function(e) {
        if (e.target.matches('[data-format="currency"]')) {
            const value = parseFloat(e.target.value) || 0;
            e.target.value = value.toFixed(2);
        }
    });

    // UAN formatting
    document.addEventListener('input', function(e) {
        if (e.target.matches('[data-format="uan"]')) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 4 && value.length <= 8) {
                value = value.slice(0, 4) + ' ' + value.slice(4);
            } else if (value.length > 8) {
                value = value.slice(0, 4) + ' ' + value.slice(4, 8) + ' ' + value.slice(8, 12);
            }
            e.target.value = value;
        }
    });

    // Setup validation for all forms with data-validate attributes
    document.querySelectorAll('form').forEach(form => {
        if (form.querySelector('[data-validate]')) {
            FormValidation.setupRealTimeValidation(form);
        }
    });
});

// Export for global access
window.Validator = FormValidation;