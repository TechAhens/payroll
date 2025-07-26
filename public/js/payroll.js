/**
 * Payroll specific JavaScript functionality
 */

// Payroll processing utilities
window.PayrollUtils = {
    // Calculate salary components
    calculateSalary: function(basicSalary, components) {
        let totalEarnings = 0;
        let totalDeductions = 0;
        const calculations = {};
        
        // Calculate each component
        Object.keys(components).forEach(componentCode => {
            const component = components[componentCode];
            let amount = 0;
            
            if (component.formula) {
                amount = this.evaluateFormula(component.formula, basicSalary, calculations);
            } else {
                amount = parseFloat(component.amount) || 0;
            }
            
            calculations[componentCode] = {
                name: component.name,
                type: component.type,
                amount: amount
            };
            
            if (component.type === 'earning') {
                totalEarnings += amount;
            } else if (component.type === 'deduction') {
                totalDeductions += amount;
            }
        });
        
        return {
            calculations: calculations,
            totalEarnings: totalEarnings,
            totalDeductions: totalDeductions,
            netSalary: totalEarnings - totalDeductions
        };
    },
    
    // Evaluate salary formula
    evaluateFormula: function(formula, basicSalary, existingCalculations) {
        let evaluatedFormula = formula;
        
        // Replace BASIC with actual value
        evaluatedFormula = evaluatedFormula.replace(/BASIC/g, basicSalary);
        
        // Replace other component codes with their calculated values
        Object.keys(existingCalculations).forEach(code => {
            const regex = new RegExp(code, 'g');
            evaluatedFormula = evaluatedFormula.replace(regex, existingCalculations[code].amount);
        });
        
        // Safe evaluation (only allow basic arithmetic)
        try {
            // Remove any non-numeric/operator characters for security
            evaluatedFormula = evaluatedFormula.replace(/[^0-9+\-*\/.() ]/g, '');
            return eval(evaluatedFormula) || 0;
        } catch (error) {
            console.error('Formula evaluation error:', error);
            return 0;
        }
    },
    
    // Format currency for display
    formatCurrency: function(amount) {
        return '₹' + parseFloat(amount).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },
    
    // Validate payroll data
    validatePayrollData: function(data) {
        const errors = [];
        
        if (!data.period_id) {
            errors.push('Please select a payroll period');
        }
        
        if (!data.employee_ids || data.employee_ids.length === 0) {
            errors.push('Please select at least one employee');
        }
        
        return errors;
    },
    
    // Process payroll for selected employees
    processPayroll: function(periodId, employeeIds, options = {}) {
        const data = {
            period_id: periodId,
            employee_ids: employeeIds,
            include_arrears: options.includeArrears || false,
            calculate_tds: options.calculateTDS || true,
            process_loans: options.processLoans || true
        };
        
        showLoading();
        
        return fetch('/payroll/process', {
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
            console.error('Payroll processing error:', error);
            throw error;
        });
    }
};

// Salary calculator functionality
document.addEventListener('DOMContentLoaded', function() {
    const salaryForm = document.getElementById('salary-calculator-form');
    if (salaryForm) {
        setupSalaryCalculator(salaryForm);
    }
    
    // Setup payroll processing form
    const payrollForm = document.getElementById('payroll-process-form');
    if (payrollForm) {
        setupPayrollProcessing(payrollForm);
    }
});

function setupSalaryCalculator(form) {
    const basicInput = form.querySelector('#basic_salary');
    const componentInputs = form.querySelectorAll('.component-amount');
    
    // Calculate on input change
    [basicInput, ...componentInputs].forEach(input => {
        if (input) {
            input.addEventListener('input', function() {
                calculateAndDisplaySalary(form);
            });
        }
    });
    
    // Initial calculation
    calculateAndDisplaySalary(form);
}

function calculateAndDisplaySalary(form) {
    const basicSalary = parseFloat(form.querySelector('#basic_salary')?.value) || 0;
    const components = {};
    
    // Collect component data
    form.querySelectorAll('.component-row').forEach(row => {
        const code = row.dataset.componentCode;
        const amountInput = row.querySelector('.component-amount');
        const formula = row.dataset.formula;
        const type = row.dataset.componentType;
        const name = row.dataset.componentName;
        
        if (code) {
            components[code] = {
                name: name,
                type: type,
                formula: formula,
                amount: amountInput ? amountInput.value : 0
            };
        }
    });
    
    // Calculate salary
    const result = PayrollUtils.calculateSalary(basicSalary, components);
    
    // Update display
    updateSalaryDisplay(result);
}

function updateSalaryDisplay(result) {
    const totalEarningsElement = document.getElementById('total-earnings');
    const totalDeductionsElement = document.getElementById('total-deductions');
    const netSalaryElement = document.getElementById('net-salary');
    
    if (totalEarningsElement) {
        totalEarningsElement.textContent = PayrollUtils.formatCurrency(result.totalEarnings);
    }
    
    if (totalDeductionsElement) {
        totalDeductionsElement.textContent = PayrollUtils.formatCurrency(result.totalDeductions);
    }
    
    if (netSalaryElement) {
        netSalaryElement.textContent = PayrollUtils.formatCurrency(result.netSalary);
    }
}

function setupPayrollProcessing(form) {
    const processingModeSelect = form.querySelector('#processing_mode');
    const employeeSelection = document.getElementById('employee-selection');
    
    if (processingModeSelect) {
        processingModeSelect.addEventListener('change', function() {
            if (this.value === 'selected') {
                employeeSelection?.classList.remove('hidden');
            } else {
                employeeSelection?.classList.add('hidden');
            }
        });
    }
}

// Payslip utilities
window.PayslipUtils = {
    generatePayslip: function(employeeId, periodId, format = 'pdf') {
        const url = `/payroll/payslip/${employeeId}/${periodId}?format=${format}`;
        window.open(url, '_blank');
    },
    
    emailPayslip: function(employeeId, periodId) {
        if (confirm('Send payslip via email to the employee?')) {
            showLoading();
            
            fetch(`/payroll/email-payslip/${employeeId}/${periodId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showMessage('Payslip sent successfully', 'success');
                } else {
                    showMessage(data.message || 'Failed to send payslip', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showMessage('Failed to send payslip', 'error');
            });
        }
    },
    
    bulkGeneratePayslips: function(periodId, employeeIds, format = 'zip') {
        const data = {
            period_id: periodId,
            employee_ids: employeeIds,
            format: format
        };
        
        showLoading();
        
        fetch('/payroll/bulk-payslips', {
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
                showMessage('Bulk payslip generation initiated', 'success');
                // Handle file download
                return response.blob();
            } else {
                throw new Error('Failed to generate payslips');
            }
        })
        .then(blob => {
            // Create download link
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `payslips_${periodId}.zip`;
            a.click();
            window.URL.revokeObjectURL(url);
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showMessage('Failed to generate payslips', 'error');
        });
    }
};