/**
 * AJAX utilities and helpers
 */

window.AjaxUtils = {
    // Default configuration
    defaults: {
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        timeout: 30000
    },
    
    // Get CSRF token
    getCSRFToken: function() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    },
    
    // Make AJAX request
    request: function(url, options = {}) {
        const config = {
            ...this.defaults,
            ...options
        };
        
        // Add CSRF token for non-GET requests
        if (config.method && config.method !== 'GET') {
            const token = this.getCSRFToken();
            if (token) {
                config.headers['X-CSRF-Token'] = token;
                
                // Add token to body if it's JSON
                if (config.body && typeof config.body === 'object') {
                    config.body.csrf_token = token;
                }
            }
        }
        
        // Convert body to JSON if it's an object
        if (config.body && typeof config.body === 'object' && !(config.body instanceof FormData)) {
            config.body = JSON.stringify(config.body);
        }
        
        // Create abort controller for timeout
        const controller = new AbortController();
        config.signal = controller.signal;
        
        const timeoutId = setTimeout(() => {
            controller.abort();
        }, config.timeout);
        
        return fetch(url, config)
            .then(response => {
                clearTimeout(timeoutId);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    return response;
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);
                
                if (error.name === 'AbortError') {
                    throw new Error('Request timeout');
                }
                
                console.error('AJAX Error:', error);
                throw error;
            });
    },
    
    // GET request
    get: function(url, params = {}) {
        const urlObj = new URL(url, window.location.origin);
        Object.keys(params).forEach(key => {
            urlObj.searchParams.append(key, params[key]);
        });
        
        return this.request(urlObj.toString(), { method: 'GET' });
    },
    
    // POST request
    post: function(url, data = {}) {
        return this.request(url, {
            method: 'POST',
            body: data
        });
    },
    
    // PUT request
    put: function(url, data = {}) {
        return this.request(url, {
            method: 'PUT',
            body: data
        });
    },
    
    // DELETE request
    delete: function(url, data = {}) {
        return this.request(url, {
            method: 'DELETE',
            body: data
        });
    },
    
    // Upload file
    uploadFile: function(url, file, additionalData = {}) {
        const formData = new FormData();
        formData.append('file', file);
        
        Object.keys(additionalData).forEach(key => {
            formData.append(key, additionalData[key]);
        });
        
        return this.request(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
                // Don't set Content-Type for FormData
            }
        });
    },
    
    // Download file
    downloadFile: function(url, filename, params = {}) {
        const urlObj = new URL(url, window.location.origin);
        Object.keys(params).forEach(key => {
            urlObj.searchParams.append(key, params[key]);
        });
        
        return fetch(urlObj.toString())
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            });
    }
};

// Form submission helper
window.FormSubmitter = {
    // Submit form via AJAX
    submit: function(form, options = {}) {
        const formData = new FormData(form);
        const url = form.action || window.location.href;
        const method = form.method || 'POST';
        
        // Convert FormData to object if needed
        let data;
        if (options.json) {
            data = {};
            for (let [key, value] of formData.entries()) {
                if (data[key]) {
                    // Handle multiple values (checkboxes, etc.)
                    if (Array.isArray(data[key])) {
                        data[key].push(value);
                    } else {
                        data[key] = [data[key], value];
                    }
                } else {
                    data[key] = value;
                }
            }
        } else {
            data = formData;
        }
        
        const config = {
            method: method,
            body: data
        };
        
        if (!options.json) {
            config.headers = {
                'X-Requested-With': 'XMLHttpRequest'
                // Don't set Content-Type for FormData
            };
        }
        
        return AjaxUtils.request(url, config);
    },
    
    // Submit form with loading state
    submitWithLoading: function(form, options = {}) {
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton ? submitButton.innerHTML : '';
        
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        }
        
        showLoading();
        
        return this.submit(form, options)
            .then(response => {
                hideLoading();
                
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
                
                return response;
            })
            .catch(error => {
                hideLoading();
                
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
                
                throw error;
            });
    }
};

// Auto-setup AJAX forms
document.addEventListener('DOMContentLoaded', function() {
    // Setup forms with data-ajax attribute
    document.querySelectorAll('form[data-ajax]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const options = {
                json: this.dataset.ajax === 'json'
            };
            
            FormSubmitter.submitWithLoading(this, options)
                .then(response => {
                    if (response.success) {
                        showMessage(response.message || 'Operation completed successfully', 'success');
                        
                        // Handle redirect
                        if (response.redirect) {
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1500);
                        }
                        
                        // Handle reload
                        if (response.reload) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        }
                        
                        // Handle reset
                        if (response.reset) {
                            this.reset();
                        }
                    } else {
                        showMessage(response.message || 'Operation failed', 'error');
                        
                        // Handle validation errors
                        if (response.errors) {
                            this.displayValidationErrors(response.errors);
                        }
                    }
                })
                .catch(error => {
                    console.error('Form submission error:', error);
                    showMessage('An error occurred. Please try again.', 'error');
                });
        });
    });
});

// API endpoints
window.API = {
    // Employee endpoints
    employees: {
        search: (query) => AjaxUtils.get('/api/employee-search', { q: query }),
        generateCode: () => AjaxUtils.get('/api/generate-employee-code'),
        bulkUpdate: (data) => AjaxUtils.post('/employees/bulk-update-salary', data)
    },
    
    // Payroll endpoints
    payroll: {
        getCurrentPeriod: () => AjaxUtils.get('/api/current-period'),
        calculateSalary: (params) => AjaxUtils.get('/api/salary-calculator', params),
        processPayroll: (data) => AjaxUtils.post('/payroll/process', data)
    },
    
    // Attendance endpoints
    attendance: {
        getSummary: (date) => AjaxUtils.get('/api/attendance-summary', { date }),
        markAttendance: (data) => AjaxUtils.post('/attendance/mark', data),
        bulkMark: (data) => AjaxUtils.post('/attendance/bulk-mark', data)
    },
    
    // Notification endpoints
    notifications: {
        getUnreadCount: () => AjaxUtils.get('/api/notifications/unread-count'),
        markAsRead: (id) => AjaxUtils.post('/notifications/mark-read', { notification_id: id }),
        markAllAsRead: () => AjaxUtils.post('/notifications/mark-all-read'),
        delete: (id) => AjaxUtils.post('/notifications/delete', { notification_id: id })
    }
};

// Export for global access
window.Ajax = AjaxUtils;
window.Forms = FormSubmitter;