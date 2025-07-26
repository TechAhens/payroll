/**
 * Reports JavaScript functionality
 */

// Report utilities
window.ReportUtils = {
    // Generate report with parameters
    generateReport: function(reportType, parameters, format = 'excel') {
        const data = {
            ...parameters,
            format: format
        };
        
        showLoading();
        
        fetch(`/reports/${reportType}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            hideLoading();
            
            if (response.ok) {
                const contentType = response.headers.get('content-type');
                
                if (contentType && contentType.includes('application/json')) {
                    return response.json().then(data => {
                        if (data.success) {
                            showMessage('Report generated successfully', 'success');
                        } else {
                            showMessage(data.message || 'Failed to generate report', 'error');
                        }
                    });
                } else {
                    // File download
                    return response.blob().then(blob => {
                        this.downloadFile(blob, `${reportType}_${new Date().toISOString().split('T')[0]}.${format}`);
                        showMessage('Report downloaded successfully', 'success');
                    });
                }
            } else {
                showMessage('Failed to generate report', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Report generation error:', error);
            showMessage('An error occurred while generating the report', 'error');
        });
    },
    
    // Download file blob
    downloadFile: function(blob, filename) {
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
    
    // Preview report data
    previewReport: function(reportType, parameters) {
        const data = {
            ...parameters,
            preview: true
        };
        
        showLoading();
        
        return fetch(`/reports/${reportType}`, {
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
            return data;
        })
        .catch(error => {
            hideLoading();
            console.error('Report preview error:', error);
            throw error;
        });
    },
    
    // Schedule report generation
    scheduleReport: function(reportType, parameters, schedule) {
        const data = {
            report_type: reportType,
            parameters: parameters,
            schedule: schedule
        };
        
        showLoading();
        
        fetch('/reports/schedule', {
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
                showMessage('Report scheduled successfully', 'success');
            } else {
                showMessage(data.message || 'Failed to schedule report', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Report scheduling error:', error);
            showMessage('Failed to schedule report', 'error');
        });
    }
};

// Custom report builder
window.CustomReportBuilder = {
    selectedTables: [],
    selectedFields: [],
    filters: [],
    
    // Initialize report builder
    init: function() {
        this.setupTableSelection();
        this.setupFieldSelection();
        this.setupFilters();
        this.setupPreview();
    },
    
    // Setup table selection
    setupTableSelection: function() {
        const tableCheckboxes = document.querySelectorAll('.table-checkbox');
        
        tableCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.updateSelectedTables();
                this.updateFieldOptions();
                this.updateQueryPreview();
            });
        });
    },
    
    // Update selected tables
    updateSelectedTables: function() {
        this.selectedTables = Array.from(document.querySelectorAll('.table-checkbox:checked'))
            .map(cb => cb.value);
    },
    
    // Setup field selection
    setupFieldSelection: function() {
        document.addEventListener('change', (e) => {
            if (e.target.matches('.field-checkbox')) {
                this.updateSelectedFields();
                this.updateQueryPreview();
            }
        });
    },
    
    // Update selected fields
    updateSelectedFields: function() {
        this.selectedFields = Array.from(document.querySelectorAll('.field-checkbox:checked'))
            .map(cb => cb.value);
    },
    
    // Setup filters
    setupFilters: function() {
        const addFilterBtn = document.getElementById('add-filter-btn');
        if (addFilterBtn) {
            addFilterBtn.addEventListener('click', () => {
                this.addFilter();
            });
        }
    },
    
    // Add filter row
    addFilter: function() {
        const filterContainer = document.getElementById('filters-container');
        if (!filterContainer) return;
        
        const filterRow = document.createElement('div');
        filterRow.className = 'filter-row flex items-center space-x-3 mb-3';
        filterRow.innerHTML = `
            <select class="form-select w-1/3 filter-field">
                <option value="">Select Field</option>
                ${this.getFieldOptions()}
            </select>
            <select class="form-select w-1/4 filter-operator">
                <option value="=">=</option>
                <option value="!=">!=</option>
                <option value=">">></option>
                <option value="<"><</option>
                <option value="LIKE">Contains</option>
            </select>
            <input type="text" class="form-input w-1/3 filter-value" placeholder="Value">
            <button type="button" class="btn btn-outline btn-sm remove-filter">
                <i class="fas fa-minus"></i>
            </button>
        `;
        
        filterContainer.appendChild(filterRow);
        
        // Add remove functionality
        filterRow.querySelector('.remove-filter').addEventListener('click', () => {
            filterRow.remove();
            this.updateQueryPreview();
        });
        
        // Update preview when filter changes
        filterRow.querySelectorAll('select, input').forEach(element => {
            element.addEventListener('change', () => {
                this.updateQueryPreview();
            });
        });
    },
    
    // Get field options for filters
    getFieldOptions: function() {
        return this.selectedFields.map(field => {
            const label = field.replace(/^[a-z]+\./, '').replace(/_/g, ' ');
            return `<option value="${field}">${label}</option>`;
        }).join('');
    },
    
    // Update field options based on selected tables
    updateFieldOptions: function() {
        const fieldContainer = document.getElementById('field-selection');
        if (!fieldContainer) return;
        
        // This would be populated based on the selected tables
        // For now, showing a placeholder
        fieldContainer.innerHTML = '<p class="text-gray-500">Select tables to see available fields</p>';
    },
    
    // Setup preview functionality
    setupPreview: function() {
        const previewBtn = document.getElementById('preview-report-btn');
        if (previewBtn) {
            previewBtn.addEventListener('click', () => {
                this.previewReport();
            });
        }
    },
    
    // Preview report
    previewReport: function() {
        if (this.selectedTables.length === 0) {
            showMessage('Please select at least one table', 'error');
            return;
        }
        
        if (this.selectedFields.length === 0) {
            showMessage('Please select at least one field', 'error');
            return;
        }
        
        const data = {
            tables: this.selectedTables,
            fields: this.selectedFields,
            filters: this.getFilters(),
            preview: true
        };
        
        ReportUtils.previewReport('custom-builder', data)
            .then(result => {
                this.displayPreview(result);
            })
            .catch(error => {
                showMessage('Failed to preview report', 'error');
            });
    },
    
    // Get current filters
    getFilters: function() {
        const filters = [];
        document.querySelectorAll('.filter-row').forEach(row => {
            const field = row.querySelector('.filter-field').value;
            const operator = row.querySelector('.filter-operator').value;
            const value = row.querySelector('.filter-value').value;
            
            if (field && operator && value) {
                filters.push({ field, operator, value });
            }
        });
        return filters;
    },
    
    // Display preview
    displayPreview: function(result) {
        const previewContainer = document.getElementById('report-preview');
        if (!previewContainer) return;
        
        if (result.success && result.data) {
            let html = '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200">';
            
            // Headers
            if (result.data.length > 0) {
                html += '<thead class="bg-gray-50"><tr>';
                Object.keys(result.data[0]).forEach(key => {
                    html += `<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">${key}</th>`;
                });
                html += '</tr></thead>';
                
                // Data rows (limit to first 10 for preview)
                html += '<tbody class="bg-white divide-y divide-gray-200">';
                result.data.slice(0, 10).forEach(row => {
                    html += '<tr>';
                    Object.values(row).forEach(value => {
                        html += `<td class="px-3 py-2 text-sm text-gray-900">${value}</td>`;
                    });
                    html += '</tr>';
                });
                html += '</tbody>';
            }
            
            html += '</table></div>';
            
            if (result.data.length > 10) {
                html += `<p class="text-sm text-gray-500 mt-2">Showing first 10 of ${result.data.length} records</p>`;
            }
            
            previewContainer.innerHTML = html;
            previewContainer.classList.remove('hidden');
        } else {
            showMessage('No data found for the selected criteria', 'warning');
        }
    },
    
    // Update query preview
    updateQueryPreview: function() {
        const previewElement = document.getElementById('query-preview');
        if (!previewElement) return;
        
        let query = 'SELECT ';
        
        if (this.selectedFields.length > 0) {
            query += this.selectedFields.join(', ');
        } else {
            query += '*';
        }
        
        query += '\nFROM ';
        
        if (this.selectedTables.length > 0) {
            query += this.selectedTables.join(', ');
        } else {
            query += 'table_name';
        }
        
        const filters = this.getFilters();
        if (filters.length > 0) {
            query += '\nWHERE ';
            const conditions = filters.map(filter => 
                `${filter.field} ${filter.operator} '${filter.value}'`
            );
            query += conditions.join(' AND ');
        }
        
        previewElement.innerHTML = `<code class="text-sm text-gray-800">${query}</code>`;
    }
};

