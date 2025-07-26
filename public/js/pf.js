/**
 * PF Management JavaScript functionality
 */

// PF utilities
window.PFUtils = {
    // Calculate PF contributions
    calculatePFContribution: function(basicSalary, pfRate = 12, ceiling = 15000) {
        const pfBasic = Math.min(basicSalary, ceiling);
        return (pfBasic * pfRate) / 100;
    },
    
    // Calculate EPS contribution
    calculateEPSContribution: function(basicSalary, epsRate = 8.33, ceiling = 15000) {
        const epsBasic = Math.min(basicSalary, ceiling);
        return (epsBasic * epsRate) / 100;
    },
    
    // Calculate EDLI contribution
    calculateEDLIContribution: function(basicSalary, edliRate = 0.5, ceiling = 15000) {
        const edliBasic = Math.min(basicSalary, ceiling);
        return (edliBasic * edliRate) / 100;
    },
    
    // Validate UAN number
    validateUAN: function(uan) {
        const cleanUAN = uan.replace(/\s/g, '');
        return cleanUAN.length === 12 && /^\d{12}$/.test(cleanUAN);
    },
    
    // Format UAN number
    formatUAN: function(uan) {
        const cleanUAN = uan.replace(/\D/g, '');
        if (cleanUAN.length <= 12) {
            return cleanUAN.replace(/(\d{4})(\d{4})(\d{4})/, '$1 $2 $3').trim();
        }
        return cleanUAN.substring(0, 12).replace(/(\d{4})(\d{4})(\d{4})/, '$1 $2 $3');
    },
    
    // Generate ECR file content
    generateECRContent: function(contributions, establishmentCode, month, year) {
        let ecr = "#Header\n";
        ecr += `ECR~${establishmentCode}~${month.padStart(2, '0')}~${year}~${contributions.length}\n`;
        
        ecr += "#Member\n";
        contributions.forEach(contrib => {
            const uan = contrib.uan_number.padStart(12, '0');
            const name = contrib.employee_name.toUpperCase();
            const basic = Math.round(contrib.basic_salary);
            const empPF = Math.round(contrib.employee_pf);
            const empPF2 = Math.round(contrib.employer_pf);
            const eps = Math.round(contrib.eps_amount);
            
            ecr += `${uan}~${name}~${basic}~${empPF}~${empPF2}~${eps}~0~0\n`;
        });
        
        const totalEmpPF = contributions.reduce((sum, c) => sum + c.employee_pf, 0);
        const totalEmpPF2 = contributions.reduce((sum, c) => sum + c.employer_pf, 0);
        const totalEPS = contributions.reduce((sum, c) => sum + c.eps_amount, 0);
        
        ecr += "#Footer\n";
        ecr += `TOTAL~${contributions.length}~${Math.round(totalEmpPF)}~${Math.round(totalEmpPF2)}~${Math.round(totalEPS)}~0~0\n`;
        
        return ecr;
    }
};

