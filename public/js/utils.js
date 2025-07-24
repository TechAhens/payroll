/**
 * Utility functions for the Payroll System
 */

// Global utility object
window.PayrollUtils = window.PayrollUtils || {};

// Number formatting utilities
PayrollUtils.formatCurrency = function(amount, currency = '₹', locale = 'en-IN') {
    return currency + parseFloat(amount || 0).toLocaleString(locale, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

PayrollUtils.formatNumber = function(number, decimals = 0) {
    return parseFloat(number || 0).toLocaleString('en-IN', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
};

// Date formatting utilities
PayrollUtils.formatDate = function(dateString, format = 'dd/mm/yyyy') {
    if (!dateString) return '';
    const date = new Date(dateString);
    
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    
    switch (format) {
        case 'dd/mm/yyyy':
            return `${day}/${month}/${year}`;
        case 'mm/dd/yyyy':
            return `${month}/${day}/${year}`;
        case 'yyyy-mm-dd':
            return `${year}-${month}-${day}`;
        case 'dd-mm-yyyy':
            return `${day}-${month}-${year}`;
        default:
            return date.toLocaleDateString();
    }
};

PayrollUtils.timeAgo = function(dateString) {
    const now = new Date();
    const date = new Date(dateString);
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) return 'just now';
    if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' minutes ago';
    if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' hours ago';
    if (diffInSeconds < 2592000) return Math.floor(diffInSeconds / 86400) + ' days ago';
    
    return PayrollUtils.formatDate(dateString);
};

// Validation utilities
PayrollUtils.validateEmail = function(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
};

PayrollUtils.validatePAN = function(pan) {
    const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
    return panRegex.test(pan);
};

PayrollUtils.validateAadhaar = function(aadhaar) {
    const cleanAadhaar = aadhaar.replace(/\s/g, '');
    return cleanAadhaar.length === 12 && /^\d{12}$/.test(cleanAadhaar);
};

PayrollUtils.validateIFSC = function(ifsc) {
    const ifscRegex = /^[A-Z]{4}0[A-Z0-9]{6}$/;
    return ifscRegex.test(ifsc);
};

PayrollUtils.validateUAN = function(uan) {
    const cleanUAN = uan.replace(/\s/g, '');
    return cleanUAN.length === 12 && /^\d{12}$/.test(cleanUAN);
};

// String utilities
PayrollUtils.capitalize = function(str) {
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

PayrollUtils.titleCase = function(str) {
    return str.replace(/\w\S*/g, function(txt) {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
};

PayrollUtils.slugify = function(str) {
    return str
        .toLowerCase()
        .replace(/[^\w ]+/g, '')
        .replace(/ +/g, '-');
};

// Array utilities
PayrollUtils.groupBy = function(array, key) {
    return array.reduce((result, item) => {
        const group = item[key];
        if (!result[group]) {
            result[group] = [];
        }
        result[group].push(item);
        return result;
    }, {});
};

PayrollUtils.sortBy = function(array, key, direction = 'asc') {
    return array.sort((a, b) => {
        const aVal = a[key];
        const bVal = b[key];
        
        if (direction === 'asc') {
            return aVal > bVal ? 1 : -1;
        } else {
            return aVal < bVal ? 1 : -1;
        }
    });
};

// Local storage utilities
PayrollUtils.storage = {
    set: function(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (e) {
            console.error('Failed to save to localStorage:', e);
        }
    },
    
    get: function(key, defaultValue = null) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : defaultValue;
        } catch (e) {
            console.error('Failed to read from localStorage:', e);
            return defaultValue;
        }
    },
    
    remove: function(key) {
        try {
            localStorage.removeItem(key);
        } catch (e) {
            console.error('Failed to remove from localStorage:', e);
        }
    }
};

// URL utilities
PayrollUtils.url = {
    addParam: function(url, key, value) {
        const urlObj = new URL(url, window.location.origin);
        urlObj.searchParams.set(key, value);
        return urlObj.toString();
    },
    
    removeParam: function(url, key) {
        const urlObj = new URL(url, window.location.origin);
        urlObj.searchParams.delete(key);
        return urlObj.toString();
    },
    
    getParam: function(key) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(key);
    }
};

// File utilities
PayrollUtils.file = {
    downloadBlob: function(blob, filename) {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    },
    
    readFileAsText: function(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = e => resolve(e.target.result);
            reader.onerror = e => reject(e);
            reader.readAsText(file);
        });
    },
    
    validateFileType: function(file, allowedTypes) {
        return allowedTypes.includes(file.type);
    },
    
    validateFileSize: function(file, maxSizeInMB) {
        const maxSizeInBytes = maxSizeInMB * 1024 * 1024;
        return file.size <= maxSizeInBytes;
    }
};

// DOM utilities
PayrollUtils.dom = {
    createElement: function(tag, attributes = {}, content = '') {
        const element = document.createElement(tag);
        
        Object.keys(attributes).forEach(key => {
            if (key === 'className') {
                element.className = attributes[key];
            } else {
                element.setAttribute(key, attributes[key]);
            }
        });
        
        if (content) {
            element.innerHTML = content;
        }
        
        return element;
    },
    
    show: function(selector) {
        const elements = document.querySelectorAll(selector);
        elements.forEach(el => el.classList.remove('hidden'));
    },
    
    hide: function(selector) {
        const elements = document.querySelectorAll(selector);
        elements.forEach(el => el.classList.add('hidden'));
    },
    
    toggle: function(selector) {
        const elements = document.querySelectorAll(selector);
        elements.forEach(el => el.classList.toggle('hidden'));
    }
};

// Animation utilities
PayrollUtils.animate = {
    fadeIn: function(element, duration = 300) {
        element.style.opacity = '0';
        element.style.display = 'block';
        
        const start = performance.now();
        
        function animate(currentTime) {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);
            
            element.style.opacity = progress;
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        }
        
        requestAnimationFrame(animate);
    },
    
    fadeOut: function(element, duration = 300) {
        const start = performance.now();
        const startOpacity = parseFloat(element.style.opacity) || 1;
        
        function animate(currentTime) {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);
            
            element.style.opacity = startOpacity * (1 - progress);
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                element.style.display = 'none';
            }
        }
        
        requestAnimationFrame(animate);
    },
    
    slideDown: function(element, duration = 300) {
        element.style.height = '0px';
        element.style.overflow = 'hidden';
        element.style.display = 'block';
        
        const targetHeight = element.scrollHeight + 'px';
        
        element.style.transition = `height ${duration}ms ease-out`;
        element.style.height = targetHeight;
        
        setTimeout(() => {
            element.style.height = '';
            element.style.overflow = '';
            element.style.transition = '';
        }, duration);
    }
};

// Debounce utility
PayrollUtils.debounce = function(func, wait, immediate) {
    let timeout;
    return function executedFunction() {
        const context = this;
        const args = arguments;
        
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        
        if (callNow) func.apply(context, args);
    };
};

// Throttle utility
PayrollUtils.throttle = function(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
};

// Export for global access
window.Utils = PayrollUtils;