// Initialize report functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize custom report builder if on that page
    if (document.getElementById('report-builder-form')) {
        CustomReportBuilder.init();
    }
    
    // Setup report form handlers
    const reportForms = document.querySelectorAll('.report-form');
    reportForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            handleReportFormSubmission(this);
        });
    });
    
    // Setup quick report buttons
    const quickReportBtns = document.querySelectorAll('.quick-report-btn');
    quickReportBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const reportType = this.dataset.reportType;
            const parameters = JSON.parse(this.dataset.parameters || '{}');
            ReportUtils.generateReport(reportType, parameters);
        });
    });
});

function handleReportFormSubmission(form) {
    const formData = new FormData(form);
    const reportType = form.dataset.reportType || 'salary-register';
    const parameters = Object.fromEntries(formData);
    
    // Validate required fields
    const requiredFields = form.querySelectorAll('[required]');
    let hasErrors = false;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('border-red-300');
            hasErrors = true;
        } else {
            field.classList.remove('border-red-300');
        }
    });
    
    if (hasErrors) {
        showMessage('Please fill in all required fields', 'error');
        return;
    }
    
    // Generate report
    ReportUtils.generateReport(reportType, parameters, parameters.format || 'excel');
}

// Report templates
window.ReportTemplates = {
    // Load predefined report template
    loadTemplate: function(templateName) {
        const templates = {
            'employee_master': {
                tables: ['employees', 'departments', 'designations'],
                fields: ['e.emp_code', 'e.first_name', 'e.last_name', 'e.email', 'd.name', 'des.name'],
                name: 'Employee Master Report'
            },
            'salary_summary': {
                tables: ['employees', 'payroll_transactions', 'salary_components'],
                fields: ['e.emp_code', 'e.first_name', 'e.last_name', 'sc.name', 'pt.amount'],
                name: 'Salary Summary Report'
            },
            'attendance_summary': {
                tables: ['employees', 'attendance'],
                fields: ['e.emp_code', 'e.first_name', 'e.last_name', 'a.attendance_date', 'a.status'],
                name: 'Attendance Summary Report'
            }
        };
        
        const template = templates[templateName];
        if (template) {
            this.applyTemplate(template);
            showMessage(`${template.name} template loaded`, 'success');
        }
    },
    
    // Apply template to form
    applyTemplate: function(template) {
        // Clear current selections
        document.querySelectorAll('.table-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.field-checkbox').forEach(cb => cb.checked = false);
        
        // Select template tables
        template.tables.forEach(table => {
            const checkbox = document.querySelector(`input[value="${table}"]`);
            if (checkbox) checkbox.checked = true;
        });
        
        // Update field selection
        CustomReportBuilder.updateSelectedTables();
        CustomReportBuilder.updateFieldOptions();
        
        // Select template fields
        setTimeout(() => {
            template.fields.forEach(field => {
                const checkbox = document.querySelector(`input[value="${field}"]`);
                if (checkbox) checkbox.checked = true;
            });
            
            CustomReportBuilder.updateSelectedFields();
            CustomReportBuilder.updateQueryPreview();
        }, 100);
    },
    
    // Save current configuration as template
    saveTemplate: function(name) {
        const template = {
            name: name,
            tables: CustomReportBuilder.selectedTables,
            fields: CustomReportBuilder.selectedFields,
            filters: CustomReportBuilder.getFilters()
        };
        
        // Save to localStorage for now (in production, save to database)
        const savedTemplates = JSON.parse(localStorage.getItem('report_templates') || '{}');
        savedTemplates[name] = template;
        localStorage.setItem('report_templates', JSON.stringify(savedTemplates));
        
        showMessage('Template saved successfully', 'success');
    }
};