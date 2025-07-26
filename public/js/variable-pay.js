/**
 * Variable Pay Management JavaScript
 */

// Variable Pay utilities
window.VariablePayUtils = {
    // Calculate performance bonus based on rating
    calculatePerformanceBonus: function(basicSalary, rating) {
        const bonusRates = {
            'excellent': 0.25,
            'good': 0.20,
            'satisfactory': 0.15,
            'needs_improvement': 0.10,
            'poor': 0.05
        };
        
        return basicSalary * (bonusRates[rating] || 0);
    },
    
    // Calculate attendance bonus
    calculateAttendanceBonus: function(attendancePercentage, basicSalary) {
        if (attendancePercentage >= 100) {
            return basicSalary * 0.05; // 5% bonus for perfect attendance
        } else if (attendancePercentage >= 95) {
            return basicSalary * 0.03; // 3% bonus for 95%+ attendance
        }
        return 0;
    },
    
    // Calculate overtime pay
    calculateOvertimePay: function(basicSalary, overtimeHours, workingDays = 22) {
        const hourlyRate = basicSalary / (workingDays * 8);
        return overtimeHours * hourlyRate * 2; // Double rate for overtime
    },
    
    // Validate variable pay entry
    validateVariablePayEntry: function(data) {
        const errors = {};
        
        if (!data.employee_id) {
            errors.employee_id = 'Employee is required';
        }
        
        if (!data.component_id) {
            errors.component_id = 'Component is required';
        }
        
        if (!data.period_id) {
            errors.period_id = 'Period is required';
        }
        
        if (!data.amount || data.amount <= 0) {
            errors.amount = 'Amount must be greater than 0';
        }
        
        return {
            valid: Object.keys(errors).length === 0,
            errors: errors
        };
    },
    
    // Format variable pay data for display
    formatVariablePayData: function(data) {
        return {
            ...data,
            formatted_amount: this.formatCurrency(data.amount),
            status_badge: this.getStatusBadge(data.status),
            performance_badge: this.getPerformanceBadge(data.performance_rating)
        };
    },
    
    // Get status badge HTML
    getStatusBadge: function(status) {
        const badges = {
            'pending': '<span class="badge badge-warning">Pending</span>',
            'approved': '<span class="badge badge-success">Approved</span>',
            'rejected': '<span class="badge badge-danger">Rejected</span>',
            'processed': '<span class="badge badge-primary">Processed</span>'
        };
        
        return badges[status] || '<span class="badge badge-secondary">Unknown</span>';
    },
    
    // Get performance badge HTML
    getPerformanceBadge: function(rating) {
        if (!rating) return '';
        
        const badges = {
            'excellent': '<span class="badge badge-success">Excellent</span>',
            'good': '<span class="badge badge-primary">Good</span>',
            'satisfactory': '<span class="badge badge-secondary">Satisfactory</span>',
            'needs_improvement': '<span class="badge badge-warning">Needs Improvement</span>',
            'poor': '<span class="badge badge-danger">Poor</span>'
        };
        
        return badges[rating] || '';
    },
    
    // Format currency
    formatCurrency: function(amount) {
        return '₹' + parseFloat(amount).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
};

// Bulk Variable Pay Entry Manager
window.BulkVariablePayManager = {
    entries: [],
    
    // Initialize bulk entry
    init: function() {
        this.setupEventListeners();
        this.loadTemplates();
    },
    
    // Setup event listeners
    setupEventListeners: function() {
        // Template selection
        document.addEventListener('change', function(e) {
            if (e.target.id === 'bulk-template-select') {
                BulkVariablePayManager.applyTemplate(e.target.value);
            }
        });
        
        // Add entry button
        document.addEventListener('click', function(e) {
            if (e.target.id === 'add-bulk-entry-btn') {
                BulkVariablePayManager.addEntry();
            }
        });
        
        // Remove entry buttons
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-entry-btn')) {
                BulkVariablePayManager.removeEntry(e.target.dataset.index);
            }
        });
        
        // Calculate buttons
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('calculate-btn')) {
                BulkVariablePayManager.calculateEntry(e.target.dataset.index);
            }
        });
        
        // Import from file
        document.addEventListener('change', function(e) {
            if (e.target.id === 'import-file-input') {
                BulkVariablePayManager.importFromFile(e.target.files[0]);
            }
        });
    },
    
    // Load templates
    loadTemplates: function() {
        fetch('/api/variable-pay-templates')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.populateTemplateSelect(data.templates);
                }
            })
            .catch(error => {
                console.error('Error loading templates:', error);
            });
    },
    
    // Populate template select
    populateTemplateSelect: function(templates) {
        const select = document.getElementById('bulk-template-select');
        if (!select) return;
        
        select.innerHTML = '<option value="">Select Template</option>';
        templates.forEach(template => {
            select.innerHTML += `<option value="${template.id}">${template.name}</option>`;
        });
    },
    
    // Apply template
    applyTemplate: function(templateId) {
        if (!templateId) return;
        
        fetch(`/api/variable-pay-templates/${templateId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.loadTemplateData(data.template);
                }
            })
            .catch(error => {
                console.error('Error loading template:', error);
            });
    },
    
    // Load template data
    loadTemplateData: function(template) {
        // Clear existing entries
        this.entries = [];
        
        // Set default component
        const componentSelect = document.getElementById('default-component-select');
        if (componentSelect && template.default_component) {
            componentSelect.value = template.default_component;
        }
        
        // Load employees based on template criteria
        this.loadEmployeesForTemplate(template);
    },
    
    // Load employees for template
    loadEmployeesForTemplate: function(template) {
        const filters = template.filters || {};
        
        fetch('/api/employees/filtered', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(filters)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.createEntriesFromEmployees(data.employees, template);
            }
        })
        .catch(error => {
            console.error('Error loading employees:', error);
        });
    },
    
    // Create entries from employees
    createEntriesFromEmployees: function(employees, template) {
        employees.forEach(employee => {
            const entry = {
                employee_id: employee.id,
                employee_name: employee.name,
                employee_code: employee.emp_code,
                component_id: template.default_component_id,
                amount: this.calculateTemplateAmount(employee, template),
                description: template.description || ''
            };
            
            this.entries.push(entry);
        });
        
        this.renderEntries();
    },
    
    // Calculate template amount
    calculateTemplateAmount: function(employee, template) {
        switch (template.calculation_method) {
            case 'percentage_of_basic':
                return employee.basic_salary * (template.percentage / 100);
            case 'fixed_amount':
                return template.fixed_amount;
            case 'hourly_rate':
                return employee.overtime_hours * template.hourly_rate;
            case 'target_based':
                return this.calculateTargetBasedAmount(employee, template);
            default:
                return 0;
        }
    },
    
    // Calculate target-based amount
    calculateTargetBasedAmount: function(employee, template) {
        const achievement = employee.target_achievement || 0;
        const target = employee.target || 100;
        const achievementPercentage = (achievement / target) * 100;
        
        if (achievementPercentage >= template.threshold) {
            return achievement * (template.incentive_rate / 100);
        }
        
        return 0;
    },
    
    // Add new entry
    addEntry: function() {
        const entry = {
            employee_id: '',
            employee_name: '',
            employee_code: '',
            component_id: '',
            amount: 0,
            description: ''
        };
        
        this.entries.push(entry);
        this.renderEntries();
    },
    
    // Remove entry
    removeEntry: function(index) {
        this.entries.splice(index, 1);
        this.renderEntries();
    },
    
    // Calculate entry amount
    calculateEntry: function(index) {
        const entry = this.entries[index];
        const employeeId = entry.employee_id;
        const componentId = entry.component_id;
        
        if (!employeeId || !componentId) {
            showMessage('Please select employee and component first', 'warning');
            return;
        }
        
        fetch('/api/calculate-variable-pay', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                employee_id: employeeId,
                component_id: componentId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.entries[index].amount = data.calculated_amount;
                this.renderEntries();
                showMessage('Amount calculated successfully', 'success');
            } else {
                showMessage(data.message || 'Calculation failed', 'error');
            }
        })
        .catch(error => {
            console.error('Calculation error:', error);
            showMessage('Calculation failed', 'error');
        });
    },
    
    // Render entries
    renderEntries: function() {
        const container = document.getElementById('bulk-entries-container');
        if (!container) return;
        
        if (this.entries.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-4"></i>
                    <p>No entries added yet</p>
                    <button type="button" id="add-first-entry" class="btn btn-primary mt-4">
                        Add First Entry
                    </button>
                </div>
            `;
            return;
        }
        
        let html = `
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Component</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
        `;
        
        this.entries.forEach((entry, index) => {
            html += `
                <tr>
                    <td class="px-3 py-2">
                        <select class="form-select entry-employee" data-index="${index}">
                            <option value="">Select Employee</option>
                            <!-- Options will be populated dynamically -->
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <select class="form-select entry-component" data-index="${index}">
                            <option value="">Select Component</option>
                            <!-- Options will be populated dynamically -->
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <div class="flex items-center space-x-2">
                            <input type="number" class="form-input entry-amount" data-index="${index}" 
                                   value="${entry.amount}" step="0.01" min="0">
                            <button type="button" class="btn btn-outline btn-sm calculate-btn" data-index="${index}">
                                <i class="fas fa-calculator"></i>
                            </button>
                        </div>
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" class="form-input entry-description" data-index="${index}" 
                               value="${entry.description}" placeholder="Optional description">
                    </td>
                    <td class="px-3 py-2">
                        <button type="button" class="btn btn-outline btn-sm remove-entry-btn" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Total Entries: ${this.entries.length} | 
                    Total Amount: ${VariablePayUtils.formatCurrency(this.getTotalAmount())}
                </div>
                <div class="space-x-2">
                    <button type="button" id="add-bulk-entry-btn" class="btn btn-outline">
                        <i class="fas fa-plus mr-2"></i>Add Entry
                    </button>
                    <button type="button" id="save-bulk-entries-btn" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>Save All Entries
                    </button>
                </div>
            </div>
        `;
        
        container.innerHTML = html;
        
        // Populate dropdowns
        this.populateDropdowns();
        
        // Bind change events
        this.bindEntryEvents();
    },
    
    // Get total amount
    getTotalAmount: function() {
        return this.entries.reduce((total, entry) => total + parseFloat(entry.amount || 0), 0);
    },
    
    // Populate dropdowns
    populateDropdowns: function() {
        // Load employees and components for dropdowns
        Promise.all([
            fetch('/api/employees/active').then(r => r.json()),
            fetch('/api/variable-components').then(r => r.json())
        ])
        .then(([employeesData, componentsData]) => {
            if (employeesData.success && componentsData.success) {
                this.populateEmployeeDropdowns(employeesData.employees);
                this.populateComponentDropdowns(componentsData.components);
            }
        })
        .catch(error => {
            console.error('Error loading dropdown data:', error);
        });
    },
    
    // Populate employee dropdowns
    populateEmployeeDropdowns: function(employees) {
        document.querySelectorAll('.entry-employee').forEach(select => {
            const index = select.dataset.index;
            const currentValue = this.entries[index]?.employee_id || '';
            
            select.innerHTML = '<option value="">Select Employee</option>';
            employees.forEach(emp => {
                const selected = emp.id == currentValue ? 'selected' : '';
                select.innerHTML += `<option value="${emp.id}" ${selected}>${emp.name} (${emp.emp_code})</option>`;
            });
        });
    },
    
    // Populate component dropdowns
    populateComponentDropdowns: function(components) {
        document.querySelectorAll('.entry-component').forEach(select => {
            const index = select.dataset.index;
            const currentValue = this.entries[index]?.component_id || '';
            
            select.innerHTML = '<option value="">Select Component</option>';
            components.forEach(comp => {
                const selected = comp.id == currentValue ? 'selected' : '';
                select.innerHTML += `<option value="${comp.id}" ${selected}>${comp.name}</option>`;
            });
        });
    },
    
    // Bind entry events
    bindEntryEvents: function() {
        // Employee selection change
        document.querySelectorAll('.entry-employee').forEach(select => {
            select.addEventListener('change', (e) => {
                const index = e.target.dataset.index;
                this.entries[index].employee_id = e.target.value;
                
                // Update employee name and code
                const selectedOption = e.target.selectedOptions[0];
                if (selectedOption && selectedOption.value) {
                    const text = selectedOption.textContent;
                    const match = text.match(/^(.+) \((.+)\)$/);
                    if (match) {
                        this.entries[index].employee_name = match[1];
                        this.entries[index].employee_code = match[2];
                    }
                }
            });
        });
        
        // Component selection change
        document.querySelectorAll('.entry-component').forEach(select => {
            select.addEventListener('change', (e) => {
                const index = e.target.dataset.index;
                this.entries[index].component_id = e.target.value;
            });
        });
        
        // Amount change
        document.querySelectorAll('.entry-amount').forEach(input => {
            input.addEventListener('change', (e) => {
                const index = e.target.dataset.index;
                this.entries[index].amount = parseFloat(e.target.value) || 0;
            });
        });
        
        // Description change
        document.querySelectorAll('.entry-description').forEach(input => {
            input.addEventListener('change', (e) => {
                const index = e.target.dataset.index;
                this.entries[index].description = e.target.value;
            });
        });
    },
    
    // Import from file
    importFromFile: function(file) {
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            try {
                const data = JSON.parse(e.target.result);
                this.entries = data;
                this.renderEntries();
                showMessage('Entries imported successfully', 'success');
            } catch (error) {
                console.error('Import error:', error);
                showMessage('Failed to import file', 'error');
            }
        };
        reader.readAsText(file);
    },
    
    // Export to file
    exportToFile: function() {
        const dataStr = JSON.stringify(this.entries, null, 2);
        const blob = new Blob([dataStr], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = `variable_pay_entries_${new Date().toISOString().split('T')[0]}.json`;
        a.click();
        
        URL.revokeObjectURL(url);
    },
    
    // Save all entries
    saveAllEntries: function() {
        if (this.entries.length === 0) {
            showMessage('No entries to save', 'warning');
            return;
        }
        
        // Validate entries
        const validationErrors = this.validateAllEntries();
        if (validationErrors.length > 0) {
            showMessage('Please fix validation errors: ' + validationErrors.join(', '), 'error');
            return;
        }
        
        showLoading();
        
        fetch('/payroll/bulk-variable-entry', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                entries: this.entries,
                period_id: document.getElementById('period-select')?.value,
                description: document.getElementById('bulk-description')?.value
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                showMessage(`Successfully saved ${data.data.success} entries`, 'success');
                if (data.data.failed > 0) {
                    showMessage(`${data.data.failed} entries failed`, 'warning');
                }
                
                // Clear entries on success
                this.entries = [];
                this.renderEntries();
            } else {
                showMessage(data.message || 'Save failed', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Save error:', error);
            showMessage('Save failed', 'error');
        });
    },
    
    // Validate all entries
    validateAllEntries: function() {
        const errors = [];
        
        this.entries.forEach((entry, index) => {
            if (!entry.employee_id) {
                errors.push(`Row ${index + 1}: Employee is required`);
            }
            if (!entry.component_id) {
                errors.push(`Row ${index + 1}: Component is required`);
            }
            if (!entry.amount || entry.amount <= 0) {
                errors.push(`Row ${index + 1}: Amount must be greater than 0`);
            }
        });
        
        return errors;
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize bulk variable pay manager if on bulk entry page
    if (document.getElementById('bulk-entries-container')) {
        BulkVariablePayManager.init();
    }
    
    // Setup save all entries button
    document.addEventListener('click', function(e) {
        if (e.target.id === 'save-bulk-entries-btn') {
            BulkVariablePayManager.saveAllEntries();
        }
    });
    
    // Setup export button
    document.addEventListener('click', function(e) {
        if (e.target.id === 'export-entries-btn') {
            BulkVariablePayManager.exportToFile();
        }
    });
});

// Export for global access
window.VariablePay = VariablePayUtils;
window.BulkVariablePay = BulkVariablePayManager;