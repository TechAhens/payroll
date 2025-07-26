/**
 * Advanced Payroll Processing JavaScript
 */

// Advanced Payroll utilities
window.AdvancedPayrollUtils = {
    // Bulk processing configuration
    processingConfig: {
        batchSize: 50,
        maxRetries: 3,
        retryDelay: 2000
    },
    
    // Process payroll in batches
    processBulkPayroll: function(config, onProgress, onComplete) {
        const { employees, options } = config;
        const batches = this.createBatches(employees, this.processingConfig.batchSize);
        let processedCount = 0;
        let totalAmount = 0;
        const results = [];
        
        this.processBatchSequentially(batches, 0, {
            onBatchComplete: (batchResult) => {
                processedCount += batchResult.processed;
                totalAmount += batchResult.amount;
                results.push(...batchResult.details);
                
                if (onProgress) {
                    onProgress({
                        processed: processedCount,
                        total: employees.length,
                        percentage: (processedCount / employees.length) * 100,
                        currentBatch: results.length,
                        totalBatches: batches.length,
                        totalAmount: totalAmount
                    });
                }
            },
            onComplete: () => {
                if (onComplete) {
                    onComplete({
                        success: true,
                        processed_count: processedCount,
                        total_amount: totalAmount,
                        results: results
                    });
                }
            },
            onError: (error) => {
                if (onComplete) {
                    onComplete({
                        success: false,
                        error: error.message,
                        processed_count: processedCount,
                        results: results
                    });
                }
            }
        });
    },
    
    // Create batches from employee list
    createBatches: function(employees, batchSize) {
        const batches = [];
        for (let i = 0; i < employees.length; i += batchSize) {
            batches.push(employees.slice(i, i + batchSize));
        }
        return batches;
    },
    
    // Process batches sequentially
    processBatchSequentially: function(batches, index, callbacks) {
        if (index >= batches.length) {
            callbacks.onComplete();
            return;
        }
        
        const batch = batches[index];
        this.processBatch(batch)
            .then(result => {
                callbacks.onBatchComplete(result);
                // Process next batch
                setTimeout(() => {
                    this.processBatchSequentially(batches, index + 1, callbacks);
                }, 100);
            })
            .catch(error => {
                console.error(`Batch ${index + 1} failed:`, error);
                callbacks.onError(error);
            });
    },
    
    // Process single batch
    processBatch: function(batch) {
        return fetch('/api/process-payroll-batch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                employees: batch,
                options: this.currentOptions
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Batch processing failed');
            }
            return data;
        });
    },
    
    // Variable pay calculator
    calculateVariablePay: function(employeeData, componentType, parameters) {
        switch (componentType) {
            case 'performance_bonus':
                return this.calculatePerformanceBonus(employeeData, parameters);
            case 'attendance_bonus':
                return this.calculateAttendanceBonus(employeeData, parameters);
            case 'overtime_pay':
                return this.calculateOvertimePay(employeeData, parameters);
            case 'sales_incentive':
                return this.calculateSalesIncentive(employeeData, parameters);
            default:
                return 0;
        }
    },
    
    // Calculate performance bonus
    calculatePerformanceBonus: function(employeeData, parameters) {
        const { basic_salary, performance_rating } = employeeData;
        const { bonus_percentages } = parameters;
        
        const percentage = bonus_percentages[performance_rating] || 0;
        return basic_salary * (percentage / 100);
    },
    
    // Calculate attendance bonus
    calculateAttendanceBonus: function(employeeData, parameters) {
        const { attendance_percentage } = employeeData;
        const { threshold, bonus_amount } = parameters;
        
        return attendance_percentage >= threshold ? bonus_amount : 0;
    },
    
    // Calculate overtime pay
    calculateOvertimePay: function(employeeData, parameters) {
        const { basic_salary, overtime_hours, working_days } = employeeData;
        const { multiplier } = parameters;
        
        const hourlyRate = basic_salary / (working_days * 8);
        return overtime_hours * hourlyRate * multiplier;
    },
    
    // Calculate sales incentive
    calculateSalesIncentive: function(employeeData, parameters) {
        const { sales_achievement, sales_target } = employeeData;
        const { incentive_rate, threshold } = parameters;
        
        if (sales_achievement >= sales_target * (threshold / 100)) {
            const excess = sales_achievement - (sales_target * (threshold / 100));
            return excess * (incentive_rate / 100);
        }
        
        return 0;
    },
    
    // Formula validator
    validateFormula: function(formula) {
        const errors = [];
        
        // Check for balanced parentheses
        const openParens = (formula.match(/\(/g) || []).length;
        const closeParens = (formula.match(/\)/g) || []).length;
        
        if (openParens !== closeParens) {
            errors.push('Unbalanced parentheses');
        }
        
        // Check for valid characters
        if (!/^[A-Z0-9_+\-*\/().,\s<>=!&|]+$/i.test(formula)) {
            errors.push('Invalid characters in formula');
        }
        
        // Check for consecutive operators
        if (/[+\-*\/]{2,}/.test(formula)) {
            errors.push('Consecutive operators not allowed');
        }
        
        // Check for division by zero
        if (/\/\s*0(?!\d)/.test(formula)) {
            errors.push('Division by zero detected');
        }
        
        return {
            valid: errors.length === 0,
            errors: errors
        };
    },
    
    // Advanced filter builder
    buildAdvancedFilter: function(criteria) {
        const filters = [];
        
        if (criteria.departments && criteria.departments.length > 0) {
            filters.push(`department_id IN (${criteria.departments.join(',')})`);
        }
        
        if (criteria.designations && criteria.designations.length > 0) {
            filters.push(`designation_id IN (${criteria.designations.join(',')})`);
        }
        
        if (criteria.salary_range) {
            if (criteria.salary_range.min) {
                filters.push(`basic_salary >= ${criteria.salary_range.min}`);
            }
            if (criteria.salary_range.max) {
                filters.push(`basic_salary <= ${criteria.salary_range.max}`);
            }
        }
        
        if (criteria.join_date_range) {
            if (criteria.join_date_range.from) {
                filters.push(`join_date >= '${criteria.join_date_range.from}'`);
            }
            if (criteria.join_date_range.to) {
                filters.push(`join_date <= '${criteria.join_date_range.to}'`);
            }
        }
        
        if (criteria.employment_type && criteria.employment_type.length > 0) {
            filters.push(`employment_type IN ('${criteria.employment_type.join("','")}')`);
        }
        
        return filters.join(' AND ');
    },
    
    // Payroll analytics
    generateAnalytics: function(payrollData) {
        const analytics = {
            summary: this.calculateSummary(payrollData),
            departmentWise: this.calculateDepartmentWise(payrollData),
            componentWise: this.calculateComponentWise(payrollData),
            trends: this.calculateTrends(payrollData)
        };
        
        return analytics;
    },
    
    // Calculate summary statistics
    calculateSummary: function(data) {
        const totalEmployees = data.length;
        const totalGross = data.reduce((sum, emp) => sum + emp.gross_salary, 0);
        const totalDeductions = data.reduce((sum, emp) => sum + emp.total_deductions, 0);
        const totalNet = data.reduce((sum, emp) => sum + emp.net_salary, 0);
        
        return {
            total_employees: totalEmployees,
            total_gross: totalGross,
            total_deductions: totalDeductions,
            total_net: totalNet,
            average_gross: totalGross / totalEmployees,
            average_net: totalNet / totalEmployees
        };
    },
    
    // Calculate department-wise statistics
    calculateDepartmentWise: function(data) {
        const departments = {};
        
        data.forEach(emp => {
            if (!departments[emp.department_name]) {
                departments[emp.department_name] = {
                    employee_count: 0,
                    total_gross: 0,
                    total_net: 0
                };
            }
            
            departments[emp.department_name].employee_count++;
            departments[emp.department_name].total_gross += emp.gross_salary;
            departments[emp.department_name].total_net += emp.net_salary;
        });
        
        // Calculate averages
        Object.keys(departments).forEach(dept => {
            const deptData = departments[dept];
            deptData.average_gross = deptData.total_gross / deptData.employee_count;
            deptData.average_net = deptData.total_net / deptData.employee_count;
        });
        
        return departments;
    },
    
    // Export utilities
    exportToExcel: function(data, filename) {
        // Create CSV content (simplified Excel export)
        const headers = Object.keys(data[0] || {});
        let csvContent = headers.join(',') + '\n';
        
        data.forEach(row => {
            const values = headers.map(header => {
                const value = row[header] || '';
                return typeof value === 'string' && value.includes(',') ? `"${value}"` : value;
            });
            csvContent += values.join(',') + '\n';
        });
        
        // Create and download file
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename + '.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    },
    
    // Bulk operations helper
    performBulkOperation: function(operation, selectedIds, parameters) {
        return fetch(`/api/bulk-${operation}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                ids: selectedIds,
                parameters: parameters
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Bulk operation failed');
            }
            return data;
        });
    }
};

// Advanced filter component
window.AdvancedFilter = {
    init: function(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        this.options = options;
        this.filters = [];
        
        this.render();
        this.bindEvents();
    },
    
    render: function() {
        this.container.innerHTML = `
            <div class="advanced-filter-builder">
                <div class="filter-header flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Advanced Filters</h3>
                    <button type="button" class="btn btn-outline btn-sm" id="add-filter-rule">
                        <i class="fas fa-plus mr-2"></i>Add Rule
                    </button>
                </div>
                <div class="filter-rules" id="filter-rules">
                    <!-- Filter rules will be added here -->
                </div>
                <div class="filter-actions mt-4 flex justify-between">
                    <button type="button" class="btn btn-outline" id="clear-filters">Clear All</button>
                    <button type="button" class="btn btn-primary" id="apply-filters">Apply Filters</button>
                </div>
            </div>
        `;
    },
    
    bindEvents: function() {
        const addRuleBtn = this.container.querySelector('#add-filter-rule');
        const clearBtn = this.container.querySelector('#clear-filters');
        const applyBtn = this.container.querySelector('#apply-filters');
        
        addRuleBtn.addEventListener('click', () => this.addFilterRule());
        clearBtn.addEventListener('click', () => this.clearAllFilters());
        applyBtn.addEventListener('click', () => this.applyFilters());
    },
    
    addFilterRule: function() {
        const ruleId = 'rule_' + Date.now();
        const rulesContainer = this.container.querySelector('#filter-rules');
        
        const ruleHtml = `
            <div class="filter-rule flex items-center space-x-3 mb-3 p-3 border rounded-lg" data-rule-id="${ruleId}">
                <select class="form-select field-select">
                    <option value="">Select Field</option>
                    <option value="department">Department</option>
                    <option value="designation">Designation</option>
                    <option value="salary_range">Salary Range</option>
                    <option value="join_date">Join Date</option>
                    <option value="employment_type">Employment Type</option>
                </select>
                
                <select class="form-select operator-select">
                    <option value="equals">Equals</option>
                    <option value="not_equals">Not Equals</option>
                    <option value="greater_than">Greater Than</option>
                    <option value="less_than">Less Than</option>
                    <option value="between">Between</option>
                    <option value="in">In</option>
                </select>
                
                <div class="value-input flex-1">
                    <input type="text" class="form-input" placeholder="Value">
                </div>
                
                <button type="button" class="btn btn-outline btn-sm remove-rule">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        rulesContainer.insertAdjacentHTML('beforeend', ruleHtml);
        
        // Bind remove event
        const newRule = rulesContainer.querySelector(`[data-rule-id="${ruleId}"]`);
        newRule.querySelector('.remove-rule').addEventListener('click', () => {
            newRule.remove();
        });
    },
    
    clearAllFilters: function() {
        this.container.querySelector('#filter-rules').innerHTML = '';
        this.filters = [];
    },
    
    applyFilters: function() {
        const rules = this.container.querySelectorAll('.filter-rule');
        this.filters = [];
        
        rules.forEach(rule => {
            const field = rule.querySelector('.field-select').value;
            const operator = rule.querySelector('.operator-select').value;
            const value = rule.querySelector('.value-input input').value;
            
            if (field && operator && value) {
                this.filters.push({ field, operator, value });
            }
        });
        
        if (this.options.onApply) {
            this.options.onApply(this.filters);
        }
    },
    
    getFilters: function() {
        return this.filters;
    }
};

// Initialize advanced payroll features
document.addEventListener('DOMContentLoaded', function() {
    // Initialize advanced filter if container exists
    const filterContainer = document.getElementById('advanced-filter-container');
    if (filterContainer) {
        AdvancedFilter.init('advanced-filter-container', {
            onApply: function(filters) {
                console.log('Filters applied:', filters);
                // Handle filter application
            }
        });
    }
    
    // Initialize bulk processing progress tracking
    const progressContainer = document.getElementById('bulk-progress-container');
    if (progressContainer) {
        initializeBulkProgressTracking();
    }
});

function initializeBulkProgressTracking() {
    let processingInterval;
    
    window.startBulkProcessingWithProgress = function(config) {
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const progressDetails = document.getElementById('progress-details');
        
        AdvancedPayrollUtils.processBulkPayroll(
            config,
            // Progress callback
            function(progress) {
                if (progressBar) {
                    progressBar.style.width = progress.percentage + '%';
                }
                if (progressText) {
                    progressText.textContent = `${progress.processed} of ${progress.total} employees processed`;
                }
                if (progressDetails) {
                    progressDetails.innerHTML = `
                        <div>Batch ${progress.currentBatch} of ${progress.totalBatches}</div>
                        <div>Total Amount: ₹${progress.totalAmount.toLocaleString()}</div>
                    `;
                }
            },
            // Complete callback
            function(result) {
                if (result.success) {
                    showMessage(`Payroll processed successfully for ${result.processed_count} employees`, 'success');
                } else {
                    showMessage('Payroll processing failed: ' + result.error, 'error');
                }
                
                // Hide progress
                const progressContainer = document.getElementById('bulk-progress-container');
                if (progressContainer) {
                    progressContainer.style.display = 'none';
                }
            }
        );
    };
}

// Export for global access
window.AdvancedPayroll = AdvancedPayrollUtils;