// PF Calculator
window.PFCalculator = {
    calculate: function(basicSalary, options = {}) {
        const pfRate = options.pfRate || 12;
        const epsRate = options.epsRate || 8.33;
        const edliRate = options.edliRate || 0.5;
        const adminRate = options.adminRate || 0.65;
        const ceiling = options.ceiling || 15000;
        
        const pfBasic = Math.min(basicSalary, ceiling);
        
        const employeePF = (pfBasic * pfRate) / 100;
        const employerPF = (pfBasic * 3.67) / 100; // 12% - 8.33%
        const eps = (pfBasic * epsRate) / 100;
        const edli = (pfBasic * edliRate) / 100;
        const adminCharges = (pfBasic * adminRate) / 100;
        
        return {
            basic_salary: basicSalary,
            pf_basic: pfBasic,
            employee_pf: employeePF,
            employer_pf: employerPF,
            eps: eps,
            edli: edli,
            admin_charges: adminCharges,
            total_employer_liability: employerPF + eps + edli + adminCharges,
            total_pf: employeePF + employerPF
        };
    },
    
    displayCalculation: function(calculation, containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        container.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Employee Contribution</h4>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span>PF (12%):</span>
                            <span>₹${calculation.employee_pf.toFixed(2)}</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Employer Contribution</h4>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span>PF (3.67%):</span>
                            <span>₹${calculation.employer_pf.toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>EPS (8.33%):</span>
                            <span>₹${calculation.eps.toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>EDLI (0.5%):</span>
                            <span>₹${calculation.edli.toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Admin (0.65%):</span>
                            <span>₹${calculation.admin_charges.toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between font-semibold border-t pt-1">
                            <span>Total:</span>
                            <span>₹${calculation.total_employer_liability.toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
};

// ECR Generation utilities
window.ECRGenerator = {
    validateData: function(contributions) {
        const errors = [];
        
        contributions.forEach((contrib, index) => {
            if (!contrib.uan_number || !PFUtils.validateUAN(contrib.uan_number)) {
                errors.push(`Row ${index + 1}: Invalid UAN number`);
            }
            
            if (!contrib.employee_name || contrib.employee_name.trim().length === 0) {
                errors.push(`Row ${index + 1}: Employee name is required`);
            }
            
            if (!contrib.basic_salary || contrib.basic_salary <= 0) {
                errors.push(`Row ${index + 1}: Invalid basic salary`);
            }
        });
        
        return errors;
    },
    
    generateFile: function(contributions, establishmentCode, month, year) {
        const errors = this.validateData(contributions);
        
        if (errors.length > 0) {
            throw new Error('Validation errors:\n' + errors.join('\n'));
        }
        
        const ecrContent = PFUtils.generateECRContent(contributions, establishmentCode, month, year);
        
        // Create and download file
        const blob = new Blob([ecrContent], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `ECR_${month.padStart(2, '0')}_${year}.txt`;
        a.click();
        window.URL.revokeObjectURL(url);
        
        return true;
    }
};

// PF Reconciliation utilities
window.PFReconciliation = {
    compareRecords: function(systemData, epfoData) {
        const matched = [];
        const unmatched = [];
        const discrepancies = [];
        
        systemData.forEach(sysRecord => {
            const epfoRecord = epfoData.find(epfo => epfo.uan_number === sysRecord.uan_number);
            
            if (epfoRecord) {
                const amountDiff = Math.abs(sysRecord.employee_pf - epfoRecord.employee_pf);
                
                if (amountDiff < 0.01) {
                    matched.push(sysRecord);
                } else {
                    discrepancies.push({
                        ...sysRecord,
                        epfo_amount: epfoRecord.employee_pf,
                        difference: sysRecord.employee_pf - epfoRecord.employee_pf
                    });
                }
            } else {
                unmatched.push(sysRecord);
            }
        });
        
        return { matched, unmatched, discrepancies };
    },
    
    generateReconciliationReport: function(reconciliationResult) {
        let csvContent = "Type,Employee Name,UAN,System Amount,EPFO Amount,Difference\n";
        
        reconciliationResult.matched.forEach(record => {
            csvContent += `Matched,"${record.employee_name}","${record.uan_number}","${record.employee_pf}","${record.employee_pf}","0"\n`;
        });
        
        reconciliationResult.discrepancies.forEach(record => {
            csvContent += `Discrepancy,"${record.employee_name}","${record.uan_number}","${record.employee_pf}","${record.epfo_amount}","${record.difference}"\n`;
        });
        
        reconciliationResult.unmatched.forEach(record => {
            csvContent += `Unmatched,"${record.employee_name}","${record.uan_number}","${record.employee_pf}","0","${record.employee_pf}"\n`;
        });
        
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `pf_reconciliation_${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
    }
};

// Initialize PF functionality
document.addEventListener('DOMContentLoaded', function() {
    // PF Calculator setup
    const pfCalculatorForm = document.getElementById('pf-calculator-form');
    if (pfCalculatorForm) {
        setupPFCalculator(pfCalculatorForm);
    }
    
    // UAN formatting
    document.addEventListener('input', function(e) {
        if (e.target.matches('[data-format="uan"]')) {
            e.target.value = PFUtils.formatUAN(e.target.value);
        }
    });
    
    // Auto-populate ECR form based on period selection
    const periodSelect = document.getElementById('period_id');
    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            autoPopulateECRForm(this.value);
        });
    }
});

function setupPFCalculator(form) {
    const basicSalaryInput = form.querySelector('#basic_salary');
    const resultContainer = form.querySelector('#pf-calculation-result');
    
    if (basicSalaryInput && resultContainer) {
        basicSalaryInput.addEventListener('input', function() {
            const basicSalary = parseFloat(this.value) || 0;
            
            if (basicSalary > 0) {
                const calculation = PFCalculator.calculate(basicSalary);
                PFCalculator.displayCalculation(calculation, resultContainer.id);
            } else {
                resultContainer.innerHTML = '';
            }
        });
    }
}

function autoPopulateECRForm(periodId) {
    if (!periodId) return;
    
    // Fetch period details and auto-populate month/year
    fetch(`/api/period-details?id=${periodId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const periodDate = new Date(data.period.start_date);
                const month = periodDate.getMonth() + 1;
                const year = periodDate.getFullYear();
                
                const monthSelect = document.getElementById('return_month');
                const yearInput = document.getElementById('return_year');
                
                if (monthSelect) monthSelect.value = month;
                if (yearInput) yearInput.value = year;
            }
        })
        .catch(error => {
            console.error('Error fetching period details:', error);
        });
}

// Export functions for global access
window.PFManager = {
    calculateContribution: PFUtils.calculatePFContribution,
    validateUAN: PFUtils.validateUAN,
    formatUAN: PFUtils.formatUAN,
    generateECR: ECRGenerator.generateFile,
    reconcileData: PFReconciliation.compareRecords
};