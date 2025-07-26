/**
 * Employee management JavaScript functionality
 */

// Employee utilities
window.EmployeeUtils = {
    // Auto-generate employee code
    generateEmployeeCode: function() {
        fetch('/api/generate-employee-code')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const empCodeInput = document.getElementById('emp_code');
                    if (empCodeInput && !empCodeInput.value) {
                        empCodeInput.value = data.code;
                    }
                }
            })
            .catch(error => {
                console.error('Error generating employee code:', error);
            });
    },
    
    // Search employees with autocomplete
    searchEmployees: function(query, callback) {
        if (query.length < 2) {
            callback([]);
            return;
        }
        
        fetch(`/api/employee-search?q=${encodeURIComponent(query)}&limit=10`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    callback(data.employees);
                } else {
                    callback([]);
                }
            })
            .catch(error => {
                console.error('Employee search error:', error);
                callback([]);
            });
    },
    
    // Validate employee data
    validateEmployeeData: function(formData) {
        const errors = {};
        
        // Required field validation
        const requiredFields = ['first_name', 'last_name', 'join_date', 'department_id', 'designation_id'];
        requiredFields.forEach(field => {
            if (!formData.get(field)) {
                errors[field] = 'This field is required';
            }
        });
        
        // Email validation
        const email = formData.get('email');
        if (email && !Utils.isValidEmail(email)) {
            errors.email = 'Please enter a valid email address';
        }
        
        // PAN validation
        const pan = formData.get('pan_number');
        if (pan && !Utils.validatePAN(pan)) {
            errors.pan_number = 'Please enter a valid PAN number';
        }
        
        // Aadhaar validation
        const aadhaar = formData.get('aadhaar_number');
        if (aadhaar && !Utils.validateAadhaar(aadhaar)) {
            errors.aadhaar_number = 'Please enter a valid Aadhaar number';
        }
        
        // IFSC validation
        const ifsc = formData.get('bank_ifsc');
        if (ifsc && !Utils.validateIFSC(ifsc)) {
            errors.bank_ifsc = 'Please enter a valid IFSC code';
        }
        
        return errors;
    },
    
    // Upload employee document
    uploadDocument: function(employeeId, file, documentType) {
        const formData = new FormData();
        formData.append('document', file);
        formData.append('document_type', documentType);
        
        showLoading();
        
        return fetch(`/employees/${employeeId}/upload-document`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            return data;
        })
        .catch(error => {
            hideLoading();
            console.error('Document upload error:', error);
            throw error;
        });
    }
};

// Setup employee form functionality
document.addEventListener('DOMContentLoaded', function() {
    // Employee form setup
    const employeeForm = document.getElementById('employee-form');
    if (employeeForm) {
        setupEmployeeForm(employeeForm);
    }
    
    // Employee search setup
    const employeeSearchInput = document.getElementById('employee-search');
    if (employeeSearchInput) {
        setupEmployeeSearch(employeeSearchInput);
    }
    
    // Document upload setup
    const documentUploadForm = document.getElementById('document-upload-form');
    if (documentUploadForm) {
        setupDocumentUpload(documentUploadForm);
    }
    
    // Salary structure form setup
    const salaryStructureForm = document.getElementById('salary-structure-form');
    if (salaryStructureForm) {
        setupSalaryStructureForm(salaryStructureForm);
    }
});

function setupEmployeeForm(form) {
    // Auto-generate employee code if empty
    const empCodeInput = form.querySelector('#emp_code');
    const firstNameInput = form.querySelector('#first_name');
    
    if (empCodeInput && firstNameInput) {
        firstNameInput.addEventListener('blur', function() {
            if (!empCodeInput.value) {
                EmployeeUtils.generateEmployeeCode();
            }
        });
    }
    
    // Department change handler
    const departmentSelect = form.querySelector('#department_id');
    const designationSelect = form.querySelector('#designation_id');
    
    if (departmentSelect && designationSelect) {
        departmentSelect.addEventListener('change', function() {
            loadDesignationsByDepartment(this.value, designationSelect);
        });
    }
    
    // Form validation
    form.addEventListener('submit', function(e) {
        const formData = new FormData(form);
        const errors = EmployeeUtils.validateEmployeeData(formData);
        
        if (Object.keys(errors).length > 0) {
            e.preventDefault();
            displayFormErrors(errors);
        }
    });
}

function setupEmployeeSearch(input) {
    const resultsContainer = document.getElementById('employee-search-results');
    
    if (!resultsContainer) return;
    
    const debouncedSearch = Utils.debounce(function(query) {
        EmployeeUtils.searchEmployees(query, function(employees) {
            displaySearchResults(employees, resultsContainer);
        });
    }, 300);
    
    input.addEventListener('input', function() {
        const query = this.value.trim();
        if (query.length >= 2) {
            debouncedSearch(query);
        } else {
            resultsContainer.innerHTML = '';
            resultsContainer.classList.add('hidden');
        }
    });
    
    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.classList.add('hidden');
        }
    });
}

function displaySearchResults(employees, container) {
    if (employees.length === 0) {
        container.innerHTML = '<div class="p-3 text-gray-500">No employees found</div>';
    } else {
        const html = employees.map(emp => `
            <div class="p-3 hover:bg-gray-50 cursor-pointer border-b" onclick="selectEmployee(${emp.id})">
                <div class="font-medium">${emp.first_name} ${emp.last_name}</div>
                <div class="text-sm text-gray-500">${emp.emp_code} • ${emp.email}</div>
            </div>
        `).join('');
        container.innerHTML = html;
    }
    
    container.classList.remove('hidden');
}

function selectEmployee(employeeId) {
    // Handle employee selection
    window.location.href = `/employees/${employeeId}`;
}

function loadDesignationsByDepartment(departmentId, designationSelect) {
    if (!departmentId) {
        designationSelect.innerHTML = '<option value="">Select Designation</option>';
        return;
    }
    
    fetch(`/api/designations-by-department?department_id=${departmentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let options = '<option value="">Select Designation</option>';
                data.designations.forEach(designation => {
                    options += `<option value="${designation.id}">${designation.name}</option>`;
                });
                designationSelect.innerHTML = options;
            }
        })
        .catch(error => {
            console.error('Error loading designations:', error);
        });
}

function setupDocumentUpload(form) {
    const fileInput = form.querySelector('input[type="file"]');
    const uploadArea = form.querySelector('.upload-area');
    
    if (uploadArea) {
        // Drag and drop functionality
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileUpload(files[0]);
            }
        });
    }
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                handleFileUpload(this.files[0]);
            }
        });
    }
}

function handleFileUpload(file) {
    // Validate file
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    
    if (file.size > maxSize) {
        showMessage('File size must be less than 5MB', 'error');
        return;
    }
    
    if (!allowedTypes.includes(file.type)) {
        showMessage('Only JPEG, PNG, and PDF files are allowed', 'error');
        return;
    }
    
    // Show file preview
    displayFilePreview(file);
}

function displayFilePreview(file) {
    const previewContainer = document.getElementById('file-preview');
    if (!previewContainer) return;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        let preview = '';
        
        if (file.type.startsWith('image/')) {
            preview = `<img src="${e.target.result}" class="max-w-xs max-h-32 object-cover rounded">`;
        } else {
            preview = `<div class="p-4 bg-gray-100 rounded">
                <i class="fas fa-file-pdf text-red-500 text-2xl"></i>
                <div class="text-sm mt-2">${file.name}</div>
            </div>`;
        }
        
        previewContainer.innerHTML = preview;
    };
    
    reader.readAsDataURL(file);
}

function setupSalaryStructureForm(form) {
    const componentInputs = form.querySelectorAll('.component-amount');
    
    componentInputs.forEach(input => {
        input.addEventListener('input', function() {
            calculateSalaryTotals(form);
        });
    });
    
    // Initial calculation
    calculateSalaryTotals(form);
}

function calculateSalaryTotals(form) {
    let totalEarnings = 0;
    let totalDeductions = 0;
    
    form.querySelectorAll('.earning-component .component-amount').forEach(input => {
        totalEarnings += parseFloat(input.value) || 0;
    });
    
    form.querySelectorAll('.deduction-component .component-amount').forEach(input => {
        totalDeductions += parseFloat(input.value) || 0;
    });
    
    const netSalary = totalEarnings - totalDeductions;
    
    // Update display
    const totalEarningsElement = document.getElementById('total-earnings');
    const totalDeductionsElement = document.getElementById('total-deductions');
    const netSalaryElement = document.getElementById('net-salary');
    
    if (totalEarningsElement) {
        totalEarningsElement.textContent = Utils.formatCurrency(totalEarnings);
    }
    
    if (totalDeductionsElement) {
        totalDeductionsElement.textContent = Utils.formatCurrency(totalDeductions);
    }
    
    if (netSalaryElement) {
        netSalaryElement.textContent = Utils.formatCurrency(netSalary);
    }
}

function displayFormErrors(errors) {
    // Clear previous errors
    document.querySelectorAll('.field-error').forEach(el => {
        el.classList.remove('field-error');
    });
    document.querySelectorAll('.error-message').forEach(el => {
        el.remove();
    });
    
    // Display new errors
    Object.keys(errors).forEach(fieldName => {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.classList.add('field-error');
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message text-red-600 text-sm mt-1';
            errorDiv.textContent = errors[fieldName];
            
            field.parentNode.appendChild(errorDiv);
        }
    });
    
    // Show summary message
    showMessage('Please correct the errors below', 'error');
}

// Employee bulk operations
window.EmployeeBulkOps = {
    exportEmployees: function(format = 'excel', filters = {}) {
        const params = new URLSearchParams(filters);
        params.append('format', format);
        
        window.location.href = `/employees/export?${params.toString()}`;
    },
    
    bulkUpdateSalary: function(employeeIds, componentId, amount) {
        const data = {
            employee_ids: employeeIds,
            component_id: componentId,
            amount: amount
        };
        
        showLoading();
        
        fetch('/employees/bulk-update-salary', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showMessage(`Salary updated for ${data.updated} employees`, 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showMessage(data.message || 'Failed to update salaries', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Bulk update error:', error);
            showMessage('Failed to update salaries', 'error');
        });
    }
};